<?php

if (!defined('APP_DIR')) {
  define("APP_DIR", realpath(dirname(__FILE__) . '/../../../'));
}
chdir(APP_DIR);
require_once 'modules/Event/Event.php';
require_once 'modules/Tickets/Tickets.php';

/**
 * Mno Event Class
 */
class MnoSoaEventOrder extends MnoSoaBaseEventOrder {
  protected $_local_entity_name = "EVENT_ORDER";

  protected function pushEventOrder() {
    $this->_log->debug("start pushEventOrder " . json_encode($this->_local_entity->column_fields));

    $id = $this->getLocalEntityIdentifier();
    if (empty($id)) { return; }

    $mno_id = $this->getMnoIdByLocalIdName($id, $this->_local_entity_name);
    $this->_id = ($this->isValidIdentifier($mno_id)) ? $mno_id->_id : null;



    $this->_log->debug("after pushEventOrder");
  }

  protected function pullEventOrder() {
    $this->_log->debug("start " . __FUNCTION__ . " for " . json_encode($this->_id));
    
    if (!empty($this->_id)) {
      $local_id = $this->getLocalIdByMnoId($this->_id);
      $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
      if ($this->isValidIdentifier($local_id)) {
        $status = constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $status = constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      $status = constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    // Map the Event
    if($this->_event_id) {
      $event_id = $this->getLocalIdByMnoIdName($this->_event_id, "EVENTS");
$this->_log->debug("FETCH EVENT ID: " . json_encode($event_id));
    } else {
      $this->_log->debug("Event Id missing");
      return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }
$this->_log->debug("PROCESSIGN ATTENDEES: " . json_encode($this->_attendees));
    // Map the attendees
    foreach ($this->_attendees as $attendee) {
$this->_log->debug("PROCESSIGN ATTENDEE: " . json_encode($attendee));
      if($attendee->person) {
$this->_log->debug("FETCH PERSON ID: " . json_encode($attendee->person->id));
        $person_id = $this->getLocalIdByMnoIdName($attendee->person->id, "PERSONS");
$this->_log->debug("FOUND PERSON : " . json_encode($person_id));

        if($person_id) {
          // Fetch the Contact
          $vtiger_contact = CRMEntity::getInstance("Contacts");
          $vtiger_contact->retrieve_entity_info($person_id->_id, "Contacts");
          $vtiger_contact->id = $person_id->_id;
          $account_id = $vtiger_contact->column_fields['account_id'];
  $this->_log->debug("VTIGER CONTACT : " . json_encode($vtiger_contact));
          // Fetch the Account
          $vtiger_account = CRMEntity::getInstance("Accounts");
          $vtiger_account->retrieve_entity_info($account_id, "Accounts");
  $this->_log->debug("VTIGER ACCOUNT : " . json_encode($vtiger_account));
          $vtiger_account->save_related_module('Event', $event_id->_id, 'Contact', $person_id->_id);
        }
      } else {
        $this->_log->debug("Skipping attendee block " . $attendee->id . " due to person missing");
      }
    }

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    
  }

  public function getLocalEntityIdentifier() {
    return $this->_local_entity->id;
  }
}

?>