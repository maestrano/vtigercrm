<?php

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../'));
}

require_once(MAESTRANO_ROOT . '/app/init/soa.php');

$maestrano = MaestranoService::getInstance();

if ($maestrano->isSoaEnabled() and $maestrano->getSoaUrl()) {
    $filepath = MAESTRANO_ROOT . '/var/_data_sequence';
    
    if (file_exists($filepath)) {
        $timestamp = trim(file_get_contents($filepath));
        $current_timestamp = round(microtime(true) * 1000);

        if (!empty($timestamp)) {
            $mno_entity = new MnoSoaEntity($opts['db_connection'], new MnoSoaBaseLogger());
            $mno_entity->getUpdates($timestamp);
        }
    }
    file_put_contents($filepath, $current_timestamp);
}

?>
