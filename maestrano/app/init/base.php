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
require_once MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require_once MAESTRANO_ROOT . '/app/init/_config_loader.php'; //configure MaestranoService

  
