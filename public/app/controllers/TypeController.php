<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

class TypeController {

	function __construct() {
		require_once MODELS . '/TypeModel.php';
	}

	public function CreateType($name, $type, $guid_prefix, $structure) {
		require_once MODELS . '/TypeModel.php';
		require_once(APP_PATH . '/system/database.php');

		$slug = strtolower(preg_replace('/[ -]/', '_', $name));

		$id = DB::Query("INSERT INTO " . TYPES_TABLE . " VALUES (NULL, '{$name}', '{$slug}', '{$type}', '{$guid_prefix}')");

		$tablePrefix = TYPE_TABLE_PREFIX;

		$sql = "CREATE TABLE IF NOT EXISTS `{$tablePrefix}{$slug}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `guid` varchar(255) NOT NULL";

		foreach($structure as $param => $param_data) {

			//enter the field into the compound types fields data table
			$param_type = $param_data['type'];
			$param_description = $param_data['description'] == null ? '' : $param_data['description'];
			$min = isset($param_data['min-items']) ? $param_data['min-items'] : 1;
			$max = isset($param_data['max-items']) ? $param_data['max-items'] : 1;
			DB::Query("INSERT INTO " . COMPOUND_TYPE_FIELDS_TABLE . " VALUES (NULL, '{$slug}', '{$param}', '{$param_type}', '{$param_description}', {$min}, {$max})");

			//add to the compound types table creation query
			$sql .= ", `{$param}` varchar(255) DEFAULT ''";
		}

		$sql .=", PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1";

		//Create new table
		DB::Query($sql);

		//Add this type to the types table
		return new Type($id, $name, $slug, $type, $guid_prefix);
	}

	public function RetrieveType($params = null, $orderby = null, $assoc = false, $forceArray = false) {
		require_once MODELS . '/TypeModel.php';

		//base query
		$sql = "SELECT * FROM " . TYPES_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				$sql .= "{$param}='{$value}'";
				if(++$i < $count) { $sql .= " AND "; }
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		$db_response = DB::ResultArray($sql);

		foreach($db_response as $row) {
			if(!$assoc) {
				$response[] = new Type($row['id'], $row['name'], $row['slug'], $row['type'], $row['guid_prefix']);
			} else {
				$response[] = $row;
			}
		}

		if(count($response) == 1 && !$forceArray) {
			return $response[0];
		} else {
			return $response;
		}
	}

	public function LoadTypes() {
		//get all types in database
		$db_types = TypeController::RetrieveType(['type'=>'compound'], null, false, true);
		//var_dump($db_types);

		//get all types in TYPES.yaml
		$yml_types = spyc_load_file(GROUPS . '/_TYPES.yml');
		//var_dump($yml_types);

		if($db_types) {
			foreach($db_types as $db_type) {
				//Delete Old Types
				if(!isset($yml_types[$db_type->name])) {
					$db_type->Delete();
					continue;
				}

				//Update Current Types
				foreach($db_type->getFields() as $field) {
					//Delete Old Fields
					if(!isset($yml_types[$db_type->name]['structure'][$field->field_name])) {
						$db_type->removeField($field->field_name);
						continue;
					}

					//Update Current fields
					//Change in type
					if($yml_types[$db_type->name]['structure'][$field->field_name]['type'] != $field->field_type) {
						//Remove the old one, add a new one.
						$db_type->removeField($field->field_name);
						$field->field_type = $yml_types[$db_type->name]['structure'][$field->field_name]['type'];
						$db_type->addField($field);
					}

					$update = false;
					//Change in description
					if(isset($yml_types[$db_type->name]['structure'][$field->field_name]['description']) && $yml_types[$db_type->name]['structure'][$field->field_name]['description'] != $field->field_description) {
						$field->field_description = $yml_types[$db_type->name]['structure'][$field->field_name]['description'];
						$update = true;
					}

					//Change the min
					if(isset($yml_types[$db_type->name]['structure'][$field->field_name]['min-items']) && $yml_types[$db_type->name]['structure'][$field->field_name]['min-items'] != $field->field_min) {
						$field->field_min = $yml_types[$db_type->name]['structure'][$field->field_name]['min-items'];
						$update = true;
					}

					//Change the max
					if(isset($yml_types[$db_type->name]['structure'][$field->field_name]['max-items']) && $yml_types[$db_type->name]['structure'][$field->field_name]['max-items'] != $field->field_max) {
						$field->field_max = $yml_types[$db_type->name]['structure'][$field->field_name]['max-items'];
						$update = true;
					}

					if($update) {
						$db_type->updateField($field);
					}
				}

				foreach($yml_types[$db_type->name]['structure'] as $yml_type_field => $yml_type_field_data) {
					if(!$db_type->hasField($yml_type_field)) {
						var_dump($yml_type_field_data);
						$name = $yml_type_field;
						$type = $yml_type_field_data['type'];
						$description = isset($yml_type_field_data['description']) ? $yml_type_field_data['description'] : '';
						$min = isset($yml_type_field_data['min-items']) ? $yml_type_field_data['min-items'] : 1;
						$max = isset($yml_type_field_data['max-items']) ? $yml_type_field_data['max-items'] : 1;
						$db_type->addField(new CompoundTypeField($db_type->slug, $name, $type, $description, $min, $max));
					}
				}

				unset($yml_types[$db_type->name]);

			}
		}

		foreach($yml_types as $yml_type_name => $yml_type_data) {
			if(TypeController::RetrieveType(['name'=>$yml_type_name]) == null) {
				TypeController::CreateType($yml_type_name, 'compound', $yml_type_data['guid_prefix'], $yml_type_data['structure']);
			}
		}

	}


	function load() {
		TypeController::LoadTypes();
		header("Location: " . SERVERPATH . "/dashboard/group/settings/loadedTypes");
	}
}
