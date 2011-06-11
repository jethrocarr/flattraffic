<?php
/*
	include/application/inc_traffic_ui.php

	Provides ui_traffic UI functions for reporting on
	traffic and providing other helpful resources.
	
*/

class traffic_ui
{
	var $date_start;
	var $date_end;


	/*
		__construct
	*/
	function __construct()
	{
		log_write("debug", "traffic_ui", "Executing __construct");

		$this->date_start	= $_SESSION["user"]["report"]["date_start"];
		$this->date_end		= $_SESSION["user"]["report"]["date_end"];
	}



	/*
		status_usage

		Generates a brief summary of usage information for the period.

		Returns
		0	No record information/fault
		#	Number of bytes transferred in period
	*/
	function status_usage()
	{
		log_write("debug", "traffic_ui", "Executing status_usage()");

		$obj_sql_cache		= New sql_query;
		$obj_sql_cache->string	= "SELECT SUM(bytes_received + bytes_sent) AS bytes FROM `cache_traffic` WHERE date >= '". $this->date_start ."' AND date <= '". $this->date_end ."'";
		$obj_sql_cache->execute();
		
		$obj_sql_cache->fetch_array();

		if (!empty($obj_sql_cache->data[0]["bytes"]))
		{
			$total = $obj_sql_cache->data[0]["bytes"];

			format_msgbox("info", "<p>In the period from ".time_format_humandate($obj_report->date_start) ." to ". time_format_humandate($obj_report->date_end) ." there has been combined usage of ". format_size_human($total) ." traffic.</p>");

			return $total;
		}

		return 0;

	} // end of  status_usage



	/*
		status_cache

		Returns the current status of the cache and rebuild link.

		Returns
		0		Cache Expired
		1		Cache Valid
	*/

	function status_cache()
	{
		log_write("debug", "traffic_ui", "Executing status_cache()");


		if ($GLOBALS["config"]["CACHE_TIME"] > (time() - $GLOBALS["config"]["CACHE_TIMEOUT"]))
		{
			format_msgbox("info", "<p>The traffic report cache is current and up-to-date - generated at ". time_format_humandate($GLOBALS["config"]["cache_time"], TRUE) ." (<a href=\"reports/update-cache.php\">re-generate now</a>)</p>");

			return 1;
		}
		else
		{
			format_msgbox("important", "<p>The traffic cache has now expired and will be regenerated shortly. <a href=\"reports/update-cache.php\">(generate now)</a></p>");

			return 0;
		}

	} // end of status_cache


} // end of traffic_ui class

?>
