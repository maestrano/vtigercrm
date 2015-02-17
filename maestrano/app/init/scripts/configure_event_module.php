<?php

  global $adb;
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Event', 'EV' ,1 ,1 ,1));
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Tickets', 'TI' ,1 ,1 ,1));

?>
