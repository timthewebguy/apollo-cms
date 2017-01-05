<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

class DashboardController {

	function __construct() {

	}

	function load() {
		$this->group(GroupController::GetFirstGroupName());
	}


	function draw_editors($group) {
		$cc = new ContentController();
		$group_content = ContentController::RetrieveContent(['content_group'=>$group->slug], null, true);
		foreach($group_content as $content) {
			$cc->render($content);
		}
	}


	function group($current_group = '', $message = '') {

		//In case we went to /dashboard/page without a page specified
		if($current_group == '') {
			$this->load();
			return;
		}

		//Get the pages
		$groups = GroupController::RetrieveGroup(null, "slug", true);

		//Load the views
		include(APP_PATH . '/views/header.php');

		include(APP_PATH . '/views/dashboard_view.php');

		$mc = new MediaController();
		$mc->load();

		include(APP_PATH . '/views/footer.php');/**/
	}

}
