<?php

class DashboardController {

	function __construct() {
		
	}

	function load() {
		//Get the pages
		$current_page = PageController::GetFirstPageName();

		$this->page($current_page);
	}

	/*


	function draw_content_editor($name, $data, $page_name) {

		$view = $this->get_view($data['type']);

		$content = get_content($page_name, $name);

		include (VIEWS . '/contentEditor/contentEditor.php');

	}

	function draw_custom_content($parent_name, $type_data, $c_data) {
		foreach($type_data['contents'] as $content_name => $data) {
			echo $data['type'];
		  $view = $this->get_view($data['type']);

		  $name = $parent_name . '-' . $content_name;


		  $content = [];
		  
		  for($i = 0; $i < count($c_data[$content_name]); $i++) {
		  	array_push($content, []);
		  	$content[$i]['index'] = $i;
		  	$content[$i]['content_value'] = $c_data[$content_name];
		  	$content[$i]['content_type'] = $data['type'];
		  }

		  //var_dump($c_data);


		  include (VIEWS . '/contentEditor/contentEditor.php');

		  //echo '<br><br>';
		}
	}*/

	function draw_editors($page) {
		$cc = new ContentController();
		foreach($page->contents as $name => $data) {
			$content = ContentController::GetContent($name, $page->name, $data);
			$cc->render($content);
		}
	}


	function page($current_page = '') {

		//In case we went to /dashboard/page without a page specified
		if($current_page == '') {
			$this->load();
			return;
		}

		//Get the pages
		$pages = PageController::GetPages();

		//sync the database before we load anything (just in case)
		DB::Sync();

		//Load the views
		include(APP_PATH . '/views/header.php');

		include(APP_PATH . '/views/dashboard_view.php');

		$mc = new MediaController();
		$mc->load();

		include(APP_PATH . '/views/footer.php');/**/
	}

}
