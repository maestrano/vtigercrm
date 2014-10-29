<?php

/**
 * Maestrano map table functions
 *
 * @author root
 */

class MnoSoaEntity extends MnoSoaBaseEntity {    
    public function getUpdates($timestamp)
    {
        $this->_log->info(__FUNCTION__ .  " start getUpdates (timestamp=" . $timestamp . ")");
        $msg = $this->callMaestrano("GET", "updates" . '/' . $timestamp);
        if (empty($msg)) { return false; }
        $this->_log->debug(__FUNCTION__ .  " after maestrano call");

        if (!empty($msg->companys) && class_exists('MnoSoaCompany')) {
            $this->_log->debug(__FUNCTION__ . " has companys");
            foreach ($msg->companys as $company) {
                $this->_log->debug(__FUNCTION__ .  " company id = " . $company->id);
                $mno_company = new MnoSoaCompany($this->_db, $this->_log);
                $mno_company->receive($company);
            }
        }
        if (!empty($msg->taxCodes) && class_exists('MnoSoaTax')) {
            $this->_log->debug(__FUNCTION__ . " has taxCodes");
            foreach ($msg->taxCodes as $taxCode) {
                $this->_log->debug(__FUNCTION__ .  " taxCode id = " . $taxCode->id);
                $mno_tax_rate = new MnoSoaTax($this->_db, $this->_log);
                $mno_tax_rate->receive($taxCode);
            }
        }
        if (!empty($msg->organizations) && class_exists('MnoSoaOrganization')) {
            $this->_log->debug(__FUNCTION__ .  " has organizations");
            foreach ($msg->organizations as $organization) {
                $this->_log->debug(__FUNCTION__ .  " org id = " . $organization->id);
                $mno_org = new MnoSoaOrganization($this->_db, $this->_log);
                $mno_org->receive($organization);
            }
        }
        if (!empty($msg->persons) && class_exists('MnoSoaPerson')) {
            $this->_log->debug(__FUNCTION__ . " has persons");
            foreach ($msg->persons as $person) {
                $this->_log->debug(__FUNCTION__ .  " person id = " . $person->id);
                $mno_person = new MnoSoaPerson($this->_db, $this->_log);
                $mno_person->receive($person);
            }
        }
        if (!empty($msg->items) && class_exists('MnoSoaItem')) {
            $this->_log->debug(__FUNCTION__ . " has items");
            foreach ($msg->items as $item) {
                $this->_log->debug(__FUNCTION__ .  " item id = " . $item->id);
                $mno_item = new MnoSoaItem($this->_db, $this->_log);
                $mno_item->receive($item);
            }
        }
        if (!empty($msg->invoices) && class_exists('MnoSoaInvoice')) {
            $this->_log->debug(__FUNCTION__ . " has invoices");
            foreach ($msg->invoices as $invoice) {
                $this->_log->debug(__FUNCTION__ .  " invoice id = " . $invoice->id);
                $mno_invoice = new MnoSoaInvoice($this->_db, $this->_log);
                $mno_invoice->receive($invoice);
            }
        }
        $this->_log->info(__FUNCTION__ .  " getUpdates successful (timestamp=" . $timestamp . ")");
    }
}
