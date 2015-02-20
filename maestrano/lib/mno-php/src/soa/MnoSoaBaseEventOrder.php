<?php

/**
 * Mno EventOrder Interface
 */
class MnoSoaBaseEventOrder extends MnoSoaBaseEntity { 
  protected $_mno_entity_name = "event_orders";
  protected $_create_rest_entity_name = "event_orders";
  protected $_create_http_operation = "POST";
  protected $_update_rest_entity_name = "event_orders";
  protected $_update_http_operation = "POST";
  protected $_receive_rest_entity_name = "event_orders";
  protected $_receive_http_operation = "GET";
  protected $_delete_rest_entity_name = "event_orders";
  protected $_delete_http_operation = "DELETE";   

  protected $_id;
  protected $_code;
  protected $_status;
  protected $_event_id;
  protected $_person_id;
  protected $_attendees;

  protected function pushEventOrder() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEventOrder class!');
  }
  
  protected function pullEventOrder() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEventOrder class!');
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEventOrder class!');
  }
  
  public function getLocalEntityIdentifier() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoEventOrder class!');
  }
  
  protected function build() {
    $this->_log->debug("start");
    
    $this->pushEventOrder();
    if ($this->_code != null) { $msg['event_order']->code = $this->_code; }
    if ($this->_status != null) { $msg['event_order']->status = $this->_status; }
    if ($this->_event_id != null) { $msg['event_order']->event->id = $this->_event_id; }
    if ($this->_person_id != null) { $msg['event_order']->person->id = $this->_person_id; }
    if ($this->_attendees != null) { $msg['event_order']->attendees = $this->_attendees; }

    $result = json_encode($msg['event_order']);

    $this->_log->debug("result = $result");

    return $result;
  }
  
  protected function persist($mno_entity) {
    $this->_log->debug("start");
    
    if (!empty($mno_entity->event_order)) {
      $mno_entity = $mno_entity->event_order;
    }
    
    if (!empty($mno_entity->id)) {
      $this->_id = $mno_entity->id;
      $this->set_if_array_key_has_value($this->_code, 'code', $mno_entity);
      $this->set_if_array_key_has_value($this->_status, 'status', $mno_entity);
      $this->set_if_array_key_has_value($this->_event_id, 'id', $mno_entity->event);
      $this->set_if_array_key_has_value($this->_person_id, 'id', $mno_entity->person);

      if (!empty($mno_entity->attendees)) {
        $this->set_if_array_key_has_value($this->_attendees, 'attendees', $mno_entity);
      }

      $this->_log->debug("id = " . $this->_id);

      $status = $this->pullEventOrder();
      $this->_log->debug("after pullEventOrder");
      
      if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
        $this->saveLocalEntity(false, $status);

        // Map event order  ID
        if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
          $local_entity_id = $this->getLocalEntityIdentifier();
          $mno_entity_id = $this->_id;
          $this->addIdMapEntry($local_entity_id, $mno_entity_id);
        }
      }
    }
    $this->_log->debug("end");
  }
}

?>