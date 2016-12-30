<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}


class DB {

	//creates a connection to the system database
	public function Connect() {
		return new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
	}



	//executes a query to the systems database, closes the connection, and returns the result
	public function Query($sql) {
		$conn = DB::Connect();
		$result = $conn->query($sql);
		$response = ($result->num_rows > 0) ? $result : $conn->insert_id;
		$conn->close();
		return $response;
	}


	//returns an associative array of the mysql query result
	public function ResultArray($sql) {
		$result = DB::Query($sql);
		$array = Array();
		if($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				array_push($array, $row);
			}
		}
		return $array;
	}



	//creates the database and tables
	public function Init() {

		$conn = DB::Connect();
		if(!$conn) {
			//if not, direct user to the config.php file
			show_404("<p><strong>Error Connecting To Database.</strong> Please Check Database Credentials in <code>congig.php</code></p>");
		} else {

			//we're ready to ensure the tables are in there

			//Groups Table
			DB::QUERY("CREATE TABLE `" . GROUPS_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `guid-prefix` int(4) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			//Content Table
			DB::QUERY("CREATE TABLE `" . CONTENT_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `GUID` varchar(255) DEFAULT NULL, `group` varchar(255) DEFAULT NULL, `description` longtext, `min` varchar(255) DEFAULT NULL, `max` varchar(255) DEFAULT NULL, `type` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			//Relations Table
			DB::QUERY("CREATE TABLE `" . RELATIONS_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `src-guid` varchar(255) DEFAULT NULL, `src-index` int(11) DEFAULT NULL, `target-guid` varchar(255) DEFAULT NULL, `target-type` varchar(255) DEFAULT NULL, `target-index` int(11) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			//Types Table
			DB::QUERY("CREATE TABLE `" . TYPES_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `name` varchar(255) DEFAULT NULL, `slug` varchar(255) DEFAULT NULL, `type` varchar(8) DEFAULT NULL COMMENT 'Compound or Base', `guid-prefix` int(4) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1");

			//Media Table
			DB::QUERY("CREATE TABLE `" . MEDIA_TABLE . "` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, `guid` varchar(255) DEFAULT NULL, `name` varchar(255) DEFAULT NULL, `path` longtext, `extension` varchar(255) DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

			//Base Types
			DB::QUERY("INSERT INTO `" . TYPES_TABLE . "` (`id`, `name`, `slug`, `type`, `guid-prefix`) VALUES (1,'text','text','base',1000), (2,'media','media','base',1100), (3,'WYSIWYG','wysiwyg','base',1110), (4,'bool','bool','base',1111)");
		}

	}


	public function CascadeDelete($obj) {

	}



	public function GUID() {
		if (function_exists('com_create_guid') === true) {
	    return trim(com_create_guid(), '{}');
	  }
	  return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

/*


	//syncs the database with the YAML files
	public function Sync() {
		DB::DeleteTypeTables();
		DB::UpdateTypeTables();
		DB::AddTypeTables();

		$pages = PageController::GetPages();
		foreach($pages as $page) {
			foreach($page->contents as $content => $content_data) {
				DB::AnalyzeContent($page->name, $content, $content_data);
			}
		}
	}



	//generates the contents string of a custom type
	public function GenerateTypeContentString($contents) {
		$content_string = '';
		foreach ($contents as $content => $content_data) {
			$min = isset($content_data['min-items']) ? $content_data['min-items'] : 1;
			$max = isset($content_data['max-items']) ? $content_data['max-items'] : $min;
			$content_string .= $content . '-' . $min . '-' . $max . ',';
		}
		return $content_string;
	}



	//delete type tables no longer found in the yaml files
	public function DeleteTypeTables() {

		$db_types = DB::ResultArray("SELECT * FROM " . TYPES_TABLE);

		//Delete old types
		foreach($db_types as $type => $type_data) {
			if(!TypeController::GetType($type_data['type_name'])) {
				DB::Query("DROP TABLE " . DATABASE_TABLE_PREFIX . "type_{$type_data['type_name']}");
				DB::Query("DELETE FROM " . TYPES_TABLE . " WHERE type_name='{$type_data['type_name']}'");
			}
		}
	}



	//update the type tables if they've changed their yaml configuration
	public function UpdateTypeTables() {
		$db_types = DB::ResultArray("SELECT * FROM " . TYPES_TABLE);

		//Update current types
		foreach($db_types as $db_type => $db_type_data) {
			$type = TypeController::GetType($db_type_data['type_name']);

			//Build the contents string for the current YAML type
			$current_type_content = DB::GenerateTypeContentString($type->structure);

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
					DB::Query("ALTER TABLE " . DATABASE_TABLE_PREFIX . "type_{$db_type_data['type_name']} DROP COLUMN {$column_to_remove}");
				}

				//Add new columns
				$columns_to_add = array_diff($old_type_data, $new_type_data);

				foreach($columns_to_add as $column_to_add) {
					DB::Query("ALTER TABLE " . DATABASE_TABLE_PREFIX . "type_{$db_type_data['type_name']} ADD {$column_to_add} LONGTEXT");
					DB::AddNewCustomTypeContent($db_type_data['type_name'], $column_to_add);
				}

				//Build the contents string for the current YAML type
				$new_type_content = DB::GenerateTypeContentString($type->structure);

				DB::Query("UPDATE " . TYPES_TABLE . " SET type_content='{$new_type_content}' WHERE type_name='{$db_type_data['type_name']}'");

			}
		}
	}




	//when a new column is added to a custom type concerning another custom type, create new entries to the latter custom type
	public function AddNewCustomTypeContent($typeName, $columnName) {

		$type = TypeController::GetType($typeName);
		$column_data = $type->structure[$columnName];

		if(TypeController::GetType($column_data['type']) != null) {

			$db_data = DB::ResultArray("SELECT * FROM " . DATABASE_TABLE_PREFIX . "type_{$typeName}");

			foreach($db_data as $db_row => $db_row_data) {
				$ids = '';
				$min = isset($column_data['min-items']) ? $column_data['min-items'] : 1;
				for($i = 0; $i < $min; $i++) {
					$ids .= DB::AddCustomTypeContent($column_data['type'], TypeController::GetType($column_data['type']));
					if($i < $min-1) {
						$ids .=',';
					}
				}
				DB::Query("UPDATE " . DATABASE_TABLE_PREFIX . "type_{$typeName} SET {$columnName}='{$ids}' WHERE id={$db_row_data['id']}");
			}
		}
	}




	//add new type tables to the database
	public function AddTypeTables() {
		$types = TypeController::GetTypes();

		//Add new types
		foreach($types as $type) {
			if(DB::Query("SELECT * FROM " . TYPES_TABLE . " WHERE type_name='{$type->name}'")->num_rows == 0) {
				DB::AddType($type);
			}
		}
	}



	//adds a type to the database
	public function AddType($type) {
		$sql = "CREATE TABLE " . DATABASE_TABLE_PREFIX . "type_{$type->name} (id INT NOT NULL AUTO_INCREMENT, guid LONGTEXT";
		$type_content = '';
		foreach ($type->structure as $column => $column_data) {
			$sql .= ", {$column} LONGTEXT";
			$min = isset($column_data['min-items']) ? $column_data['min-items'] : 1;
			$max = isset($column_data['max-items']) ? $column_data['max-items'] : $min;
			$type_content .= $column . '-' . $min . '-' . $max . ',';
		}
		$sql .= ", PRIMARY KEY (ID))";
		DB::Query($sql);
		$type_guid_prefix = random_int(1000, 9999);
		DB::Query("INSERT INTO " . TYPES_TABLE . " (type_name, type_content, type_guid_prefix) VALUES ('{$type->name}','{$type_content}','{$type_guid_prefix}')");
	}



	//gets the GUID prefix of a custom type table
	public function GetCustomTypeGUIDPrefix($type_name) {
		$response = DB::Query("SELECT type_guid_prefix FROM " . TYPES_TABLE . " WHERE type_name='{$type_name}'");
		return $response->fetch_assoc()['type_guid_prefix'];
	}



	//gets the custom type name by a GUID prefix
	public function GetCustomTypeNameByPrefix($prefix) {
		$response = DB::Query("SELECT type_name FROM " . TYPES_TABLE . " WHERE type_guid_prefix='{$prefix}'");
		return $response->fetch_assoc()['type_name'];
	}

	//gets the id by a GUID
	public function GetIDByGUID($guid) {
		$prefix = explode('--', $guid)[0];
		$table = $prefix == '0000' ? CONTENT_TABLE : DATABASE_TABLE_PREFIX . 'type_' . DB::GetCustomTypeNameByPrefix($prefix);

		$response = DB::Query("SELECT id FROM {$table} WHERE guid='{$guid}'");
		return $response->fetch_assoc()['id'];
	}



	//analyes the content found in yaml page files
	public function AnalyzeContent($page, $name, $data) {
		$sql = "SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}'";

		$db_content = DB::ResultArray($sql);

		$min = isset($data['min-items']) ? $data['min-items'] : 1;
		$max = isset($data['max-items']) ? $data['max-items'] : $min;

		$num_rows = count($db_content);

		if($num_rows > 0) {
			DB::NormalizeIndexes($page, $name, $data);
		}

		$add_content_counter = $min - $num_rows;
		$guid = '0000--' . DB::GUID(); //defined here to ensure it is global for this content
		while ($add_content_counter > 0) {
			DB::AddContent($page, $name, $data, $guid);
			$add_content_counter--;
		}

		if($max != 'unlimited') {
			$remove_content_counter = $num_rows - $max;
			//echo $remove_content_counter;
			while($remove_content_counter > 0) {
				DB::RemoveContent($page, $name, $data);
				$remove_content_counter--;
			}
		}
	}



	//makes sure the indexes of the contents start at 0 and count up to the number of entries
	public function NormalizeIndexes($page, $name, $data) {
		$db_content = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index ASC");
		if(count($db_content) > 0)  {
			for($i = 0; $i < count($db_content); $i++) {
				DB::Query("UPDATE " . CONTENT_TABLE . " SET content_index='{$i}' WHERE id={$db_content[$i]['id']}");
			}
		}
	}



	//returns the next available content index
	public function GetContentNextIndex($page, $name) {
		$index = 0;
		$content = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index DESC");
		if(count($content) > 0) {
			$index = $content[0]['content_index'] + 1;
		}
		return $index;
	}



	//adds content to the database
	public function AddContent($page, $name, $data, $guid = '') {
		$index = DB::GetContentNextIndex($page, $name);
		$type_name = $data['type'];
		$type_data = TypeController::GetType($type_name);

		if($guid == '') {//called outside of db generation
			$db_data = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page=
			'{$page}'");
			if(count($db_data) > 0) { // grab the old guid
				$guid = $db_data[0]['guid'];
			} else { // lol wtf happened
				$guid = '0000--' . DB::GUID();
			}
		}

		$value = '';

		if($type_data != null) {
			$value = DB::AddCustomTypeContent($type_name, $type_data);
		}
		DB::Query("INSERT INTO " . CONTENT_TABLE . " VALUES (NULL, '{$guid}', '{$name}', '{$index}', '{$value}', '{$page}', '{$type_name}')");
	}



	//recursively adds custom type content to the respective tables
	public function AddCustomTypeContent($type_name, $type) {
		$guid = DB::GetCustomTypeGUIDPrefix($type_name) . '--' . DB::GUID();

		$sql = "INSERT INTO " . DATABASE_TABLE_PREFIX . "type_{$type_name} VALUES (NULL, '{$guid}'";

		foreach($type->structure as $type_content => $type_content_data) {
			$content_type_name = $type_content_data['type'];
			$content_type_data = TypeController::GetType($content_type_name);

			if($content_type_data != null) {
				$ids = '';

				$min = isset($type_content_data['min-items']) ? $type_content_data['min-items'] : 1;
				for($i = 0; $i < $min; $i++) {
					$ids .= DB::AddCustomTypeContent($content_type_name, $content_type_data);
					if($i < $min-1) {
						$ids .= ',';
					}
				}
				$sql .= ", '{$ids}'";
			} else {
				$sql .= ", ''";
			}

		}
		$sql .=")";

		$id = DB::Query($sql);

		return $id;
	}



	//returns the highest current content index
	public function GetContentHighestIndex($page, $name) {
		$index = 0;
		$content = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index DESC");
		if(count($content) > 0) {
			$index = $content[0]['content_index'];
		}
		return $index;
	}



	//removes content from the database
	public function RemoveContent($page, $name, $data, $index = null) {
		if($index == null) {
			$index = DB::GetContentHighestIndex($page, $name);
		}
		$type_name = $data['type'];
		$type_data = TypeController::GetType($type_name);
		$row_data = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' AND content_index='{$index}'")[0];

		if($type_data != null) {
			DB::RemoveCustomTypeContent($row_data['content_value'], $type_name, $type_data);
		}

		DB::Query("DELETE FROM " . CONTENT_TABLE . " WHERE id='{$row_data['id']}'");
	}



	//recursively removes custom type content from the respecitve tables
	public function RemoveCustomTypeContent($content_id, $type_name, $type_data) {
		foreach($type_data->structure as $content_name => $content_data) {
			$content_type = TypeController::GetType($content_data['type']);
			if($content_type != null) {
				$content_value_ids = explode(',', DB::ResultArray("SELECT {$content_name} FROM " . DATABASE_TABLE_PREFIX . "type_{$type_name} WHERE id={$content_id}")[0][$content_name]);
				foreach($content_value_ids as $content_value_id) {
					DB::RemoveCustomTypeContent($content_value_id, $content_data['type'], $content_type);
				}
			}
		}
		DB::Query("DELETE FROM " . DATABASE_TABLE_PREFIX . "type_{$type_name} WHERE id={$content_id}");
	}



	//selects content from the database
	public function GetContent($page, $name, $index = null) {
		$sql = "SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ";
		if($index == null) {
			$sql .= "ORDER BY content_index";
		} else {
			$sql .= "AND content_index='{$index}'";
		}

		$response = DB::ResultArray($sql);

		return $response;
	}

	//selects content from the custom type database
	public function GetCustomTypecontent($type, $id) {
		$sql = "SELECT * FROM " . DATABASE_TABLE_PREFIX . "type_{$type} WHERE id='{$id}'";

		$response = DB::ResultArray($sql);

		return $response;
	}



	public function UpdateContent($guid, $value, $target = 'content_value') {

		$prefix = explode('--', $guid)[0];

		$table = $prefix == '0000' ? TYPES_TABLE : DATABASE_TABLE_PREFIX . 'type_' . DB::GetCustomTypeNameByPrefix($prefix);

		$sql = "UPDATE {$table} SET {$target}='{$value}' WHERE guid='{$guid}'";

		DB::Query($sql);
	}
	*/
}
