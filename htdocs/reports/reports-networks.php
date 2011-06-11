<?php
/*
	reports/reports-networks.php

	Provides reports of traffic usage per configured network.
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
		$this->obj_table->add_column("standard", "network_ipaddress", "networks.ipaddress");
		$this->obj_table->add_column("standard", "network_description", "networks.description");
		$this->obj_table->add_column("bytes", "bytes_received", "SUM(cache_traffic.bytes_received)");
		$this->obj_table->add_column("bytes", "bytes_sent", "SUM(cache_traffic.bytes_sent)");

		// defaults
		$this->obj_table->columns		= array("network_ipaddress", "network_description", "bytes_received", "bytes_sent");
		$this->obj_table->columns_order		= array("network_ipaddress");

		// totals
		$this->obj_table->total_columns		= array("bytes_received", "bytes_sent");
		$this->obj_table->total_rows		= array("bytes_received", "bytes_sent");

		// assemble query
		$this->obj_table->sql_obj->prepare_sql_settable("networks");
		$this->obj_table->sql_obj->prepare_sql_addjoin("LEFT JOIN cache_traffic ON cache_traffic.id_network = networks.id");
		$this->obj_table->sql_obj->prepare_sql_addfield("networks.id", "");
		$this->obj_table->sql_obj->prepare_sql_addgroupby("networks.id");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();



		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS :: NETWORKS</h3>";

		print "<p>This report displays traffic usage per network.</p>";


		$this->obj_table->render_table_html();
	
	}
}

?>	
