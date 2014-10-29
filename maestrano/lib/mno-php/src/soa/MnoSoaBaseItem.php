<?php

/**
 * Mno Item Interface
 */
class MnoSoaBaseItem extends MnoSoaBaseEntity
{
    protected $_mno_entity_name = "items";
    protected $_create_rest_entity_name = "items";
    protected $_create_http_operation = "POST";
    protected $_update_rest_entity_name = "items";
    protected $_update_http_operation = "POST";
    protected $_receive_rest_entity_name = "items";
    protected $_receive_http_operation = "GET";
    protected $_delete_rest_entity_name = "items";
    protected $_delete_http_operation = "DELETE";    
    
    protected $_id;
    protected $_name;
    protected $_code;
    protected $_description;
    protected $_status;
    protected $_type;
    protected $_unit;
    protected $_sale_price;
    protected $_purchase_price;
    protected $_sale_tax_code;
    protected $_taxes;

    protected function pushId() {
      throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    /**
    * Translate Maestrano identifier to local identifier
    * 
    * @return Status code 
    *           STATUS_ERROR -> Error
    *           STATUS_NEW_ID -> New identifier
    *           STATUS_EXISTING_ID -> Existing identifier
    *           STATUS_DELETED_ID -> Deleted identifier
    */
    protected function pullId() {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pushName() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullName() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushCode() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullCode() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushDescription() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullDescription() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushStatus() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullStatus() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushType() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullType() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushUnit() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullUnit() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushSalePrice() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullSalePrice() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }

    protected function pushPurchasePrice() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function pullPurchasePrice() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    public function getLocalEntityIdentifier() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoItem class!');
    }
    
    /**
    * Build a Maestrano item message
    * 
    * @return Item: the item json object
    */
    protected function build() {
        $this->_log->debug(__FUNCTION__ . " start build function");
        $this->pushId();
        $this->_log->debug(__FUNCTION__ . " after Id");
        $this->pushName();
        $this->pushCode();
        $this->pushDescription();
        $this->pushStatus();
        $this->pushType();
        $this->pushUnit();
        $this->pushSalePrice();
        $this->pushPurchasePrice();
        
        if ($this->_name != null) { $msg['item']->name = $this->_name; }
        if ($this->_code != null) { $msg['item']->code = $this->_code; }
        if ($this->_description != null) { $msg['item']->description = $this->_description; }
        if ($this->_status != null) { $msg['item']->status = $this->_status; }
        if ($this->_type != null) { $msg['item']->type = $this->_type; }
        if ($this->_unit != null) { $msg['item']->unit = $this->_unit; }
        if ($this->_sale_price != null) { $msg['item']->sale->netAmount = $this->_sale_price; }
        if ($this->_purchase_price != null) { $msg['item']->purchase->netAmount = $this->_purchase_price; }
        if ($this->_taxes != null) { $msg['item']->taxes = $this->_taxes; }
        if ($this->_sale_tax_code != null) { $msg['item']->saleTaxCode->id = $this->_sale_tax_code; }
  
        $this->_log->debug(__FUNCTION__ . " after creating message array");
        $result = json_encode($msg['item']);
      
        $this->_log->debug(__FUNCTION__ . " result = " . $result);
      
        return json_encode($msg['item']);
    }
    
    protected function persist($mno_entity) {
        $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " mno_entity = " . json_encode($mno_entity));
        
        if (!empty($mno_entity->item)) {
            $mno_entity = $mno_entity->item;
        }
        
        if (!empty($mno_entity->id)) {
            $this->_id = $mno_entity->id;
            $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
            $this->set_if_array_key_has_value($this->_code, 'code', $mno_entity);
            $this->set_if_array_key_has_value($this->_description, 'description', $mno_entity);
            $this->set_if_array_key_has_value($this->_status, 'status', $mno_entity);
            $this->set_if_array_key_has_value($this->_type, 'type', $mno_entity);
            $this->set_if_array_key_has_value($this->_unit, 'unit', $mno_entity);
            
            if (!empty($mno_entity->sale)) {
                $this->set_if_array_key_has_value($this->_sale_price, 'netAmount', $mno_entity->sale);
            }
            if (!empty($mno_entity->purchase)) {
                $this->set_if_array_key_has_value($this->_purchase_price, 'netAmount', $mno_entity->purchase);
            }
            if (!empty($mno_entity->taxes)) {
                $this->set_if_array_key_has_value($this->_taxes, 'taxes', $mno_entity);
            }
            if (!empty($mno_entity->saleTaxCode)) {
                $this->set_if_array_key_has_value($this->_sale_tax_code, 'saleTaxCode', $mno_entity);
            }

            $this->set_if_array_key_has_value($this->_entity, 'entity', $mno_entity);

            $this->_log->debug(__FUNCTION__ . " persist item id = " . $this->_id);

            $status = $this->pullId();
            $is_new_id = $status == constant('MnoSoaBaseEntity::STATUS_NEW_ID');
            $is_existing_id = $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');

            $this->_log->debug(__FUNCTION__ . " is_new_id = " . $is_new_id);
            $this->_log->debug(__FUNCTION__ . " is_existing_id = " . $is_existing_id);
            
            if ($is_new_id || $is_existing_id) {
                $this->_log->debug(__FUNCTION__ . " start pull functions");
                $this->pullName();
                $this->pullCode();
                $this->pullDescription();
                $this->pullStatus();
                $this->pullType();
                $this->pullUnit();
                $this->pullSalePrice();
                $this->pullPurchasePrice();
                $this->_log->debug(__FUNCTION__ . " after attributes set");

                $this->saveLocalEntity(false, $status);
            }

            $local_entity_id = $this->getLocalEntityIdentifier();
            $mno_entity_id = $this->_id;

            if ($is_new_id && !empty($local_entity_id) && !empty($mno_entity_id)) {
                $this->addIdMapEntry($local_entity_id, $mno_entity_id);
            }
        }
        $this->_log->debug(__FUNCTION__ . " end persist");
    }
}

?>