<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

	//Database Stuff
	//Check Database Server Credentials
	$conn = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD);
	if($conn->connect_error) {
		//we cannot connect to the database server
		die('Problem in connecting to the database server. Please check the credentials in config.php');
	}

	//Create the Database
	if((@new Mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME)) !== true) {
		//we have no database
		$conn = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD);

		if($conn->query("CREATE DATABASE `" . DATABASE_NAME . '`') !== true) {
			die("Error creating database: " . $conn->error);
		}
		$conn->close();
	}

	//Install the database tables
	$conn = new Mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);

	$conn->query("DROP TABLE IF EXISTS `ap_compound_type_fields`");
	$conn->query("CREATE TABLE `ap_compound_type_fields` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `type` varchar(255) DEFAULT NULL, `field_name` varchar(255) DEFAULT NULL, `field_type` varchar(255) DEFAULT NULL, `field_description` longtext, `field_min` int(11) DEFAULT NULL, `field_max` int(11) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_content`");
	$conn->query("CREATE TABLE `ap_content` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `guid` varchar(255) DEFAULT NULL, `content_group` varchar(255) DEFAULT NULL, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `data` varchar(255) DEFAULT NULL, `description` longtext, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_data`");
	$conn->query("CREATE TABLE `ap_data` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `guid` varchar(255) DEFAULT NULL, `type` varchar(255) DEFAULT NULL, `value` varchar(255) DEFAULT NULL, `min` int(11) DEFAULT NULL, `max` int(11) DEFAULT NULL, `data_order` int(11) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_groups`");
	$conn->query("CREATE TABLE `ap_groups` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `guid_prefix` varchar(4) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_media_library`");
	$conn->query("CREATE TABLE `ap_media_library` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `GUID` varchar(255) DEFAULT NULL, `path` longtext, `extension` varchar(10) DEFAULT NULL, `name` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_type_bool`");
	$conn->query("CREATE TABLE `ap_type_bool` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `GUID` varchar(255) DEFAULT '', `value` tinyint(1) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_type_media`");
	$conn->query("CREATE TABLE `ap_type_media` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `GUID` varchar(255) DEFAULT NULL, `value` longtext, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_type_text`");
	$conn->query("CREATE TABLE `ap_type_text` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `GUID` varchar(255) DEFAULT NULL, `value` longtext, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_type_wysiwyg`");
	$conn->query("CREATE TABLE `ap_type_wysiwyg` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `GUID` varchar(255) DEFAULT NULL, `value` longtext, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("DROP TABLE IF EXISTS `ap_types`");
	$conn->query("CREATE TABLE `ap_types` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `type` varchar(255) DEFAULT NULL, `guid_prefix` varchar(4) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

	$conn->query("INSERT INTO `ap_types` (`id`, `name`, `slug`, `type`, `guid_prefix`) VALUES (1,'Text','text','base','TEXT'), (2,'Media','media','base','MED'), (3,'WYSIWYG','wysiwyg','base','WYS'), (4,'Bool','bool','base','BOOL');");

	$conn->close();

	//Load the types and groups
	TypeController::LoadTypes();
	GroupController::LoadGroups();


	//Update installed.php to reflect the installation status
	$installed = fopen('installed.php', 'w');
	$content = "<?php\n\n//This script is automatically generated and keeps track\n//of the installation status of this system.\n//DO NOT MODIFY\n\nDEFINE('INSTALLED', TRUE);";
	fwrite($installed, $content);
	fclose($installed);
