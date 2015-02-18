<?php

/**
 * Mno Event Interface
 */
class MnoSoaBaseEvent extends MnoSoaBaseEntity {
  protected $_mno_entity_name = "events";
  protected $_create_rest_entity_name = "events";
  protected $_create_http_operation = "POST";
  protected $_update_rest_entity_name = "events";
  protected $_update_http_operation = "POST";
  protected $_receive_rest_entity_name = "events";
  protected $_receive_http_operation = "GET";
  protected $_delete_rest_entity_name = "events";
  protected $_delete_http_operation = "DELETE";    
  
  protected $_id;
  protected $_code;
  protected $_name;
  protected $_description;
  protected $_url;
  protected $_capacity;
  protected $_currency;
  protected $_start_date;
  protected $_end_date;
  protected $_ticket_classes;

  protected function pushEvent() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEvent class!');
  }
  
  protected function pullEvent() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEvent class!');
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEvent class!');
  }
  
  public function getLocalEntityIdentifier() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEvent class!');
  }

  protected function pullTickets() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEvent class!');
  }
  
  protected function build() {
    $this->_log->debug("start");
    $this->pushEvent();
    if ($this->_code != null) { $msg['event']->code = $this->_code; }
    if ($this->_name != null) { $msg['event']->name = $this->_name; }
    if ($this->_description != null) { $msg['event']->description = $this->_description; }
    if ($this->_url != null) { $msg['event']->url = $this->_url; }
    if ($this->_capacity != null) { $msg['event']->capacity = $this->_capacity; }
    if ($this->_currency != null) { $msg['event']->currency = $this->_currency; }
    if ($this->_start_date != null) { $msg['event']->startDate = $this->_start_date; }
    if ($this->_end_date != null) { $msg['event']->endDate = $this->_end_date; }
    if ($this->_ticket_classes != null) { $msg['event']->ticketClasses = $this->_ticket_classes; }

    $result = json_encode($msg['event']);

    $this->_log->debug("result = $result");

    return $result;
  }
  
  protected function persist($mno_entity) {
    $this->_log->debug("start");
    
    if (!empty($mno_entity->event)) {
      $mno_entity = $mno_entity->event;
    }
    
    if (!empty($mno_entity->id)) {
      $this->_id = $mno_entity->id;
      $this->set_if_array_key_has_value($this->_code, 'code', $mno_entity);
      $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
      $this->set_if_array_key_has_value($this->_description, 'description', $mno_entity);
      $this->set_if_array_key_has_value($this->_url, 'url', $mno_entity);
      $this->set_if_array_key_has_value($this->_capacity, 'capacity', $mno_entity);
      $this->set_if_array_key_has_value($this->_currency, 'currency', $mno_entity);
      $this->set_if_array_key_has_value($this->_start_date, 'startDate', $mno_entity);
      $this->set_if_array_key_has_value($this->_end_date, 'endDate', $mno_entity);

      if (!empty($mno_entity->ticketClasses)) {
        $this->set_if_array_key_has_value($this->_ticket_classes, 'ticketClasses', $mno_entity);
      }

      $this->_log->debug("id = " . $this->_id);

      $status = $this->pullEvent();
      $this->_log->debug("after pullEvent");
      
      if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
        $this->saveLocalEntity(false, $status);

        // Map event ID
        if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
          $local_entity_id = $this->getLocalEntityIdentifier();
          $mno_entity_id = $this->_id;
          $this->addIdMapEntry($local_entity_id, $mno_entity_id);
        }

        // Pull tickets
        $this->pullTickets();
      }
    }
    $this->_log->debug("end");
  }
}

?>