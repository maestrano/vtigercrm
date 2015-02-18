<?php
  
  if (!defined('ROOT_PATH')) {
    define("ROOT_PATH", realpath(dirname(__FILE__) . '/../../../../'));
  }
  chdir(ROOT_PATH);

  // Install Event and Tickets modules
  require_once('include/utils/utils.php');
  $package = new Vtiger_Package();
  $package->import(MAESTRANO_ROOT . '/modules/Event.zip', true);
  $package->import(MAESTRANO_ROOT . '/modules/Tickets.zip', true);

  global $adb;
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Event', 'EV' ,1 ,1 ,1));
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Tickets', 'TI' ,1 ,1 ,1));

?>
