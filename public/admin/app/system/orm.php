<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

function get_content($page, $name) {
	$page_data = get_page($page);
	$content_data = $page_data[$name];
	$db_data = result_array("SELECT * FROM " . CONTENT_TABLE . " WHERE content_page='{$page}' AND content_name='{$name}'");

	for($i = 0; $i < count($db_data); $i++) {
		if(is_custom_type($db_data[$i]['content_type'])) {
			$db_data[$i]['content_value'] = get_custom_type_content($db_data[$i]['content_type'], $db_data[$i]['content_value'])[0];
		}
	}

	return $db_data;
}


function get_custom_type_content($type, $id) {
	$type_data = get_type($type);
	$db_data = result_array("SELECT * FROM " . DATABASE_TABLE_PREFIX . "type_{$type} WHERE id={$id}");
	foreach($type_data['contents'] as $content_name => $content_data) {
		if(is_custom_type($content_data['type'])) {
			for($i = 0; $i < count($db_data); $i++) {
				$ids = explode(',', $db_data[$i][$content_name]);
				$values = array();
				foreach($ids as $id) {
					array_push($values, get_custom_type_content($content_data['type'], $id));
				}
				$db_data[$i][$content_name] = $values;
			}
		}
	}
	return $db_data;
}
