<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}


class DB {

	//creates a connection to the system database
	public function Connect() {
		return new mysqli(DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
	}



	//executes a query to the systems database, closes the connection, and returns the result
	public function Query($sql) {
		$conn = DB::Connect();
		$result = $conn->query($sql);
		$response = ($result->num_rows > 0) ? $result : $conn->insert_id;
		$conn->close();
		return $response;
	}


	//returns an associative array of the mysql query result
	public function ResultArray($sql) {
		$result = DB::Query($sql);
		$array = Array();
		if($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				array_push($array, $row);
			}
		}
		return $array;
	}



	public function GUID() {
		if (function_exists('com_create_guid') === true) {
	    return trim(com_create_guid(), '{}');
	  }
	  return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

}
