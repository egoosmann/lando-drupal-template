<?php

use Drupal\Core\Installer\InstallerKernel;

/*
 |--------------------------------------------------------------------------
 | Trusted host patterns
 |--------------------------------------------------------------------------
 |
 |  The base domains are used to set the trusted host patterns.
 |
*/
$settings['trusted_host_patterns'] = $settings['base_domains'];

/*
 |--------------------------------------------------------------------------
 | Database
 |--------------------------------------------------------------------------
 |
 |  The database settings are set here.
 |
*/
$databases['default']['default'] = [
  'database' => $settings['db_name'],
  'username' => $settings['db_user'],
  'password' => $settings['db_pass'],
  'prefix' => '',
  'host' => $settings['db_host'],
  'port' => $settings['db_port'],
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
  'charset' => 'utf8mb4',
  'collation' => 'utf8mb4_general_ci',
];

/*
 |--------------------------------------------------------------------------
 | Salt
 |--------------------------------------------------------------------------
 |
 |  The hash salt that is used.
 |
*/
$settings['hash_salt'] = $settings['hash_salt'];

/*
 |--------------------------------------------------------------------------
 | Folders
 |--------------------------------------------------------------------------
 |
 |  Defines the config sync directory and the private file path.
 |
*/
$settings['config_sync_directory'] = '../config/sync';
$settings['file_private_path'] = '../private';

/*
 |--------------------------------------------------------------------------
 | PHP errors
 |--------------------------------------------------------------------------
 |
 |  Settings for displaying PHP errors.
 |
*/
if ($settings['show_php_errors'] === TRUE) {
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
}

/*
 |--------------------------------------------------------------------------
 | Cache settings
 |--------------------------------------------------------------------------
 |
 |  Settings for enabling or disabling the cache.
 |
*/
if ($settings['disable_cache'] === TRUE) {
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
}

/*
 |--------------------------------------------------------------------------
 | Module specific settings
 |--------------------------------------------------------------------------
 |
 |  Settings for several modules are set here.
 |
*/
// ### Environment indicator
if (is_dir("$app_root/modules/contrib/environment_indicator")) {
  $config['environment_indicator.indicator']['name'] = $settings['environment'];
  $config['environment_indicator.indicator']['fg_color'] = match($settings['environment']) {'development' => '#000000', 'staging' => '#eaa521', 'production' => '#ff0000'};
  $config['environment_indicator.indicator']['bg_color'] = match($settings['environment']) {'development' => '#000000', 'staging' => '#eaa521', 'production' => '#ff0000'};
}

// ### SMTP
if (is_dir("$app_root/modules/contrib/smtp")) {
  $config['smtp.settings']['smtp_on'] = $settings['smtp_on'];
  $config['smtp.settings']['smtp_username'] = $settings['smtp_username'];
  $config['smtp.settings']['smtp_password'] = $settings['smtp_password'];
  $config['smtp.settings']['smtp_host'] = $settings['smtp_host'];
  $config['smtp.settings']['smtp_port'] = $settings['smtp_port'];
  $config['smtp.settings']['smtp_protocol'] = $settings['smtp_protocol'];
  $config['smtp.settings']['smtp_autotls'] = $settings['smtp_autotls'];
  $config['smtp.settings']['smtp_from'] = $settings['smtp_from'];
  $config['smtp.settings']['smtp_fromname'] = $settings['smtp_fromname'];
  $config['smtp.settings']['smtp_allowhtml'] = $settings['smtp_allowhtml'];
  $config['smtp.settings']['smtp_debugging'] = $settings['smtp_debugging'];
}

// ### Sentry
if (is_dir("$app_root/modules/contrib/raven")) {
  $config['raven.settings']['client_key'] = $settings['sentry_client_key']; // php logging
  $config['raven.settings']['public_dsn'] = $settings['sentry_public_dsn']; // javascript logging
  $config['raven.settings']['environment'] = $settings['sentry_environment']; // environment name
  $config['raven.settings']['release'] = $settings['sentry_release']; // release or version
}

// ### Redis
if (is_dir("$app_root/modules/contrib/redis")) {
  $settings['container_yamls'][] = 'modules/contrib/redis/example.services.yml';
  $settings['redis.connection']['interface'] = $settings['redis_interface'];
  $settings['redis.connection']['host'] = $settings['redis_host'];
  $settings['redis_compress_length'] = $settings['redis_compress_length'];
  $settings['cache']['default'] = 'cache.backend.redis';
  $settings['cache_prefix'] = $settings['db_name'] . '_';
}

// ### Seckit
if (is_dir("$app_root/modules/contrib/seckit")) {
  $settings['seckit.settings']['seckit_xss']['csp']['checkbox'] = $settings['seckit_csp_enabled'];
  $settings['seckit.settings']['seckit_xss']['csp']['report-only'] = $settings['seckit_csp_report_only'];
  $settings['seckit.settings']['seckit_xss']['csp']['upgrade-req'] = $settings['seckit_csp_upgrade_requests'];
  $settings['seckit.settings']['seckit_ssl']['hsts'] = $settings['seckit_hsts_enabled'];
  $settings['seckit.settings']['seckit_various']['disable_autocomplete'] = $settings['seckit_various_disable_autocomplete'];
}

/*
 |--------------------------------------------------------------------------
 | Project specific settings
 |--------------------------------------------------------------------------
 |
 |  Settings for project specific settings.
 |
*/
if (file_exists($app_root . '/' . $site_path . '/settings.local.specific.php')) {
  include $app_root . '/' . $site_path . '/settings.local.specific.php';
}
