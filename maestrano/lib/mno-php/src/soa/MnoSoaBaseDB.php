<?php

/**
 * Mno DB Map Interface
 */
class MnoSoaBaseDB {
    protected $_db;
    protected $_log;
    
    public function __construct($db, $log)
    {
	$this->_db = $db;
        $this->_log = $log;
    }
    
    public function addIdMapEntry($local_id, $local_entity_name, $mno_id, $mno_entity_name) 
    {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoDB class!');
    }
    
    public function getMnoIdByLocalIdName($local_id, $local_entity_name)
    {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoDB class!');
    }
    
    public function getLocalIdByMnoIdName($mno_id, $mno_entity_name)
    {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoDB class!');
    }
    
    public function deleteIdMapEntry($local_id, $local_entity_name)
    {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoDB class!');
    }
}

?>