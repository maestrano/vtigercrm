<?php

/**
 * Mno Company Class
 */
class MnoSoaCompany extends MnoSoaBaseCompany
{
  protected $_local_entity_name = "company";

  protected function pushCompany() {
    $this->_log->debug(__FUNCTION__ . " start");

    $sql="select * from vtiger_organizationdetails";
    $result = $this->_db->pquery($sql, array());
    $this->_name = $this->_db->query_result($result,0,'organizationname');
    $this->_address = $this->_db->query_result($result,0,'address');
    $this->_city = $this->_db->query_result($result,0,'city');
    $this->_state = $this->_db->query_result($result,0,'state');
    $this->_postcode = $this->_db->query_result($result,0,'code');
    $this->_country = $this->_db->query_result($result,0,'country');
    $this->_phone = $this->_db->query_result($result,0,'phone');
    $this->_website = $this->_db->query_result($result,0,'website');

    $this->_log->debug(__FUNCTION__ . " end");
  }

  protected function pullCompany() {
    $this->_log->debug(__FUNCTION__ . " start " . $this->_id);

    $this->_local_entity = (object) array();
    $this->_local_entity->name = $this->_name;
    $this->_local_entity->currency = $this->_currency;
    $this->_local_entity->logo = $this->_logo;
    $this->_local_entity->email = $this->_email;
    $this->_local_entity->address = $this->_address;
    $this->_local_entity->postcode = $this->_postcode;
    $this->_local_entity->state = $this->_state;
    $this->_local_entity->city = $this->_city;
    $this->_local_entity->country = $this->_country;
    $this->_local_entity->website = $this->_website;
    $this->_local_entity->phone = $this->_phone;

    $this->_log->debug(__FUNCTION__ . " end " . $this->_id);
  }

  protected function saveLocalEntity($push_to_maestrano) {
    $this->_log->debug(__FUNCTION__ . " start " . json_encode($this->_local_entity));

    $organization_name = $this->_name;
    $org_name = $this->_name;
    $organization_address = $this->_address;
    $organization_city = $this->_city;
    $organization_state = $this->_state;
    $organization_code = $this->_postcode;
    $organization_country = $this->_country;
    $organization_phone = $this->_phone;
    $organization_website = $this->_website;
    $organization_logo = $this->saveLogo();

    $sql="update vtiger_organizationdetails set organizationname = ?, address = ?, city = ?, state = ?,  code = ?, country = ?,  phone = ?, website = ?, logoname = ?";
    $params = array($organization_name, $organization_address, $organization_city, $organization_state, $organization_code, $organization_country, $organization_phone, $organization_website, $organization_logo);
    $this->_db->pquery($sql, $params);

    // Save logo
    $this->saveLogo();

    // TODO: Map currency

    $this->_log->debug(__FUNCTION__ . " end ");
  }

  protected function saveLogo() {
    global $root_directory;

    $path = $root_directory . "test/logo/";

    if(isset($this->_local_entity->logo->logo)) {
      // Save logo file locally
      $filename = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . '.jpg';
      $tmpLogoFilePath = $path . $filename;
      file_put_contents($tmpLogoFilePath, file_get_contents($this->_local_entity->logo->logo));

      return $filename;
    }
  }

}

?>
