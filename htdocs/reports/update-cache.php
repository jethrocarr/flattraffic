<?php
/*
	reports/update-cache.php

	Access: reports

	Updates the cache for the traffic data
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('reports'))
{
	/*
		Checks
	*/

	if (empty($GLOBALS["config"]["SERVICE_TRAFFIC_DB_TYPE"]))
	{
		log_write("error", "page", "No netflow database has been configured yet - configure your traffic database before requesting reports");
	}

	if (empty($GLOBALS["config"]["STATS_REPORT_RAW"]))
	{
		log_write("error", "page", "You are not able to view this report, the STATS_REPORT_RAW option is disabled.");
	}


	if (!error_check())
	{
		$obj_report = New traffic_reports;

		$obj_report->build_cache();

	} // end if error check
	
}
else
{
	error_render_noperms();
}

// return to previous page
header("Location: ../index.php");
exit(0);

?>
