<?php
/*
	reports/filter_period_range.php

	Access: reports

	Change the selected month that is being viewed.
*/

// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('reports'))
{
	/*
		Fetch Form Data
	*/

	$data["period_start"]		= @security_form_input_predefined("date_string", "period_range", 1, "");
	$data["page"]			= @security_form_input_predefined("any", "page", 1, "");


	/*
		Apply Date Range
	*/

	$_SESSION["user"]["report"]["date_start"]	= $data["period_start"];
	$_SESSION["user"]["report"]["date_end"]		= sql_get_singlevalue("SELECT DATE_ADD('". $data["period_start"] ."', INTERVAL 1 MONTH ) as value");
	

	/*
		Check/Regenerate Cache
	*/

	if (!error_check())
	{
		$obj_report = New traffic_reports;

		if ($obj_report->check_cache())
		{
			log_write("debug", "process", "Cache is current");
		}
		else
		{
			log_write("debug", "process", "Rebuilding expired cache for selected time period");

			$obj_report->build_cache();
		}

	} // end if error check
	


	/*
		Reload Form Page
	*/

	header("Location: ../index.php?page=". $data["page"] ."");
	exit(0);

}
else
{
	error_render_noperms();
}

// return to previous page
header("Location: ../index.php");
exit(0);

?>
