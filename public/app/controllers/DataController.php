<?php

class DataController {

	function __construct() {
		require_once MODELS . '/DataModel.php';
	}

	public function CreateData($type_slug, $min = 1, $max = 1) {
		require_once(APP_PATH . '/system/database.php');
		require_once(MODELS . '/DataModel.php');
		require_once(CONTROLLERS . '/TypeController.php');

		//Ensure that there is never a negative amount of data
		if($max < $min) { $max = $min; }

		$guid = DB::GUID();

		$type = TypeController::RetrieveType(['slug'=>$type_slug]);
		$table = TYPE_TABLE_PREFIX . $type->slug;

		//cycle through to make the minimum number of entries
		for($i = 0; $i < $min; $i++) {

			//value of this data object
			$value[] = $type->guid_prefix . '--' . DB::GUID();

			if($type->type == 'base') {

				//Create Table Reference for this data packet
				if($type->slug == 'media') {
					//ID, GUID, PATH, EXTENSION, NAME
					DB::Query("INSERT INTO {$table} VALUES (NULL, '{$value[$i]}', NULL, NULL, NULL)");
				} else {
					//ID, GUID, VALUE
					DB::Query("INSERT INTO {$table} VALUES (NULL, '{$value[$i]}', NULL)");
				}

			} else {

				//create the query
				$valueSQL = "INSERT INTO {$table} VALUES (NULL, '{$value[$i]}'";
				foreach($type->getFields() as $field) {
					$valueSQL .= ", '" . DataController::CreateData($field->field_type, $field->field_min, $field->field_max)->guid . "'";
				}
				$valueSQL .= ")";

				//Create the compound type row
				DB::Query($valueSQL);
			}

			DB::Query("INSERT INTO " . DATA_TABLE . " VALUES (NULL, '{$guid}', '{$type_slug}', '{$value[$i]}', {$min}, {$max}, {$i})");

		}//forloop

		if($min == 1 && $max == 1 && count($value) == 1) {
			$value = $value[0];
		}

		return new Data($guid, $type_slug, $value, $min, $max);
	}


	public function RetrieveContent($params = null, $orderby = null) {
		//base query
		$sql = "SELECT * FROM " . DATA_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				if(property_exists('Data', $param)) {
					$sql .= $param . "='{$value}'";
					$sql .= (++$i === $count) ? ', ' : '';
				}
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		$response = DB::ResultArray($sql);

		if(count($response) == 1) {
			return $response[0];
		} else {
			return $response;
		}
	}


}
