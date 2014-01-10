<?php

define('TEST_ROOT', __DIR__);

// Dependency: php-saml
define('PHP_SAML_DIR', './../../lib/php-saml/src/OneLogin/Saml/');
require PHP_SAML_DIR . 'AuthRequest.php';
require PHP_SAML_DIR . 'Response.php';
require PHP_SAML_DIR . 'Settings.php';
require PHP_SAML_DIR . 'XmlSec.php';

// Dependency: mno-php/sso
define('MNO_PHP_SSO_DIR', './../../lib/mno-php/src/sso/');
require MNO_PHP_SSO_DIR . 'MnoSsoBaseUser.php';

// Dependency: Collabtive native classes
define('COLLAB_INCLUDE_DIR', './../../../include/');
require COLLAB_INCLUDE_DIR . 'initfunctions.php';
require COLLAB_INCLUDE_DIR . 'class.mylog.php';
require COLLAB_INCLUDE_DIR . 'class.user.php';
require COLLAB_INCLUDE_DIR . 'class.roles.php';

// Tested class: 
define('TEST_INT_SSO_DIR', './../sso/');
require TEST_INT_SSO_DIR . 'MnoSsoUser.php';

// Set timezone
date_default_timezone_set('UTC');