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
$settings['smtp_username'] = '';
$settings['smtp_password'] = '';
$settings['smtp_host'] = '';
$settings['smtp_port'] = '587'; // 25, 465, 587, 1025
$settings['smtp_protocol'] = 'tls'; // standard, ssl, tls
$settings['smtp_autotls'] = 'on';

// ### Sentry
$settings['sentry_client_key'] = NULL; // php logging
$settings['sentry_public_dsn'] = NULL; // javascript logging
$settings['sentry_environment'] = NULL; // environment name
$settings['sentry_release'] = NULL; // release or version

// ### Redis
$settings['redis_interface'] = 'PhpRedis';
$settings['redis_host'] = '127.0.0.1';
$settings['redis_compress_length'] = 100;

// ### Include overall settings
if (file_exists($app_root . '/' . $site_path . '/default.settings.local.overall.php')) {
  include $app_root . '/' . $site_path . '/default.settings.local.overall.php';
}

// ### Overrides
// Place any project specific settings or config (like keys, passwords, etc.) here.
