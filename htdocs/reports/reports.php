<?php
/*
	reports/reports.php

	Reports Summary Page
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

		return 1;
	}

	function execute()
	{
		// nothing todo
		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS</h3>";

		print "<p>This page provides traffic reports for the specified data range.</p>";
		
		$obj_report = New traffic_ui;

		$obj_report->status_usage();
		print "<br>";
		$obj_report->status_cache();
	}
}

?>	
