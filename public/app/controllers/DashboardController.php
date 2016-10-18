<?php

class DashboardController {

	function __construct() {
		
	}

	function load() {
		//Get the pages
		$pages = get_pages();

		//Save the first page's name
		foreach($pages as $key => $value) break;

		//Set the current page
		$current_page = $key;
		if(isset($_GET['parameter_a'])) {
			if(get_page($_GET['parameter_a']) != null) {
				$current_page = $_GET['parameter_a'];
			}
		}

		//Remove the $key binding, because why not
		unset($key);

		$this->page($current_page);
	}


	function draw_content_editor($name, $data, $page_name) {

		/*switch($data['type']) {
			case 'text':
				$view =  VIEWS . '/contentEditor/contentEditor_text.php';
				break;
			case 'wysiwyg':
				$view =  VIEWS . '/contentEditor/contentEditor_wysiwyg.php';
				break;
			case 'image':
				$view =  VIEWS . '/contentEditor/contentEditor_image.php';
				break;
			default:
				$view = VIEWS . '/contentEditor/contentEditor_custom.php';
				break;
		}

		include (VIEWS . '/contentEditor/contentEditor.php');*/
		$content = get_content($page_name, $name);
		
	}

	function page($current_page = '') {

		//In case we went to /dashboard/page without a page specified
		if($current_page == '') {
			$this->load();
			return;
		}

		//Get the pages
		$pages = get_pages();

		//sync the database before we load anything (just in case)
		sync_db();

		//Load the views
		include(APP_PATH . '/views/header.php');

		include(APP_PATH . '/views/dashboard_view.php');

		$mc = new MediaController();
		$mc->load();

		include(APP_PATH . '/views/footer.php');/**/
	}

}
