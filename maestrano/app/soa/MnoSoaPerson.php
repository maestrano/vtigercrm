<?php

error_log("start loading mnosoaperson");

/**
 * Mno Organization Class
 */
class MnoSoaPerson extends MnoSoaBasePerson
{
    protected $_local_entity_name = "contacts";
    
    protected function pushId() {
        $this->_log->debug(__FUNCTION__ . " start");
	$id = $this->getLocalEntityIdentifier();
	
	if (!empty($id)) {
	    error_log("id is not empty, id = " . $id);
	    $mno_id = $this->getMnoIdByLocalId($id);

	    if ($this->isValidIdentifier($mno_id)) {
                $this->_log->debug(__FUNCTION__ . " this->getMnoIdByLocalId(id) = " . json_encode($mno_id));
		$this->_id = $mno_id->_id;
	    }
	}
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pullId() {
        $this->_log->debug(__FUNCTION__ . " start " . $this->_id);
        
	if (!empty($this->_id)) {
	    $local_id = $this->getLocalIdByMnoId($this->_id);
	    $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
	    
	    if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " is STATUS_EXISTING_ID");
		$this->_local_entity = CRMEntity::getInstance("Contacts");
		$this->_local_entity->retrieve_entity_info($local_id->_id,"Contacts");
		vtlib_setup_modulevars("Contacts", $this->_local_entity);
		$this->_local_entity->id = $local_id->_id;
		$this->_local_entity->mode = 'edit';
		return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
	    } else if ($this->isDeletedIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
                return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
            } else {
		$this->_local_entity = new Contacts();
		$this->pullName();
		return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
	    }
	}
        $this->_log->debug(__FUNCTION__ . " return STATUS_ERROR");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
    
    protected function pushName() {
        $this->_log->debug(__FUNCTION__ . " start");
        //$hp = $this->mapSalutationToHonorificPrefix($this->_local_entity->column_fields['salutationtype']);
        $this->_name->honorificPrefix = $this->push_set_or_delete_value($hp);
        $this->_name->givenNames = $this->push_set_or_delete_value($this->_local_entity->column_fields['firstname']);
        $this->_name->familyName = $this->push_set_or_delete_value($this->_local_entity->column_fields['lastname']);
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    
    
    protected function pullName() {
        $this->_log->debug(__FUNCTION__ . " start");
        //$hp = $this->mapHonorificPrefixToSalutation($this->_name->honorificPrefix);
        //$this->_local_entity->column_fields['salutationtype'] = $this->pull_set_or_delete_value($hp);
        $this->_local_entity->column_fields['firstname'] = $this->pull_set_or_delete_value($this->_name->givenNames);
        $this->_local_entity->column_fields['lastname'] = $this->pull_set_or_delete_value($this->_name->familyName);
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pushBirthDate() {
        $this->_log->debug(__FUNCTION__ . " start");
        $this->_birth_date = $this->push_set_or_delete_value($this->_local_entity->column_fields['birthday']);
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pullBirthDate() {
        $this->_log->debug(__FUNCTION__ . " start");
        $this->_local_entity->column_fields['birthday'] = $this->pull_set_or_delete_value($this->_birth_date);
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pushGender() {
	// DO NOTHING
    }
    
    protected function pullGender() {
	// DO NOTHING
    }
    
    protected function pushAddresses() {
        $this->_log->debug(__FUNCTION__ . " start");
        // MAILING ADDRESS -> POSTAL ADDRESS
        $this->_address->work->postalAddress->streetAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['mailingstreet']);
        $this->_address->work->postalAddress->locality = $this->push_set_or_delete_value($this->_local_entity->column_fields['mailingcity']);
        $this->_address->work->postalAddress->region = $this->push_set_or_delete_value($this->_local_entity->column_fields['mailingstate']);
        $this->_address->work->postalAddress->postalCode = $this->push_set_or_delete_value($this->_local_entity->column_fields['mailingzip']);
        $country_code = $this->mapCountryToISO3166($this->_local_entity->column_fields['mailingcountry']);
        $this->_address->work->postalAddress->country = strtoupper($this->push_set_or_delete_value($country_code));
        // OTHER ADDRESS -> POSTAL ADDRESS #2
        $this->_address->work->postalAddress2->streetAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['otherstreet']);
        $this->_address->work->postalAddress2->locality = $this->push_set_or_delete_value($this->_local_entity->column_fields['othercity']);
        $this->_address->work->postalAddress2->region = $this->push_set_or_delete_value($this->_local_entity->column_fields['otherstate']);
        $this->_address->work->postalAddress2->postalCode = $this->push_set_or_delete_value($this->_local_entity->column_fields['otherzip']);
        $country_code = $this->mapCountryToISO3166($this->_local_entity->column_fields['othercountry']);
        $this->_address->work->postalAddress2->country = strtoupper($this->push_set_or_delete_value($country_code));
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pullAddresses() {
        $this->_log->debug(__FUNCTION__ . " start");
        // POSTAL ADDRESS -> MAILING ADDRESS
        $this->_local_entity->column_fields['mailingstreet'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress->streetAddress);
        $this->_local_entity->column_fields['mailingcity'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress->locality);
        $this->_local_entity->column_fields['mailingstate'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress->region);
        $this->_local_entity->column_fields['mailingzip'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress->postalCode);
        $country = $this->mapISO3166ToCountry($this->_address->work->postalAddress->country);
        $this->_local_entity->column_fields['mailingcountry'] = $this->pull_set_or_delete_value($country);
        // POSTAL ADDRESS #2 -> OTHER ADDRESS
        $this->_local_entity->column_fields['otherstreet'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress2->streetAddress);
        $this->_local_entity->column_fields['othercity'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress2->locality);
        $this->_local_entity->column_fields['otherstate'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress2->region);
        $this->_local_entity->column_fields['otherzip'] = $this->pull_set_or_delete_value($this->_address->work->postalAddress2->postalCode);
        $country = $this->mapISO3166ToCountry($this->_address->work->postalAddress2->country);
        $this->_local_entity->column_fields['othercountry'] = $this->pull_set_or_delete_value($country);
        $this->_log->debug(__FUNCTION__ . " end");
    }
    
    protected function pushEmails() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['email']);
	$this->_email->emailAddress2 = $this->push_set_or_delete_value($this->_local_entity->column_fields['secondaryemail']);
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    protected function pullEmails() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_local_entity->column_fields['email'] = $this->pull_set_or_delete_value($this->_email->emailAddress);
        $this->_local_entity->column_fields['secondaryemail'] = $this->pull_set_or_delete_value($this->_email->emailAddress2);
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    
    protected function pushTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_telephone->work->voice = $this->push_set_or_delete_value($this->_local_entity->column_fields['phone']);
        $this->_telephone->home->mobile = $this->push_set_or_delete_value($this->_local_entity->column_fields['mobile']);
        $this->_telephone->home->voice = $this->push_set_or_delete_value($this->_local_entity->column_fields['homephone']);
        $this->_telephone->work->voice2 = $this->push_set_or_delete_value($this->_local_entity->column_fields['otherphone']);
        $this->_telephone->work->fax = $this->push_set_or_delete_value($this->_local_entity->column_fields['fax']);
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    protected function pullTelephones() {
        $this->_log->debug(__FUNCTION__ . " start ");
        $this->_local_entity->column_fields['phone'] = $this->pull_set_or_delete_value($this->_telephone->work->voice);
        $this->_local_entity->column_fields['mobile'] = $this->pull_set_or_delete_value($this->_telephone->home->mobile);
        $this->_local_entity->column_fields['homephone'] = $this->pull_set_or_delete_value($this->_telephone->home->voice);
        $this->_local_entity->column_fields['otherphone'] = $this->pull_set_or_delete_value($this->_telephone->work->voice2);
        $this->_local_entity->column_fields['fax'] = $this->pull_set_or_delete_value($this->_telephone->work->fax);
        $this->_log->debug(__FUNCTION__ . " end ");
    }
    
    protected function pushWebsites() {
	// DO NOTHING
    }
    
    protected function pullWebsites() {
	// DO NOTHING
    }
    
    protected function pushEntity() {
	$this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
        $this->_entity->customer = true;
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
    }
    
    protected function pullEntity() {
	// DO NOTHING
    }
    
    protected function pushRole() {
        $local_id = $this->_local_entity->column_fields['account_id'];
        
        if (!empty($local_id)) {
            $mno_id = $this->getMnoIdByLocalIdName($local_id, 'accounts');
        } else {
            $this->_role = null;
            return;
        }
        
        if ($this->isValidIdentifier($mno_id)) {
            $this->_log->debug(__FUNCTION__ . " mno_id = " . json_encode($mno_id));
            $this->_role->organization->id = $mno_id->_id;
            $this->_role->title = $this->push_set_or_delete_value($this->_local_entity->column_fields['title']);
        } else if ($this->isDeletedIdentifier($mno_id)) {
            // do not update
            return;
        } else {
            $org_contact = CRMEntity::getInstance("Accounts");
            $org_contact->retrieve_entity_info($local_id,"Accounts");
            vtlib_setup_modulevars("Accounts", $this->_local_entity);
            $org_contact->id = $local_id;
            
            $organization = new MnoSoaOrganization($this->_db, $this->_log);		
            $organization->send($org_contact);

            $mno_id = $this->getMnoIdByLocalId($local_id);

            if ($this->isValidIdentifier($mno_id)) {
                $this->_role->organization->id = $mno_id->_id;
                $this->_role->title = $this->push_set_or_delete_value($this->_local_entity->column_fields['title']);
            }
        }
    }
    
    protected function pullRole() {
        if (empty($this->_role->organization->id)) {
            $this->_local_entity->column_fields['account_id'] = "";
            $this->_local_entity->column_fields['title'] = "";
        } else {
            $local_id = $this->getLocalIdByMnoIdName($this->_role->organization->id, "organizations");
            
            if ($this->isValidIdentifier($local_id)) {
                $this->_log->debug(__FUNCTION__ . " local_id = " . json_encode($local_id));
                $this->_local_entity->column_fields['account_id'] = $this->pull_set_or_delete_value($local_id->_id);
                $this->_local_entity->column_fields['title'] = $this->pull_set_or_delete_value($this->_role->title);
            } else if ($this->isDeletedIdentifier($local_id)) {
                // do not update
                return;
            } else {
                $notification->entity = "organizations";
                $notification->id = $this->_role->organization->id;
                $organization = new MnoSoaOrganization($this->_db, $this->_log);		
                $organization->receiveNotification($notification);
                $this->_local_entity->column_fields['account_id'] = $this->pull_set_or_delete_value($organization->_local_entity->id);
                $this->_local_entity->column_fields['title'] = $this->pull_set_or_delete_value($this->_role->title);
            }            
        }
    }
    
    protected function saveLocalEntity($push_to_maestrano) {
	$this->_local_entity->save("Contacts", '', $push_to_maestrano);
    }
    
    protected function mapSalutationToHonorificPrefix($in) {
        $in_form = strtoupper(trim($in));
        
        switch ($in_form) {
            case "MR.": return "MR";
            case "MS.": return "MS";
            case "MRS.": return "MRS";
            case "DR.": return "DR";
            case "PROF.": return "PROF";
            default: return null;
        }
    }

    protected function mapHonorificPrefixToSalutation($in) {
        $in_form = strtoupper(trim($in));
        
        switch ($in_form) {
            case "MR": return "MR.";
            case "MS": return "MS.";
            case "MRS": return "MRS.";
            case "DR": return "DR.";
            case "PROF": return "PROF.";
            default: return null;
        }
    }
    
    protected function getLocalEntityIdentifier() {
        return $this->_local_entity->id;
    }
}

?>