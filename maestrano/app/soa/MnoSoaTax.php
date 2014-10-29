<?php

/**
 * Mno Tax Class
 */
class MnoSoaTax extends MnoSoaBaseTax {
  protected $_local_entity_name = "TAX";

  public function sendAllTaxes() {
    $this->_log->debug("start sendAllTaxes()");
    // Push all tax codes
    $tax_details = getAllTaxes();
    foreach ($tax_details as $tax_detail) {
      $this->_log->debug("push tax " . json_encode($tax_detail));

      $mno_id = $this->getMnoIdByLocalId($tax_detail['taxid']);
      $this->_log->debug("push tax with mno_id" . json_encode($mno_id));

      $mno_tax = new MnoSoaTax($this->_db, $this->_log);
      $mno_tax->_id = $mno_id->_id;
      $mno_tax->_name = $tax_detail['taxlabel'];
      $mno_tax->_rate = $tax_detail['percentage'];
      $mno_tax->send($tax_detail);
    }
    $this->_log->debug("end sendAllTaxes()");
  }

  protected function pushTax() {
    $this->_log->debug("start pushTax " . json_encode($this->_local_entity));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;

    // Push tax attributes
    // TODO

    $this->_log->debug("after pushTax");
  }

  protected function pullTax() {
    $this->_log->debug("start " . __FUNCTION__ . " for " . json_encode($this->_id));
        
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);
      $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
      
      if ($this->isValidIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " updating tax rate " . json_encode($local_id));
        $status = constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $status = constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $this->_log->debug(__FUNCTION__ . " creating new tax rate " . json_encode($local_id));
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status");

    $local_id = $this->getLocalIdByMnoId($this->_id);
    $tax_name = $this->pull_set_or_delete_value($this->_name);
    $tax_rate = $this->pull_set_or_delete_value($this->_rate);

    $this->_log->debug("creating or updating tax $tax_name => $tax_rate with id " . json_encode($local_id));

    if(!isset($tax_rate) || $tax_rate == 0.0) { return null; }

    if($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
      // Try to find any existing tax rate with same name
      $local_tax = $this->findTaxByLabel($tax_name);
      if(isset($local_tax)) {
        $tax_id = $local_tax['taxid'];
        $query = "UPDATE vtiger_inventorytaxinfo SET percentage=? AND taxlabel=? WHERE taxid=?";
        $this->_db->pquery($query, array($tax_rate, $tax_name, $tax_id));
      } else {
        $tax_id = $this->addTaxType($tax_name, $tax_rate);
      }

      // Map Tax ID
      $this->addIdMapEntry($tax_id, $this->_id);
    }

    if($status == constant('MnoSoaBaseEntity::STATUS_EXISTING_ID')) {
      // Update tax rate
      $tax_id = $local_id->_id;
      if(isset($tax_id)) {
        $update_query = "UPDATE vtiger_inventorytaxinfo SET percentage=? AND taxlabel=? WHERE taxid=?";
        // THIS LINE RESETS ALL TAX RATE TO 1 - NEED TO INVESTIGATE WHY
        // $this->_db->pquery($update_query, array($tax_rate, $tax_name, $tax_id));
      }
    }
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity['taxid'];
  }

  private function findTaxByLabel($tax_label) {
    $tax_details = getAllTaxes();
    foreach ($tax_details as $tax_detail) {
      if($tax_detail['taxlabel'] == $tax_label) {
        return $tax_detail;
      }
    }
    return null;
  }

  private function addTaxType($taxlabel, $taxvalue) {
    $check_query = "select taxlabel from vtiger_inventorytaxinfo where taxlabel=?";
    $check_res = $this->_db->pquery($check_query, array($taxlabel));
    if($this->_db->num_rows($check_res) > 0) { return null; }

    $taxid = $this->_db->getUniqueID("vtiger_inventorytaxinfo");
    $taxname = "tax".$taxid;
    $query = "alter table vtiger_inventoryproductrel add column $taxname decimal(7,3) default NULL";
    $res = $this->_db->pquery($query, array());

    if($res) {
      $query = "insert into vtiger_inventorytaxinfo values(?,?,?,?,?)";
      $params = array($taxid, $taxname, $taxlabel, $taxvalue, 0);
      $result = $this->_db->pquery($query, $params);
    }

    return $taxid;
  }
}

?>