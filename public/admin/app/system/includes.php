<?php if(!DEFINED('BASEPATH')) {Die('No Script Access!');}

//YAML parser
require_once(APP_PATH . '/libraries/spyc.php');

//database
require_once(APP_PATH . '/system/database.php');

//functions
require_once(APP_PATH . '/system/functions.php');

//Initialize the database
//DB::Init();

//load the controller classes
$controllers = array_diff(scandir(CONTROLLERS, 1), ['..', '.']);
foreach ($controllers as $controller) {
	require_once (CONTROLLERS . '/' . $controller);
}
