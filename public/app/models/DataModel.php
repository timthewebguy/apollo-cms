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
		echo 'update ';
		$type = TypeController::RetrieveType(['slug'=>$this->type]);
		if($type->type == 'compound') {
			if($this->min == 1 && $this->max == 1) {
				//compound non array
				echo 'compound non array :: ';
				foreach($this->value as $field_value) {
					$field_value->Update();
				}
			} else {
				//compound array
				echo 'compound array :: ';
				for($i = 0; $i < count($this->value); $i++) {
					foreach($this->value[$i] as $field_value) {
						$field_value->Update();
					}
				}
			}
		} else {
			if($this->min == 1 && $this->max == 1) {
				//base non array
				echo 'base non array :: ';
				$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$this->guid}'")[0]['value'];
				$sql = "UPDATE " . TYPE_TABLE_PREFIX . "{$this->type} SET value='{$this->value}' WHERE guid='{$valueGUID}'";
				DB::Query($sql);
				echo $sql;
			} else {
				//base aray
				echo 'base array :: ';
				for($i = 0; $i < count($this->value); $i++) {
					$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . "WHERE guid='{$this->guid}' AND order={$i}")[0]['value'];
					DB::Query("UPDATE " . TYPE_TABLE_PREFIX . "{$this->type} SET value='{$this->value[$i]}' WHERE guid='{$valueGUID}'");
				}
			}
		}
		echo '<br>';
		DB::Query("UPDATE " . DATA_TABLE . " SET guid='{$this->guid}', type='{$this->type}', value='{$this->value}' WHERE id={$this->id}");
	}

	function Delete() {
		DB::Query("DELETE FROM " . DATA_TABLE . " WHERE id='{$this->id}'");
	}
}
