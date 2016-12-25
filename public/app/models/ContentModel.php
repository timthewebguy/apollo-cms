<?php

/*
* Content Model
*/

class Content {
	public $id;
	public $name;
	public $slug;
	public $guid;
	public $group;
	public $description;
	public $data;


	function __construct($id, $name, $slug, $guid, $group, $data, $description) {

		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');

		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->guid = $guid;
		$this->group = $group;
		$this->description = $description;
		$this->data = $data;

	}

	function Save() {
		DB::Query("UPDATE " . CONTENT_TABLE . " SET name='{$this->name}', slug='{$this->slug}', guid='{$this->guid}', group='{$this->group}', description='{$this->description}', data='{$this->data}' WHERE id={$this->id}");

		return $this;
	}

	function Delete() {
		DB::Query("DELETE FROM " . CONTENT_TABLE . " WHERE id='{$this->id}'");

		DB::CascadeDelete($this);
	}
}
