<?php
	DEFINE('BASEPATH', $_SERVER['DOCUMENT_ROOT']);

	require_once('app/system/config.php');
	require_once('app/system/includes.php');

	/*$class_name = (ucfirst(strtolower(isset($_GET['controller']) ? $_GET['controller'] : 'dashboard')) . 'Controller');
	if(!class_exists($class_name)) {
		show_404();
	}
	$class = new $class_name;

	$method = strtolower(isset($_GET['method']) ? $_GET['method'] : 'load');
	if(!method_exists($class, $method)) {
		show_404();
	}

	if(isset($_GET['parameter'])) {
		$class->$method($_GET['parameter']);
	} else {
		$class->$method();
	}*/

	//DB::Init();


	//$type = TypeController::CreateType('Button', 'compound', '1234', ['text'=>['type'=>'text', 'description'=>'button text'], 'link'=>['type'=>'text', 'description'=>'button link']]);
	$type = TypeController::RetrieveType(['slug'=>'button', 'guid_prefix'=>'1234']);

	//$group = GroupController::CreateGroup('Home', '9999');
	//$group = GroupController::RetrieveGroup(['slug'=>'home']);

	$data = DataController::CreateData('button', 3);
