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
require_once APP_DIR . '/modules/Products/Products.php';
require_once APP_DIR . '/modules/Products/Products.php';
require_once APP_DIR . '/modules/Invoice/Invoice.php';
require_once APP_DIR . '/modules/SalesOrder/SalesOrder.php';
require_once APP_DIR . '/modules/Event/Event.php';
require_once APP_DIR . '/modules/Tickets/Tickets.php';
require_once APP_DIR . '/modules/Emails/mail.php';

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction

$opts = array();

// Set database connection
$opts['db_connection'] = PearDatabase::getInstance();

// Set default user for entities creation
global $current_user;
if(!isset($current_user->id)) {
	$current_user->id = "1";
}
