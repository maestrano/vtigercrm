<?php

// Class helper for database connection
class PDOMock extends PDO {
    public function __construct() {}
    
    // Make it final to avoid stubbing
    public final function quote($arg)
    {
      return "'$arg'";
    }
}

// Class Test
class MnoSsoUserTest extends PHPUnit_Framework_TestCase
{
    private $_saml_settings;
    
    public function setUp()
    {
      parent::setUp();
      
      // Create SESSION
      $_SESSION = array();
      
      // Create global db variable $adb
      global $adb;
      $adb = $this->getMock('PearDatabase');
      
      $settings = new OneLogin_Saml_Settings;
      $settings->idpSingleSignOnUrl = 'http://localhost:3000/api/v1/auth/saml';

      // The certificate for the users account in the IdP
      $settings->idpPublicCertificate = <<<CERTIFICATE
-----BEGIN CERTIFICATE-----
MIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD
VQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT
EU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ
KoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5
WhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP
MA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG
A1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz
dHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu
P2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I
5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2
28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo
eHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ
jYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE
BxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN
bWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u
Y29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc
MPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc
2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES
Rkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==
-----END CERTIFICATE-----
CERTIFICATE;

      // The URL where to the SAML Response/SAML Assertion will be posted
      $settings->spReturnUrl = 'http://localhost:8888/maestrano/auth/saml/consume.php';

      // Name of this application
      $settings->spIssuer = 'bla.app.dev.maestrano.io';

      // Tells the IdP to return the email address of the current user
      $settings->requestedNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
      
      $this->_saml_settings = $settings;
    }
    
    // Used to test protected methods
    protected static function getMethod($name) {
      $class = new ReflectionClass('MnoSsoUser');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }
    
    public function testFunctionGetLocalIdByUid()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('getLocalIdByUid');
      
      // Build User
      $adb = $this->getMock('PearDatabase');
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = true;
      $sso_user->connection = $adb;
      $expected_id = 1234;
      
      // Stub pquery
      $adb->expects($this->once())
               ->method('pquery')
               ->with($this->equalTo("SELECT id from vtiger_users where mno_uid=?"), $this->equalTo(array($sso_user->uid)))
               ->will($this->returnValue('resultset'));
      
      // Stub query results
      $adb->expects($this->once())
               ->method('query_result')
               ->will($this->returnValue($expected_id));
      
      // Test return value
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionGetLocalIdByEmail()
    {
    }
    
    public function testFunctionSetLocalUid()
    {
    }
    
    public function testFunctionSyncLocalDetails()
    {
      
    }
    
    public function testFunctionCreateLocalUser()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('createLocalUser');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = true;
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a user stub
      $sso_user->_user = $this->getMock('Users');
      $sso_user->_user->expects($this->once())
               ->method('save')
               ->with($this->equalTo('Users'))
               ->will($this->returnValue($expected_id));
      
      
      // Test method returns the right id and used buildLocalUser
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
      $this->assertEquals($sso_user->email,$sso_user->_user->column_fields['user_name']);
    }
    
    
    public function testFunctionSignIn()
    {
    }
    
    public function testFunctionBuildLocalUser()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('buildLocalUser');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = true; 
      
      // Run method
      $protected_method->invokeArgs($sso_user,array());
      
      // Test that user fields have been populated correctly
      $f = $sso_user->_user->column_fields;
      $this->assertEquals($sso_user->email, $f["user_name"]);
      $this->assertEquals($sso_user->email, $f["email1"]);
      $this->assertEquals("on", $f["is_admin"]);
      $this->assertEquals("123456789", $f["user_password"]);
      $this->assertEquals("123456789", $f["confirm_password"]);
      $this->assertEquals($sso_user->name, $f["first_name"]);
      $this->assertEquals($sso_user->surname, $f["last_name"]);
      $this->assertEquals("H2", $f["roleid"]); # H2 role cannot be deleted
      $this->assertEquals("Active", $f["status"]);
      $this->assertEquals("Today", $f["activity_view"]);
      $this->assertEquals("Today", $f["lead_view"]);
      $this->assertEquals("", $f["hour_format"]);
      $this->assertEquals("", $f["end_hour"]);
      $this->assertEquals("", $f["start_hour"]);
      $this->assertEquals("", $f["title"]);
      $this->assertEquals("", $f["phone_work"]);
      $this->assertEquals("", $f["department"]);
      $this->assertEquals("", $f["phone_mobile"]);
      $this->assertEquals("", $f["reports_to_id"]);
      $this->assertEquals("", $f["phone_other"]);
      $this->assertEquals("", $f["email2"]);
      $this->assertEquals("", $f["phone_fax"]);
      $this->assertEquals("", $f["secondaryemail"]);
      $this->assertEquals("", $f["phone_home"]);
      $this->assertEquals("dd-mm-yyyy", $f["date_format"]);
      $this->assertEquals("", $f["signature"]);
      $this->assertEquals("", $f["description"]);
      $this->assertEquals("", $f["address_street"]);
      $this->assertEquals("", $f["address_city"]);
      $this->assertEquals("", $f["address_state"]);
      $this->assertEquals("", $f["address_postalcode"]);
      $this->assertEquals("", $f["address_country"]);
      $this->assertEquals("", $f["accesskey"]);
      $this->assertEquals("UTC", $f["time_zone"]);
      $this->assertEquals("1", $f["currency_id"]);
      $this->assertEquals("123,456,789", $f["currency_grouping_pattern"]);
      $this->assertEquals("", $f["currency_decimal_separator"]);
      $this->assertEquals("", $f["currency_grouping_separator"]);
      $this->assertEquals("$1.0", $f["currency_symbol_placement"]);
      $this->assertEquals("", $f["imagename"]);
      $this->assertEquals("on", $f["internal_mailer"]);
      $this->assertEquals("softed", $f["theme"]);
      $this->assertEquals("en_us", $f["language"]);
      $this->assertEquals("None", $f["reminder_interval"]);
      $this->assertEquals("", $f["asterisk_extension"]);
      $this->assertEquals("on", $f["use_asterisk"]);
      $this->assertEquals("", $f["ccurrency_name"]);
      $this->assertEquals("", $f["currency_code"]);
      $this->assertEquals("", $f["currency_symbol"]);
      $this->assertEquals("", $f["conv_rate"]);
    }
    
    public function testFunctionIsLocalUserAdminWhenAppOwner()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('isLocalUserAdmin');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->app_owner = true;    
      
      // Run method
      $this->assertEquals(true, $protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionIsLocalUserAdminWhenOrgaAdmin()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('isLocalUserAdmin');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->app_owner = false;
      $sso_user->organizations = array('org-xyz' => array('name' => 'MyOrga', 'role' => 'Admin'));
      
      // Run method
      $this->assertEquals(true, $protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionIsLocalUserAdminWhenNoAdmin()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('isLocalUserAdmin');
      
      // Create global db variable $adb
      global $adb;
      $adb = $this->getMock('PearDatabase');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->app_owner = false;
      $sso_user->organizations = array('org-xyz' => array('name' => 'MyOrga', 'role' => 'Member'));
      
      // Run method
      $this->assertEquals(false, $protected_method->invokeArgs($sso_user,array()));
    }
}