<?php

class ContentController {

	function __construct() {
		require_once MODELS . '/Content_model.php';
	}

	public function GetContent($name, $page, $content_data = []) {
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

		if(count($path) == 2) {
			$page = PageController::GetPage($path[0]);

			DB::AddContent($path[0], $path[1], $page->contents[$path[1]]);

			$content = ContentController::GetContent($path[1], $path[0]);
			$view = $this->get_view($content);
			$index = count($content->values) - 1;
			$value = $content->values[$index];

			include $view;
		} else {
			var_dump($_POST['guid']);
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

	}


}
