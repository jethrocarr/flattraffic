<?php
/*
	networks/delete.php

	access:
		admin

	Allows the selected network to be deleted.
*/

class page_output
{
	var $obj_network;
	var $obj_menu_nav;
	var $obj_form;


	function page_output()
	{

		// initate object
		$this->obj_network	= New network;

		// fetch variables
		$this->obj_network->id	= security_script_input('/^[0-9]*$/', $_GET["id"]);


		// define the navigiation menu
		$this->obj_menu_nav = New menu_nav;

		$this->obj_menu_nav->add_item("Network Details", "page=networks/view.php&id=". $this->obj_network->id ."");
		$this->obj_menu_nav->add_item("Delete Network", "page=networks/delete.php&id=". $this->obj_network->id ."", TRUE);
	}


	function check_permissions()
	{
		return user_permissions_get("admin");
	}


	function check_requirements()
	{
		// make sure the network is valid
		if (!$this->obj_network->verify_id())
		{
			log_write("error", "page_output", "The requested network (". $this->obj_network->id .") does not exist - possibly it has been deleted?");
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
		$this->obj_form->formname	= "network_delete";
		$this->obj_form->language	= $_SESSION["user"]["lang"];

		$this->obj_form->action		= "networks/delete-process.php";
		$this->obj_form->method		= "post";



		// general
		$structure = NULL;
		$structure["fieldname"] 	= "ipaddress";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);
							
		$structure = NULL;
		$structure["fieldname"]		= "description";
		$structure["type"]		= "text";
		$this->obj_form->add_input($structure);


		// hidden section
		$structure = NULL;
		$structure["fieldname"] 	= "id_network";
		$structure["type"]		= "hidden";
		$structure["defaultvalue"]	= $this->obj_network->id;
		$this->obj_form->add_input($structure);
			

		// confirm delete
		$structure = NULL;
		$structure["fieldname"] 	= "delete_confirm";
		$structure["type"]		= "checkbox";
		$structure["options"]["label"]	= "Yes, I wish to delete this network and realise that once deleted the data can not be recovered.";
		$this->obj_form->add_input($structure);

		// submit
		$structure = NULL;
		$structure["fieldname"] 	= "submit";
		$structure["type"]		= "submit";
		$structure["defaultvalue"]	= "delete";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["network_delete"]	= array("ipaddress", "description");
		$this->obj_form->subforms["hidden"]		= array("id_network");
		$this->obj_form->subforms["submit"]		= array("delete_confirm", "submit");


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
		print "<h3>DELETE DOMAIN</h3><br>";
		print "<p>This page allows you to delete an unwanted network - take care to make sure you are deleting the network that you intend to, this action is not reversable.</p>";

	
		// display the form
		$this->obj_form->render_form();
	}

}

?>
