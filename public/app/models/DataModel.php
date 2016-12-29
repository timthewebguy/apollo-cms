<?php

/**
* Data Model
*/
class Data
{
	public $guid;
	public $type;
	public $value;
	public $min;
	public $max;

	function __construct($guid, $type, $value, $min, $max) {
		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');
		require_once(CONTROLLERS . '/TypeController.php');

		//set object variables
		$this->guid = $guid;
		$this->type = $type;
		$this->value = $value;
		$this->min = $min;
		$this->max = $max;
	}

	function Update() {
		$type = TypeController::RetrieveType(['slug'=>$this->type]);
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

	function Swap($a, $b) {

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
}
