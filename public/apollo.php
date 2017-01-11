<?php
/*
 * APOLLO CMS
 * Client Side API
 */

DEFINE('BASEPATH', $_SERVER['DOCUMENT_ROOT']);

//Adjust the path here to the proper config.php
require_once('admin/app/system/config.php');

require_once(APP_PATH . '/system/includes.php');

//Shorthand functions ---------------------------------------------
function GetGroup($slug) { return GroupController::RetrievEgroup(['slug'=>$slug]); }
function GetContentByGroup($group) { return ContentController::RetrieveContent(['content_group'=>$group->slug]); }
