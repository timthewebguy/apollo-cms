<?php 

//YAML parser
require_once(APP_PATH . '/libraries/spyc.php');

//page and type functions
require_once(APP_PATH . '/system/page_functions.php');
require_once(APP_PATH . '/system/type_functions.php');

//database
require_once(APP_PATH . '/system/database.php');

//orm
require_once(APP_PATH . '/system/orm.php');

//functions
require_once(APP_PATH . '/system/functions.php');

//Initialize the database
init_db();

//load the controller classes
$controllers = array_diff(scandir(CONTROLLERS, 1), ['..', '.']);
foreach ($controllers as $controller) {
	require_once (CONTROLLERS . '/' . $controller);
}
