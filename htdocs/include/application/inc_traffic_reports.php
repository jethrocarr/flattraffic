<?php
/*
	include/application/inc_traffic_reporting.php

	Objects and functions for querying the configured MySQL database
	and fetching netflow records in a useful format.
*/



/*
	CLASS: TRAFFIC_REPORTS

	Main traffic reporting class, includes varity of functions for traffic
	reporting.
*/
class traffic_reports
{
	var $obj_db_traffic;		// traffic DB connection

	var $date_start;		// start of billing period
	var $date_end;			// end of billing period


	/*
		__construct

		Establish connection to the traffic DB and generate date range if unset
	*/
	function __construct()
	{
		log_write("debug", "traffic_reports", "Executing __construct");


		/*
			Establish database connection
		*/
		if (empty($GLOBALS["config"]["SERVICE_TRAFFIC_DB_NAME"]))
		{
			log_write("warning", "traffic_reports", "Traffic database has not been configured");
		}
		else
		{
			if ($GLOBALS["config"]["SERVICE_TRAFFIC_DB_TYPE"] == "mysql_netflow_single")
			{
				$this->obj_db_traffic = New sql_query;

				if (!$this->obj_db_traffic->session_init("mysql", $GLOBALS["config"]["SERVICE_TRAFFIC_DB_HOST"], $GLOBALS["config"]["SERVICE_TRAFFIC_DB_NAME"], $GLOBALS["config"]["SERVICE_TRAFFIC_DB_USERNAME"], $GLOBALS["config"]["SERVICE_TRAFFIC_DB_PASSWORD"]))
				{
					log_write("error", "traffic_reports", "An unexpected error occured whilst establishing a connection to the traffic database");
				}
			}
			else
			{
				log_write("error", "traffic_reports", "Unknown database type configured, most likely new feature coding error");
			}
		}


		/*
			Date Range
		*/
		
		if (!empty($_GET["date_start"]) && !empty($_GET["date_end"]))
		{
			$_SESSION["user"]["report"]["date_start"]	= security_script_input_predefined("date", $_GET["date_start"]);
			$_SESSION["user"]["report"]["date_end"]		= security_script_input_predefined("date", $_GET["date_end"]);
		}


		if (!empty($_GET["date_start_dd"]) && !empty($_GET["date_end_dd"]))
		{
			$_SESSION["user"]["report"]["date_start"]	= security_script_input_predefined("date", $_GET["date_start_yyyy"] ."-". $_GET["date_start_mm"] ."-". $_GET["date_start_dd"]);
			$_SESSION["user"]["report"]["date_end"]		= security_script_input_predefined("date", $_GET["date_end_yyyy"] ."-". $_GET["date_end_mm"] ."-". $_GET["date_end_dd"]);
		}

		// fetch from session if set
		if (!empty($_SESSION["user"]["report"]["date_start"]) && !empty($_SESSION["user"]["report"]["date_start"]))
		{
			$this->date_start	= $_SESSION["user"]["report"]["date_start"];
			$this->date_end		= $_SESSION["user"]["report"]["date_end"];
		}
		else
		{
			// generate
			if (date("d") > $GLOBALS["config"]["UPSTREAM_BILLING_REPEAT_DATE"])
			{
				$this->date_start	= date("Y-m") ."-". $GLOBALS["config"]["UPSTREAM_BILLING_REPEAT_DATE"];
				$this->date_end		= sql_get_singlevalue("SELECT DATE_ADD('". $this->date_start ."', INTERVAL 1 MONTH ) as value");
			}
			else
			{
				$this->date_start	= date("Y-m") ."-". $GLOBALS["config"]["UPSTREAM_BILLING_REPEAT_DATE"];
				$this->date_start	= sql_get_singlevalue("SELECT DATE_SUB('". $this->date_start ."', INTERVAL 1 MONTH ) as value");
				
				$this->date_end		=  date("Y-m") ."-". $GLOBALS["config"]["UPSTREAM_BILLING_REPEAT_DATE"];
				$this->date_end		= sql_get_singlevalue("SELECT DATE_SUB('". $this->date_end ."', INTERVAL 1 DAY ) as value");
			}

			// save session values
			$_SESSION["user"]["report"]["date_start"]	= $this->date_start;
			$_SESSION["user"]["report"]["date_end"]		= $this->date_end;

		}

		log_write("debug", "traffic_reports", "Report date range is ". $this->date_start ." to ". $this->date_end ."");

	}




	/*
		fetch_networks

		We need to know which networks are internal and which are external, so we can report on
		traffic usage for all internal hosts, not the other way around.
	*/
	function fetch_networks()
	{
		log_write("debug", "traffic_reports", "Executing fetch_networks()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT id, ipaddress FROM networks";
		$sql_obj->execute();

		if ($sql_obj->num_rows())
		{
			$sql_obj->fetch_array();

			foreach ($sql_obj->data as $data_row)
			{
				// generate IPs for the provided ranges
				if (preg_match("/^(?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3}\/[0-9]*$/", $data_row["ipaddress"]))
				{
					/*
						IPv4

						Here we split the IPv4 address and work out the decimal verion of the network and broadcast addresses
						so we can run checks to see if an IP belongs within those ranges.
					*/


					$address = explode('/', $data_row["ipaddress"]);		// eg: 192.168.0.0/24

					// calculate subnet mask
					$bin = NULL;

					for ($i = 1; $i <= 32; $i++)
					{
						$bin .= $address[1] >= $i ? '1' : '0';
					}

					// calculate key values
					$long_netmask	= bindec($bin);					// eg: 255.255.255.0
					$long_network	= ip2long($address[0]);				// eg: 192.168.0.0
					$long_broadcast	= ($long_network | ~($long_netmask));		// eg: 192.168.0.255

					// add to array structure
					$this->networks[ $data_row["ipaddress"] ]["network"]	= $long_network;
					$this->networks[ $data_row["ipaddress"] ]["broadcast"]	= $long_broadcast;
					$this->networks[ $data_row["ipaddress"] ]["id"]		= $data_row["id"];
				}
			}
		}
		else
		{
			log_write("warning", "traffic_reports", "There are no monitored networks in the configuration");
		}

		unset($sql_obj);
	}



	/*
		build_cache

		Reads in data from the raw netflow tables and generates cache inside memory tables in MySQL. All reports
		then query against these memory tables for performance reasons.

		The memory cache tables store a daily total per IP address matching the configured network ranges, for traffic
		sent/recieved.

		It does not store the destination IP information, as this allows a huge reduction in stored information -if such
		low level records are required, best to query the direct netflow database.

		Returns
		0		Failure
		1		Success
	*/
	function build_cache()
	{
		log_write("debug", "traffic_reports", "Executing build_cache()");


		/*
			Establish Memory Table Connection
		*/

		log_write("debug", "traffic_reports", "Establishing and clearing cache_traffic MEMORY table");

		$obj_sql_cache			= New sql_query;

		$obj_sql_cache->string		= "TRUNCATE TABLE `cache_traffic`";
		
		if (!$obj_sql_cache->execute())
		{
			log_write("error", "traffic_reports", "Unable to clear memory cache tables (TRAFFIC)");
			return 0;
		}


		$obj_sql_cache->string		= "TRUNCATE TABLE `cache_rdns`";
		
		if (!$obj_sql_cache->execute())
		{
			log_write("error", "traffic_reports", "Unable to clear memory cache tables (RDNS)");
			return 0;
		}



		/*
			Fetch required information
		*/

		// fetch network info if required
		if (empty($this->networks))
		{
			$this->fetch_networks();
		}



		/*
			Query Traffic Database

			Because of the size of netflow databases, we fetch the records on a per-day basis within the configured range, to prevent running out
			of PHP memory when 10 million rows are returned.
		*/

		log_write("debug", "traffic_reports", "Fetching raw records for each date within range");

		// generate date range
		$tmp_date		= $this->date_start;
		$date_range		= array();

		$date_range[]		= $this->date_start;

		while ($tmp_date != $this->date_end)
		{
			$tmp_date	= explode("-", $tmp_date);
			$tmp_date 	= date("Y-m-d", mktime(0,0,0,$tmp_date[1], ($tmp_date[2] +1), $tmp_date[0]));

			$date_range[]	= $tmp_date;
		}

		for ($i=0; $i < count($date_range); $i++)
		{
			// strip "-" charactor
			$date_range[$i] = str_replace("-", "", $date_range[$i]);
		}


		// store reverse DNS
		$addresses_resolved = array();


		foreach ($date_range as $date)
		{
			// query traffic database
			$this->obj_db_traffic->string	= "SELECT src_addr, dst_addr, SUM(octets) as bytes FROM traffic WHERE received >= '". $date ."' AND received <= DATE_ADD('". $date ."', INTERVAL 1 DAY) GROUP BY src_addr, dst_addr ";
			$this->obj_db_traffic->execute();


			$data_raw = array();
			

			if ($this->obj_db_traffic->num_rows())
			{
				$this->obj_db_traffic->fetch_array();

				foreach ($this->obj_db_traffic->data as $data_row)
				{
					/*
						Here we run through all the known networks to determine whether the current row
						is for a network we wish to report on.

						We check both the source and destination IP addresses against the network ranges
						and then handle appropiately.
					*/


					// check if the src or dst address belongs to one of the range
					$long_src	= ip2long($data_row["src_addr"]);
					$long_dst	= ip2long($data_row["dst_addr"]);

					// track local status
					$local_src	= 0;
					$local_dst	= 0;

					foreach (array_keys($this->networks) as $network)
					{
						if ($long_src >= $this->networks[ $network ]["network"] && $long_src <= $this->networks[ $network ]["broadcast"])
						{
							// IP is in a local rage
							$local_src = $this->networks[ $network ]["id"];
						}

						if ($long_dst >= $this->networks[ $network ]["network"] && $long_dst <= $this->networks[ $network ]["broadcast"])
						{
							// IP is in a local rage
							$local_dst = $this->networks[ $network ]["id"];
						}

					} // end of foreach

					if ($local_src && !$local_dst)
					{
						// source IP is a local range, destination is an external range
						$data_raw[ $local_src ][ $data_row["src_addr"] ]["sent"] += $data_row["bytes"];
					}
					elseif ($local_dst && !$local_src)
					{
						// destination IP is a local range, source is a external range
						$data_raw[ $local_dst ][ $data_row["dst_addr"] ]["received"] += $data_row["bytes"];
					}
					elseif ($local_dst && $local_src)
					{
						// both IPs are local, thus we should exclude from report - don't want to report on local traffic!
						//print "excluded: dst ". $data_row["dst_addr"] ." to src ". $data_row["src_addr"] ."<br>";
						
						if ($GLOBALS["config"]["STATS_INCLUDE_UNMATCHED"])
						{
							$data_raw[0]["local"] += $data_row["bytes"];
						}
					}
					else
					{
						// neither IP is local
						//print "excluded: dst ". $data_row["dst_addr"] ." to src ". $data_row["src_addr"] ."<br>";


						if ($GLOBALS["config"]["STATS_INCLUDE_UNMATCHED"])
						{
							$data_raw[0]["unmatched"] += $data_row["bytes"];
						}
					}

				} // end of loop through traffic rows

			} // end if traffic rows


			/*
				Process data and insert into caches
			*/

			foreach (array_keys($data_raw) as $id_network)
			{
				foreach (array_keys($data_raw[ $id_network ]) as $ipaddress)
				{
					/*
						Insert traffic into cache
					*/

					$obj_sql_cache->string	= "INSERT INTO `cache_traffic` (id_network, date, ipaddress, bytes_received, bytes_sent) VALUES ('". $id_network ."', '". $date ."', '". $ipaddress ."', '". $data_raw[ $id_network ][$ipaddress]["received"] ."', '". $data_raw[ $id_network ][$ipaddress]["sent"] ."')";
					$obj_sql_cache->execute();

	
					/*
						Lookup reverse DNS if configured and store in a cache - keep DNS load to
						a minimum
					*/

					if ($GLOBALS["config"]["STATS_INCLUDE_RDNS"] && !in_array($ipaddress, $addresses_resolved))
					{
						$hostname		= @gethostbyaddr($ipaddress);

						$obj_sql_cache->string	= "INSERT INTO `cache_rdns` (ipaddress, reverse) VALUES ('". $ipaddress ."', '". $hostname ."')";
						$obj_sql_cache->execute();

						$addresses_resolved[]	= $ipaddress;
					}
				}
			}


			// clear data
			unset($data_raw);
			unset($this->obj_db_traffic->data);
			unset($this->obj_db_traffic->data_num_rows);

		} // end of daily loop


		// update cache build time
		$sql_obj		= New sql_query;
		$sql_obj->string	= "UPDATE config SET value='". time() ."' WHERE name='CACHE_TIME'";
		$sql_obj->execute();

	} // end of build_cache_traffic


	/*
		__destruct

		Destory old database session
	*/
	function __destruct()
	{
		log_write("debug", "traffic_reports", "Executing __destruct");

		unset($this->data);
		unset($this->summary);
		unset($this->obj_db_traffic);
	}

} // end of class traffic_reports




?>
