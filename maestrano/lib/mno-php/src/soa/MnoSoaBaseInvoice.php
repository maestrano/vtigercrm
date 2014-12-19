<?php

/**
 * Mno Invoice Interface
 */
class MnoSoaBaseInvoice extends MnoSoaBaseEntity
{
  protected $_mno_entity_name = "invoices";
  protected $_create_rest_entity_name = "invoices";
  protected $_create_http_operation = "POST";
  protected $_update_rest_entity_name = "invoices";
  protected $_update_http_operation = "POST";
  protected $_receive_rest_entity_name = "invoices";
  protected $_receive_http_operation = "GET";
  protected $_delete_rest_entity_name = "invoices";
  protected $_delete_http_operation = "DELETE";

  protected $_enable_delete_notifications=true;
  
  protected $_id;
  protected $_title;
  protected $_transaction_number;
  protected $_transaction_date;
  protected $_amount;
  protected $_currency;
  protected $_due_date;
  protected $_status;
  protected $_type;
  protected $_balance;
  protected $_deposit;
  protected $_organization_id;
  protected $_person_id;
  protected $_invoice_lines;

  protected function pushInvoice() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoInvoice class!');
  }
  
  protected function pullInvoice() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoInvoice class!');
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoInvoice class!');
  }
  
  public function getLocalEntityIdentifier() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoInvoice class!');
  }
  
  protected function build() {
    $this->_log->debug("start");
    $this->pushInvoice();
    if ($this->_title != null) { $msg['invoice']->title = $this->_title; }
    if ($this->_transaction_number != null) { $msg['invoice']->transactionNumber = $this->_transaction_number; }
    if ($this->_transaction_date != null) { $msg['invoice']->transactionDate = $this->_transaction_date; }
    if ($this->_amount != null) { $msg['invoice']->amount = $this->_amount; }
    if ($this->_currency != null) { $msg['invoice']->currency = $this->_currency; }
    if ($this->_due_date != null) { $msg['invoice']->dueDate = $this->_due_date; }
    if ($this->_status != null) { $msg['invoice']->status = $this->_status; }
    if ($this->_type != null) { $msg['invoice']->type = $this->_type; }
    if ($this->_balance != null) { $msg['invoice']->balance = $this->_balance; }
    if ($this->_deposit != null) { $msg['invoice']->deposit = $this->_deposit; }
    if ($this->_organization_id != null) { $msg['invoice']->organization->id = $this->_organization_id; }
    if ($this->_person_id != null) { $msg['invoice']->person->id = $this->_person_id; }
    if ($this->_invoice_lines != null) { $msg['invoice']->invoiceLines = $this->_invoice_lines; }

    $result = json_encode($msg['invoice']);

    $this->_log->debug("result = $result");

    return $result;
  }
  
  protected function persist($mno_entity) {
    $this->_log->debug("start");
    
    if (!empty($mno_entity->invoice)) {
      $mno_entity = $mno_entity->invoice;
    }
    
    if (!empty($mno_entity->id)) {
      $this->_id = $mno_entity->id;
      $this->set_if_array_key_has_value($this->_title, 'title', $mno_entity);
      $this->set_if_array_key_has_value($this->_transaction_number, 'transactionNumber', $mno_entity);
      $this->set_if_array_key_has_value($this->_transaction_date, 'transactionDate', $mno_entity);
      $this->set_if_array_key_has_value($this->_amount, 'amount', $mno_entity);
      $this->set_if_array_key_has_value($this->_currency, 'currency', $mno_entity);
      $this->set_if_array_key_has_value($this->_due_date, 'dueDate', $mno_entity);
      $this->set_if_array_key_has_value($this->_status, 'status', $mno_entity);
      $this->set_if_array_key_has_value($this->_type, 'type', $mno_entity);
      $this->set_if_array_key_has_value($this->_balance, 'balance', $mno_entity);
      $this->set_if_array_key_has_value($this->_deposit, 'deposit', $mno_entity);

      if (!empty($mno_entity->organization)) {
        $this->set_if_array_key_has_value($this->_organization_id, 'id', $mno_entity->organization);
      }

      if (!empty($mno_entity->person)) {
        $this->set_if_array_key_has_value($this->_person_id, 'id', $mno_entity->person);
      }

      if (!empty($mno_entity->invoiceLines)) {
        $this->set_if_array_key_has_value($this->_invoice_lines, 'invoiceLines', $mno_entity);
      }

      $this->_log->debug("id = " . $this->_id);

      $status = $this->pullInvoice();
      $this->_log->debug("after pullInvoice");
      
      if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
        $this->saveLocalEntity(false, $status);
      }
    }
    $this->_log->debug("end");
  }
}

?>