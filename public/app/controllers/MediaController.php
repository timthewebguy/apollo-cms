<?php 
class MediaController {

	function __construct() {
		require_once MODELS . '/MediaObject_model.php';
	}

	function upload() {

		header('Content-Type:application/json');

		if(!empty($_FILES['uploaded']['name'])) {

			foreach($_FILES['uploaded']['name'] as $position => $file_name) {

				if(move_uploaded_file($_FILES['uploaded']['tmp_name'][$position], UPLOADS . '/' . $file_name)) {

					$ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$name = basename($file_name, '.' . $ext);
					$id = db_query("INSERT INTO " . MEDIA_TABLE . " VALUES (NULL, '{$name}', '{$ext}', '/app/uploads/{$file_name}')");
					$data = result_array("SELECT * FROM " . MEDIA_TABLE . " WHERE id={$id}")[0];
					$media = new MediaObject($data['media_name'], $data['media_ext'], $data['media_abs_path'], $data['id']);
					include(VIEWS . '/mediaBrowser/mediaObject_view.php');
				}
			}
		}
	}


	function delete() {

		$path = result_array("SELECT media_abs_path FROM " . MEDIA_TABLE . " WHERE id={$_POST['delete_id']}")[0]['media_abs_path'];

		$path = str_replace('/app', '..', $path);

		$success = unlink($path);

		echo $success;

		db_query("DELETE FROM " . MEDIA_TABLE . " WHERE id={$_POST['delete_id']}");
	}

	public function fetch_all_media() {

		$media = result_array("SELECT * FROM " . MEDIA_TABLE);
		$media_objects = array();
		
		foreach($media as $media_data) {
			array_push($media_objects, new MediaObject($media_data['media_name'], $media_data['media_ext'], $media_data['media_abs_path'], $media_data['id']));
		}
		return $media_objects;
	}


	function load() {

		$all_media = MediaController::fetch_all_media();

		include_once VIEWS . '/mediaBrowser/media_view.php';

	}
	
}
