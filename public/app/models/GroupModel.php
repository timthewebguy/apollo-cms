<?php

/**
* Group Model
*/
class Group
{
	public $id;
	public $name;
	public $slug;
	public $guid_prefix;

	function __construct($id, $name, $slug, $guid_prefix) {
		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');

		//set object variables
		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->guid_prefix = $guid_prefix;
	}

	function Update() {
		DB::Query("UPDATE " . GROUPS_TABLE . " SET name='{$this->name}', slug='{$this->slug}', guid_prefix='{$this->guid_prefix}' WHERE id={$this->id}");
	}

	function Delete() {
		DB::Query("DELETE FROM " . GROUPS_TABLE . " WHERE id='{$this->id}'");
	}
}
