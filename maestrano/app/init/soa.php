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


// Add custom related_to fields
require_once APP_DIR . '/vtlib/Vtiger/Utils.php';

createRelateToField('Accounts', 'Contacts', 'Main Contact Person', 'main_contact', 'vtiger_accountscf');
createRelateToField('Potentials', 'Contacts', 'Main Point of Contact', 'main_point_contact', 'vtiger_potentialscf');
createRelateToField('Potentials', 'Event', 'Event', 'lead_event', 'vtiger_potentialscf');
createRelateToField('Project', 'Event', 'Event', 'project_event', 'vtiger_projectcf');
createRelateToField('Project', 'Leads', 'Lead', 'project_lead', 'vtiger_projectcf');
createRelateToField('Project', 'Potentials', 'Sponsorship', 'project_potential', 'vtiger_projectcf');


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