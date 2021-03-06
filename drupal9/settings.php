<?php

use Drupal\Component\Utility\Crypt;

define('LANDO_INFO', json_decode($_ENV['LANDO_INFO'], TRUE));

/*
 |--------------------------------------------------------------------------
 | Setup
 |--------------------------------------------------------------------------
 |
 | To start with developing this application, you need to
 | fill in the empty variables below (in the SETUP section).
 | Every other variable in the SETUP section is set to a
 | default value, but may be changed if needed.
 |
 |--------------------------------------------------------------------------
 | Settings
 |--------------------------------------------------------------------------
 |
 |  The base domain is usually the domain you are working on
 |
*/

$base_domains = array_map(function ($url) {
  return trim(str_replace(['http://', 'https://'], '', $url), '/');
}, LANDO_INFO['appserver_nginx']['urls']);

/*
 |--------------------------------------------------------------------------
 | Reroute email
 |--------------------------------------------------------------------------
 |
 | Toggle the reroute email module functionality. When $reroute_email = TRUE,
 | all emails will be rerouted. When set  | FALSE, e-mails will be normally
 | send.
*/

$reroute_email = TRUE;

/*
 |--------------------------------------------------------------------------
 | Database Settings
 |--------------------------------------------------------------------------
 |
 |  Settings to connect to the local database.
*/

$db_name = LANDO_INFO['database']['creds']['database'];
$db_user = LANDO_INFO['database']['creds']['user'];
$db_pass = LANDO_INFO['database']['creds']['password'];
$db_host = LANDO_INFO['database']['internal_connection']['host'];
$db_port = LANDO_INFO['database']['internal_connection']['port'];

/*
 |--------------------------------------------------------------------------
 | SMTP/Mail settings
 |--------------------------------------------------------------------------
 |
 |  Settings for setting up SMTP/Mail.
 |
*/

$config['smtp.settings']['smtp_password'] = '';
$config['smtp.settings']['smtp_username'] = '';
$config['smtp.settings']['smtp_host'] = 'mailhog';
$config['smtp.settings']['smtp_hostbackup'] = '';
$config['smtp.settings']['smtp_port'] = '1025';
$config['smtp.settings']['smtp_protocol'] = 'standard';

/*
 |--------------------------------------------------------------------------
 | Config Settings
 |--------------------------------------------------------------------------
 |
 |  Settings for config files and file folders (public path and
 |  temporary directories.
 |
*/

$settings['container_yamls'][] = 'sites/default/services.yml';
$settings['config_sync_directory'] = '../config/sync';

// Do not change this line, because the file is committed to the repository.
// Read more: https://drupal.stackexchange.com/questions/271393/why-is-it-possible-to-write-any-string-in-the-hash-salt
$settings['hash_salt'] = Crypt::hashBase64($db_name.$db_user.$db_pass.$db_host.$db_port);

/*
 |--------------------------------------------------------------------------
 | PHP Settings
 |--------------------------------------------------------------------------
 |
 |  Settings for Solr search platform
 |
*/

$show_php_errors = TRUE;

/*
 |--------------------------------------------------------------------------
 | Drupal Settings
 |--------------------------------------------------------------------------
 |
 |  Settings specified for Drupal platform
 |
*/

$disable_cache = TRUE;

/*
 |--------------------------------------------------------------------------
 | Settings
 |--------------------------------------------------------------------------
 |
 |  The SETTINGS section will be filled with the
 |  variables filled in in the SETUP section.
 |  It's not necessary to change something in
 |  this section.
 |
*/

// The trusted host patterns.
$settings['trusted_host_patterns'] = array_merge([
  'localhost',
], $base_domains);

// Set error reporting.
if ($show_php_errors) {
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
}

// Setup the default database connection.
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
}

// Reroute e-mail
$config['reroute_email.settings']['enable'] = (isset($reroute_email) && $reroute_email) ? TRUE : FALSE;

if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}
