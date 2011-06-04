<?php
/*
	admin/admin.php

	Summary/link page for administrator tools.
*/
class page_output
{
	function check_permissions()
	{
		return user_permissions_get("admin");
	}

	function check_requirements()
	{
		// nothing todo
		return 1;
	}

	function execute()
	{
		// nothing todo
		return 1;
	}

	function render_html()
	{
		print "<h3>ADMINISTRATOR TOOLS</h3>";

		print "<p>Select from the following administration tools:</p>";

		// configuration
		print "<br>";
		format_linkbox("default", "index.php?page=admin/config.php", "<p><b>CONFIGURATION</b></p>
			<p>Adjust application configuration.</p>");

		// user management
		print "<br>";
		format_linkbox("default", "index.php?page=user/users.php", "<p><b>USER MANAGEMENT</b></p>
			<p>Add, adjust or delete users, or configure user permissions, as well as staff access rights.</p>");

/*
		// blacklist defence
		print "<br>";
		format_linkbox("default", "index.php?page=admin/blacklist.php", "<p><b>BRUTE-FORCE BLACKLISTING</b></p>
			<p>If you are running the Amberdms Billing System on a publically accessible server, it is highly
			recommended that you configure blacklisting to prevent brute-force password guessing by attackers. To
			enable/disable blacklisting or to view/unlock backlisted addresses, use the <a href=\"admin/blacklist.php\">blacklisting</a> page.</p>");
*/

	}
}

?>	
