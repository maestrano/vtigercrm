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

    $sql="update vtiger_organizationdetails set organizationname = ?, address = ?, city = ?, state = ?,  code = ?, country = ?,  phone = ?, website = ?";
    $params = array($organization_name, $organization_address, $organization_city, $organization_state, $organization_code, $organization_country, $organization_phone, $organization_website);
    $this->_db->pquery($sql, $params);

    // Save logo
    $this->saveLogo();

    // Map currency
    $this->saveCurrency();

    $this->_log->debug(__FUNCTION__ . " end ");
  }

  protected function saveLogo() {
    global $root_directory;

    if(isset($this->_local_entity->logo->logo)) {
      // Save logo file locally
      $path = $root_directory . "test/logo/";
      $filename = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10) . '.jpg';
      $tmpLogoFilePath = $path . $filename;
      file_put_contents($tmpLogoFilePath, file_get_contents($this->_local_entity->logo->logo));

      $sql="update vtiger_organizationdetails set logoname = ?";
      $params = array($filename);
      $this->_db->pquery($sql, $params);
    }
  }

  protected function saveCurrency() {
    $result = $this->_db->pquery("select id from vtiger_currency_info where currency_code=?", array($this->_local_entity->currency));
    if($result->_numOfRows > 0) {
      $this->_log->debug("currency " . json_encode($this->_local_entity->currency) . " already exists");
    } else {
      // Fetch currency details
      $result_currency = $this->_db->pquery("SELECT * from vtiger_currencies WHERE currency_code=?", array($this->_local_entity->currency));
      if($result_currency->_numOfRows > 0) {
        $currencyid = $this->_db->query_result($result_currency,0,'currencyid');
        $currency_name = $this->_db->query_result($result_currency,0,'currency_name');
        $currency_code = $this->_db->query_result($result_currency,0,'currency_code');
        $currency_symbol = $this->_db->query_result($result_currency,0,'currency_symbol');

        // Insert new company currency
        $sql = "insert into vtiger_currency_info (id, currency_name, currency_code, currency_symbol, conversion_rate, currency_status, defaultid, deleted) values(?,?,?,?,?,?,?,?)";
        $params = array($this->_db->getUniqueID("vtiger_currency_info"), $currency_name, $currency_code, $currency_symbol, 1, 'Active','0','0');
        $this->_db->pquery($sql, $params);
      } else {
        $this->_log->debug("currency with code " . json_encode($this->_local_entity->currency) . " not found in vTiger");
      }
    }
  }

  public function getLocalEntityIdentifier() {
    return 0;
  }

}

?>
