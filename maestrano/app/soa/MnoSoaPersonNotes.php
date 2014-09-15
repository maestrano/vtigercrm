<?php

/**
 * Mno Person Notes Class
 */
class MnoSoaPersonNotes extends MnoSoaBaseEntity
{
  protected $_mno_entity_name = "notes";
  protected $_local_entity_name = "mod_comments";

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

  protected function persist($notes) {
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " notes = " . json_encode($notes));

    $person_id = $this->_mno_person->getLocalEntityIdentifier();
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " person = " . json_encode($this->_mno_person));
    $this->_log->debug(__CLASS__ . " " . __FUNCTION__ . " person local id= " . json_encode($person_id));

    foreach ($notes as $key => $note) {
      if (!empty($key)) {
        $this->_log->debug(__FUNCTION__ . " persist person note id = " . $key);

        $is_update = false;
        $local_id = $this->getLocalIdByMnoId($key);
        if ($this->isValidIdentifier($local_id)) {
          $is_update = true;
        }
        $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
        
        $mod_comment = CRMEntity::getInstance("ModComments");
        if ($is_update) {
          $mod_comment->retrieve_entity_info($local_id->_id, "ModComments");
          $mod_comment->id = $local_id->_id;
          $mod_comment->mode = 'edit';
        } else if ($this->isDeletedIdentifier($local_id)) {
          continue;
        } else {
          $mod_comment->column_fields['assigned_user_id'] = 0;
          $mod_comment->column_fields['related_to'] = $person_id;
        }

        $mod_comment->column_fields['commentcontent'] = $note->description;
        $mod_comment->save("ModComments", '', false);

        if(!$is_update) {
          $this->addIdMapEntry($mod_comment->id, $key);
        }
      }
    }

    $this->_log->debug(__FUNCTION__ . " end persist");
  }

}

?>