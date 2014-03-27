<?php

/**
 * Mno Person Interface
 */
class MnoSoaBasePerson extends MnoSoaBaseEntity
{
    protected $_mno_entity_name = "persons";
    protected $_create_rest_entity_name = "persons";
    protected $_create_http_operation = "POST";
    protected $_update_rest_entity_name = "persons";
    protected $_update_http_operation = "POST";
    protected $_receive_rest_entity_name = "persons";
    protected $_receive_http_operation = "GET";
    protected $_delete_rest_entity_name = "persons";
    protected $_delete_http_operation = "DELETE";    
    
    protected $_id;
    protected $_name;
    protected $_birth_date;
    protected $_gender;
    protected $_address;
    protected $_email;
    protected $_telephone;
    protected $_website;
    protected $_entity;
    protected $_role;  

    protected function pushId() {
	throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
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
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushName() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullName() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushBirthDate() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullBirthDate() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushGender() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullGender() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushAddresses() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullAddresses() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushEmails() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullEmails() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushTelephones() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullTelephones() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushWebsites() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullWebsites() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushEntity() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullEntity() {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pushRole() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function pullRole() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    protected function saveLocalEntity($push_to_maestrano, $status) {
		throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
    }
    
    public function getLocalEntityIdentifier() {
        throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoPerson class!');
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
		$this->pushBirthDate();
		$this->_log->debug(__FUNCTION__ . " after Birth Date");
		$this->pushGender();
		$this->_log->debug(__FUNCTION__ . " after Annual Revenue");
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
        $this->pushRole();
        $this->_log->debug(__FUNCTION__ . " after Role");
        
        if ($this->_name != null) { $msg['person']->name = $this->_name; }
        if ($this->_birth_date != null) { $msg['person']->birthDate = $this->_birth_date; }
        if ($this->_gender != null) { $msg['person']->gender = $this->_gender; }
        if ($this->_address != null) { $msg['person']->contacts->address = $this->_address; }
        if ($this->_email != null) { $msg['person']->contacts->email = $this->_email; }
        if ($this->_telephone != null) { $msg['person']->contacts->telephone = $this->_telephone; }
        if ($this->_website != null) { $msg['person']->contacts->website = $this->_website; }
        if ($this->_entity != null) { $msg['person']->entity = $this->_entity; }
        if ($this->_role != null) { $msg['person']->role = $this->_role; }
	
		$this->_log->debug(__FUNCTION__ . " after creating message array");
		$result = json_encode($msg['person']);
	
		$this->_log->debug(__FUNCTION__ . " result = " . $result);
	
		return json_encode($msg['person']);
    }
    
    protected function persist($mno_entity) {
        $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " mno_entity = " . json_encode($mno_entity));
        
        if (!empty($mno_entity->person)) {
            $mno_entity = $mno_entity->person;
        }
        
        if (!empty($mno_entity->id)) {
            $this->_id = $mno_entity->id;
            $this->set_if_array_key_has_value($this->_name, 'name', $mno_entity);
            $this->set_if_array_key_has_value($this->_birth_date, 'birthDate', $mno_entity);
            $this->set_if_array_key_has_value($this->_gender, 'gender', $mno_entity);
            
            if (!empty($mno_entity->contacts)) {
                $this->set_if_array_key_has_value($this->_address, 'address', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_email, 'email', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_telephone, 'telephone', $mno_entity->contacts);
                $this->set_if_array_key_has_value($this->_website, 'website', $mno_entity->contacts);
            }
            
            $this->set_if_array_key_has_value($this->_entity, 'entity', $mno_entity);
            $this->set_if_array_key_has_value($this->_role, 'role', $mno_entity);

            $this->_log->debug(__FUNCTION__ . " persist person id = " . $this->_id);

            $status = $this->pullId();
            $is_new_id = $status == constant('MnoSoaBaseEntity::STATUS_NEW_ID');
            $is_existing_id = $status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');

            $this->_log->debug(__FUNCTION__ . " is_new_id = " . $is_new_id);
            $this->_log->debug(__FUNCTION__ . " is_existing_id = " . $is_existing_id);
            
            if ($is_new_id || $is_existing_id) {
                $this->_log->debug(__FUNCTION__ . " start pull functions");
                $this->pullName();
                $this->_log->debug(__FUNCTION__ . " after name");
                $this->pullBirthDate();
                $this->_log->debug(__FUNCTION__ . " after birth date");
                $this->pullGender();
                $this->_log->debug(__FUNCTION__ . " after gender");
                $this->pullAddresses();
                $this->_log->debug(__FUNCTION__ . " after addresses");
                $this->pullEmails();
                $this->_log->debug(__FUNCTION__ . " after emails");
                $this->pullTelephones();
                $this->_log->debug(__FUNCTION__ . " after telephones");
                $this->pullWebsites();
                $this->_log->debug(__FUNCTION__ . " after websites");
                $this->pullEntity();
                $this->_log->debug(__FUNCTION__ . " after entity");
                $this->pullRole();
                $this->_log->debug(__FUNCTION__ . " after role");

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