<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../'));
}

require_once(MAESTRANO_ROOT . '/app/init/soa.php');

// Run init scripts
$init_script_file = MAESTRANO_ROOT . '/var/_init_scripts';
$init_script_content = file_get_contents($init_script_file);
$script_dirs = MAESTRANO_ROOT . '/app/init/scripts';
$script_files = array_diff(scandir($script_dirs), array('..', '.'));
// Iterate over already loaded scripts
foreach ($script_files as $script_file) {
$contained = strpos($init_script_content, $script_file);
error_log("FILE CONTAINED? $contained => " . json_encode($contained));
  if($contained !== 0) {
    // Run script file
    require_once($script_dirs . "/" . $script_file);
    file_put_contents($init_script_file, $script_file . "\n", FILE_APPEND);
  }
}

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

?>
