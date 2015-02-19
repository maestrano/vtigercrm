<?php

/**
 * Mno Ticket Class
 */
class MnoSoaTicket extends MnoSoaBaseTicket {
  protected $_local_entity_name = "TICKET";

  public function __construct($event_id, $db, $log) {
    parent::__construct($db, $log);
    $this->_event_id = $event_id;
  }

  protected function pushTicket() {
    $this->_log->debug("start pushTicket " . json_encode($this->_local_entity->column_fields));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;

    if(isset($this->_local_entity->column_fields['tksticketname'])) { 
      $this->_name = $this->push_set_or_delete_value($this->_local_entity->column_fields['tksticketname']);
    }

    $this->_log->debug("after pushTicket");
  }

  protected function pullTicket() {
    $this->_log->debug("start " . __FUNCTION__ . " for " . json_encode($this->_id));
    
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);
      $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
      if ($this->isValidIdentifier($local_id)) {
        $this->_local_entity = CRMEntity::getInstance("Tickets");
        $this->_local_entity->retrieve_entity_info($local_id->_id, "Tickets");
        vtlib_setup_modulevars("Tickets", $this->_local_entity);
        $this->_local_entity->id = $local_id->_id;
        $this->_local_entity->mode = 'edit';
        $status = constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $status = constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $this->_local_entity = new Tickets();
        $this->_local_entity->column_fields['assigned_user_id'] = "1";
        $this->_local_entity->column_fields['salesorder_no'] = 'AUTO GEN ON SAVE';
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    if($this->_event_id) { $this->_local_entity->column_fields['tksevent'] = $this->pull_set_or_delete_value($this->_event_id); }
    if($this->_name) { $this->_local_entity->column_fields['tksticketname'] = $this->pull_set_or_delete_value($this->_name); }
    if($this->_description) { $this->_local_entity->column_fields['tksdescription'] = $this->pull_set_or_delete_value($this->_description); }
    if($this->_quantity_total) { $this->_local_entity->column_fields['tksamountavailable'] = $this->pull_set_or_delete_value($this->_quantity_total); }
    if($this->_cost && $this->_cost->price) { $this->_local_entity->column_fields['tksticketprice'] = floatval($this->pull_set_or_delete_value($this->_cost->price)); }
    if($this->_sales_start && $this->_cost->price) { $this->_local_entity->column_fields['tksstartdate'] = date('Y-m-d', $this->_sales_start); }
    if($this->_sales_end && $this->_cost->price) { $this->_local_entity->column_fields['tksenddate'] = date('Y-m-d', $this->_sales_end); }

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    $this->_local_entity->save("Tickets", '', $push_to_maestrano);
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->column_fields['record_id'];
  }
}

?>