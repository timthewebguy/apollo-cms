<?php 

class ContentController {

	function __construct() {
		require_once MODELS . '/Content_model.php';
	}

	public function GetContent($name, $page, $content_structure) {
		require_once MODELS . '/Content_model.php';

		$db_entries = DB::ResultArray("SELECT * FROM " . CONTENT_TABLE . " WHERE content_name='{$name}' AND content_page='{$page}' ORDER BY content_index");

		if(count($db_entries) == 0) {
			return null;
		}

		$description = $content_structure['description'];
		$type = $db_entries[0]['content_type'];

		$values = [];
		foreach($db_entries as $entry) {
			$values[] = $entry['content_value'];
		}

		$minValues = isset($content_structure['min-items']) ? $content_structure['min-items'] : 1;
		$maxValues = isset($content_structure['max-items']) ? $content_structure['max-items'] : 1;

		$content = new Content($name, $page, $description, $type, $values, $minValues, $maxValues);
		return $content;
	}

	function get_view($content) {
		switch($content->type) {
			case 'text':
				return  VIEWS . '/contentEditor/contentEditor_text.php';
				break;
			case 'wysiwyg':
				return  VIEWS . '/contentEditor/contentEditor_wysiwyg.php';
				break;
			case 'image':
				return  VIEWS . '/contentEditor/contentEditor_image.php';
				break;
			default:
				return VIEWS . '/contentEditor/contentEditor_custom.php';
				break;
		}
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

				$content = new Content($parent->name . '-' . $index . '-' . $content_name, $parent->page, $content_data['description'], $content_data['type'], $values, $min, $max);
				$this->render($content);

		}
	}

}
