<?php

/**
 * Mno Invoice Class
 */
class MnoSoaInvoice extends MnoSoaBaseInvoice {
  protected $_local_entity_name = "INVOICE";

  protected function pushInvoice() {
    $this->_log->debug("start pushInvoice " . json_encode($this->_local_entity->column_fields));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;

    if($this->_local_entity->column_fields['invoice_no'] != 'AUTO GEN ON SAVE') {
      $this->_transaction_number = $this->push_set_or_delete_value($this->_local_entity->column_fields['invoice_no']);
    }

    if(isset($this->_local_entity->column_fields['invoicedate'])) { $this->_transaction_date = strtotime($this->_local_entity->column_fields['invoicedate']); }
    if(isset($this->_local_entity->column_fields['duedate'])) { $this->_due_date = strtotime($this->_local_entity->column_fields['duedate']); }
    
    $this->_amount = array();
    if(isset($this->_local_entity->column_fields['total'])) { $this->_amount["price"] = $this->_local_entity->column_fields['total']; }

    // Map status
    $invoicestatus = $this->_local_entity->column_fields['invoicestatus'];
    if(isset($invoicestatus)) {
      if($invoicestatus == 'Sent') {
        $this->_status = 'SUBMITTED';
      } else if($invoicestatus == 'Approved') {
        $this->_status = 'AUTHORISED';
      } else if($invoicestatus == 'Paid') {
        $this->_status = 'PAID';
      } else {
        $this->_status = 'DRAFT';
      }
    } else {
      $this->_status = 'DRAFT';
    }

    // Map Organization
    if(isset($this->_local_entity->column_fields['account_id'])) {
      $mno_oragnization_id = $this->getMnoIdByLocalIdName($this->_local_entity->column_fields['account_id'], "ACCOUNTS");
      $this->_organization_id = $mno_oragnization_id->_id;
    }

    // Map Contact
    if(isset($this->_local_entity->column_fields['contact_id'])) {
      $mno_person_id = $this->getMnoIdByLocalIdName($this->_local_entity->column_fields['contact_id'], "CONTACTS");
      $this->_person_id = $mno_person_id->_id;
    }

    // Map invoice lines
    $this->_invoice_lines = array();
    $tot_no_prod = $this->_local_entity->column_fields['totalProductCount'];
    for($i=1; $i<=$tot_no_prod; $i++) {
      $invoice_line = array();

      if(isset($id)) {
        // vTiger recreates the invoice lines on every save, so local IDs are not mappable
        // Use Invoice ID + Line number instead
        $invoice_line_id = $id . "-" . $i;
        $mno_entity = $this->getMnoIdByLocalIdName($invoice_line_id, "INVOICE_LINE");
        if($this->isValidIdentifier($mno_entity)) {
          $invoice_line_mno_id = $mno_entity->_id;
        } else {
          // Generate and save ID
          $invoice_line_mno_id = uniqid();
          $this->_mno_soa_db_interface->addIdMapEntry($invoice_line_id, "INVOICE_LINE", $invoice_line_mno_id, "INVOICE_LINE");
        }
      } else {
        $invoice_line_mno_id = uniqid();
      }

      if($this->_local_entity->column_fields["deleted".$i] == 1) {
        $invoice_line = '';
      } else {
        $invoice_line['lineNumber'] = $i;

        $quantity = floatval($this->_local_entity->column_fields['qty'.$i]);
        $invoice_line['quantity'] = $quantity;

        $total_line_tax = 0;
        if(isset($this->_local_entity->column_fields['popup_tax_row'.$i])) {
          $total_line_tax = floatval($this->_local_entity->column_fields['popup_tax_row'.$i]);
        }

        $unit_price = floatval($this->_local_entity->column_fields['listPrice'.$i]);
        $invoice_line['unitPrice']['netAmount'] = $unit_price;
        $invoice_line['unitPrice']['taxAmount'] = $total_line_tax / $quantity;
        $invoice_line['unitPrice']['price'] = $unit_price + $total_line_tax / $quantity;

        $invoice_line['totalPrice']['taxAmount'] = $total_line_tax;
        $invoice_line['totalPrice']['netAmount'] = $unit_price * $quantity;
        $invoice_line['totalPrice']['price'] = $unit_price * $quantity + $total_line_tax;

        if($this->_local_entity->column_fields["discount_type".$i] == 'percentage') {
          $discount_percentage = $this->_local_entity->column_fields['discount_percentage'.$i];
        }

        $product_id = $this->_local_entity->column_fields['hdnProductId'.$i];
        if(isset($product_id)) {
          $mno_product_id = $this->getMnoIdByLocalIdName($product_id, "PRODUCTS");
          $item_id = $mno_product_id->_id;
          $invoice_line['item']->id = $mno_product_id->_id;
        }
      }

      $this->_invoice_lines[$invoice_line_mno_id] = $invoice_line;
    }

    $this->_log->debug("after pushInvoice");
  }

  protected function pullInvoice() {
    $this->_log->debug("start " . __FUNCTION__ . " for " . json_encode($this->_id));
        
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);
      $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));

      if($this->_type == 'SUPPLIER') {
        // TODO: Map as a SalesOrder
        $this->_log->debug("skipping supplier sale order");
        return constant('MnoSoaBaseEntity::STATUS_ERROR');
      }
      
      if ($this->isValidIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_EXISTING_ID");
        $this->_local_entity = CRMEntity::getInstance("Invoice");
        $this->_local_entity->retrieve_entity_info($local_id->_id,"Invoice");
        vtlib_setup_modulevars("Invoice", $this->_local_entity);
        $this->_local_entity->id = $local_id->_id;
        $this->_local_entity->mode = 'edit';
        $status = constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $status = constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $this->_local_entity = new Invoice();
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }

      // Map invoice attributes
      $this->_local_entity->column_fields['subject'] = $this->pull_set_or_delete_value($this->_transaction_number);
      $this->_local_entity->column_fields['invoice_no'] = $this->pull_set_or_delete_value($this->_transaction_number);
      if($this->_transaction_date) { $this->_local_entity->column_fields['invoicedate'] = date('Y-m-d', $this->_transaction_date); }
      if($this->_due_date) { $this->_local_entity->column_fields['duedate'] = date('Y-m-d', $this->_due_date); }

      // Map status
      if($this->_status == 'SUBMITTED') {
        $this->_local_entity->column_fields['invoicestatus'] = 'Sent';
      } else if($this->_status == 'AUTHORISED') {
        $this->_local_entity->column_fields['invoicestatus'] = 'Approved';
      } else if($this->_status == 'PAID') {
        $this->_local_entity->column_fields['invoicestatus'] = 'Paid';
      } else {
        $this->_local_entity->column_fields['invoicestatus'] = 'Created';
      }

      // Map local organization
      if($this->_organization_id) {
        $local_id = $this->getLocalIdByMnoIdName($this->_organization_id, "organizations");
        if ($this->isValidIdentifier($local_id)) {
          $this->_log->debug(__FUNCTION__ . " organization local_id = " . json_encode($local_id));
          $this->_local_entity->column_fields['account_id'] = $local_id->_id;
        } else {
          // Fetch remote Organization if missing
          $notification->entity = "organizations";
          $notification->id = $this->_organization_id;
          $organization = new MnoSoaOrganization($this->_db);   
          $status = $organization->receiveNotification($notification);
          if ($status) {
            $this->_local_entity->column_fields['account_id'] = $organization->_local_entity->id;
          }
        }
      }

      // Map local contact
      if($this->_person_id) {
        $local_id = $this->getLocalIdByMnoIdName($this->_person_id, "persons");
        if ($this->isValidIdentifier($local_id)) {
          $this->_log->debug(__FUNCTION__ . " person local_id = " . json_encode($local_id));
          $this->_local_entity->column_fields['contact_id'] = $local_id->_id;
        } else {
          // Fetch remote person if missing
          $notification->entity = "persons";
          $notification->id = $this->_person_id;
          $person = new MnoSoaPerson($this->_db);   
          $status = $person->receiveNotification($notification);
          if ($status) {
            $this->_local_entity->column_fields['contact_id'] = $person->_local_entity->id;
          }
        }
      }

      // Map invoice lines
      if(!empty($this->_invoice_lines)) {
        // The class include/utils/InventoryUtils.php expects to find a $_REQUEST object with the invoice lines populated
        
        $_REQUEST['subtotal'] = $this->_amount->price;
        $_REQUEST['total'] = $this->_amount->price;

        $line_count = 0;
        foreach($this->_invoice_lines as $line_id => $line) {
          $line_count++;

          $local_line_id = $this->getLocalIdByMnoId($line_id);
          if($this->isDeletedIdentifier($local_line_id)) {
            continue;
          }

          // Map item
          if(!empty($line->item)) {
            $local_item_id = $this->getLocalIdByMnoIdName($line->item->id, "ITEMS");
            $_REQUEST['hdnProductId'.$line_count] = $local_item_id->_id;
          }

          // Map attributes
          $_REQUEST['qty'.$line_count] = $line->quantity;
          $_REQUEST['listPrice'.$line_count] = $line->unitPrice->price;

          if(isset($line->reductionPercent)) {
            $_REQUEST['discount_type'.$line_count] = 'percentage';
            $_REQUEST['discount_percentage'.$line_count] = $line->reductionPercent;
          }

          // TODO: Map taxes
        }
        $_REQUEST['totalProductCount'] = $line_count;
      }

    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    return $status;
  }

  public function send($local_entity) {
    parent::send($local_entity);

    $this->mapInvoiceLinesIds();
  }

  protected function mapInvoiceLinesIds() {
    $local_entity_id = $this->getLocalEntityIdentifier();
    $mno_entity_id = $this->_id;


    // Map invoice lines IDs
    foreach($this->_invoice_lines as $line_id => $line) {
      $invoice_line_id = $local_entity_id . "-" . $line->lineNumber;
      $mno_entity = $this->getMnoIdByLocalIdName($invoice_line_id, "INVOICE_LINE");
      if (!$this->isValidIdentifier($mno_entity)) {
        $this->_mno_soa_db_interface->addIdMapEntry($invoice_line_id, "INVOICE_LINE", $line_id, "INVOICE_LINE");
      }
    }
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    $this->_local_entity->save("Invoice", '', $push_to_maestrano);

    // Map invoice ID
    if ($status == constant('MnoSoaBaseEntity::STATUS_NEW_ID')) {
      $local_entity_id = $this->getLocalEntityIdentifier();
      $mno_entity_id = $this->_id;
      $this->addIdMapEntry($local_entity_id, $mno_entity_id);
    }

    $this->mapInvoiceLinesIds();
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->id;
  }
}

?>