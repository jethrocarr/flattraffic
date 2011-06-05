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
		/*
			Establish Report
		*/

		$this->obj_report	= New traffic_reports;
		



		return 1;
	}

	function render_html()
	{
		print "<h3>REPORTS</h3>";

		print "<p>This page provides traffic reports for the specified data range.</p>";
		
	}
}

?>	
