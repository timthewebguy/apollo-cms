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
	if(!$conn) {
		$conn = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD);
		$conn->query("CREATE DATABASE " . DATABASE_NAME);
		$conn->close();
	}

	$conn = connect_db();
	if(!$conn) {
		die("Something went wrong in connecting to / creating the database. Please check the config.php file.");
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




//syncs the database with the YAML files
function sync_db() {
	delete_type_tables();
	update_type_tables();
	add_type_tables();

	$pages = get_pages();
	foreach($pages as $page => $page_content) {
		foreach($page_content as $content => $content_data) {
			analyze_content($page, $content, $content_data);
		}
	}
}



//generates the contents string of a custom type
function generate_type_content_string($contents) {
	$content_string = '';
	foreach ($contents as $content => $content_data) {
		$min = isset($content_data['min-items']) ? $content_data['min-items'] : 1;
		$max = isset($content_data['max-items']) ? $content_data['max-items'] : $min;
		$content_string .= $content . '-' . $min . '-' . $max . ',';
	}
	return $content_string;
}



//delete type tables no longer found in the yaml files
function delete_type_tables() {

	$types = get_types();
	$db_types = result_array("SELECT * FROM " . TYPES_TABLE);

	//Delete old types
	foreach($db_types as $type => $type_data) {
		if(!get_type($type_data['type_name'])) {
			
			db_query("DROP TABLE " . DATABASE_TABLE_PREFIX . "type_{$type_data['type_name']}");
			db_query("DELETE FROM " . TYPES_TABLE . " WHERE type_name='{$type_data['type_name']}'");
		}
	}
}



//update the type tables if they've changed their yaml configuration
function update_type_tables() {
	$types = get_types();
	$db_types = result_array("SELECT * FROM " . TYPES_TABLE);

	//Update current types
	foreach($db_types as $db_type => $db_type_data) {
		$type = get_type($db_type_data['type_name']);

		//Build the contents string for the current YAML type
		$current_type_content = generate_type_content_string($type['contents']);

		//compare the contents strings
		if($current_type_content != $db_type_data['type_content']) {

			//build the array of columns
			$new_type_data = explode(',', $db_type_data['type_content']);
			$old_type_data = explode(',', $current_type_content);

			for($i = 0; $i < max(count($new_type_data), count($old_type_data)); $i++) {
				if($new_type_data[$i]) {
					$new_type_data[$i] = explode('-', $new_type_data[$i])[0];
				}
				if($old_type_data[$i]) {
					$old_type_data[$i] = explode('-', $old_type_data[$i])[0];
				}
			}
			//remove the last blank element
			unset($new_type_data[count($new_type_data) - 1]);
			unset($old_type_data[count($old_type_data) - 1]);

			//Remove columns that no longer exist
			$columns_to_remove = array_diff($new_type_data, $old_type_data);

			foreach($columns_to_remove as $column_to_remove) {
				db_query("ALTER TABLE " . DATABASE_TABLE_PREFIX . "type_{$db_type_data['type_name']} DROP COLUMN {$column_to_remove}");
			}

			//Add new columns
			$columns_to_add = array_diff($old_type_data, $new_type_data);

			foreach($columns_to_add as $column_to_add) {
				db_query("ALTER TABLE " . DATABASE_TABLE_PREFIX . "type_{$db_type_data['type_name']} ADD {$column_to_add} LONGTEXT");
				add_new_custom_type_content($db_type_data['type_name'], $column_to_add);
			}

			//Build the contents string for the current YAML type
			$new_type_content = generate_type_content_string($type['contents']);

			db_query("UPDATE " . TYPES_TABLE . " SET type_content='{$new_type_content}' WHERE type_name='{$db_type_data['type_name']}'");		

		}
	}
}




//when a new column is added to a custom type concerning another custom type, create new entries to the latter custom type
function add_new_custom_type_content($typeName, $columnName) {

	$type = get_type($typeName);
	$column_data = $type['contents'][$columnName];

	if(get_type($column_data['type']) != null) {

		$db_data = result_array("SELECT * FROM " . DATABASE_TABLE_PREFIX . "type_{$typeName}");

		foreach($db_data as $db_row => $db_row_data) {
			$ids = '';
			$min = isset($column_data['min-items']) ? $column_data['min-items'] : 1;
			for($i = 0; $i < $min; $i++) {
				$ids .= add_custom_type_content($column_data['type'], get_type($column_data['type']));
				if($i < $min-1) {
					$ids .=',';
				}
			}
			db_query("UPDATE " . DATABASE_TABLE_PREFIX . "type_{$typeName} SET {$columnName}='{$ids}' WHERE id={$db_row_data['id']}");
		}
	} 
}

function remove_old_custom_type_content($type, $ids) {
	var_dump($type);
	var_dump($ids);
}




//add new type tables to the database
function add_type_tables() {
	$types = get_types();

	//Add new types
	foreach($types as $type => $type_data) {
		if(db_query("SELECT * FROM " . TYPES_TABLE . " WHERE type_name='{$type}'")->num_rows == 0) {
			add_type($type, $type_data);
		}
	}
}



//adds a type to the database
function add_type($type, $data) {
	$sql = "CREATE TABLE " . DATABASE_TABLE_PREFIX . "type_{$type} (id INT NOT NULL AUTO_INCREMENT";
	$type_content = '';
	foreach ($data['contents'] as $column => $column_data) {
		$sql .= ", {$column} LONGTEXT";
		$min = isset($column_data['min-items']) ? $column_data['min-items'] : 1;
		$max = isset($column_data['max-items']) ? $column_data['max-items'] : $min;
		$type_content .= $column . '-' . $min . '-' . $max . ',';
	}
	$sql .= ", PRIMARY KEY (ID))";
	db_query($sql);
	db_query("INSERT INTO " . TYPES_TABLE . " (type_name, type_content) VALUES ('{$type}','{$type_content}')");
}



//analyes the content found in yaml page files
function analyze_content($page, $name, $data) {
	$sql = "SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}'";

	$db_content = result_array($sql);

	$min = isset($data['min-items']) ? $data['min-items'] : 1;
	$max = isset($data['max-items']) ? $data['max-items'] : $min;

	$num_rows = count($db_content);

	if($num_rows > 0) {
		normalize_indexes($page, $name, $data);
	}

	$add_content_counter = $min - $num_rows;
	while ($add_content_counter > 0) {
		add_content($page, $name, $data);
		$add_content_counter--;
	}

	if($max != 'unlimited') {
		$remove_content_counter = $num_rows - $max;
		while($remove_content_counter > 0) {
			remove_content($page, $name, $data);
			$remove_content_counter--;
		}
	}
}



//makes sure the indexes of the contents start at 0 and count up to the number of entries
function normalize_indexes($page, $name, $data) {
	$db_content = result_array("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index ASC");
	if(count($db_content) > 0)  {
		for($i = 0; $i < count($db_content); $i++) {
			db_query("UPDATE " . CONTENT_TABLE . " SET content_index='{$i}' WHERE id={$db_content[$i]['id']}");
		}
	}
}



//returns the next available content index
function get_content_next_index($page, $name) {
	$index = 0;
	$content = result_array("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index DESC");
	if(count($content) > 0) {
		$index = $content[0]['content_index'] + 1;
	}
	return $index;
}



//adds content to the database
function add_content($page, $name, $data) {
	$index = get_content_next_index($page, $name);
	$type_name = $data['type'];
	$type_data = get_type($type_name);

	$value = '';

	if($type_data != null) {
		$value = add_custom_type_content($type_name, $type_data);
	}
	db_query("INSERT INTO " . CONTENT_TABLE . " VALUES (NULL, '{$name}', '{$index}', '{$value}', '{$page}', '{$type_name}')");
}



//recursively adds custom type content to the respective tables
function add_custom_type_content($type_name, $type_data) {
	$id = '';
	$sql = "INSERT INTO " . DATABASE_TABLE_PREFIX . "type_{$type_name} VALUES (NULL";

	foreach($type_data['contents'] as $type_content => $type_content_data) {
		$content_type_name = $type_content_data['type'];
		$content_type_data = get_type($content_type_name);

		if($content_type_data != null) {
			$ids = '';

			$min = isset($type_content_data['min-items']) ? $type_content_data['min-items'] : 1;
			for($i = 0; $i < $min; $i++) {
				$ids .= add_custom_type_content($content_type_name, $content_type_data);
				if($i < $min-1) {
					$ids .=',';
				}
			}
			$sql .= ", '{$ids}'";
		} else {
			$sql .= ", ''";
		}

	}
	$sql .=")";

	$id = db_query($sql);

	return $id;
}



//returns the highest current content index
function get_content_highest_index($page, $name) {
	$index = 0;
	$content = result_array("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index DESC");
	if(count($content) > 0) {
		$index = $content[0]['content_index'];
	}
	return $index;
}



//removes content from the database
function remove_content($page, $name, $data) {
	$index = get_content_highest_index($page, $name);
	$type_name = $data['type'];
	$type_data = get_type($type_name);
	$id = db_query("SELECT content_value FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' AND content_index='{$index}'")->fetch_assoc()['content_value'];
	

	if($type_data != null) {
		remove_custom_type_content($id, $type_name, $type_data);
	}

	db_query("DELETE FROM " . CONTENT_TABLE . " WHERE id='{$id}'");
}



//recursively removes custom type content from the respecitve tables
function remove_custom_type_content($content_id, $type_name, $type_data) {
	foreach($type_data['contents'] as $content_name => $content_data) {
		$content_type = get_type($content_data['type']);
		if($content_type != null) {
			$content_value_ids = explode(',', db_query("SELECT {$content_name} FROM yc_type_{$type_name} WHERE id={$content_id}")->fetch_assoc()[$content_name]);
			foreach($content_value_ids as $content_value_id) {
				remove_custom_type_content($content_value_id, $content_data['type'], $content_type);
			}
		}
	}
	db_query("DELETE FROM " . DATABASE_TABLE_PREFIX . "type_{$type_name} WHERE id={$content_id}");
}

