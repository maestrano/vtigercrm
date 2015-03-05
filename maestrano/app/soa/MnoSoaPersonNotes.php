<?php

/**
 * Mno Person Notes Class
 */
class MnoSoaPersonNotes extends MnoSoaBaseEntity
{
  protected $_mno_entity_name = "notes";
  protected $_local_entity_name = "documents";

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
          } else {
            // Save the note as a document
            $is_update = false;
            $local_id = $this->getLocalIdByMnoId($key);
            if ($this->isValidIdentifier($local_id)) { $is_update = true; }
            $this->_log->debug(__FUNCTION__ . " this->getLocalIdByMnoId(this->_id) = " . json_encode($local_id));
            
            // Save note as a Document
            $this->_log->debug(__FUNCTION__ . " persisting comments");
            $document = CRMEntity::getInstance("Documents");
            if ($is_update) {
              $document->retrieve_entity_info($local_id->_id, "Documents");
              $document->id = $local_id->_id;
              $document->mode = 'edit';
            } else if ($this->isDeletedIdentifier($local_id)) {
              continue;
            } else {
              $document->column_fields['assigned_user_id'] = "1";
              $document->parentid = $person_id;
            }

            $document->column_fields['notecontent'] = $note->description;
            $document->column_fields['title'] = $note->tag;
            $document->column_fields['notes_title'] = $note->tag;
            $document->save("Documents", '');

            if(!$is_update) { $this->addIdMapEntry($document->id, $key); }
          }
        }
      }
    }

    $this->_log->debug(__FUNCTION__ . " end persist");
  }

}

?>