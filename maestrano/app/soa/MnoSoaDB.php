<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaDB extends MnoSoaBaseDB {
    
    /**
    * Update identifier map table
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    * @param	string	mno_id                  Maestrano entity identifier
    * @param	string	mno_entity_name         Maestrano entity name
    *
    * @return 	boolean Record inserted
    */
            
    public function addIdMapEntry($local_id, $local_entity_name, $mno_id, $mno_entity_name) {	
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
	// Fetch record
	$query = "INSERT INTO mno_id_map (mno_entity_guid, mno_entity_name, app_entity_id, app_entity_name, db_timestamp) VALUES (?,?,?,?,UTC_TIMESTAMP)";	
        $result = $this->_db->pquery($query, array($mno_id, strtoupper($mno_entity_name), $local_id, strtoupper($local_entity_name)));
        $this->_log->debug("addIdMapEntry query = ".$query);

        if ($this->_db->num_rows($result) > 0) {
            $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " return true");
            return true;
        } else {
            $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " return false");
            return false;
        }
    }
    
    /**
    * Get Maestrano GUID when provided with a local identifier
    * @param  	string 	local_id                Local entity identifier
    * @param    string  local_entity_name       Local entity name
    *
    * @return 	boolean Record found	
    */
    
    public function getMnoIdByLocalIdName($localId, $localEntityName)
    {
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
        $mno_entity = null;
        
	// Fetch record
	$query = "SELECT mno_entity_guid, mno_entity_name, deleted_flag from mno_id_map where app_entity_id=? and app_entity_name=?";
        $result = $this->_db->pquery($query, array($localId, strtoupper($localEntityName)));
        
	// Return id value
	if ($this->_db->num_rows($result) > 0) {
            $mno_entity_guid = trim($this->_db->query_result($result,0,"mno_entity_guid"));
            $mno_entity_name = trim($this->_db->query_result($result,0,"mno_entity_name"));
            $deleted_flag = trim($this->_db->query_result($result,0,"deleted_flag"));
            
            if (!empty($mno_entity_guid) && !empty($mno_entity_name)) {
                $mno_entity = (object) array (
                    "_id" => $mno_entity_guid,
                    "_entity" => $mno_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
        
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . "returning mno_entity = ".json_encode($mno_entity));
	return $mno_entity;
    }
    
    public function getLocalIdByMnoIdName($mnoId, $mnoEntityName)
    {
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
	$local_entity = null;
        
	// Fetch record
	$query = "SELECT app_entity_id, app_entity_name, deleted_flag from mno_id_map where mno_entity_guid=? and mno_entity_name=?";
	$result = $this->_db->pquery($query, array($mnoId, strtoupper($mnoEntityName)));
        
	// Return id value
	if ($this->_db->num_rows($result) > 0) {
            $app_entity_id = trim($this->_db->query_result($result,0,"app_entity_id"));
            $app_entity_name = trim($this->_db->query_result($result,0,"app_entity_name"));
            $deleted_flag = trim($this->_db->query_result($result,0,"deleted_flag"));
            
            if (!empty($app_entity_id) && !empty($app_entity_name)) {
                $local_entity = (object) array (
                    "_id" => $app_entity_id,
                    "_entity" => $app_entity_name,
                    "_deleted_flag" => $deleted_flag
                );
            }
	}
	
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . "returning mno_entity = ".json_encode($local_entity));
	return $local_entity;
    }
    
    public function deleteIdMapEntry($localId, $localEntityName) 
    {
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
        // Logically delete record
        $query = "UPDATE mno_id_map SET deleted_flag=1 WHERE app_entity_id=? and app_entity_name=?";
        $result = $this->_db->pquery($query, array($localId, strtoupper($localEntityName)));
        
        if ($this->_db->num_rows($result) > 0) {
            $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " return true");
            return true;
        } else {
            $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " return false");
            return false;
        }
    }
}
