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

		//set object variables
		$this->guid = $guid;
		$this->type = $type;
		$this->value = $value;
		$this->min = $min;
		$this->max = $max;
	}

	function Update() {
		DB::Query("UPDATE " . DATA_TABLE . " SET guid='{$this->guid}', type='{$this->type}', value='{$this->value}' WHERE id={$this->id}");
	}

	function Delete() {
		DB::Query("DELETE FROM " . DATA_TABLE . " WHERE id='{$this->id}'");
	}
}
