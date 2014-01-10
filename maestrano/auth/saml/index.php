<?php
/**
 * This controller creates a SAML request and redirects to
 * Maestrano SAML Identity Provider
 *
 */

error_reporting(0);

$mno_settings = NULL;
require '../../app/init/auth.php';

// Build SAML request and Redirect to IDP
$authRequest = new OneLogin_Saml_AuthRequest($mno_settings->getSamlSettings());
$url = $authRequest->getRedirectUrl();

header("Location: $url");