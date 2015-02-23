<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../'));
}

// Run init scripts
$init_script_file = MAESTRANO_ROOT . '/var/_init_scripts';
$init_script_content = file_get_contents($init_script_file);
$script_dirs = MAESTRANO_ROOT . '/app/init/scripts';
$script_files = array_diff(scandir($script_dirs), array('..', '.'));
// Iterate over already loaded scripts
foreach ($script_files as $script_file) {
$contained = strpos($init_script_content, $script_file);
  if($contained !== 0) {
    // Run script file
    require_once($script_dirs . "/" . $script_file);
    file_put_contents($init_script_file, $script_file . "\n", FILE_APPEND);
  }
}

chdir(MAESTRANO_ROOT);
require_once(MAESTRANO_ROOT . '/app/init/soa.php');

// Fetch Connec! updates
$maestrano = MaestranoService::getInstance();
if ($maestrano->isSoaEnabled() and $maestrano->getSoaUrl()) {
  $filepath = MAESTRANO_ROOT . '/var/_data_sequence';
  $status = false;
  
  if (file_exists($filepath)) {
    $timestamp = trim(file_get_contents($filepath));
    $current_timestamp = round(microtime(true) * 1000);
    
    if (empty($timestamp)) { $timestamp = 0; } 

    $mno_entity = new MnoSoaEntity($opts['db_connection'], new MnoSoaBaseLogger());
    $status = $mno_entity->getUpdates($timestamp);
  }
  
  if ($status) {
    file_put_contents($filepath, $current_timestamp);
  }
}

// Add custom related_to fields
require_once APP_DIR . '/vtlib/Vtiger/Utils.php';

createRelateToField('Accounts', 'Contacts', 'Main Contact Person', 'main_contact', 'vtiger_accountscf');
createRelateToField('Potentials', 'Contacts', 'Main Point of Contact', 'main_point_contact', 'vtiger_potentialscf');
createRelateToField('Potentials', 'Event', 'Event', 'lead_event', 'vtiger_potentialscf');
createRelateToField('Project', 'Event', 'Event', 'project_event', 'vtiger_projectcf');
createRelateToField('Project', 'Leads', 'Lead', 'project_lead', 'vtiger_projectcf');
createRelateToField('Project', 'Potentials', 'Sponsorship', 'project_potential', 'vtiger_projectcf');

createRelateList('Event', 'Contacts', 'Contacts');
createRelateList('Event', 'Tickets', 'Tickets');
createRelateList('Tickets', 'Contacts', 'Contacts');

function createRelateToField($tabname, $relatesTo, $fieldlabel, $columnname, $tablename) {
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
  include_once('vtlib/Vtiger/Module.php');
  $moduleInstance = Vtiger_Module::getInstance($tabname);
  $contactsModule = Vtiger_Module::getInstance($targetModule);
  $relationLabel  = $relationLabel;
  $moduleInstance->unsetRelatedList($contactsModule);
  $moduleInstance->setRelatedList($contactsModule, $relationLabel, Array('SELECT'));
}

?>
