<?php
/*
	reports/reports-daily.php

	Provides reports of traffic usage on a daily basis.
*/
class page_output
{
	var $obj_table;

	
	function check_permissions()
	{
		return user_permissions_get("reports");
	}

	function check_requirements()
	{
		if (empty($GLOBALS["config"]["SERVICE_TRAFFIC_DB_TYPE"]))
		{
			log_write("error", "page", "No netflow database has been configured yet - configure your traffic database before requesting reports");
			return 0;
		}

		if (empty($GLOBALS["config"]["STATS_REPORT_RAW"]))
		{
			log_write("error", "page", "You are not able to view this report, the STATS_REPORT_RAW option is disabled.");
			return 0;
		}

		return 1;
	}

	function execute()
	{
		/*
			Establish Table Object
		*/


		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "report_networks";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "date", "cache_traffic.date");
		$this->obj_table->add_column("standard", "bytes_received", "SUM(cache_traffic.bytes_received)");
		$this->obj_table->add_column("standard", "bytes_sent", "SUM(cache_traffic.bytes_sent)");

		// defaults
		$this->obj_table->columns		= array("date", "bytes_received", "bytes_sent");
		$this->obj_table->columns_order		= array("date");

		// totals
		$this->obj_table->total_columns		= array("bytes_received", "bytes_sent");
		$this->obj_table->total_rows		= array("bytes_received", "bytes_sent");

		// assemble query
		$this->obj_table->sql_obj->prepare_sql_settable("cache_traffic");
		$this->obj_table->sql_obj->prepare_sql_addgroupby("cache_traffic.date");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();



		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS :: DAILY</h3>";

		print "<p>This report displays traffic usage per network.</p>";


		$this->obj_table->render_table_html();
	
	}
}

?>	
