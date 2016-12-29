<?php

class ContentController {

	function __construct() {
		require_once MODELS . '/ContentModel.php';
	}

	public function CreateContent($group, $name, $data, $description = '') {
		require_once(APP_PATH . '/system/database.php');
		require_once(MODELS . '/ContentModel.php');

		$slug = strtolower(preg_replace('/ /', '-', $name));
		$guid = DB::GUID();

		$id = DB::Query("INSERT INTO " . CONTENT_TABLE . " VALUES (NULL, '{$guid}', '{$group}', '{$name}', '{$slug}', '{$data->guid}', '{$description}')");

		return new Content($id, $name, $slug, $guid, $group, $data, $description);
	}

	public function RetrieveContent($params = null, $orderby = null) {

		require_once(APP_PATH . '/system/database.php');
		require_once(CONTROLLERS . '/DataController.php');
		require_once(MODELS . '/ContentModel.php');

		//base query
		$sql = "SELECT * FROM " . CONTENT_TABLE;

		//if there are parameters, apply them
		if($params != null) {
			$sql .= " WHERE ";
			//keep track of where commas shold go
			$i = 0;
			$count = count($params);
			foreach($params as $param => $value) {
				$sql .= $param . "='{$value}'";
				if(++$i < $count) { $sql .= " AND "; }
			}
		}

		//if there is an orderby
		if($orderby != null) {
			$sql .= " ORDER BY " . $orderby;
		}

		echo $sql;

		$db_response = DB::ResultArray($sql);

		foreach($db_response as $row) {
			$response[] = new Content($row['id'], $row['name'], $row['slug'], $row['guid'], $row['content_group'], DataController::RetrieveData(['guid'=>$row['data']]), $row['description']);
		}

		if(count($response) == 1) {
			return $response[0];
		} else {
			return $response;
		}
	}

}
	/*public function GetContent($name, $page, $content_data = []) {
		require_once MODELS . '/Content_model.php';

		if($content_data == []) {
			$p = PageController::GetPage($page);
			$content_data = $p->contents[$name];
		}

		$db_entries = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index");

		if(count($db_entries) == 0) {
			return null;
		}

		$description = $content_data['description'];
		$type = $db_entries[0]['content_type'];

		$values = [];
		foreach($db_entries as $entry) {
			$values[] = $entry['content_value'];
		}

		$guid = $db_entries[0]['guid'];

		$minValues = isset($content_data['min-items']) ? $content_data['min-items'] : 1;
		$maxValues = isset($content_data['max-items']) ? $content_data['max-items'] : 1;

		$content = new Content($name, $page, $description, $type, $values, $guid, $minValues, $maxValues);
		return $content;
	}

	function get_view($content) {
		switch($content->type) {
			case 'text':
				$view = VIEWS . '/contentEditor/contentEditor_text.php';
				break;
			case 'wysiwyg':
				$view = VIEWS . '/contentEditor/contentEditor_wysiwyg.php';
				break;
			case 'image':
				$view = VIEWS . '/contentEditor/contentEditor_image.php';
				break;
			default:
				$view = VIEWS . '/contentEditor/contentEditor_custom.php';
				break;
		}
		return $view;
	}

	function render($content) {
		$view = $this->get_view($content);

		include VIEWS . '/contentEditor/contentEditor.php';
	}

	function draw_custom_editor($id, $parent, $index) {
		$type_name = $parent->type;
		$type_data = TypeController::GetType($parent->type);


		foreach($type_data->structure as $content_name=>$content_data) {
			$db_data = DB::ResultArray("SELECT * FROM " . DATABASE_TABLE_PREFIX . "type_{$type_name} WHERE id = '{$id}'")[0];

			$isCustomType = TypeController::IsCustomType($content_data['type']);

			$min = isset($content_data['min-items']) && $isCustomType ? $content_data['min-items'] : 1;
			$max = isset($content_data['max-items']) && $isCustomType ? $content_data['max-items'] : 1;

			$values = [];

			if($isCustomType) {
				$values = explode(',', $db_data[$content_name]);
			} else {
				$values = [$db_data[$content_name]];
			}

			$guid = $db_data['guid'];

			$content = new Content($parent->name . '__' . $index . '__' . $content_name, $parent->page, $content_data['description'], $content_data['type'], $values, $guid, $min, $max);
			$this->render($content);

		}
	}

	function add_content() {

		if(!isset($_POST['data'])) { return; }

		$path = explode('__', $_POST['data']);

		$page = PageController::GetPage($path[0]);

		if(count($path) == 2) {

			DB::AddContent($path[0], $path[1], $page->contents[$path[1]]);

			$content = ContentController::GetContent($path[1], $path[0]);
			$view = $this->get_view($content);
			$index = count($content->values) - 1;
			$value = $content->values[$index];

			include $view;
		} else {

			$guid_prefix = explode('--', $_POST['guid'])[0];
			$type_name = DB::GetCustomTypeNameByPrefix($guid_prefix);
			$type = TypeController::GetType($type_name);

			$value = $path[count($path) - 1];

			$type_to_add = $type->structure[$value]['type'];
			$type_to_add_data = TypeController::GetType($type_to_add);

			$id = DB::AddCustomTypeContent($type_to_add, $type_to_add_data);

			$current_content = DB::GetCustomTypeContent($type_name, DB::GetIDByGUID($_POST['guid']))[0];
			$added_content = DB::GetCustomTypeContent($type_to_add, $id);

			DB::UpdateContent($_POST['guid'], $current_content[$value] . ',' . $id, $value);
			//recalculate current_content after the UpdateContent
			$current_content = DB::GetCustomTypeContent($type_name, DB::GetIDByGUID($_POST['guid']))[0];

			$name = array_splice($path, 0, 1);
			$content = new Content($path[0], join('__', $name), 'hello world', $type_to_add, [$id],  $added_content[0]['guid'], $type->structure[$value]['min-items'], $type->structure[$value]['max-items']);

			var_dump($content);
			//$value = $id;
			$index = count(explode(',', $current_content[$value])) - 1;
			$view = $this->get_view($content);

			//echo $view;

			include $view;
		}
	}

	function remove_content() {

		if(!isset($_POST['target'])) { return; }

		$path = explode('__', $_POST['target']);

		if(count($path) == 2) {
			$page = PageController::GetPage($path[0]);

			DB::RemoveContent($path[0], $path[1], $page->contents[$path[1]], $_POST['index']);

			echo 'success';
		} else {
			var_dump($path);
			echo $_POST['index'] . ' ';

			$type = TypeController::GetType(ContentController::GetContent($path[1], $path[0])->type);

			var_dump($db_content);

			for($i = 3; $i < count($path) + 1; $i += 2) {

			}
		}

	}*/
