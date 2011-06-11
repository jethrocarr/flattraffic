<?php
/*
	networks/delete-process.php

	access:
		admin

	Deletes an unwanted network.
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

	$obj_network		= New network;
	$obj_network->id	= security_form_input_predefined("int", "id_network", 1, "");


	// for error return if needed
	@security_form_input_predefined("any", "ipaddress", 0, "");
	@security_form_input_predefined("any", "description", 0, "");

	// confirm deletion
	@security_form_input_predefined("any", "delete_confirm", 1, "You must confirm the deletion");




	/*
		Verify Data
	*/


	// verify the selected network exists
	if (!$obj_network->verify_id())
	{
		log_write("error", "process", "The network you have attempted to delete - ". $obj_network->id ." - does not exist in this system.");
	}




	/*
		Process Data
	*/

	if (error_check())
	{
		$_SESSION["error"]["form"]["network_delete"]	= "failed";
		header("Location: ../index.php?page=networks/delete.php&id=". $obj_network->id ."");

		exit(0);
	}
	else
	{
		// clear error data
		error_clear();



		/*
			Delete network
		*/

		$obj_network->load_data();

		$obj_network->action_delete();



		/*
			Return
		*/

		header("Location: ../index.php?page=networks/networks.php");
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
