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
	This application can require large amounts of RAM and time for cache generation,
	if you are getting out of memory errors, boost it up.

	Raw netflow records tend to get large quickly, so also be aware that your MySQL
	database will get hammered very quickly with big I/O hungry queries.
*/

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '240');



/*
	Force debugging on for all users + scripts
	(note: debugging can be enabled on a per-user basis by an admin via the web interface)
*/
// $_SESSION["user"]["debug"] = "on";


?>
