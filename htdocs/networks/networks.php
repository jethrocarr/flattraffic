<?php
/*
	networks/networks.php

*/

class page_output
{
	var $obj_table;


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
		/*
			Table of Network Ranges
		*/

		// establish a new table object
		$this->obj_table = New table;

		$this->obj_table->language	= $_SESSION["user"]["lang"];
		$this->obj_table->tablename	= "name_servers";

		// define all the columns and structure
		$this->obj_table->add_column("standard", "ipaddress", "");
		$this->obj_table->add_column("standard", "description", "");

		// defaults
		$this->obj_table->columns		= array("ipaddress", "description");
		$this->obj_table->columns_order		= array("ipaddress");

		// assemble query
		$this->obj_table->sql_obj->prepare_sql_settable("networks");
		$this->obj_table->sql_obj->prepare_sql_addfield("id", "");

		// load data
		$this->obj_table->generate_sql();
		$this->obj_table->load_data_sql();


		return 1;
	}

	function render_html()
	{
		print "<h3>HOST AND NETWORKS CONFIGURATION</h3>";
		print "<p>The following list defines all internal networks - you need to configure all internal ranges here, so that FlatTraffic knows what is external countable traffic, vs internal only traffic</p>";

		if (!$this->obj_table->data_num_rows)
		{
			format_msgbox("important", "<p>There are currently no monitored networks configured - you must add some networks before you can use this application for any report generation.</p>");
		}
		else
		{
			// details link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_details", "networks/view.php", $structure);

			// delete link
			$structure = NULL;
			$structure["id"]["column"]	= "id";
			$this->obj_table->add_link("tbl_lnk_delete", "networks/delete.php", $structure);


			// display the table
			$this->obj_table->render_table_html();
		}


		// add link
		print "<p><a class=\"button\" href=\"index.php?page=networks/add.php\">Add Host or Network Range</a></p>";


	}
}

?>	
