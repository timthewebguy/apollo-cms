<?php

class GroupController {

	function __contstruct() {
		require MODELS . '/Group_model.php';
	}


	/*
	*	usage:
	* GroupController::CreateGroup('Home', '1234');
	*/

	public function CreateGroup($name, $guid_prefix) {
		require_once MODELS . '/GroupModel.php';
		require_once APP_PATH . '/system/database.php';

		$slug = strtolower(preg_replace('/ /', '-', $name));

		$id = DB::Query("INSERT INTO " . GROUPS_TABLE . " VALUES (NULL, '{$name}', '{$slug}', {$guid_prefix})");

		return new Group($id, $name, $slug, $guid_prefix);
	}

	/*
	* usage:
	* GroupController::RetrieveGroup();
	* GroupController::RetrieveGroup(['name'=>'home']);
	* GroupController::RetrieveGroup(null, 'guid_prefix');
	*/

	public function RetrieveGroup($params = null, $orderby = null) {
		require MODELS . '/GroupModel.php';
		//base query
		$sql = "SELECT * FROM " . GROUPS_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				if(property_exists('Group', $param)) {
					$sql .= $param . "='{$value}'";
					if(++$i < $count) { $sql .= " AND "; }
				}
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		$db_response = DB::ResultArray($sql);

		foreach($db_response as $row) {
			$response[] = new Group($row['id'], $row['name'], $row['slug'], $row['guid_prefix']);
		}

		if(count($response) == 1) {
			return $response[0];
		} else {
			return $response;
		}
	}


	/*function UpdateGroups() {

	}

	public function GetFirstGroupName() {
		$files = array_diff(scandir(PAGES, 1), ['..', '.']);
		$response = '';

		foreach($files as $file) {
			$response = basename($file, '.yml');
			break;
		}

		unset($file);

		return $response;
	}

	public function GetGroups() {
		require_once MODELS . '/Group_model.php';

		$files = array_diff(scandir(PAGES, 1), ['..', '.']);
		$pages = [];

		foreach($files as $file) {
			if($file != '_TYPES.yml') {
				$pages[basename($file, '.yml')] = spyc_load_file(PAGES . '/' . $file);
			}
		}

		$response = [];

		foreach ($pages as $name => $contents) {
			$response[] = new Group($name, $contents);
		}

		return $response;
	}

	public function GetGroup($target_page) {

		$pages = GroupController::GetGroups();
		foreach($pages as $page) {
			if($page->name == $target_page) {
				return $page;
			}
		}
		return null;
	}*/

}
