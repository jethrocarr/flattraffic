<?php
/*
	networks/view.php

	access:
		admin

	Displays the details and permits adjustment of the selected network range.
*/

class page_output
{
	var $obj_network;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_network		= New network;

		// fetch variables
		$this->obj_network->id		= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Network Details", "page=networks/view.php&id=". $this->obj_network->id ."", TRUE);
		$this->obj_menu_nav->add_item("Delete Network", "page=networks/delete.php&id=". $this->obj_network->id ."");
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
		// make sure the server is valid
		if (!$this->obj_network->verify_id())
		{
			log_write("error", "page_output", "The requested network (". $this->obj_network->id .") does not exist - possibly the network has been deleted?");
			return 0;
		}

		return 1;
	}



	function execute()
	{
		/*
			Define form structure
		*/
		$this->obj_form			= New form_input;
		$this->obj_form->formname	= "network_edit";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "networks/edit-process.php";
		$this->obj_form->method		= "post";

		// general
		$structure = NULL;
		$structure["fieldname"] 	= "ipaddress";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"] 	= "description";
		$structure["type"]		= "input";
		$structure["options"]["req"]	= "yes";
		$this->obj_form->add_input($structure);



		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_network";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_network->id;
		$this->obj_form->add_input($structure);
			
		// submit section
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["network_details"]	= array("ipaddress", "description");
		$this->obj_form->subforms["hidden"]		= array("id_network");
		$this->obj_form->subforms["submit"]		= array("submit");


		// import data
		if (error_check())
		{
			$this->obj_form->load_data_error();
		}
		else
		{
			if ($this->obj_network->load_data())
			{
				$this->obj_form->structure["ipaddress"]["defaultvalue"]		= $this->obj_network->data["ipaddress"];
				$this->obj_form->structure["description"]["defaultvalue"]	= $this->obj_network->data["description"];
			}
		}
	}


	function render_html()
	{
		// title + summary
		print "<h3>NETWORK DETAILS</h3><br>";
		print "<p>This page displays the network details for the selected range.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
