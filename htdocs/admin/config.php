<?php
/*
	admin/config.php
	
	access: admin users only

	Configuration page for FlatTraffic reporting application.
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
		// nothing to do
		return 1;
	}


	function execute()
	{
		/*
			Define form structure
		*/
		
		$this->obj_form = New form_input;
		$this->obj_form->formname = "config";
		$this->obj_form->language = $_SESSION["user"]["lang"];

		$this->obj_form->action = "admin/config-process.php";
		$this->obj_form->method = "post";



		// data usage DB
		$structure = NULL;
		$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_TYPE";
		$structure["type"]					= "radio";
		
		// COLLECTOR START
		
		/*
			This must be the name of your collector-DB structure, if in doubt have a chat
			with one of the existing developers.
		*/
		$structure["values"]					= array("mysql_netflow_single");
		$structure["translations"]["mysql_netflow_single"]	= "MySQL netflow database, single \"traffic\" table";

		// COLLECTOR END

		$structure["options"]["autoselect"]			= "yes";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_HOST";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_NAME";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_USERNAME";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "SERVICE_TRAFFIC_DB_PASSWORD";
		$structure["type"]					= "input";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

	
		// performance options
		$structure = NULL;
		$structure["fieldname"]					= "PERF_CACHEMODE";
		$structure["type"]					= "radio";
		$structure["values"]					= array("max_speed", "min_ram");
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);


		// data tidy
		$structure = NULL;
		$structure["fieldname"]					= "TRUNCATE_DB_LOCAL";
		$structure["type"]					= "checkbox";
		$structure["options"]["label"]				= lang_trans("help_truncate_db_local");
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "TRUNCATE_DB_UNMATCHED";
		$structure["type"]					= "checkbox";
		$structure["options"]["label"]				= lang_trans("help_truncate_db_unmatched");
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);




		// upstream billing
		$structure = NULL;
		$structure["fieldname"]					= "UPSTREAM_BILLING";
		$structure["type"]					= "text";
		$structure["defaultvalue"]				= lang_trans("text_about_upstream_billing");
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "UPSTREAM_BILLING_MODE";
		$structure["type"]					= "radio";
		$structure["values"]					= array("period_monthly");
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "UPSTREAM_BILLING_REPEAT_DATE";
		$structure["type"]					= "input";
		$structure["options"]["width"]				= "100";
		$structure["options"]["maxlength"]			= "2";
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]					= "BYTECOUNT";
		$structure["type"]					= "radio";
		$structure["values"]					= array("1000", "1024");
		$structure["options"]["no_translate_fieldname"]		= "yes";
		$structure["defaultvalue"]				= "1024";
		$this->obj_form->add_input($structure);


		// statistics/reports
		$structure = NULL;
		$structure["fieldname"]				= "STATS_REPORT_OVERVIEW";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= lang_trans("help_stats_report_overview");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "STATS_REPORT_PERUSER";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= lang_trans("help_stats_report_peruser");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "STATS_REPORT_RAW";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= lang_trans("help_stats_report_raw");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "STATS_INCLUDE_UNMATCHED";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= lang_trans("help_stats_include_unmatched");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "STATS_INCLUDE_RDNS";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= lang_trans("help_stats_include_rdns");
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);




		// contributions
		$structure = NULL;
		$structure["fieldname"]				= "PHONE_HOME";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Phone home to the developers with application, OS and PHP version so we can better improve this software. (all information is anonymous and private)";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);


		// security options
		$structure = NULL;
		$structure["fieldname"]				= "SESSION_TIMEOUT";
		$structure["type"]				= "input";
		$structure["options"]["label"]			= " seconds idle before logging user out";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$structure["defaultvalue"]			= "7200";
		$this->obj_form->add_input($structure);

/*
		Blacklist Functions not currently provided

		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_ENABLE";
		$structure["type"]				= "checkbox";
		$structure["options"]["label"]			= "Enable to prevent brute-force login attempts";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);

		$structure = NULL;
		$structure["fieldname"]				= "BLACKLIST_LIMIT";
		$structure["type"]				= "input";
		$structure["options"]["no_translate_fieldname"]	= "yes";
		$this->obj_form->add_input($structure);
*/

		// submit section
		$structure = NULL;
		$structure["fieldname"]			= "submit";
		$structure["type"]			= "submit";
		$structure["defaultvalue"]		= "Save Changes";
		$this->obj_form->add_input($structure);
		
		
		// define subforms
		$this->obj_form->subforms["config_netflow"]		= array("SERVICE_TRAFFIC_DB_TYPE", "SERVICE_TRAFFIC_DB_HOST", "SERVICE_TRAFFIC_DB_NAME", "SERVICE_TRAFFIC_DB_USERNAME", "SERVICE_TRAFFIC_DB_PASSWORD");
		$this->obj_form->subforms["config_truncate"]		= array("TRUNCATE_DB_LOCAL", "TRUNCATE_DB_UNMATCHED");
//		$this->obj_form->subforms["config_performance"]		= array("PERF_CACHEMODE");
		$this->obj_form->subforms["config_upstream"]		= array("UPSTREAM_BILLING", "UPSTREAM_BILLING_MODE", "UPSTREAM_BILLING_REPEAT_DATE", "BYTECOUNT");
		$this->obj_form->subforms["config_stats"]		= array("STATS_REPORT_OVERVIEW", "STATS_REPORT_PERUSER", "STATS_REPORT_RAW", "STATS_INCLUDE_UNMATCHED", "STATS_INCLUDE_RDNS");
		$this->obj_form->subforms["config_contributions"]	= array("PHONE_HOME");
//		$this->obj_form->subforms["config_security"]		= array("SESSION_TIMEOUT", "BLACKLIST_ENABLE", "BLACKLIST_LIMIT");
		$this->obj_form->subforms["config_security"]		= array("SESSION_TIMEOUT");
		
		$this->obj_form->subforms["submit"]			= array("submit");

		if (error_check())
		{
			// load error datas
			$this->obj_form->load_data_error();
		}
		else
		{
			// fetch all the values from the database
			$sql_config_obj		= New sql_query;
			$sql_config_obj->string	= "SELECT name, value FROM config ORDER BY name";
			$sql_config_obj->execute();
			$sql_config_obj->fetch_array();

			foreach ($sql_config_obj->data as $data_config)
			{
				$this->obj_form->structure[ $data_config["name"] ]["defaultvalue"] = $data_config["value"];
			}

			unset($sql_config_obj);
		}
	}



	function render_html()
	{
		// Title + Summary
		print "<h3>CONFIGURATION APPLICATION</h3><br>";
		print "<p>This page allows you to adjust the application configuration including the details for the traffic database.</p>";
	
		// display the form
		$this->obj_form->render_form();
	}
	
}

?>
