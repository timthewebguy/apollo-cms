<?php
	//Paths for directories
	DEFINE('APP_PATH', BASEPATH . '/app');
	DEFINE('SERVERPATH', 'http://' . $_SERVER['HTTP_HOST']);
	
	DEFINE('PAGES', APP_PATH . '/pages');
	DEFINE('VIEWS', APP_PATH .'/views');
	DEFINE('MODELS', APP_PATH . '/models');
	DEFINE('CONTROLLERS', APP_PATH . '/controllers');
	DEFINE('UPLOADS', APP_PATH . '/uploads');



	//Database Credentials
	DEFINE('DATABASE_SERVER', 'mysql');
	DEFINE('DATABASE_NAME', 'apollo-db');
	DEFINE('DATABASE_USER', 'root');
	DEFINE('DATABASE_PASSWORD', 'root');

	DEFINE('DATABASE_TABLE_PREFIX', 'ap_');

	DEFINE('CONTENT_TABLE', DATABASE_TABLE_PREFIX . 'content');
	DEFINE('MEDIA_TABLE', DATABASE_TABLE_PREFIX . 'media');
	DEFINE('TYPES_TABLE', DATABASE_TABLE_PREFIX . 'types');
