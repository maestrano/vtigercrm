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
define("APP_DIR", realpath(MAESTRANO_ROOT . '/../'));
chdir(APP_DIR);
require_once APP_DIR . '/modules/Users/Users.php';
require_once APP_DIR . '/modules/Accounts/Accounts.php';
require_once APP_DIR . '/modules/Contacts/Contacts.php';

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction

