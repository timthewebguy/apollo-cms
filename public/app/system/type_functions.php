<?php 

function get_types() {
	//Return the types found in TYPES.yml
	return spyc_load_file(PAGES . '/TYPES.yml');

}

function get_type($target_type) {
	$types = get_types();

	foreach($types as $type => $type_data) {
		if($type == $target_type) {
			return $type_data;
		}
	}
	return null;
}

function is_custom_type($type) {
	return get_type($type) != null;
}
