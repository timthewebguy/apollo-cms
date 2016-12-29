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


	//$type = TypeController::CreateType('Button', 'compound', 'BTN', ['text'=>['type'=>'text', 'description'=>'button text'], 'link'=>['type'=>'text', 'description'=>'button link']]);
	//$type = TypeController::RetrieveType(['slug'=>'button']);

	//TypeController::CreateType('Hero Slide', 'compound', 'HS', ['text'=>['type'=>'text', 'description'=>'Text to be displayed on the hero slide'], 'image'=>['type'=>'media', 'description'=>'Background image of the hero slide'], 'ctas'=>['type'=>'button', 'description'=>'CTA buttons for the hero slide. Up to three can be added.', 'min'=>1, 'max'=>3]]);

	//$group = GroupController::CreateGroup('Home', 'HOME');
	//$group = GroupController::RetrieveGroup(['slug'=>'home']);

	//$data = DataController::CreateData('button', 3);
	$buttons = DataController::RetrieveData(['type'=>'button']);
	$buttons->value[0]['text']->value = 'Hello World';
	$buttons->value[0]['link']->value = 'Hello World Link Here!';
	$buttons->Update();
