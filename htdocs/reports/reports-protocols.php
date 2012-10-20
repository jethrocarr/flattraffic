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
		$this->obj_table->tablename	= "report_protocols";

		// define all the columns and structure
		$this->obj_table->add_column("string", "port", "cache_protocols.port");
		$this->obj_table->add_column("bytes", "bytes", "cache_protocols.bytes");

		// defaults
		$this->obj_table->columns		= array("port", "bytes");
		$this->obj_table->columns_order		= array("port");

		// totals
		$this->obj_table->total_columns		= array("bytes");

		// assemble query
		$this->obj_table->sql_obj->prepare_sql_settable("cache_protocols");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();

		// run query
		$this->obj_table->render_table_prepare();


		/*
			Re-labelling
		*/
		for ($i=0; $i <= $this->obj_table->data_num_rows; $i++)
		{
			if ($this->obj_table->data[$i]["port"] == "0")
			{
				$this->obj_table->data_render[$i]["port"] = "ICMP";
			}
		}


		/*
			UI controls
		*/

		$this->obj_ui	= New traffic_ui;


		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS :: PROTOCOLS</h3>";

		print "<p>This report displays traffic usage by protocol for the selected billing period.</p>";

		$this->obj_ui->filter_period_range();

		$this->obj_table->render_table_html();
	
	}
}

?>	
