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

			format_msgbox("info", "<p>In the period from ".time_format_humandate($this->date_start) ." to ". time_format_humandate($this->date_end) ." there has been combined usage of ". format_size_human($total) ." traffic.</p>");

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


	/*
		filter_period_range

		Provides a filter form for selecting the period to display data for.
	*/

	function filter_period_range()
	{
		log_write("debug", "traffic_ui", "Executing filter_period_range()");


		/*
			Fetch date ranges
		*/

		$obj_traffic 	= New traffic_reports;
		$periods 	= $obj_traffic->fetch_periods();


		/*
			Define Form Structure
		*/


		// form header
		$form		= New form_input;

		$form->action	= "reports/filter_period_range.php";
		$form->method	= "post";


		// period selection
		$structure = NULL;
		$structure["fieldname"]		= "period_range";
		$structure["type"]		= "dropdown";
		$structure["defaultvalue"]	= $this->date_start;

		foreach ($periods as $period)
		{
			$structure["values"][]				= $period["start"];
			$structure["translations"][ $period["start"] ]	= time_format_humandate($period["start"]) ." to ". time_format_humandate($period["end"]);
		}

		$form->add_input($structure);



		// form submit components
		$structure = NULL;
		$structure["fieldname"]		= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "ui_button_change_period";
		$form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]		= "page";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $_GET["page"];
		$form->add_input($structure);




		/*
			Render Form
		*/

		print "<table width=\"100%\" class=\"table_highlight_info\">";
		print "<tr>";
			print "<td>";
			print "<p><b>Selected Period: ". time_format_humandate($this->date_start) ." to ". time_format_humandate($this->date_end) ."</b></p>";

			print "<form method=\"". $form->method ."\" action=\"". $form->action ."\">";

			$form->render_field("period_range");

			print "<br><br>";
			$form->render_field("page");
			$form->render_field("submit");

			print "</form>";

			print "</td>";
		print "</tr>";
		print "</table>";
		print "<br>";


	} // end of filter_period_range

} // end of traffic_ui class

?>
