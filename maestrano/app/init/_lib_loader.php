<?php
//define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

//-----------------------------------------------
// Require dependencies
//-----------------------------------------------
define('PHP_SAML_XMLSECLIBS_DIR', MAESTRANO_ROOT . '/lib/php-saml/ext/xmlseclibs/');
require_once PHP_SAML_XMLSECLIBS_DIR . 'xmlseclibs.php';

define('PHP_SAML_DIR', MAESTRANO_ROOT . '/lib/php-saml/src/OneLogin/Saml/');
require_once PHP_SAML_DIR . 'AuthRequest.php';
require_once PHP_SAML_DIR . 'Response.php';
require_once PHP_SAML_DIR . 'Settings.php';
require_once PHP_SAML_DIR . 'XmlSec.php';

//-----------------------------------------------
// Require Maestrano library
//-----------------------------------------------
define('MNO_PHP_DIR', MAESTRANO_ROOT . '/lib/mno-php/src/');
require_once MNO_PHP_DIR . 'MnoSettings.php';
require_once MNO_PHP_DIR . 'MaestranoService.php';
require_once MNO_PHP_DIR . 'sso/MnoSsoBaseUser.php';
require_once MNO_PHP_DIR . 'sso/MnoSsoSession.php';
require_once MNO_PHP_DIR . 'soa/MnoSoaBaseLogger.php';
require_once MNO_PHP_DIR . 'soa/MnoSoaBaseDB.php';
require_once MNO_PHP_DIR . 'soa/MnoSoaBaseEntity.php';
require_once MNO_PHP_DIR . 'soa/MnoSoaBaseOrganization.php';
require_once MNO_PHP_DIR . 'soa/MnoSoaBasePerson.php';

//-----------------------------------------------
// Require Maestrano app files
//-----------------------------------------------
define('MNO_APP_DIR', MAESTRANO_ROOT . '/app/');
require_once MNO_APP_DIR . 'sso/MnoSsoUser.php';
require_once MNO_APP_DIR . 'soa/MnoSoaEntity.php';
require_once MNO_APP_DIR . 'soa/MnoSoaDB.php';
require_once MNO_APP_DIR . 'soa/MnoSoaOrganization.php';
require_once MNO_APP_DIR . 'soa/MnoSoaPerson.php';
