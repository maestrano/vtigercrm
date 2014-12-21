<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require_once MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);
require_once APP_DIR . '/modules/Users/Users.php';
require_once APP_DIR . 'modules/Users/LoginHistory.php';

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


