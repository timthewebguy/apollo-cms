<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}
class MediaController {

	function __construct() {
		require_once MODELS . '/MediaObject.php';
	}

	function upload() {

		header('Content-Type:application/json');

		if(!empty($_FILES['uploaded']['name'])) {

			foreach($_FILES['uploaded']['name'] as $position => $file_name) {

				if(move_uploaded_file($_FILES['uploaded']['tmp_name'][$position], UPLOADS . '/' . $file_name)) {

					$ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$name = basename($file_name, '.' . $ext);
					$guid = DB::Guid();
					$id = DB::Query("INSERT INTO " . MEDIA_TABLE . " VALUES (NULL, '{$guid}', '/app/uploads/{$file_name}', '{$ext}',  '{$name}')");
					$data = DB::ResultArray("SELECT * FROM " . MEDIA_TABLE . " WHERE id={$id}")[0];
					$media = new MediaObject($data['name'], $data['guid'], $data['extension'], $data['path'], $data['id']);
					include(VIEWS . '/mediaBrowser/mediaObject_view.php');

				}
			}
		}
	}


	function delete() {

		$path = DB::ResultArray("SELECT path FROM " . MEDIA_TABLE . " WHERE id={$_POST['delete_id']}")[0]['media_abs_path'];

		$path = str_replace('/app', '..', $path);

		$success = unlink($path);

		echo $success;

		DB::Query("DELETE FROM " . MEDIA_TABLE . " WHERE id={$_POST['delete_id']}");
	}

	public function fetch_all_media() {

		$media = DB::ResultArray("SELECT * FROM " . MEDIA_TABLE);
		$media_objects = array();

		foreach($media as $media_data) {
			$media_objects[] = new MediaObject($media_data['name'], $media_data['guid'], $media_data['extension'], $media_data['path'], $media_data['id']);
		}
		return $media_objects;
	}


	function load() {

		$all_media = MediaController::fetch_all_media();

		include_once VIEWS . '/mediaBrowser/media_view.php';

	}

}
