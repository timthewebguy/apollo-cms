<?php

/**
* Type Model
*/
class Type
{
	public $id;
	public $name;
	public $slug;
	public $type; //compound or base
	public $guid_prefix;

	function __construct($id, $name, $slug, $type, $guid_prefix) {
		//include the database object for CRUD operations.
		require_once(APP_PATH . '/system/database.php');

		//set object variables
		$this->id = $id;
		$this->name = $name;
		$this->slug = $slug;
		$this->type = $type;
		$this->guid_prefix = $guid_prefix;
	}

	function Update() {
		DB::Query("UPDATE " . TYPES_TABLE . " SET guid='{$this->guid}', type='{$this->type}', value='{$this->value}' WHERE id={$this->id}");
	}

	function Delete() {
		DB::Query("DELETE FROM " . TYPES_TABLE . " WHERE id='{$this->id}'");
	}

	//gets the compound type fields
	function getFields() {
		if($this->type != 'compound') { return null; }

		$db_response = DB::ResultArray("SELECT * FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}'");

		foreach($db_response as $row) {
			$response[] = new CompoundTypeField($this->slug, $row['field_name'], $row['field_type'], $row['field_description'], $row['field_min'], $row['field_max']);
		}

		return $response;

	}

	//gets a specific field from a compound type
	function getField($field_name) {
		if($this->type != 'compound') { return null; }

		$db_response = DB::ResultArray("SELECT * FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}' AND field_name='{$field_name}'");

		return new CompoundTypeField($this->slug, $db_response[0]['field_name'], $db_response[0]['field_type'], $db_response[0]['field_description'], $db_response[0]['field_min'], $db_response[0]['field_max']);
	}

	//checks if a compound type has a field
	function hasField($field_name) {
		if($this->type != 'compound') { return false; }

		return count(DB::ResultArray("SELECT * FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}' AND field_name='{$field_name}'")) == 1;
	}

	//adds a field to a compound type
	function addField($field) {
		if($this->type != 'compound') { return null; }

		if(!$this->hasField($field->field_name)) {
			DB::Query("INSERT INTO " . COMPOUND_TYPE_FIELDS_TABLE . " VALUES (NULL, '{$this->slug}', '{$field->field_name}', '{$field->field_type}', '{$field->field_description}', {$field->field_min}, {$field->field_max})");
		}

		return $this;
	}

	//removes a field from a compound type
	function removeField($field_name) {
		if($this->type != 'compound') { return null; }

		if($this->hasField($field_name)) {
			DB::Query("DELETE FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}' AND field_name='{$field_name}'");
		}

		return $this;
	}
}

class CompoundTypeField {
	public $type;
	public $field_name;
	public $field_type;
	public $field_description;
	public $field_min;
	public $field_max;

	function __construct($type, $field_name, $field_type, $field_description, $field_min, $field_max) {
		$this->type = $type;
		$this->field_name = $field_name;
		$this->field_type = $field_type;
		$this->field_description = $field_description;
		$this->field_min = $field_min;
		$this->field_max = $field_max;
	}
}
