<?php 

function get_pages() {
	//Get files
	$files = array_diff(scandir(PAGES, 1), ['..', '.']);
	//Store the pages
	$pages = array();
	//Loop through files
	foreach($files as $file) {
		//Make sure we don't have the types file
		if($file != 'TYPES.yml') {
			//Add the YAML to the pages array
			$pages[basename($file, '.yml')] = spyc_load_file(PAGES . '/' . $file);
		}
	}
	//Return the pages
	return $pages;
}

function get_page($target_page) {
	$pages = get_pages();
	foreach($pages as $page => $page_data) {
		if($page == $target_page) {
			return $page_data;
		}
	}
	return null;
}
