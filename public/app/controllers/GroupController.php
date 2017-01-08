<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

class GroupController {

	function __contstruct() {
		require_once MODELS . '/Group_model.php';
	}


	/*
	*	usage:
	* GroupController::CreateGroup('Home', '1234');
	*/

	public function CreateGroup($name) {
		require_once MODELS . '/GroupModel.php';
		require_once APP_PATH . '/system/database.php';

		$slug = strtolower(preg_replace('/ /', '-', $name));

		$id = DB::Query("INSERT INTO " . GROUPS_TABLE . " VALUES (NULL, '{$name}', '{$slug}')");

		return new Group($id, $name, $slug);
	}

	/*
	* usage:
	* GroupController::RetrieveGroup();
	* GroupController::RetrieveGroup(['name'=>'home']);
	* GroupController::RetrieveGroup(null, 'guid_prefix');
	*/

	public function RetrieveGroup($params = null, $orderby = null, $forceArray = false) {
		require_once MODELS . '/GroupModel.php';
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
		$response = array();

		foreach($db_response as $row) {
			$response[] = new Group($row['id'], $row['name'], $row['slug'], $row['guid_prefix']);
		}

		if(count($response) == 1 && !$forceArray) {
			return $response[0];
		} else {
			return $response;
		}
	}

	public function GetFirstGroupName() {
		require_once APP_PATH . '/system/database.php';

		$groups = DB::ResultArray("SELECT * FROM " . GROUPS_TABLE . " ORDER BY slug");

		foreach($groups as $g) {
			$response = $g['name'];
			break;
		}

		return $response;
	}

	public function LoadGroups() {

		$db_groups = GroupController::RetrieveGroup(null, null, true);

		$yml_groups = array();
		$files = array_diff(scandir(GROUPS, 1), ['..', '.']);
		foreach($files as $file) {
			if(basename($file, '.yml') != '_TYPES') {
				$yml_groups[basename($file, '.yml')] = spyc_load_file(GROUPS . '/' . $file);
			}
		}

		if($db_groups) {
			foreach($db_groups as $db_group) {
				//delete old groups
				if(!isset($yml_groups[$db_group->name])) {
					$db_group->Delete();
					continue;
				}

				$yml_group = $yml_groups[$db_group->name];

				//update the content of an existing group
				$group_content = ContentController::RetrieveContent(['content_group'=>$db_group->slug]);
				if($group_content) {
					foreach($group_content as $c) {
						if(!isset($yml_group[$c->name])) {
							$c->Delete();
							continue;
						}

						$yml_content = $yml_group[$c->name];

						$update = false;
						if($c->data->type != $yml_content['type']) {
							$c->data->type = $yml_content['type'];
							$update = true;
						}
						if($c->description != $yml_content['description']) {
							$c->description = $yml_content['description'];
							$update = true;
						}
						if($c->data->min != $yml_content['min-items']) {
							$c->data->min = $yml_content['min-items'];
							$update = true;
						}
						if($c->data->min != $yml_content['max-items']) {
							$c->data->max = $yml_content['max-items'];
							$update = true;
						}
						if($update) {
							$c->Update();
						}

						unset($yml_groups[$db_group->name][$c->name]);
					}
				}

				foreach($yml_groups[$db_group->name] as $yml_content_name => $yml_content_data) {
					$min = (isset($yml_content_data['min-items'])) ? $yml_content_data['min-items'] : 1;
					$max = (isset($yml_content_data['max-items'])) ? $yml_content_data['max-items'] : 1;
					$data = DataController::CreateData($yml_content_data['type'], $min, $max);
					$description = (isset($yml_content_data['description'])) ? $yml_content_data['description'] : '';

					ContentController::CreateContent($db_group->slug, $yml_content_name, $data, $description);
				}

				unset($yml_groups[$db_group->name]);
			}
		}

		foreach($yml_groups as $yml_group_name => $yml_group_content) {
			$group = GroupController::CreateGroup($yml_group_name);
			foreach($yml_group_content as $yml_content_name => $yml_content_data) {
				$min = (isset($yml_content_data['min-items'])) ? $yml_content_data['min-items'] : 1;
				$max = (isset($yml_content_data['max-items'])) ? $yml_content_data['max-items'] : 1;
				$data = DataController::CreateData($yml_content_data['type'], $min, $max);
				$description = (isset($yml_content_data['description'])) ? $yml_content_data['description'] : '';

				ContentController::CreateContent($group->slug, $yml_content_name, $data, $description);
			}
		}
	}

	function load() {
		GroupController::LoadGroups();
		header("Location: " . SERVERPATH . "/dashboard/group/settings/loadedGroups");
	}

	function save() {

		foreach($_POST['changeData'] as $guid => $value) {

			$data_guid = DB::ResultArray("SELECT * FROM " . DATA_TABLE . " WHERE value='{$guid}'")[0]['guid'];
			$data = DataController::RetrieveData(['guid' => $data_guid]);

			if($data->min == 1 && $data->max == 1) {
				//not an array
				$data->value = $value;
			} else {
				//array
				$index = array_search($guid, $data->valueGUID);
				$data->value[$index] = $value;
			}

			$data->update();

			echo 'success';

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
