<?php

// ### Domains
$base_domains = [
  '',
];

// ### Database
$db_name = '';
$db_user = '';
$db_pass = '';
$db_host = '127.0.0.1';
$db_port = '3306';

// ### Display errors and enable/disable cache.
$show_php_errors = FALSE;
$disable_cache = FALSE;

// ### SMTP
/*
$config['smtp.settings']['smtp_password'] = '';
$config['smtp.settings']['smtp_username'] = '';
$config['smtp.settings']['smtp_host'] = 'mailhog';
$config['smtp.settings']['smtp_hostbackup'] = '';
$config['smtp.settings']['smtp_port'] = '1025';
$config['smtp.settings']['smtp_protocol'] = 'standard';
*/

// ### Sentry
$config['raven.settings']['client_key'] = NULL; // php logging
$config['raven.settings']['public_dsn'] = NULL; // javascript logging
$config['raven.settings']['environment'] = NULL; // environment name
$config['raven.settings']['release'] = NULL; // release or version

// ### Settings
$settings['config_sync_directory'] = '../config/sync';
$settings['file_private_path'] = '../private';
$settings['hash_salt'] = '';

// ### Trusted host patterns
$settings['trusted_host_patterns'] = $base_domains;

// ### Database.
$databases['default']['default'] = [
  'database' => $db_name,
  'username' => $db_user,
  'password' => $db_pass,
  'prefix' => '',
  'host' => $db_host,
  'port' => $db_port,
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
];

if ($show_php_errors) {
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
}

if ($disable_cache) {
  $settings['container_yamls'][] = 'sites/development.services.yml';
  $config['system.logging']['error_level'] = 'verbose';
  $config['system.performance']['cache']['page']['use_internal'] = FALSE;
  $config['system.performance']['css']['preprocess'] = FALSE;
  $config['system.performance']['css']['gzip'] = FALSE;
  $config['system.performance']['js']['preprocess'] = FALSE;
  $config['system.performance']['js']['gzip'] = FALSE;
  $config['system.performance']['response']['gzip'] = FALSE;

  $config['views.settings']['ui']['show']['sql_query']['enabled'] = TRUE;
  $config['views.settings']['ui']['show']['performance_statistics'] = TRUE;

  $cache_bins = ['bootstrap','config','data','default','discovery','dynamic_page_cache','entity','menu','migrate','render','rest','static','toolbar'];
  foreach ($cache_bins as $bin) {
    $settings['cache']['bins'][$bin] = 'cache.backend.null';
  }
}
else {
  // Enable some caching methods.
  $config['system.logging']['error_level'] = 'hide';
  $config['system.performance']['cache']['page']['use_internal'] = TRUE;
  $config['system.performance']['css']['preprocess'] = TRUE;
  $config['system.performance']['css']['gzip'] = TRUE;
  $config['system.performance']['js']['preprocess'] = TRUE;
  $config['system.performance']['js']['gzip'] = TRUE;
  $config['system.performance']['response']['gzip'] = TRUE;

  $config['views.settings']['ui']['show']['sql_query']['enabled'] = FALSE;
  $config['views.settings']['ui']['show']['performance_statistics'] = FALSE;

  if (file_exists('modules/contrib/redis/example.services.yml')) {
    $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';
    $settings['redis.connection']['interface'] = 'PhpRedis';
    $settings['redis.connection']['host'] = '127.0.0.1';
    $settings['redis_compress_length'] = 100;
    $settings['cache']['default'] = 'cache.backend.redis';
    $settings['cache_prefix'] = $db_name . '_';
  }
}
