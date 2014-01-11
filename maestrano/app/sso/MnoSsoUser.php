<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  /**
   * Vtiger User object
   * @var PDO
   */
  public $_user = null;
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $db_connection = null)
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Define global log
    global $log;
    $log = LoggerManager::getLogger('user');
    
    // Assign new attributes
    $this->connection = $db_connection;
    $this->_user = new Users();
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  // protected function setInSession()
  // {
  //   // First set $conn variable (used internally by collabtive methods)
  //   $conn = $this->connection;
  //   
  //   $sel1 = $conn->query("SELECT ID,name,lastlogin FROM user WHERE ID = $this->local_id");
  //   $chk = $sel1->fetch();
  //   if ($chk["ID"] != "") {
  //       $now = time();
  //       
  //       // Set session
  //       $this->session['userid'] = $chk['ID'];
  //       $this->session['username'] = stripslashes($chk['name']);
  //       $this->session['lastlogin'] = $now;
  //       
  //       // Update last login timestamp
  //       $upd1 = $conn->query("UPDATE user SET lastlogin = '$now' WHERE ID = $this->local_id");
  //       
  //       return true;
  //   } else {
  //       return false;
  //   }
  // }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      // Build local user
      $this->buildLocalUser();
      
      // Save user
      $lid = $this->_user->save('Users');
    }
    
    return $lid;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {    
    // Fetch record
    $query = "SELECT id from vtiger_users where mno_uid=?";
    $result = $this->connection->pquery($query, array($this->uid));
    
    // Return id value
    if ($result) {
      return $this->connection->query_result($result,0,'id');
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  // protected function getLocalIdByEmail()
  // {
  //   $result = $this->connection->query("SELECT ID FROM user WHERE email = {$this->connection->quote($this->email)} LIMIT 1")->fetch();
  //   
  //   if ($result && $result['ID']) {
  //     return $result['ID'];
  //   }
  //   
  //   return null;
  // }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   // protected function syncLocalDetails()
   // {
   //   if($this->local_id) {
   //     $upd = $this->connection->query("UPDATE user SET name = {$this->connection->quote($this->name . ' ' . $this->surname)}, email = {$this->connection->quote($this->email)} WHERE ID = $this->local_id");
   //     return $upd;
   //   }
   //   
   //   return false;
   // }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  // protected function setLocalUid()
  // {
  //   if($this->local_id) {
  //     $upd = $this->connection->query("UPDATE user SET mno_uid = {$this->connection->quote($this->uid)} WHERE ID = $this->local_id");
  //     return $upd;
  //   }
  //   
  //   return false;
  // }
  
  /**
   * Return whether the user should be admin or
   * not.
   * User is considered admin if app_owner or if
   * admin of the orga owning this app
   *
   * @return boolean true if admin, false otherwise
   */
  protected function isLocalUserAdmin() {
    $ret_value = false;
    
    if ($this->app_owner) {
      $ret_value = true; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $ret_value = true;
        } else {
          $ret_value = false;
        }
      }
    }
    
    return $ret_value;
  }
  
  /**
   * Build a vtiger user for creation
   *
   * @return Users the user object
   */
   protected function buildLocalUser()
   {
     $fields = &$this->_user->column_fields;
     $fields["user_name"] = $this->email;
     $fields["email1"] = $this->email;
     $fields["is_admin"] = $this->isLocalUserAdmin() ? 'on' : 'off';
     $fields["user_password"] = "123456789";
     $fields["confirm_password"] = "123456789";
     $fields["first_name"] = $this->name;
     $fields["last_name"] = $this->surname;
     $fields["roleid"] = "H2"; # H2 role cannot be deleted
     $fields["status"] = "Active";
     $fields["activity_view"] = "Today";
     $fields["lead_view"] = "Today";
     $fields["hour_format"] = "";
     $fields["end_hour"] = "";
     $fields["start_hour"] = "";
     $fields["title"] = "";
     $fields["phone_work"] = "";
     $fields["department"] = "";
     $fields["phone_mobile"] = "";
     $fields["reports_to_id"] = "";
     $fields["phone_other"] = "";
     $fields["email2"] = "";
     $fields["phone_fax"] = "";
     $fields["secondaryemail"] = "";
     $fields["phone_home"] = "";
     $fields["date_format"] = "dd-mm-yyyy";
     $fields["signature"] = "";
     $fields["description"] = "";
     $fields["address_street"] = "";
     $fields["address_city"] = "";
     $fields["address_state"] = "";
     $fields["address_postalcode"] = "";
     $fields["address_country"] = "";
     $fields["accesskey"] = "";
     $fields["time_zone"] = "UTC";
     $fields["currency_id"] = "1";
     $fields["currency_grouping_pattern"] = "123,456,789";
     $fields["currency_decimal_separator"] = "";
     $fields["currency_grouping_separator"] = "";
     $fields["currency_symbol_placement"] = "$1.0";
     $fields["imagename"] = "";
     $fields["internal_mailer"] = "on";
     $fields["theme"] = "softed";
     $fields["language"] = "en_us";
     $fields["reminder_interval"] = "None";
     $fields["asterisk_extension"] = "";
     $fields["use_asterisk"] = "on";
     $fields["ccurrency_name"] = "";
     $fields["currency_code"] = "";
     $fields["currency_symbol"] = "";
     $fields["conv_rate"] = "";
     
     return $this->_user;
   }
}