<?php

if (!defined('APP_DIR')) {
  define("APP_DIR", realpath(dirname(__FILE__) . '/../../../'));
}
chdir(APP_DIR);

require_once 'vtlib/Vtiger/Field.php';
require_once 'vtlib/Vtiger/Module.php';

if(file_exists ('modules/Event/Event.php')) {
  require_once 'modules/Event/Event.php';
  require_once 'modules/Tickets/Tickets.php';
}

/**
 * Mno EventOrder Class
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
        $this->_log->debug("Event Order already processed, skip");
        return constant('MnoSoaBaseEntity::STATUS_EXISTING_ID');
      } else if ($this->isDeletedIdentifier($local_id)) {
        $this->_log->debug(__FUNCTION__ . " is STATUS_DELETED_ID");
        $this->_log->debug("Event Order deleted, skip");
        return constant('MnoSoaBaseEntity::STATUS_DELETED_ID');
      } else {
        $status = constant('MnoSoaBaseEntity::STATUS_NEW_ID');
      }
    } else {
      return constant('MnoSoaBaseEntity::STATUS_ERROR');
    }

    // Map the Event
    $this->_log->debug("map EventOrder related Event " . $this->_event_id);
    if(is_null($this->_event_id)) { return null; }
   
    $event_id = $this->getLocalIdByMnoIdName($this->_event_id, "EVENTS");
    if(is_null($event_id)) {
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
    // Fetch the Event
    $vtiger_event = CRMEntity::getInstance("Event");
    $vtiger_event->retrieve_entity_info($event_id->_id, "Event");

    // Map the attendees
    foreach ($this->_attendees as $attendee) {
      if($attendee->person) {
        $this->_log->debug("map EventOrder related Attendee " . $attendee->person->id);
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
        if(is_null($person_id)) { return null; }

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

        // Map Organization Membership Type onto Ticket
        $organization_membership_label = $this->findFieldByLabel('Accounts', 'Membership Type');
        if($organization_membership_label) {
          // Find Ticket Membership Type label
          $ticket_membership_label = $this->findFieldByLabel('Tickets', 'Membership Type');
          if($ticket_membership_label) {
            // Fetch Organization membership type
            $organization_membership = $vtiger_account->column_fields[$organization_membership_label->name];
            if(is_null($organization_membership)) { $organization_membership = ''; }

            // Find or Create Ticket of this Membership Type
            $ticket = $this->findTicketByEventAndType($event_id->_id, $organization_membership);

            if(is_null($ticket)) {
              $ticket = new Tickets();
              $ticket->column_fields['assigned_user_id'] = "1";
              $ticket->column_fields['tksevent'] = $event_id->_id;
              $ticket->column_fields['tksticketname'] = $vtiger_event->column_fields['tkseventname'] . " - " . $organization_membership;
              $ticket->column_fields['tksdescription'] = $vtiger_event->column_fields['tkseventname'] . " - " . $organization_membership;
              $ticket->column_fields['tickets_tksmembershiptype'] = $organization_membership;
              $ticket->column_fields['tksamountavailable'] = null;
              $ticket->column_fields['tksticketprice'] = null;
              $ticket->column_fields['tksstartdate'] = $vtiger_event->column_fields['tksdateofevent'];
              $ticket->column_fields['tksenddate'] = null;
              $ticket->column_fields['tkschannels'] = 'Online';
              $ticket->save('Tickets');
            }

            // Link the Contacts to the Ticket
            $vtiger_event->save_related_module('Tickets', $ticket->id, 'Contact', $vtiger_contact->id);
          }
        }
      }
    }

    return $status;
  }

  protected function saveLocalEntity($push_to_maestrano, $status) {
    $this->_log->debug("start saveLocalEntity status=$status " . json_encode($this->_local_entity->column_fields));
    
  }

  public function getLocalEntityIdentifier() {
    return 1;
  }

  private function findFieldByLabel($module_name, $field_label) {
    $account_module = Vtiger_Module::getInstance($module_name);
    $fields = Vtiger_Field::getAllForModule($account_module);
    foreach ($fields as $field) {
      if($field->label == $field_label) {
        return $field;
      }
    }
    return null;
  }

  private function findTicketByEventAndType($event_id, $membership_type) {
    $ticket = CRMEntity::getInstance("Tickets");
    $query = $ticket->getListQuery('Tickets', ' AND vtiger_tickets.tksevent = ' . $event_id . " AND vtiger_tickets.tickets_tksmembershiptype = '" . $membership_type . "'");
    $result = $this->_db->pquery($query, array());
    if($result) {
      foreach ($result as $event_ticket) {     
        $ticket_id = $event_ticket['crmid'];
        $ticket->retrieve_entity_info($ticket_id, "Tickets");
        $ticket->id = $ticket_id;
        return $ticket;
      }
    }
    return null;
  }
}

?>