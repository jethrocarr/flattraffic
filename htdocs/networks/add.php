<?php
/*
	networks/add.php

	access:
		admin

	Allows a new network to be added to the system.
*/

class page_output
{
	var $obj_form;


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
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "network_add";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "networks/edit-process.php";
		$this->obj_form->method		= "post";

		$structure = NULL;
		$structure["fieldname"] 	= "ipaddress";
		$structure["type"]		= "input";
		$structure["options"]["help"]	= "eg: 192.168.0.0/24 or 192.168.0.20/32";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "description";
		$structure["type"]		= "input";
		$this->obj_form->add_input($structure);



		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["network_details"]	= array("ipaddress", "description");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>ADD NEW DOMAIN</h3><br>";
		print "<p>Use this page to add a new network to the system.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
