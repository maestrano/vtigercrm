<?php
  
  if (!defined('ROOT_PATH')) {
    define("ROOT_PATH", realpath(dirname(__FILE__) . '/../../../../'));
  }
  chdir(ROOT_PATH);

  error_log("Installation of the Events and Tickets modules");

  // Install Event and Tickets modules
  require_once('include/utils/utils.php');
  $package = new Vtiger_Package();
  $package->import(MAESTRANO_ROOT . '/modules/Event.zip', true);
  $package->import(MAESTRANO_ROOT . '/modules/Tickets.zip', true);

  global $adb;
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Event', 'EV' ,1 ,1 ,1));
  $adb->pquery("INSERT INTO vtiger_modentity_num (num_id, semodule, prefix, start_id, cur_id, active) VALUES (?,?,?,?,?,?)",array($adb->getUniqueId("vtiger_modentity_num"), 'Tickets', 'TI' ,1 ,1 ,1));


  // Add custom related_to fields
  require_once('vtlib/Vtiger/Utils.php');
  include_once('vtlib/Vtiger/Module.php');

  createRelateToField('Potentials', 'Event', 'Event', 'lead_event', 'vtiger_potentialscf');
  createRelateToField('Project', 'Event', 'Event', 'project_event', 'vtiger_projectcf');
  createRelateList('Event', 'Contacts', 'Contacts');
  createRelateList('Event', 'Tickets', 'Tickets');
  createRelateList('Tickets', 'Contacts', 'Contacts');
  createRelateList('Contacts', 'Event', 'Event');
  createRelateList('Contacts', 'Tickets', 'Tickets');
  createRelateList('Leads', 'Event', 'Event');
  createRelateList('Leads', 'Tickets', 'Tickets');
  
  function createRelateToField($tabname, $relatesTo, $fieldlabel, $columnname, $tablename) {
    // Unlink related module is already set
    $moduleObject = Vtiger_Module::getInstance($tabname);
    $targetModuleInstance = Vtiger_Module::getInstance($relatesTo);
    $fieldObject = Vtiger_Field::getInstance($fieldlabel, $moduleObject);
    if($fieldObject) { $fieldObject->unsetRelatedModules($targetModuleInstance); }

    $adb = PearDatabase::getInstance();
    $log = new MnoSoaBaseLogger();
    $tabid = getTabid($tabname);
    $sql = "SELECT fieldid FROM vtiger_field WHERE tabid=? AND fieldlabel=?";
    $result = $adb->pquery($sql, array($tabid, $fieldlabel));
    $fieldid = $adb->query_result($result,0,"fieldid");
    
    if(isset($fieldid) && $fieldid > 0) {
      $log->debug("field $fieldlabel under tab $tabname already exists");
      Vtiger_Utils::ExecuteQuery("UPDATE vtiger_field SET uitype=10, typeofdata='V~O' WHERE fieldid=$fieldid");
    } else {
      $log->debug("creating field $fieldlabel under tab $tabname");
      $blockid = getBlockId($tabid,'LBL_CUSTOM_INFORMATION');
      $fieldid = $adb->getUniqueID("vtiger_field");
      $sequece = $adb->getUniqueID("vtiger_customfield_sequence");
      Vtiger_Utils::ExecuteQuery("insert into vtiger_field (tabid, fieldid, columnname, tablename, generatedtype, uitype, fieldname, fieldlabel, readonly, presence, maximumlength, sequence, block, displaytype, typeofdata, quickcreate, quickcreatesequence, masseditable, info_type) values (".$tabid.",".$fieldid.",'".$columnname."','".$tablename."',1,'10','".$columnname."','".$fieldlabel."',0,2,255,$sequece,$blockid,1,'V~O',1,1,2,'BAS')");
    }
    $sql = "SELECT count(*) as count FROM vtiger_fieldmodulerel WHERE fieldid=?";
    $result = $adb->pquery($sql, array($fieldid));
    $count = $adb->query_result($result,0,"count");
    
    if(isset($count) && $count > 0) {
      $log->debug("fieldmodulerel $fieldid already exists");
    } else {
      $log->debug("creating fieldmodulerel $fieldid");
      Vtiger_Utils::ExecuteQuery("insert into vtiger_fieldmodulerel (fieldid, module, relmodule, status, sequence) values ($fieldid, '$tabname', '$relatesTo', NULL, 0)");
      Vtiger_Utils::ExecuteQuery("ALTER TABLE $tablename ADD COLUMN $columnname VARCHAR(255)");
    }
  }

  function createRelateList($tabname, $targetModule, $relationLabel) {
    $moduleInstance = Vtiger_Module::getInstance($tabname);
    $targetModuleInstance = Vtiger_Module::getInstance($targetModule);
    $relationLabel = $relationLabel;
    $moduleInstance->unsetRelatedList($targetModuleInstance);
    $moduleInstance->setRelatedList($targetModuleInstance, $relationLabel, Array('SELECT'));
  }
?>
