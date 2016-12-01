<?php 

class PageController {

	public function GetFirstPageName() {
		$files = array_diff(scandir(PAGES, 1), ['..', '.']);
		$response = '';

		foreach($files as $file) {
			$response = basename($file, '.yml');
			break;
		}

		unset($file);

		return $response;
	}

	public function GetPages() {
		require_once MODELS . '/Page_model.php';
		
		$files = array_diff(scandir(PAGES, 1), ['..', '.']);
		$pages = [];
		
		foreach($files as $file) {
			if($file != '_TYPES.yml') {
				$pages[basename($file, '.yml')] = spyc_load_file(PAGES . '/' . $file);
			}
		}
		
		$response = [];

		foreach ($pages as $name => $contents) {
			$response[] = new Page($name, $contents);
		}

		return $response;
	}

	public function GetPage($target_page) {

		$pages = PageController::GetPages();
		foreach($pages as $page) {
			if($page->name == $target_page) {
				return $page;
			}
		}
		return null;
	}

}
