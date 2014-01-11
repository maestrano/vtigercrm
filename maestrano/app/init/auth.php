<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

//-----------------------------------------------
// Load Libraries & Settings
//-----------------------------------------------
require MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require MAESTRANO_ROOT . '/app/init/_config_loader.php'; //set $mno_settings variable

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', '../../../');
require APP_DIR . 'modules/Users/Users.php ';

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $conn variable then it will
// automatically be passed to the MnoSsoUser object
// as a database connection object
// if (!empty($db_name) and !empty($db_user)) {
//     $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
// }


