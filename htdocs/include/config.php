<?php
/*
	MASTER CONFIGURATION FILE

	This file contains key application configuration options and values for
	developers rather than users/admins.

	DO NOT MAKE ANY CHANGES TO THIS FILE, INSTEAD PLEASE MAKE ANY ADJUSTMENTS
	TO "config-settings.php" TO ENSURE CORRECT APPLICATION OPERATION.

	If config-settings.php does not exist, then you need to copy sample_config.php
	into it's place.
*/



$GLOBALS["config"] = array();



/*
	Define Application Name & Versions
*/

// define the application details
$GLOBALS["config"]["app_name"]			= "Amberphplib";
$GLOBALS["config"]["app_version"]		= "1";

// define the schema version required
$GLOBALS["config"]["schema_version"]		= "20091026";



/*
	Apply required PHP settings
*/
ini_set('memory_limit', '32M');			// note that Amberphplib doesn't need much RAM apart from when
						// doing source diffs or graph generation.



/*
	Inherit User Configuration
*/
include("config-settings.php");



/*
	Session Management
*/

// Initate session variables
if ($_SERVER['SERVER_NAME'])
{
	// proper session variables
	session_name("amberphplib");
	session_start();
}
else
{
	// trick to make logging and error system work correctly for scripts.
	$GLOBALS["_SESSION"]	= array();
	$_SESSION["mode"]	= "cli";
}



/*
	Connect to Databases
*/
include("database.php");

?>
