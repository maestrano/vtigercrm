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
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " activities = " . json_encode($activities));

    $person_id = $this->_mno_person->getLocalEntityIdentifier();
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " person = " . json_encode($this->_mno_person));
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " person local id= " . json_encode($person_id));

    foreach ($activities as $key => $activity) {
      if (!empty($key)) {
        $this->_log->debug(__FUNCTION__ . " persist person activity " . json_encode($activity));

        $is_update = false;
        $local_id = $this->getLocalIdByMnoId($key);
        if ($this->isValidIdentifier($local_id)) {
          $is_update = true;
        }
        $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
        
        $activity = CRMEntity::getInstance("Calendar");
        if ($is_update) {
          $activity->retrieve_entity_info($local_id->_id, "Calendar");
          $activity->id = $local_id->_id;
          $activity->mode = 'edit';
        } else if ($this->isDeletedIdentifier($local_id)) {
          continue;
        }

        $activity->column_fields['subject'] = $activity->name;
        $activity->column_fields['assigned_user_id'] = 1;
        // $activity->column_fields['date_start'] = '2014-09-15';
        // $activity->column_fields['time_start'] = '11:50:00';
        // $activity->column_fields['time_end'] = '12:00:00';
        // $activity->column_fields['due_date'] = '2014-09-15';
        $activity->column_fields['description'] = $activity->description;
        $activity->column_fields['status'] = $activity->status;
        $activity->column_fields['activitytype'] = 'Task';
        $activity->column_fields['taskpriority'] = 'High';
        // $activity->column_fields['parent_id'] = "269";
        $activity->column_fields['contact_id'] = $person_id;
        $activity->column_fields['taskstatus'] = 'Not Started';
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