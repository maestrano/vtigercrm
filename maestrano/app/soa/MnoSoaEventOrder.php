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
    } else {
      $this->_log->debug("Event Id missing, fetching entity_id=" . $this->_event_id);
      
      $notification->entity = "events";
      $notification->id = $this->_event_id;
      $event = new MnoSoaEvent($this->_db, $this->_log);    
      $status = $event->receiveNotification($notification);
      if ($status) {
        $event_id = $this->getLocalIdByMnoIdName($this->_event_id, "EVENTS");
        if($event_id == null) { return constant('MnoSoaBaseEntity::STATUS_ERROR'); }
      }
    }

    // Map the attendees
    foreach ($this->_attendees as $attendee) {
      if($attendee->person) {
        $person_id = $this->getLocalIdByMnoIdName($attendee->person->id, "PERSONS");

        if(is_null($person_id)) {
          $this->_log->debug("Person Id missing, fetching entity_id=" . $attendee->person->id);
          $notification->entity = "persons";
          $notification->id = $attendee->person->id;
          $person = new MnoSoaPerson($this->_db, $this->_log);    
          $status = $person->receiveNotification($notification);
          if ($status) {
            $person_id = $this->getLocalIdByMnoIdName($attendee->person->id, "PERSONS");
          }
        }

        if($person_id) {
          // Fetch the Contact
          $vtiger_contact = CRMEntity::getInstance("Contacts");
          $vtiger_contact->retrieve_entity_info($person_id->_id, "Contacts");
          $vtiger_contact->id = $person_id->_id;
          $account_id = $vtiger_contact->column_fields['account_id'];
          // Fetch the Contact Organization
          $vtiger_account = CRMEntity::getInstance("Accounts");
          $vtiger_account->retrieve_entity_info($account_id, "Accounts");

          // Register the Contact to the Event
          $vtiger_account->save_related_module('Event', $event_id->_id, 'Contact', $person_id->_id);
        }
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