<?php

use Drupal\Component\Utility\Crypt;

// ### Define LANDO_INFO
if (!defined('LANDO_INFO')) {
  define('LANDO_INFO', json_decode($_ENV['LANDO_INFO'], TRUE));
}

// ### Environment
$settings['environment'] = 'development';

// ### Domains
$settings['base_domains'] = array_map(function ($url) {
  return trim(str_replace(['http://', 'https://'], '', $url), '/');
}, LANDO_INFO['appserver_nginx']['urls']);

// ### PHP
$settings['show_php_errors'] = TRUE;

// ### Cache
$settings['disable_cache'] = TRUE;

// ### Database
$settings['db_name'] = LANDO_INFO['database']['creds']['database'];
$settings['db_user'] = LANDO_INFO['database']['creds']['user'];
$settings['db_pass'] = LANDO_INFO['database']['creds']['password'];
$settings['db_host'] = LANDO_INFO['database']['internal_connection']['host'];
$settings['db_port'] = LANDO_INFO['database']['internal_connection']['port'];

// ### Salt
$settings['hash_salt'] = Crypt::hashBase64($settings['db_name'].$settings['db_user'].$settings['db_pass'].$settings['db_host'].$settings['db_port']);

// ### SMTP
$settings['smtp_username'] = '';
$settings['smtp_password'] = '';
$settings['smtp_host'] = 'mailhog';
$settings['smtp_port'] = '1025'; // 25, 465, 587, 1025
$settings['smtp_protocol'] = 'standard'; // standard, ssl, tls
$settings['smtp_autotls'] = 'off';

// ### Sentry
$settings['sentry_client_key'] = NULL; // php logging
$settings['sentry_public_dsn'] = NULL; // javascript logging
$settings['sentry_environment'] = NULL; // environment name
$settings['sentry_release'] = NULL; // release or version

// ### Redis
$settings['redis_interface'] = 'PhpRedis';
$settings['redis_host'] = 'cache';
$settings['redis_compress_length'] = 100;

// ### Include overall settings
if (file_exists($app_root . '/' . $site_path . '/default.settings.local.overall.php')) {
  include $app_root . '/' . $site_path . '/default.settings.local.overall.php';
}

// ### Overrides
// Place any project specific settings or config (like keys, passwords, etc.) here.
