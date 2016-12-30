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

		$guid = DATA_GUID_PREFIX . '--' . DB::GUID();

		$type = TypeController::RetrieveType(['slug'=>$type_slug]);
		$table = TYPE_TABLE_PREFIX . $type->slug;

		//cycle through to make the minimum number of entries
		for($i = 0; $i < $min; $i++) {

			//value of this data object
			$valueGUID = $type->guid_prefix . '--' . DB::GUID();

			if($type->type == 'base') {

				//Create Table Reference for this data packet
				//ID, GUID, VALUE
				DB::Query("INSERT INTO {$table} VALUES (NULL, '{$valueGUID}', NULL)");

				$value[$i] = '';

			} else {

				//create the query
				$valueSQL = "INSERT INTO {$table} VALUES (NULL, '{$valueGUID}'";
				foreach($type->getFields() as $field) {
					$data = DataController::CreateData($field->field_type, $field->field_min, $field->field_max);
					$valueSQL .= ", '" . $data->guid . "'";
					$value[$i][$field->field_name] = $data;
				}
				$valueSQL .= ")";

				//Create the compound type row
				DB::Query($valueSQL);
			}

			DB::Query("INSERT INTO " . DATA_TABLE . " VALUES (NULL, '{$guid}', '{$type_slug}', '{$valueGUID}', {$min}, {$max}, {$i})");

		}//forloop

		if($min == 1 && $max == 1 && count($value) == 1) {
			$value = $value[0];
		}

		return new Data($guid, $type_slug, $value, $min, $max);
	}


	public function RetrieveData($params = null, $orderby = null, $forceArray = false) {

		require_once(APP_PATH . '/system/database.php');
		require_once(MODELS . '/DataModel.php');
		require_once(CONTROLLERS . '/TypeController.php');

		//base query
		$sql = "SELECT * FROM " . DATA_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				$sql .= $param . "='{$value}'";
				if(++$i < $count) { $sql .= " AND "; }
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		//get the db results
		$db_response = DB::ResultArray($sql);
		$db_values = [];

		//when a data item occupies more than one row,
		//condense it to one row with an array of values
		foreach($db_response as $row) {
			if($db_values[$row['guid']] != null) {
				unset($db_response[array_search($row, $db_response)]);
			}
			$db_values[$row['guid']][$row['data_order']] = $row['value'];
		}

		//convert db rows into Data objects
		foreach($db_response as $row) {
			//type object
			$type = TypeController::RetrieveType(['slug'=>$row['type']]);

			$value = array();
			$response = array();

			for($i = 0; $i < count($db_values[$row['guid']]); $i++) {

				//data in the type's table
				$valueData = DB::ResultArray("SELECT * FROM " . TYPE_TABLE_PREFIX . "{$type->slug} WHERE guid='{$db_values[$row['guid']][$i]}'")[0];

				//if compound, set the value to an associative array of values
				if($type->type == 'compound') {
					foreach($type->getFields() as $field) {
						$field_name = $field->field_name;
						$value[$i][$field_name] = DataController::RetrieveData(['guid'=>$valueData[$field_name]]);
					}
				} else {
					$value[$i] = $valueData['value'];
				}

			}

			//if the value is not of an array, reduce it to a single value
			if($row['min'] == '1' && $row['max'] == '1') {
				$value = $value[0];
			}

			//generate the new data response
			$response[] = new Data($row['guid'], $row['type'], $value, intval($row['min']), intval($row['max']));
		}

		if(count($response) == 1 && !$forceArray) {
			return $response[0];
		} else {
			return $response;
		}
	}


}
