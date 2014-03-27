<?php

/**
 * Mno Organization Interface
 */
class MnoSoaBaseOrganization extends MnoSoaBaseEntity
{
    protected $_mno_entity_name = "organizations";
    protected $_create_rest_entity_name = "organizations";
    protected $_create_http_operation = "POST";
    protected $_update_rest_entity_name = "organizations";
    protected $_update_http_operation = "POST";
    protected $_receive_rest_entity_name = "organizations";
    protected $_receive_http_operation = "GET";
    protected $_delete_rest_entity_name = "organizations";
    protected $_delete_http_operation = "DELETE";    
    
    protected $_id;
    protected $_name;
    protected $_industry;
    protected $_annual_revenue;
    protected $_capital;
    protected $_number_of_employees;
    protected $_address;
    protected $_email;
    protected $_telephone;
    protected $_website;
    protected $_entity;
  

    protected function pushId() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
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
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushName() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullName() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushIndustry() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullIndustry() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushAnnualRevenue() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullAnnualRevenue() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushCapital() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullCapital() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushNumberOfEmployees() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullNumberOfEmployees() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushAddresses() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullAddresses() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushEmails() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullEmails() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushTelephones() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullTelephones() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushWebsites() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullWebsites() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pushEntity() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function pullEntity() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    public function getLocalEntityIdentifier() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoOrganization class!');
    }
    
    /**
    * Build a Maestrano organization message
    * 
    * @return Organization the organization json object
    */
    protected function build() {        
	$this->_log->debug(__FUNCTION__ . " start build function");
	$this->pushId();
	$this->_log->debug(__FUNCTION__ . " after Id");
	$this->pushName();
	$this->_log->debug(__FUNCTION__ . " after Name");
	$this->pushIndustry();
	$this->_log->debug(__FUNCTION__ . " after Industry");
	$this->pushAnnualRevenue();
	$this->_log->debug(__FUNCTION__ . " after Annual Revenue");
        $this->pushCapital();
        $this->_log->debug(__FUNCTION__ . " after Capital");
	$this->pushNumberOfEmployees();
	$this->_log->debug(__FUNCTION__ . " after Number of Employees");
	$this->pushAddresses();
	$this->_log->debug(__FUNCTION__ . " after Addresses");
	$this->pushEmails();
	$this->_log->debug(__FUNCTION__ . " after Emails");
	$this->pushTelephones();
	$this->_log->debug(__FUNCTION__ . " after Telephones");
	$this->pushWebsites();
	$this->_log->debug(__FUNCTION__ . " after Websites");
	$this->pushEntity();
	$this->_log->debug(__FUNCTION__ . " after Entity");
	
        if ($this->_name != null) { $msg['organization']->name = $this->_name; }
        if ($this->_industry != null) { $msg['organization']->industry = $this->_industry; }
        if ($this->_annual_revenue != null) { $msg['organization']->annualRevenue = $this->_annual_revenue; }
        if ($this->_capital != null) { $msg['organization']->capital = $this->_capital; }
        if ($this->_number_of_employees != null) { $msg['organization']->numberOfEmployees = $this->_number_of_employees; }
        if ($this->_address != null) { $msg['organization']->contacts->address = $this->_address; }
        if ($this->_email != null) { $msg['organization']->contacts->email = $this->_email; }
        if ($this->_telephone != null) { $msg['organization']->contacts->telephone = $this->_telephone; }
        if ($this->_website != null) { $msg['organization']->contacts->website = $this->_website; }
        if ($this->_entity != null) { $msg['organization']->entity = $this->_entity; }
	
	$this->_log->debug(__FUNCTION__ . " after creating message array");
	$result = json_encode($msg['organization']);
	
	$this->_log->debug(__FUNCTION__ . " result = " . $result);
	
	return json_encode($msg['organization']);
    }
    
    protected function persist($mno_entity) {
        $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " mno_entity = " . json_encode($mno_entity));
        
        if (!empty($mno_entity->organization)) {
            $mno_entity = $mno_entity->organization;
        }
                
        if (!empty($mno_entity->id)) {
            $this->_id = $mno_entity->id;
            $this->_log->debug(__FUNCTION__ . " after id");
            $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
            $this->_log->debug(__FUNCTION__ . " after name");
            $this->set_if_array_key_has_value($this->_industry, 'industry', $mno_entity);
            $this->set_if_array_key_has_value($this->_annual_revenue, 'annualRevenue', $mno_entity);
            $this->set_if_array_key_has_value($this->_capital, 'capital', $mno_entity);
            $this->set_if_array_key_has_value($this->_number_of_employees, 'numberOfEmployees', $mno_entity);
            
            $this->_log->debug(__FUNCTION__ . " before contacts");
            if (!empty($mno_entity->contacts)) {
                $this->set_if_array_key_has_value($this->_address, 'address', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_email, 'email', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_telephone, 'telephone', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_website, 'website', $mno_entity->contacts);
            }
            $this->_log->debug(__FUNCTION__ . " after contacts");
            
            $this->set_if_array_key_has_value($this->_entity, 'entity', $mno_entity);

            $this->_log->debug(__FUNCTION__ . " persist organization id = " . $this->_id);

            $status = $this->pullId();
            $this->_log->debug(__FUNCTION__ . " after id");
            $is_new_id = $status == constant('MnoSoaBaseEntity::STATUS_NEW_ID');
            $is_existing_id = $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');

            if ($is_new_id || $is_existing_id) {
                $this->pullName();
                $this->pullIndustry();
                $this->pullAnnualRevenue();
                $this->pullCapital();
                $this->pullNumberOfEmployees();
                $this->pullAddresses();
                $this->pullEmails();
                $this->pullTelephones();
                $this->pullWebsites();
                $this->pullEntity();

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
    
    /**
    *	Helper functions
    *
    */
}

?>