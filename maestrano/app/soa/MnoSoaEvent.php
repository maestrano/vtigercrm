<?php

/**
 * Mno Event Class
 */
class MnoSoaEvent extends MnoSoaBaseEvent {
  protected $_local_entity_name = "EVENT";

  protected function pushEvent() {
    $this->_log->debug("start pushEvent " . json_encode($this->_local_entity->column_fields));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;

    if(isset($this->_local_entity->column_fields['tkseventname'])) { 
      $this->_name = $this->push_set_or_delete_value($this->_local_entity->column_fields['tkseventname']);
    }

    $this->_log->debug("after pushEvent");
  }

  protected function pullEvent() {
    $this->_log->debug("start " . __FUNCTION__ . " for " . json_encode($this->_id));
    
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);
      $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
      if ($this->isValidIdentifier($local_id)) {
        $this->_local_entity = CRMEntity::getInstance("Event");
        $this->_local_entity->retrieve_entity_info($local_id->_id, "Event");
        vtlib_setup_modulevars("Event", $this->_local_entity);
        $this->_local_entity->id = $local_id->_id;
        $this->_local_entity->mode = 'edit';
        $status = constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $status = constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $this->_local_entity = new Event();
        $this->_local_entity->column_fields['assigned_user_id'] = "1";
        $this->_local_entity->column_fields['salesorder_no'] = 'AUTO GEN ON SAVE';
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    $this->_local_entity->column_fields['tkseventname'] = $this->pull_set_or_delete_value($this->_name);

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    
    $this->_local_entity->save("Event", '', $push_to_maestrano);

    // Map event ID
    if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
      $local_entity_id = $this->getLocalEntityIdentifier();
      $mno_entity_id = $this->_id;
      $this->addIdMapEntry($local_entity_id, $mno_entity_id);
    }
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->id;
  }
}

?>