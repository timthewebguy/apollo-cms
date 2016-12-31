<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

class TypeController {

	function __construct() {
		require_once MODELS . '/TypeModel.php';
	}

	public function CreateType($name, $type, $guid_prefix, $structure) {
		require_once MODELS . '/TypeModel.php';
		require_once(APP_PATH . '/system/database.php');

		$slug = strtolower(preg_replace('/[ -]/', '_', $name));

		$id = DB::Query("INSERT INTO " . TYPES_TABLE . " VALUES (NULL, '{$name}', '{$slug}', '{$type}', '{$guid_prefix}')");

		$tablePrefix = TYPE_TABLE_PREFIX;

		$sql = "CREATE TABLE IF NOT EXISTS `{$tablePrefix}{$slug}` (`id` int(11) unsigned NOT NULL AUTO_INCREMENT, `guid` varchar(255) NOT NULL";

		foreach($structure as $param => $param_data) {

			//enter the field into the compound types fields data table
			$param_type = $param_data['type'];
			$param_description = $param_data['description'] == null ? '' : $param_data['description'];
			$min = isset($param_data['min']) ? $param_data['min'] : 1;
			$max = isset($param_data['max']) ? $param_data['max'] : 1;
			DB::Query("INSERT INTO " . COMPOUND_TYPE_FIELDS_TABLE . " VALUES (NULL, '{$slug}', '{$param}', '{$param_type}', '{$param_description}', {$min}, {$max})");

			//add to the compound types table creation query
			$sql .= ", `{$param}` varchar(255) DEFAULT ''";
		}

		$sql .=", PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=latin1";

		//Create new table
		DB::Query($sql);

		//Add this type to the types table
		return new Type($id, $name, $slug, $type, $guid_prefix);
	}

	public function RetrieveType($params = null, $orderby = null, $assoc = false) {
		require_once MODELS . '/TypeModel.php';

		//base query
		$sql = "SELECT * FROM " . TYPES_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				$sql .= "{$param}='{$value}'";
				if(++$i < $count) { $sql .= " AND "; }
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		$db_response = DB::ResultArray($sql);

		foreach($db_response as $row) {
			if(!$assoc) {
				$response[] = new Type($row['id'], $row['name'], $row['slug'], $row['type'], $row['guid_prefix']);
			} else {
				$response[] = $row;
			}
		}

		if(count($response) == 1) {
			return $response[0];
		} else {
			return $response;
		}
	}

	public function LoadTypes() {
		//get all types in database
		$db_types = TypeController::RetrieveType(null, null, true);

		//get all types in TYPES.yaml
		$yml_types = spyc_load_file(GROUPS . '/_TYPES.yml');

		//Delete Old Types
		foreach($db_types as $db_type) {
			if(!isset($yml_types[$db_type->name])) {
				$db_type->Delete();
			}
		}

		//Update Current Types
		//foreach()
	}
}
