<?php
	DEFINE('BASEPATH', $_SERVER['DOCUMENT_ROOT']);

	require_once('app/system/config.php');
	require_once('app/system/includes.php');

	require_once('app/system/installed.php');
	if(!INSTALLED) {
		require('app/system/install.php');
	}

	$class_name = (ucfirst(strtolower(isset($_GET['controller']) ? $_GET['controller'] : 'dashboard')) . 'Controller');
	if(!class_exists($class_name)) {
		show_404();
	}
	$class = new $class_name;

	$method = strtolower(isset($_GET['method']) ? $_GET['method'] : 'load');
	if(!method_exists($class, $method)) {
		show_404();
	}

	if(isset($_GET['parameter']) && isset($_GET['parameter_b'])) {
		$class->$method($_GET['parameter'], $_GET['parameter_b']);
	} elseif(isset($_GET['parameter'])) {
		$class->$method($_GET['parameter']);
	} else {
		$class->$method();
	}
