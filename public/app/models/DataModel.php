<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

/**
* Data Model
*/
class Data
{
	public $guid;
	public $type;
	public $value;
	public $valueGUID;
	public $min;
	public $max;

	function __construct($guid, $type, $value, $valueGUID, $min, $max) {
		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');
		require_once(CONTROLLERS . '/TypeController.php');

		//set object variables
		$this->guid = $guid;
		$this->type = $type;
		$this->value = $value;
		$this->valueGUID = $valueGUID;
		$this->min = $min;
		$this->max = $max;
	}

	function Update() {
		$type = TypeController::RetrieveType(['slug'=>$this->type]);

		$prevData = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'")[0];
		$prevMin = $prevData['min'];
		$prevMax = $prevData['max'];

		DB::Query("UPDATE " . DATA_TABLE . " SET min={$this->min}, max={$this->max} WHERE guid='{$this->guid}'");

		if($prevMin != 1 || $prevMax != 1 || $this->min != 1 || $this->max != 1) {

			if(count($this->value) < $this->min) {
				for($i = 0; $i < ($this->min - count($this->value)); $i++) {
					$this->AddValue();
				}
			}
			if(count($this->value) > $this->max) {
				for($i = 0; $i < (count($this->value) - $this->max); $i++) {
					$this->RemoveValue('last');
				}
			}
		}

		if($prevMin == 1 && $prevMax == 1 && ($this->min != 1 || $this->max != 1)) {
			//this used to not be an array, but now it is
			$this->value[0] = $this->value;
		} else if($prevMin != 1 && $prevMax != 1 && $this->min == 1 && $this->max == 1) {
			//this used to be an array but now it isn't
			$this->value = $this->value[0];
		}

		if($type->type == 'compound') {
			if($this->min == 1 && $this->max == 1) {
				//compound non array
				foreach($this->value as $field_value) {
					$field_value->Update();
				}
			} else {
				//compound array
				for($i = 0; $i < count($this->value); $i++) {
					foreach($this->value[$i] as $field_value) {
						$field_value->Update();
					}
				}
			}
		} else {
			if($this->min == 1 && $this->max == 1) {
				//base non array
				$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'")[0]['value'];
				DB::Query("UPDATE " . TYPE_TABLE_PREFIX . "{$this->type} SET value='{$this->value}' WHERE guid='{$valueGUID}'");
			} else {
				//base aray
				for($i = 0; $i < count($this->value); $i++) {
					$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}' AND data_order={$i}")[0]['value'];
					DB::Query("UPDATE " . TYPE_TABLE_PREFIX . "{$this->type} SET value='{$this->value[$i]}' WHERE guid='{$valueGUID}'");
				}
			}
		}
	}

	function Delete() {
		$type = TypeController::RetrieveType(['slug'=>$this->type]);

		if($type->type == 'compound') {
			//delete all sub data objects
			if($this->min == 1 && $this->max == 1) {
				//non array
				foreach($type->getFields() as $field) {
					$this->$value[$field->field_name]->Delete();
				}
			} else {
				//array
				for($i = 0; $i < count($this->value); $i++) {
					foreach($type->getFields() as $field) {
						$this->value[$i][$field->field_name]->Delete();
					}
				}
			}
		}

		//remove value from type table
		$valueGUIDs = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'");
		foreach($valueGUIDs as $row) {
			$valueGUID = $row['value'];
			//delete the row in the type table
			DB::Query("DELETE FROM " . TYPE_TABLE_PREFIX . "{$this->type} WHERE guid='{$valueGUID}'");
		}

		//remove this from data table
		DB::Query("DELETE FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'");
	}

	function SwapValues($a, $b) {

		/*if($a >= count($this->value) || $b >= count($this->value)) {
			return $this;
		}*/

		//get the pk's for the elements to swap
		$a_id = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}' AND data_order={$a}")[0]['id'];
		$b_id = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}' AND data_order={$b}")[0]['id'];

		//swap them
		DB::Query("UPDATE " . DATA_TABLE . " SET data_order={$b} WHERE id={$a_id}");
		DB::Query("UPDATE " . DATA_TABLE . " SET data_order={$a} WHERE id={$b_id}");

		//swap them in the data object and return it
		$tmp = $this->value[$a];
		$this->value[$a] = $this->value[$b];
		$this->value[$b] = $tmp;

		return $this;
	}

	function AddValue() {

		//make sure we are not at capacity
		if(count($this->value) == $this->max) { return; }

		$type = Typecontroller::RetrieveType(['slug'=>$this->type]);
		$valueGUID = $type->guid_prefix . '--' . DB::GUID();
		$order = count($this->value);

		if($type->type == 'base') {

			DB::Query("INSERT INTO " . TYPE_TABLE_PREFIX . "{$type->slug} VALUES (NULL, '{$valueGUID}', '')");
			$this->value[$order] = '';

		} else {

			$sql = "INSERT INTO " . TYPE_TABLE_PREFIX . "{$type->slug} VALUES (NULL, '{$valueGUID}'";
			foreach($type->getFields() as $field) {
				$field_data = DataController::CreateData($field->field_type, $field->field_min, $field->field_max);
				$sql .= ", '{$field_data->guid}'";
				$this->value[$order][$field->field_name] = $field_data;
			}
			$sql .= ")";
			DB::Query($sql);

		}

		DB::Query("INSERT INTO " . DATA_TABLE . " VALUES (NULL, '{$this->guid}', '{$this->type}', '{$valueGUID}', {$this->min}, {$this->max}, {$order})");

		return $this;

	}

	function RemoveValue($index) {

		//make sure we are not at capacity
		if(count($this->value) == $this->min) { return; }

		//translate keywords into int indexes
		if($index == 'last') { $index = count($this->value) - 1; }
		if($index == 'first') { $index = 0; }

		$type = Typecontroller::RetrieveType(['slug'=>$this->type]);

		if($type->type == 'base') {

			$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}' AND data_order={$order}")[0]['value'];
			DB::Query("DELETE FROM " . TYPE_TABLE_PREFIX . "{$type->slug} WHERE guid='{$valueGUID}'");

		} else {

			foreach($type->getFields() as $field) {
				$this->value[$index][$field->field_name]->Delete();
			}

		}

		unset($this->value[$index]);
		DB::Query("DELETE FROM " . DATA_TABLE . " WHERE guid='{$this->guid}' AND data_order={$index}");

		$this->NormalizeOrders();

	}

	function NormalizeOrders() {

		//make sure this data is an array
		if($this->min == $this->max) { return; }

		$db_data = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'");
		$i = 0;
		foreach($db_data as $row) {
			$id = $row['id'];
			DB::Query("UPDATE " . DATA_TABLE . " SET data_order={$i} WHERE guid='{$this->guid}' AND id={$id}");
			$i++;
		}
	}

	function IsArray() {
		return !($this->min==1 && $this->max == 1);
	}

}
