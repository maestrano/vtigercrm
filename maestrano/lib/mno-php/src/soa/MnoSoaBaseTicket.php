<?php

/**
 * Mno Ticket Interface. Synchronized under Event object.
 */
class MnoSoaBaseTicket extends MnoSoaBaseEntity {
  protected $_mno_entity_name = "tickets";
  protected $_create_rest_entity_name = "tickets";
  protected $_create_http_operation = "POST";
  protected $_update_rest_entity_name = "tickets";
  protected $_update_http_operation = "POST";
  protected $_receive_rest_entity_name = "tickets";
  protected $_receive_http_operation = "GET";
  protected $_delete_rest_entity_name = "tickets";
  protected $_delete_http_operation = "DELETE";    
  
  protected $_id;
  protected $_event_id;
  protected $_name;
  protected $_description;
  protected $_minimum_quantity;
  protected $_maximum_quantity;
  protected $_quantity_total;
  protected $_quantity_sold;
  protected $_sales_start;
  protected $_sales_end;
  protected $_cost;
  protected $_fee;

  protected function pushTicket() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTicket class!');
  }
  
  protected function pullTicket() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTicket class!');
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTicket class!');
  }
  
  public function getLocalEntityIdentifier() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTicket class!');
  }
  
  protected function build() {
    $this->_log->debug("start");

    $this->pushTicket();

    if ($this->_name != null) { $msg['ticket']->name = $this->_name; }

    $result = json_encode($msg['ticket']);
    $this->_log->debug("result = $result");

    return $result;
  }
  
  protected function persist($mno_entity) {
    $this->_log->debug("start");
    
    if (!empty($mno_entity->ticket)) {
      $mno_entity = $mno_entity->ticket;
    }
    
    if (!empty($mno_entity->id)) {
      $this->_id = $mno_entity->id;

      $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
      $this->set_if_array_key_has_value($this->_description, 'description', $mno_entity);
      $this->set_if_array_key_has_value($this->_minimum_quantity, 'minimumQuantity', $mno_entity);
      $this->set_if_array_key_has_value($this->_maximum_quantity, 'maximumQuantity', $mno_entity);
      $this->set_if_array_key_has_value($this->_quantity_total, 'quantityTotal', $mno_entity);
      $this->set_if_array_key_has_value($this->_quantity_sold, 'quantitySold', $mno_entity);
      $this->set_if_array_key_has_value($this->_sales_start, 'salesStart', $mno_entity);
      $this->set_if_array_key_has_value($this->_sales_end, 'salesEnd', $mno_entity);
      $this->set_if_array_key_has_value($this->_cost, 'cost', $mno_entity);
      $this->set_if_array_key_has_value($this->_fee, 'fee', $mno_entity);

      $this->_log->debug("id = " . $this->_id);

      $status = $this->pullTicket();
      $this->_log->debug("after pullTicket");
      
      if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
        $this->saveLocalEntity(false, $status);

        // Map ticket ID
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