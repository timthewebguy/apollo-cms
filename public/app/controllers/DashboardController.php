<?php

class DashboardController {

	function __construct() {
		
	}

	function load() {
		$page = PageController::GetFirstPageName();

		$this->page($page);
	}


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
