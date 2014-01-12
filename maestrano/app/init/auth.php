<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

//-----------------------------------------------
// Load Libraries & Settings
//-----------------------------------------------
require MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require MAESTRANO_ROOT . '/app/init/_config_loader.php'; //set $mno_settings variable

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
//define('APP_DIR', '../../../');
define('APP_DIR', '/Users/Arnaud/Sites/apps-dev/app-vtigercrm');
chdir(APP_DIR);
require 'modules/Users/Users.php';

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
$opts = array();

// Set database connection
$opts['db_connection'] = PearDatabase::getInstance();

// Set application unique key
global $application_unique_key;
$opts['app_unique_key'] = $application_unique_key;


