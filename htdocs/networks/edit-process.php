<?php
/*
	networks/edit-process.php

	access:
		admn

	Updates or adds a network range.
*/


// includes
require("../include/config.php");
require("../include/amberphplib/main.php");
require("../include/application/main.php");


if (user_permissions_get('admin'))
{
	/*
		Form Input
	*/

	$obj_network			= New network;
	$obj_network->id		= security_form_input_predefined("int", "id_network", 0, "");


	// are we editing an existing network or adding a new one?
	if ($obj_network->id)
	{
		if (!$obj_network->verify_id())
		{
			log_write("error", "process", "The network you have attempted to edit - ". $obj_name_server->id ." - does not exist in this system.");
		}
		else
		{
			// load existing data
			$obj_network->load_data();
		}
	}


	// load data
	$obj_network->data["ipaddress"]		= security_form_input_predefined("iprange", "ipaddress", 1, "");
	$obj_network->data["description"]	= security_form_input_predefined("iprange", "description", 1, "");



	/*
		Verify Data
	*/

	if ($obj_network->verify_ipaddress())
	{
		log_write("error", "process", "The requested network/IP range already exists.");

		error_flag_field("ipaddress");
	}


	/*
		Process Data
	*/

	if (error_check())
	{
		if ($obj_network->id)
		{
			$_SESSION["error"]["form"]["network_edit"]	= "failed";
			header("Location: ../index.php?page=networks/view.php&id=". $obj_network->id ."");
		}
		else
		{
			$_SESSION["error"]["form"]["network_add"]	= "failed";
			header("Location: ../index.php?page=networks/add.php");
		}

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();


		/*
			Update network
		*/

		$obj_network->action_update();


		/*
			Return
		*/

		header("Location: ../index.php?page=networks/view.php&id=". $obj_network->id ."");
		exit(0);


	} // if valid data input
	
	
} // end of "is user logged in?"
else
{
	// user does not have permissions to access this page.
	error_render_noperms();
	header("Location: ../index.php?page=message.php");
	exit(0);
}


?>
