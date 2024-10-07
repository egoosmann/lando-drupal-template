<?php

// ### Environment
$settings['environment'] = 'production';

// ### Domains
$settings['base_domains'] = [
  '',
];

// ### PHP
$settings['show_php_errors'] = FALSE;

// ### Cache
$settings['disable_cache'] = FALSE;

// ### Database
$settings['db_name'] = '';
$settings['db_user'] = '';
$settings['db_pass'] = '';
$settings['db_host'] = '127.0.0.1';
$settings['db_port'] = '3306';

// ### Salt
$settings['hash_salt'] = '';

// ### SMTP
$settings['smtp_on'] = TRUE;
$settings['smtp_username'] = '';
$settings['smtp_password'] = '';
$settings['smtp_host'] = '';
$settings['smtp_port'] = '587'; // 25, 465, 587, 1025
$settings['smtp_protocol'] = 'tls'; // standard, ssl, tls
$settings['smtp_autotls'] = TRUE;
$settings['smtp_from'] = '';
$settings['smtp_fromname'] = '';
$settings['smtp_allowhtml'] = TRUE;
$settings['smtp_debugging'] = FALSE;

// ### Sentry
$settings['sentry_client_key'] = NULL; // php logging
$settings['sentry_public_dsn'] = NULL; // javascript logging
$settings['sentry_environment'] = NULL; // environment name
$settings['sentry_release'] = NULL; // release or version

// ### Redis
$settings['redis_interface'] = 'PhpRedis';
$settings['redis_host'] = '127.0.0.1';
$settings['redis_compress_length'] = 100;

// ### Seckit
$settings['seckit_csp_enabled'] = TRUE;
$settings['seckit_csp_report_only'] = TRUE;
$settings['seckit_csp_upgrade_requests'] = TRUE;
$settings['seckit_hsts_enabled'] = TRUE;
$settings['seckit_various_disable_autocomplete'] = TRUE;

// ### Include overall settings
if (file_exists($app_root . '/' . $site_path . '/default.settings.local.overall.php')) {
  include $app_root . '/' . $site_path . '/default.settings.local.overall.php';
}
