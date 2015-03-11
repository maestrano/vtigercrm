<?php
// Get full host (protocal + server host)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$full_host = $protocol . $_SERVER['HTTP_HOST'];

// Id of the application
$mno_settings->app_id = 'vtiger.app.dev.maestrano.io';

// Name of your application
$mno_settings->app_name = 'vtigercrm';

// Enable Maestrano SSO for this app
$mno_settings->sso_enabled = true;

// SSO initialization URL
$mno_settings->sso_init_url = $full_host . '/maestrano/auth/saml/index.php';

// SSO processing url
$mno_settings->sso_return_url = $full_host . '/maestrano/auth/saml/consume.php';

// SSO initialization URL
$mno_settings->soa_init_url = $full_host . '/maestrano/data/initialize.php';

// Enable Maestrano SSO for this app
$mno_settings->soa_enabled = true;

// API Keys
$api_key = '4a9385079b27de81155fedec44a45738c346bba2445dc6eafe4f256ef3d8b85a';
$api_secret = 'c40f55b3-f9fa-4a9b-aba2-5bfb93a2e9f4';
