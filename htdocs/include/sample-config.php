<?php
/*
	Sample Configuration File, this should be installed as config-settings.php

	This file should be read-only by the httpd user. All other users should be denied.

	Configuration for flattraffic web application - this file defines the main database
	configuration settings, all further configuration options can be adjusted after login
	in the web-based interface.
*/



/*
	Database Configuration
*/
$config["db_host"] = "localhost";			// hostname of the MySQL server
$config["db_name"] = "flattraffic";				// database name
$config["db_user"] = "root";				// MySQL user
$config["db_pass"] = "";				// MySQL password (if any)


/*
	Force debugging on for all users + scripts
	(note: debugging can be enabled on a per-user basis by an admin via the web interface)
*/
// $_SESSION["user"]["debug"] = "on";


?>
