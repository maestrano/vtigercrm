<?php

/**
 * Mno Tax Interface
 */
class MnoSoaBaseTax extends MnoSoaBaseEntity
{
    protected $_mno_entity_name = "tax_codes";
    protected $_create_rest_entity_name = "tax_codes";
    protected $_create_http_operation = "POST";
    protected $_update_rest_entity_name = "tax_codes";
    protected $_update_http_operation = "POST";
    protected $_receive_rest_entity_name = "tax_codes";
    protected $_receive_http_operation = "GET";
    protected $_delete_rest_entity_name = "tax_codes";
    protected $_delete_http_operation = "DELETE";    
    
    protected $_id;
    protected $_name;
    protected $_rate;

    protected function pushTax() {
      throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTax class!');
    }
    
    protected function pullTax() {
      throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTax class!');
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTax class!');
    }
    
    public function getLocalEntityIdentifier() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoTax class!');
    }
    
    /**
    * Build a Maestrano tax_code message
    * 
    * @return Tax: the tax_code json object
    */
    protected function build() {
        $this->_log->debug(__FUNCTION__ . " start build function");
        $this->pushTax();
        $this->_log->debug(__FUNCTION__ . " after Id");
        
        if ($this->_name != null) { $msg['taxCode']->name = $this->_name; }
        if ($this->_rate != null) { $msg['taxCode']->saleTaxRate = $this->_rate; }
  
        $this->_log->debug(__FUNCTION__ . " after creating message array");
        $result = json_encode($msg['taxCode']);
        $this->_log->debug(__FUNCTION__ . " result = " . $result);
      
        return $result;
    }
    
    protected function persist($mno_entity) {
        $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " mno_entity = " . json_encode($mno_entity));
        
        if (!empty($mno_entity->taxCode)) {
          $mno_entity = $mno_entity->taxCode;
        }
        
        if (!empty($mno_entity->id)) {
          $this->_id = $mno_entity->id;
          $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
          $this->set_if_array_key_has_value($this->_rate, 'saleTaxRate', $mno_entity);

          $this->set_if_array_key_has_value($this->_entity, 'entity', $mno_entity);

          $this->_log->debug(__FUNCTION__ . " persist tax_code id = " . $this->_id);

          $status = $this->pullTax();
          $this->_log->debug(__FUNCTION__ . " pullTax status = " . $status);
          
          if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID') || $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
            $this->saveLocalEntity(false, $status);
          }
        }
        $this->_log->debug(__FUNCTION__ . " end persist");
    }
}

?>