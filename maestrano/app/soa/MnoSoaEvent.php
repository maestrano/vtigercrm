<?php

if (!defined('APP_DIR')) {
  define("APP_DIR", realpath(dirname(__FILE__) . '/../../../'));
}
chdir(APP_DIR);
if(file_exists ('modules/Event/Event.php')) {
  require_once 'modules/Event/Event.php';
  require_once 'modules/Tickets/Tickets.php';
}
/**
 * Mno Event Class
 */
class MnoSoaEvent extends MnoSoaBaseEvent {
  protected $_local_entity_name = "EVENT";
  protected $_local_tickets = array();

  protected function pushEvent() {
    $this->_log->debug("start pushEvent " . json_encode($this->_local_entity->column_fields));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;

    if(isset($this->_local_entity->column_fields['tkseventname'])) { 
      $this->_name = $this->push_set_or_delete_value($this->_local_entity->column_fields['tkseventname']);
    }

    if(isset($this->_local_entity->column_fields['tksticketsavailable'])) { 
      $this->_capacity = $this->push_set_or_delete_value($this->_local_entity->column_fields['tksticketsavailable']);
    }

    if(isset($this->_local_entity->column_fields['tksdateofevent'])) {
      $this->_start_date = strtotime($this->push_set_or_delete_value($this->_local_entity->column_fields['tksdateofevent']));
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
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    if($this->_name) { $this->_local_entity->column_fields['tkseventname'] = $this->pull_set_or_delete_value($this->_name); }
    if($this->_start_date) { $this->_local_entity->column_fields['tksdateofevent'] = date('Y-m-d', $this->_start_date); }
    if($this->_capacity) { $this->_local_entity->column_fields['tksticketsavailable'] = $this->pull_set_or_delete_value($this->_capacity); }

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    $this->_local_entity->save("Event", '', $push_to_maestrano);

    // Force Event code if specified
    if($this->_code) {
      $sql = "UPDATE " . $this->_local_entity->table_name . " SET eventno = ? WHERE eventid = ?";
      $params = array($this->_code, $this->_local_entity->id);
      $this->_db->pquery($sql, $params);
    }
  }

  protected function pullTickets() {
    foreach ($this->_ticket_classes as $ticket_class) {
      $mno_ticket = new MnoSoaTicket($this->getLocalEntityIdentifier(), $this->_db, $this->_log);
      $mno_ticket->persist($ticket_class);
    }
  }

  protected function pushTickets() {
    $this->_ticket_classes = array();
    $this->_local_tickets = array();

    $ticket = new Tickets();
    $query = $ticket->getListQuery('Tickets', ' AND vtiger_tickets.tksevent = ' . $this->getLocalEntityIdentifier());
    $result = $this->_db->pquery($query);
    
    foreach ($result as $event_ticket) {     
      $ticket_id = $event_ticket['crmid'];

      $ticket = CRMEntity::getInstance("Tickets");
      $ticket->retrieve_entity_info($ticket_id, "Tickets");
      $ticket->id = $ticket_id;
      
      $mno_ticket = new MnoSoaTicket($this->getLocalEntityIdentifier(), $this->_db, $this->_log);
      $mno_ticket->_local_entity = $ticket;
      $ticket_hash = $mno_ticket->build();

      array_push($this->_ticket_classes, json_decode($ticket_hash));
      array_push($this->_local_tickets, $ticket);
    }
  }

  // Map Ticket ids after pushing to Connec!
  public function afterSend($response) {
    foreach ($response->ticketClasses as $index => $ticketClass) {
      $ticket_mno_id = $ticketClass->id;
      $ticket_local_id = $this->_local_tickets[$index]->id;

      if(is_null($ticket_mno_id)) { continue; }
      if(is_null($ticket_local_id)) { continue; }
      
      $ticket_id_map = $this->_mno_soa_db_interface->getMnoIdByLocalIdName($ticket_local_id, 'TICKET');
      if(is_null($ticket_id_map)) {
        $this->_mno_soa_db_interface->addIdMapEntry($ticket_local_id, 'TICKET', $ticket_mno_id, 'TICKETS');
      }
    }
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->id;
  }
}

?>