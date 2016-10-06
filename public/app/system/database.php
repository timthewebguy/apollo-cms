<?php 


//creates a connection to the system database
function connect_db() {
	return new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
}



//executes a query to the systems database, closes the connection, and returns the result
function db_query($sql) {
	$conn = connect_db();
	$result = $conn->query($sql);
	$response = ($result->num_rows > 0) ? $result : $conn->insert_id;
	$conn->close();
	return $response;
}



//creates the database and tables
function init_db() {

	//connect to / create the database
	$conn = connect_db();
	if($conn == false) {
		$conn = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD);
		$conn->query("CREATE DATABASE " . DATABASE_NAME);
		$conn->close();

	}

	$conn = connect_db();
	if($conn == false) {
		die("Something went wrong in connectong to / creating the database. Please check the config.php file.");
	} else {
		$conn->close();
		//create the tables

		if(db_query("SHOW TABLES LIKE " . TYPES_TABLE)->num_rows != 1) {
			db_query("CREATE TABLE `" . TYPES_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`type_name` varchar(255) DEFAULT NULL,`type_content` longtext,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
		}
		if(db_query("SHOW TABLES LIKE " . CONTENT_TABLE)->num_rows != 1) {
			db_query("CREATE TABLE `" . CONTENT_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`content_name` varchar(255) DEFAULT NULL,`content_index` int(11) DEFAULT NULL,`content_value` longtext,`content_page` varchar(255) DEFAULT NULL,`content_type` varchar(255) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
		}
		if(db_query("SHOW TABLES LIKE " . MEDIA_TABLE)->num_rows != 1) {
			db_query("CREATE TABLE `" . MEDIA_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`media_name` varchar(255) DEFAULT NULL,`media_ext` varchar(50) DEFAULT NULL,`media_abs_path` longtext,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
		}
	}
}

//returns an associative array of the mysql query result
function result_array($sql) {
	$result = db_query($sql);
	$array = Array();
	if($result->num_rows > 0) {
		while ($row = $result->fetch_assoc()) {
			array_push($array, $row);
		}
	}
	return $array;
}
