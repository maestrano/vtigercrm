<?php

/**
 * Mno Company Interface
 */
class MnoSoaBaseCompany extends MnoSoaBaseEntity
{
    protected $_mno_entity_name = "company";
    protected $_create_rest_entity_name = "company";
    protected $_create_http_operation = "POST";
    protected $_update_rest_entity_name = "company";
    protected $_update_http_operation = "POST";
    protected $_receive_rest_entity_name = "company";
    protected $_receive_http_operation = "GET";
    protected $_delete_rest_entity_name = "company";
    protected $_delete_http_operation = "DELETE";    
    
    protected $_name;
    protected $_currency;
    protected $_email;
    protected $_address;
    protected $_postcode;
    protected $_state;
    protected $_city;
    protected $_country;
    protected $_website;
    protected $_phone;
    protected $_logo;

    protected function pushCompany() {
      throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoCompany class!');
    }
    
    protected function pullCompany() {
      throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoCompany class!');
    }
  
    /**
    * Build a Maestrano Company message
    */
    protected function build() {        
      $this->_log->debug(__FUNCTION__ . " start");

      $this->pushCompany();

      if ($this->_name != null) { $msg['company']->name = $this->_name; }
      if ($this->_currency != null) { $msg['company']->currency = $this->_currency; }
      if ($this->_logo != null) { $msg['company']->logo = $this->_logo; }

      if ($this->_email != null) { $msg['company']->contacts->email->emailAddress = $this->_email; }
      if ($this->_address != null) { $msg['company']->contacts->address->streetAddress->streetAddress = $this->_address; }
      if ($this->_city != null) { $msg['company']->contacts->address->streetAddress->locality = $this->_city; }
      if ($this->_postcode != null) { $msg['company']->contacts->address->streetAddress->postalCode = $this->_postcode; }
      if ($this->_state != null) { $msg['company']->contacts->address->streetAddress->region = $this->_state; }
      if ($this->_country != null) { $msg['company']->contacts->address->streetAddress->country = $this->_country; }
      if ($this->_website != null) { $msg['company']->contacts->website->url = $this->_website; }
      if ($this->_phone != null) { $msg['company']->contacts->telephone->voice = $this->_phone; }

    	$result = json_encode($msg);
      $this->_log->debug(__FUNCTION__ . " result = " . $result);

      return $result;
    }
    
    /**
    * Persists the Maestrano Company from message
    */
    protected function persist($mno_entity) {
      $this->_log->debug(__FUNCTION__ . " start");
      
      if (!empty($mno_entity->company)) {
          $mno_entity = $mno_entity->company;
      }
              
      if (!empty($mno_entity->id)) {
          $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
          $this->set_if_array_key_has_value($this->_currency, 'currency', $mno_entity);
          $this->set_if_array_key_has_value($this->_logo, 'logo', $mno_entity);

          $this->set_if_array_key_has_value($this->_email, 'emailAddress', $mno_entity->contacts->email);
          $this->set_if_array_key_has_value($this->_address, 'streetAddress', $mno_entity->contacts->address->streetAddress);
          $this->set_if_array_key_has_value($this->_city, 'locality', $mno_entity->contacts->address->streetAddress);
          $this->set_if_array_key_has_value($this->_postcode, 'postalCode', $mno_entity->contacts->address->streetAddress);
          $this->set_if_array_key_has_value($this->_state, 'region', $mno_entity->contacts->address->streetAddress);
          $this->set_if_array_key_has_value($this->_country, 'country', $mno_entity->contacts->address->streetAddress);
          $this->set_if_array_key_has_value($this->_website, 'url', $mno_entity->contacts->website);
          $this->set_if_array_key_has_value($this->_phone, 'voice', $mno_entity->contacts->telephone);

          $this->pullCompany();

          $this->saveLocalEntity(false);
      }
      $this->_log->debug(__FUNCTION__ . " end");
    }
}

?>