<?php
/*
	reports/reports-raw.php

	Provides raw reports, traffic count for the range per IP address.
*/
class page_output
{
	var $obj_report;
	
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
		$this->obj_table->tablename	= "report_raw";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "ipaddress", "");
		$this->obj_table->add_column("standard", "hostname", "");
		$this->obj_table->add_column("standard", "bytes", "");
		$this->obj_table->add_column("standard", "bytes_human", "");

		// defaults
		if ($GLOBALS["config"]["STATS_INCLUDE_RDNS"])
		{
			$this->obj_table->columns		= array("ipaddress", "hostname", "bytes", "bytes_human");
		}
		else
		{
			$this->obj_table->columns		= array("ipaddress", "bytes", "bytes_human");
		}

		$this->obj_table->total_columns		= array("bytes", "bytes_human");



		/*
			Fetch Report Data
		*/
		$this->obj_report	= New traffic_reports;
		
		$this->obj_report->fetch_raw();



		/*
			Load Report data into table
		*/

		$i=0;
		foreach (array_keys($this->obj_report->data["raw"]) as $ipaddress)
		{
			$this->obj_table->data[$i]["ipaddress"]	= $ipaddress;
			$this->obj_table->data[$i]["bytes"]	= $this->obj_report->data["raw"][ $ipaddress ];

			if ($GLOBALS["config"]["STATS_INCLUDE_RDNS"])
			{
				$this->obj_table->data[$i]["hostname"]	= @gethostbyaddr($ipaddress);
			}

			$i++;
		}

		$this->obj_table->data_num_rows = $i;
			

		// prepare the data early, this gives us totals and then ability to update formatting
		$this->obj_table->render_table_prepare();


		// format bytes
		for ($i=0; $i < $this->obj_table->data_num_rows; $i++)
		{
			$this->obj_table->data_render[$i]["bytes_human"] = format_size_human($this->obj_table->data_render[$i]["bytes"]);
		}
		
		$this->obj_table->data_render["total"]["bytes_human"] = format_size_human($this->obj_table->data_render["total"]["bytes"]);

	
		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS :: RAW</h3>";

		print "<p>The raw report provides full stats for the selected date range for all traffic flows.</p>";


		$this->obj_table->render_table_html();

	
	}
}

?>	
