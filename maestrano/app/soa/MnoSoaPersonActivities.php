<?php

/**
 * Mno Person Activity Class
 */
class MnoSoaPersonActivities extends MnoSoaBaseEntity
{
  protected $_mno_entity_name = "activity";
  protected $_local_entity_name = "activity";

  protected $_mno_person;

  public function __construct($db, $log, $mno_person)
  {
    parent::__construct($db, $log);
    $this->_log->debug(__FUNCTION__ . " CONSTRUCT " . json_encode($mno_person));
    $this->_mno_person = $mno_person;
  }

  protected function build() {
    $this->_log->debug(__FUNCTION__ . " start");
    // TODO
    $this->_log->debug(__FUNCTION__ . " end");
  }

  protected function persist($activities) {
    global $adb;

    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " activities = " . json_encode($activities));

    $person_id = $this->_mno_person->getLocalEntityIdentifier();

    $contact = CRMEntity::getInstance("Contacts");
    $contact->retrieve_entity_info($person_id, "Contacts");

    foreach ($activities as $key => $mno_activity) {
      if (!empty($key)) {
        $this->_log->debug(__FUNCTION__ . " persist person activity " . json_encode($mno_activity));

        $is_update = false;
        $local_id = $this->getLocalIdByMnoId($key);
        if ($this->isValidIdentifier($local_id)) {
          $is_update = true;
        }
        $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
        
        // vTiger activities are mapped as 'Calendar' type
        $activity = CRMEntity::getInstance("Calendar");
        if ($is_update) {
          $activity->retrieve_entity_info($local_id->_id, "Calendar");
          $activity->id = $local_id->_id;
          $activity->mode = 'edit';
        } else if ($this->isDeletedIdentifier($local_id)) {
          continue;
        }

        // Get local user id from username
        $user_mno_id = key($mno_activity->assignedTo);
        $query = "SELECT id from vtiger_users where mno_uid=?";
        $result = $adb->pquery($query, array($user_mno_id));
        $user_id = $adb->query_result($result, 0, 'id');
        if(!isset($user_id)) { $user_id = "1"; }

        // Set the activity attributes
        $activity->column_fields['subject'] = $mno_activity->name;
        $activity->column_fields['assigned_user_id'] = $user_id;
        $activity->column_fields['date_start'] = gmdate("Y-m-d", $mno_activity->startDate);
        $activity->column_fields['time_start'] = gmdate("H:i:s", $mno_activity->startDate);
        $activity->column_fields['time_end'] = gmdate("H:i:s", $mno_activity->startDate);
        $activity->column_fields['due_date'] = gmdate("Y-m-d", $mno_activity->dueDate);
        $activity->column_fields['description'] = $mno_activity->description;
        $activity->column_fields['activitytype'] = 'Task';
        $activity->column_fields['taskpriority'] = 'High';
        $activity->column_fields['parent_id'] = $contact->column_fields['account_id'];
        $activity->column_fields['contact_id'] = $person_id;
        $activity->column_fields['taskstatus'] = $mno_activity->status;
        $activity->column_fields['sendnotification'] = 'on';
        $activity->column_fields['visibility'] = 'Private';

        $activity->save("Calendar", '', false);

        if(!$is_update) {
          $this->addIdMapEntry($activity->id, $key);
        }
      }
    }

    $this->_log->debug(__FUNCTION__ . " end persist");
  }

}

?>