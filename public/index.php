<?php
	DEFINE('BASEPATH', $_SERVER['DOCUMENT_ROOT']);

	require_once('app/system/config.php');
	require_once('app/system/includes.php');

	//require_once('app/system/installed.php');
	//if(!INSTALLED) {
	//	require('app/system/install.php');
	//}

	//TypeController::LoadTypes();

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
	$button_type = TypeController::RetrieveType(['slug'=>'button']);
	$text_field = $button_type->getField('text');
	$text_field->field_min = 1;
	$text_field->field_max = 1;
	$button_type->updateField($text_field);
	//$button_type->addField(new CompoundTypeField('button', 'color', 'text', 'color of the button', 1, 1));
	//$button_type->removeField('color');

	//TypeController::CreateType('Hero Slide', 'compound', 'HS', ['text'=>['type'=>'text', 'description'=>'Text to be displayed on the hero slide'], 'image'=>['type'=>'media', 'description'=>'Background image of the hero slide'], 'ctas'=>['type'=>'button', 'description'=>'CTA buttons for the hero slide. Up to three can be added.', 'min'=>1, 'max'=>3]]);

	//$group = GroupController::CreateGroup('Home', 'HOME');
	//$group = GroupController::RetrieveGroup(['slug'=>'home']);

	//DataController::CreateData('button', 2, 5);
	//$buttons = DataController::RetrieveData(['guid'=>'DATA--3B3C19EE-5271-4185-ACF9-39CFCE7C8AC2']);
	//var_dump($buttons);
	//$buttons->AddValue();
	//$buttons->value[2]['text']->value = 'Testing';
	//$buttons->Update();
	//$buttons->RemoveValue(2);


	//$content = ContentController::CreateContent('home', 'Buttons', $buttons, 'Buttons on the home page!');

	//$buttons->Swap(0, 1);
	//$buttons->Delete();
