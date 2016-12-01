<?php 


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



	//creates the database and tables
	public function Init() {

		//connect to / create the database
		$conn = DB::Connect();
		if(!$conn) {
			$conn = new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD);
			$conn->query("CREATE DATABASE " . DATABASE_NAME);
			$conn->close();
		}

		$conn = DB::Connect();
		if(!$conn) {
			die("Something went wrong in connecting to / creating the database. Please check the database settings in config.php file.");
		} else {
			$conn->close();
			//create the tables

			if(DB::Query("SHOW TABLES LIKE " . TYPES_TABLE)->num_rows != 1) {
				DB::Query("CREATE TABLE `" . TYPES_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`type_name` varchar(255) DEFAULT NULL,`type_content` longtext,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
			}
			if(DB::Query("SHOW TABLES LIKE " . CONTENT_TABLE)->num_rows != 1) {
				DB::Query("CREATE TABLE `" . CONTENT_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`content_name` varchar(255) DEFAULT NULL,`content_index` int(11) DEFAULT NULL,`content_value` longtext,`content_page` varchar(255) DEFAULT NULL,`content_type` varchar(255) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
			}
			if(DB::Query("SHOW TABLES LIKE " . MEDIA_TABLE)->num_rows != 1) {
				DB::Query("CREATE TABLE `" . MEDIA_TABLE . "` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT,`media_name` varchar(255) DEFAULT NULL,`media_ext` varchar(50) DEFAULT NULL,`media_abs_path` longtext,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
			}
		}
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
		$sql = "CREATE TABLE " . DATABASE_TABLE_PREFIX . "type_{$type->name} (id INT NOT NULL AUTO_INCREMENT";
		$type_content = '';
		foreach ($type->structure as $column => $column_data) {
			$sql .= ", {$column} LONGTEXT";
			$min = isset($column_data['min-items']) ? $column_data['min-items'] : 1;
			$max = isset($column_data['max-items']) ? $column_data['max-items'] : $min;
			$type_content .= $column . '-' . $min . '-' . $max . ',';
		}
		$sql .= ", PRIMARY KEY (ID))";
		DB::Query($sql);
		DB::Query("INSERT INTO " . TYPES_TABLE . " (type_name, type_content) VALUES ('{$type->name}','{$type_content}')");
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
		while ($add_content_counter > 0) {
			DB::AddContent($page, $name, $data);
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
	public function AddContent($page, $name, $data) {
		$index = DB::GetContentNextIndex($page, $name);
		$type_name = $data['type'];
		$type_data = TypeController::GetType($type_name);

		$value = '';

		if($type_data != null) {
			$value = DB::AddCustomTypeContent($type_name, $type_data);
		}
		DB::Query("INSERT INTO " . CONTENT_TABLE . " VALUES (NULL, '{$name}', '{$index}', '{$value}', '{$page}', '{$type_name}')");
	}



	//recursively adds custom type content to the respective tables
	public function AddCustomTypeContent($type_name, $type) {
		$id = '';
		$sql = "INSERT INTO " . DATABASE_TABLE_PREFIX . "type_{$type_name} VALUES (NULL";

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

}
