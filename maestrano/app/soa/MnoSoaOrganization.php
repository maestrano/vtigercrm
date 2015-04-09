<?php

/**
* Mno Organization Class
*/
class MnoSoaOrganization extends MnoSoaBaseOrganization
{
  protected $_local_entity_name = "accounts";

  protected function pushId() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
    $id = $this->getLocalEntityIdentifier();

    if (!empty($id)) {
      $mno_id = $this->getMnoIdByLocalId($id);

      if ($this->isValidIdentifier($mno_id)) {
        $this->_id = $mno_id->_id;
      }
    }
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end");
  }

  protected function pullId() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start");
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);

      if ($this->isValidIdentifier($local_id)) {
        $this->_local_entity = CRMEntity::getInstance("Accounts");
        $this->_local_entity->retrieve_entity_info($local_id->_id,"Accounts");
        vtlib_setup_modulevars("Accounts", $this->_local_entity);
        $this->_local_entity->id = $local_id->_id;
        $this->_local_entity->mode = 'edit';
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " is STATUS_EXISTING_ID");
        return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " is STATUS_DELETED_ID");
        return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $this->_local_entity = new Accounts();
        $this->_local_entity->column_fields['assigned_user_id'] = "1";
        $this->pullName();
        return constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    }
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " return STATUS_ERROR");
    return constant('MnoSoaBaseEntity::STATUS_ERROR');
  }

  protected function pushName() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_name = $this->push_set_or_delete_value($this->_local_entity->column_fields['accountname']);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullName() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['accountname'] = $this->pull_set_or_delete_value($this->_name);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushDescription() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_description = $this->push_set_or_delete_value($this->_local_entity->column_fields['description']);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullDescription() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['description'] = $this->pull_set_or_delete_value($this->_description);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushIndustry() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $industry = $this->push_set_or_delete_value($this->_local_entity->column_fields['industry']);
    if (strcmp($industry, '--None--') == 0 || strcmp($industry, 'Other') == 0) { $industry = ""; }
    $this->_industry = $industry;
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullIndustry() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['industry'] = $this->pull_set_or_delete_value($this->_industry);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushAnnualRevenue() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $annual_revenue = $this->getNumeric($this->_local_entity->column_fields['annual_revenue']);
    $this->_annual_revenue = $this->push_set_or_delete_value($annual_revenue);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullAnnualRevenue() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['annual_revenue'] = $this->pull_set_or_delete_value($this->_annual_revenue);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushCapital() {
// DO NOTHING
  }

  protected function pullCapital() {
// DO NOTHING
  }

  protected function pushNumberOfEmployees() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $number_of_employees = $this->getNumeric($this->_local_entity->column_fields['employees']);
    $this->_number_of_employees = $this->push_set_or_delete_value($number_of_employees);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullNumberOfEmployees() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['employees'] = $this->pull_set_or_delete_value($this->_number_of_employees);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushAddresses() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
// BILLING ADDRESS -> POSTAL ADDRESS
    $this->_address->postalAddress->streetAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['bill_street']);
    $this->_address->postalAddress->locality = $this->push_set_or_delete_value($this->_local_entity->column_fields['bill_city']);
    $this->_address->postalAddress->region = $this->push_set_or_delete_value($this->_local_entity->column_fields['bill_state']);
    $this->_address->postalAddress->postalCode = $this->push_set_or_delete_value($this->_local_entity->column_fields['bill_code']);
    $country_code = $this->mapCountryToISO3166($this->_local_entity->column_fields['bill_country']);
    $this->_address->postalAddress->country = strtoupper($this->push_set_or_delete_value($country_code));
// SHIPPING ADDRESS -> STREET ADDRESS
    $this->_address->streetAddress->streetAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['ship_street']);
    $this->_address->streetAddress->locality = $this->push_set_or_delete_value($this->_local_entity->column_fields['ship_city']);
    $this->_address->streetAddress->region = $this->push_set_or_delete_value($this->_local_entity->column_fields['ship_state']);
    $this->_address->streetAddress->postalCode = $this->push_set_or_delete_value($this->_local_entity->column_fields['ship_code']);
    $country_code = $this->mapCountryToISO3166($this->_local_entity->column_fields['ship_country']);
    $this->_address->streetAddress->country = strtoupper($this->push_set_or_delete_value($country_code));
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullAddresses() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
// POSTAL ADDRESS -> BILLING ADDRESS
    $this->_local_entity->column_fields['bill_street'] = $this->pull_set_or_delete_value($this->_address->postalAddress->streetAddress);
    $this->_local_entity->column_fields['bill_city'] = $this->pull_set_or_delete_value($this->_address->postalAddress->locality);
    $this->_local_entity->column_fields['bill_state'] = $this->pull_set_or_delete_value($this->_address->postalAddress->region);
    $this->_local_entity->column_fields['bill_code'] = $this->pull_set_or_delete_value($this->_address->postalAddress->postalCode);
    $country = $this->mapISO3166ToCountry($this->_address->postalAddress->country);
    $this->_local_entity->column_fields['bill_country'] = $this->pull_set_or_delete_value($country);
// STREET ADDRESS -> SHIPPING ADDRESS
    $this->_local_entity->column_fields['ship_street'] = $this->pull_set_or_delete_value($this->_address->streetAddress->streetAddress);
    $this->_local_entity->column_fields['ship_city'] = $this->pull_set_or_delete_value($this->_address->streetAddress->locality);
    $this->_local_entity->column_fields['ship_state'] = $this->pull_set_or_delete_value($this->_address->streetAddress->region);
    $this->_local_entity->column_fields['ship_code'] = $this->pull_set_or_delete_value($this->_address->streetAddress->postalCode);
    $country = $this->mapISO3166ToCountry($this->_address->streetAddress->country);
    $this->_local_entity->column_fields['ship_country'] = $this->pull_set_or_delete_value($country);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushEmails() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_email->emailAddress = $this->push_set_or_delete_value($this->_local_entity->column_fields['email1']);
    $this->_email->emailAddress2 = $this->push_set_or_delete_value($this->_local_entity->column_fields['email2']);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullEmails() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['email1'] = $this->pull_set_or_delete_value($this->_email->emailAddress);
    $this->_local_entity->column_fields['email2'] = $this->pull_set_or_delete_value($this->_email->emailAddress2);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }


  protected function pushTelephones() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_telephone->voice = $this->push_set_or_delete_value($this->_local_entity->column_fields['phone']);
    $this->_telephone->voice2 = $this->push_set_or_delete_value($this->_local_entity->column_fields['otherphone']);
    $this->_telephone->fax = $this->push_set_or_delete_value($this->_local_entity->column_fields['fax']);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullTelephones() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['phone'] = $this->pull_set_or_delete_value($this->_telephone->voice);
    $this->_local_entity->column_fields['otherphone'] = $this->pull_set_or_delete_value($this->_telephone->voice2);
    $this->_local_entity->column_fields['fax'] = $this->pull_set_or_delete_value($this->_telephone->fax);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushWebsites() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_website->url = $this->push_set_or_delete_value($this->_local_entity->column_fields['website']);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pullWebsites() {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->column_fields['website'] = $this->pull_set_or_delete_value($this->_website->url, "");
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  protected function pushEntity() {
// DO NOTHING
  }

  protected function pullEntity() {
// DO NOTHING
  }

  protected function saveLocalEntity($push_to_maestrano) {
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " start ");
    $this->_local_entity->save("Accounts", '', $push_to_maestrano);
    $this->_log->debug(__CLASS__ . ' ' . __FUNCTION__ . " end ");
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->id;
  }
}

?>