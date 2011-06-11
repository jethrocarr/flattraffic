<?php
/*
	inc_network.php

	Provides high-level functions for managing network entries.
*/



/*
	CLASS: NETWORK

	Network range handling logic
*/

class network
{
	var $id;		// ID of the network to manipulate
	var $data;

	var $sql_obj;



	/*
		__construct()
	*/

	function __construct()
	{
		log_debug("network", "Executing __construct()");

		// init SQL object
		$this->sql_obj	= New sql_query;
	}



	/*
		verify_id

		Checks that the provided ID is a valid network name.

		Results
		0	Failure to find the ID
		1	Success - network exists
	*/

	function verify_id()
	{
		log_debug("network", "Executing verify_id()");

		if ($this->id)
		{
			$this->sql_obj->string	= "SELECT id FROM `networks` WHERE id='". $this->id ."' LIMIT 1";
			$this->sql_obj->execute();

			if ($this->sql_obj->num_rows())
			{
				return 1;
			}
		}

		return 0;

	} // end of verify_id



	/*
		verify_ipaddress

		Verify that the network ipaddress is not already in use. NOTE: this function does not perform
		within-subnet checks, to see if one subnet is already included inside another subnet.

		Results
		0	IP Unused
		1	IP Configured
	*/

	function verify_ipaddress()
	{
		log_debug("network", "Executing verify_ipaddress()");
		
		$this->sql_obj->string		= "SELECT id FROM `networks` WHERE ipaddress='". $this->data["ipaddress"] ."' ";

		if ($this->id)
			$this->sql_obj->string	.= " AND id!='". $this->id ."'";

		$this->sql_obj->string		.= " LIMIT 1";
		$this->sql_obj->execute();

		if ($this->sql_obj->num_rows())
		{
			return 1;
		}
		
		return 0;

	} // end of verify_ipaddress



	/*
		load_data

		Load the data for the network into $this->data

		Returns
		0	failure
		1	success
	*/
	function load_data()
	{
		log_debug("network", "Executing load_data()");

		$this->data = array();

		$this->sql_obj->string	= "SELECT * FROM `networks` WHERE id='". $this->id ."' LIMIT 1";
		$this->sql_obj->execute();

		if ($this->sql_obj->num_rows())
		{
			$this->sql_obj->fetch_array();

			// load base network data
			$this->data = $this->sql_obj->data[0];

			return 1;
		}

		// failure
		return 0;

	} // end of load_data



	/*
		action_create

		Create a new network based on the data in $this->data

		Results
		0	Failure
		#	Success - return ID
	*/
	function action_create()
	{
		log_debug("network", "Executing action_create()");

		// create a new network
		$this->sql_obj->string	= "INSERT INTO `networks` (ipaddress) VALUES ('". $this->data["ipaddress"] ."')";
		$this->sql_obj->execute();

		$this->id = $this->sql_obj->fetch_insert_id();


		return $this->id;

	} // end of action_create




	/*
		action_update

		Update a network's details based on the data in $this->data. If no ID is provided,
		it will first call the action_create function.

		Returns
		0	failure
		#	success - returns the ID
	*/
	function action_update()
	{
		log_debug("network", "Executing action_update()");


		/*
			Start Transaction
		*/
		$this->sql_obj->trans_begin();


		/*
			If no ID supplied, create a new network first
		*/
		if (!$this->id)
		{
			$mode = "create";

			if (!$this->action_create())
			{
				return 0;
			}
		}
		else
		{
			$mode = "update";
		}




		/*
			Update network details
		*/

		$this->sql_obj->string	= "UPDATE `networks` SET "
						."ipaddress='". $this->data["ipaddress"] ."', "
						."description='". $this->data["description"] ."' "
						."WHERE id='". $this->id ."' LIMIT 1";
		$this->sql_obj->execute();



		/*
			Commit
		*/

		if (error_check())
		{
			$this->sql_obj->trans_rollback();

			log_write("error", "network", "An error occured when updating the network.");

			return 0;
		}
		else
		{
			$this->sql_obj->trans_commit();

			if ($mode == "update")
			{
				log_write("notification", "network", "Network range has been successfully updated.");
			}
			else
			{
				log_write("notification", "network", "Network successfully created.");
			}
			
			return $this->id;
		}

	} // end of action_update


	/*
		action_delete

		Deletes a network

		Results
		0	failure
		1	success
	*/
	function action_delete()
	{
		log_debug("network", "Executing action_delete()");

		/*
			Start Transaction
		*/

		$this->sql_obj->trans_begin();


		/*
			Delete network
		*/
			
		$this->sql_obj->string	= "DELETE FROM `networks` WHERE id='". $this->id ."' LIMIT 1";
		$this->sql_obj->execute();


		/*
			Commit
		*/
		
		if (error_check())
		{
			$this->sql_obj->trans_rollback();

			log_write("error", "network", "An error occured whilst trying to delete the selected network.");

			return 0;
		}
		else
		{
			$this->sql_obj->trans_commit();

			log_write("notification", "network", "The network has been successfully deleted.");

			return 1;
		}
	}


} // end of class:network


?>
