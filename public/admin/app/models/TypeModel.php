<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

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
		require_once(CONTROLLERS . '/DataController.php');

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

		$types = DB::ResultArray("SELECT DISTINCT type FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE field_type='{$this->slug}'");
		foreach($types as $type) {
			TypeController::RetrieveType(['slug'=>$type['type']])->removeField($this->slug);
		}

		DB::Query("DELETE FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}'");
		DB::Query("DROP TABLE " . TYPE_TABLE_PREFIX . "{$this->slug}");

		$data = DataController::RetrieveData(['type'=>$this->slug]);
		if($data) {
			foreach($data as $d) {
				$d->Delete();
			}
		}
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

		DB::Query("ALTER TABLE " . TYPE_TABLE_PREFIX . "{$this->slug} ADD {$field->field_name} varchar(255) DEFAULT NULL");

		$data_of_type = DataController::RetrieveData(['type'=>$this->slug], null, true);
		if($data_of_type) {
			foreach($data_of_type as $data) {
				if($data->min == 1 && $data->max == 1) {
					$fieldData = DataController::CreateData($field->field_type, $field->field_min, $field->field_max);
					$data->value[$field->field_name] = $fieldData;
					$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$data->guid}'")[0]['value'];
					DB::Query("UPDATE " . TYPE_TABLE_PREFIX . "{$this->slug} SET {$field->field_name}='{$fieldData->guid}' WHERE guid='{$valueGUID}'");
				} else {
					for($i = 0; $i < count($data->value); $i++) {
						$fieldData = DataController::CreateData($field->field_type, $field->field_min, $field->field_max);
						$data->value[$i][$field->field_name] = $fieldData;
						$valueGUID = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE guid='{$data->guid}' AND data_order={$i}")[0]['value'];
						DB::Query("UPDATE " . TYPE_TABLE_PREFIX . "{$this->slug} SET {$field->field_name}='{$fieldData->guid}' WHERE guid='{$valueGUID}'");
					}
				}
			}
		}

		return $this;
	}

	//removes a field from a compound type
	function removeField($field_name) {
		if($this->type != 'compound') { return null; }

		if(!$this->hasField($field_name)) { return; }
		//save the field for after we delete it from the db
		$field = $this->getField($field_name);

		$data_of_type = DataController::RetrieveData(['type'=>$this->slug], null, true);

		if($data_of_type) {
			foreach($data_of_type as $data) {
				if($data->min == 1 && $data->max == 1) {
					$data->value[$field->field_name]->Delete();
				} else {
					for($i = 0; $i < count($data->value); $i++) {
						$data->value[$i][$field->field_name]->Delete();
					}
				}
			}
		}

		//delete the field from the db
		DB::Query("DELETE FROM " . COMPOUND_TYPE_FIELDS_TABLE . " WHERE type='{$this->slug}' AND field_name='{$field_name}'");

		//drop the table column
		DB::Query("ALTER TABLE " . TYPE_TABLE_PREFIX . "{$this->slug} DROP COLUMN {$field_name}");

		return $this;
	}

	function updateField($field) {

		DB::Query("UPDATE " . COMPOUND_TYPE_FIELDS_TABLE . " SET field_type='{$field->field_type}', field_description='{$field->field_description}', field_min={$field->field_min}, field_max={$field->field_max} WHERE type='{$field->type}' AND field_name='{$field->field_name}'");

		$data = DataController::RetrieveData(['type'=>$this->slug], null, true);
		if($data) {
			foreach($data as $d) {
				if($d->IsArray()) {
					for($i = 0; $i < count($d->valueGUID); $i++) {
						$dbRow = DB::ResultArray("SELECT * FROM " . TYPE_TABLE_PREFIX . "{$this->slug} WHERE guid='{$d->valueGUID[$i]}'")[0];
						$value = DataController::RetrieveData(['guid'=>$dbRow[$field->field_name]]);
						$value->min = $field->field_min;
						$value->max = $field->field_max;
						$value->Update();
					}
				} else {
					$dbRow = DB::ResultArray("SELECT * FROM " . TYPE_TABLE_PREFIX . "{$this->slug} WHERE guid='{$d->valueGUID}'")[0];
					$value = DataController::RetrieveData(['guid'=>$dbRow[$field->field_name]]);
					$value->min = $field->field_min;
					$value->max = $field->field_max;
					$value->Update();
				}
			}
		}
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
