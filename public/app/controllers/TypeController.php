<?php 

class TypeController {

	public function GetTypes() {

		require_once(MODELS . '/Type_model.php');

		$types = spyc_load_file(PAGES . '/_TYPES.yml');
		$response = [];

		foreach($types as $type_name => $type_data) {
			$response[] = new Type($type_name, $type_data['description'], $type_data['displayValue'], $type_data['structure']);
		}

		return $response;
	}

	function GetType($target_type) {
		$types = TypeController::GetTypes();

		foreach($types as $type) {
			if($type->name == $target_type) {
				return $type;
			}
		}
		return null;
	}

	function IsCustomType($type) {
		return (TypeController::GetType($type) != null) ? true : false;
	}
}
