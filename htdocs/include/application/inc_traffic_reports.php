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

	var $summary;			// summary data
	var $hosts;			// array of internal IPs/hosts


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

		}

		log_write("debug", "traffic_reports", "Report date range is ". $this->date_start ." to ". $this->date_end ."");

	}



	/*
		summary

		Generates a brief summary of usage information for the period.
	*/
	function summary()
	{
		log_write("debug", "traffic_reports", "Executing summary()");

		$this->obj_db_traffic->string	= "SELECT SUM(octets) as value FROM traffic WHERE received >= '". $this->date_start ."' AND received <= '". $this->date_end ."'";
		$this->obj_db_traffic->execute();
		$this->obj_db_traffic->fetch_array();

		$this->summary["total"]		= $this->obj_db_traffic->data[0]["value"];


	} // end of summary()



	/*
		fetch_networks

		We need to know which networks are internal and which are external, so we can report on
		traffic usage for all internal hosts, not the other way around.
	*/
	function fetch_networks()
	{
		log_write("debug", "traffic_reports", "Executing fetch_networks()");

		$sql_obj		= New sql_query;
		$sql_obj->string	= "SELECT ipaddress FROM networks";
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
		fetch_raw

		Fetches raw stats into $this->data["raw"]
	*/
	function fetch_raw()
	{
		log_write("debug", "traffic_reports", "Executing fetch_raw()");

		// fetch network info if required
		if (empty($this->networks))
		{
			$this->fetch_networks();
		}


		// query traffic database
		$this->obj_db_traffic->string	= "SELECT src_addr, dst_addr, SUM(octets) as bytes FROM traffic WHERE received >= '". $this->date_start ."' AND received <= '". $this->date_end ."' GROUP BY src_addr, dst_addr ";
		$this->obj_db_traffic->execute();
		

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
						$local_src = 1;
					}

					if ($long_dst >= $this->networks[ $network ]["network"] && $long_dst <= $this->networks[ $network ]["broadcast"])
					{
						// IP is in a local rage
						$local_dst = 1;
					}

				} // end of foreach

				if ($local_src && !$local_dst)
				{
					// source IP is a local range, destination is an external range
					$this->data["raw"][ $data_row["src_addr"] ] += $data_row["bytes"];
				}
				elseif ($local_dst && !$local_src)
				{
					// destination IP is a local range, source is a external range
					$this->data["raw"][ $data_row["dst_addr"] ] += $data_row["bytes"];
				}
				elseif ($local_dst && $local_src)
				{
					// both IPs are local, thus we should exclude from report - don't want to report on local traffic!
					//print "excluded: dst ". $data_row["dst_addr"] ." to src ". $data_row["src_addr"] ."<br>";
					
					if ($GLOBALS["config"]["STATS_INCLUDE_UNMATCHED"])
					{
						$this->data["raw"]["unmatched"] += $data_row["bytes"];
					}
				}
				else
				{
					// neither IP is local
					//print "excluded: dst ". $data_row["dst_addr"] ." to src ". $data_row["src_addr"] ."<br>";


					if ($GLOBALS["config"]["STATS_INCLUDE_UNMATCHED"])
					{
						$this->data["raw"]["unmatched"] += $data_row["bytes"];
					}
				}

			} // end of loop through traffic rows


		// sort by IP
		uksort( $this->data["raw"], 'strnatcmp');


		} // end if traffic rows

	} // end of fetch_raw



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
}

?>
