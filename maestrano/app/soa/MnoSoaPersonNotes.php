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

    foreach ($notes as $key => $note) {
      if (!empty($key)) {
        $this->_log->debug(__FUNCTION__ . " persist person note id = " . $key . " => " . json_encode($note));

        $is_update = false;
        $local_id = $this->getLocalIdByMnoId($key);
        if ($this->isValidIdentifier($local_id)) {
          $is_update = true;
        }
        $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
        
        // Save note as a Comment
        $this->_log->debug(__FUNCTION__ . " persisting comments");
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

        // Try to map note to a Contact custom field
        $this->_log->debug(__FUNCTION__ . " persisting custom fields");
        if(!is_null($note->tag) && $note->tag!='') {
          $this->_log->debug(__FUNCTION__ . " analysing tag $note->tag");
          // Match the note tag against a custom field
          $query = "SELECT fieldname from vtiger_field where tablename='vtiger_contactscf' and fieldlabel=?";
          $result = $this->_db->pquery($query, array($note->tag));
          if ($this->_db->num_rows($result)) {
            $fieldname = trim($this->_db->query_result($result, 0, "fieldname"));
            $this->_log->debug(__FUNCTION__ . " tag mapped to $fieldname");

            $query = "SELECT contactid from vtiger_contactscf where contactid=?";
            $result = $this->_db->pquery($query, array($person_id));
            if ($this->_db->num_rows($result)) {
              $query = "UPDATE vtiger_contactscf SET $fieldname = ? WHERE contactid = ?";  
              $result = $this->_db->pquery($query, array($note->value, $person_id));
            } else {
              $query = "INSERT INTO vtiger_contactscf (contactid, $fieldname) VALUES (?,?)";  
              $result = $this->_db->pquery($query, array($person_id, $note->value));
            }
          }
        }
      }
    }

    $this->_log->debug(__FUNCTION__ . " end persist");
  }

}

?>