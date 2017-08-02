<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

/**
 * Environment-specific settings.
 */
if (file_exists('/var/www/site-php')) {
  // Require prod settings on live environments.
  require '/var/www/site-php/settings.inc';
}
else {
  // Provide defaults for local development.
  $databases['default']['default'] = [
    'database' => 'drupal',
    'username' => 'drupal',
    'password' => 'drupal',
    'prefix' => '',
    'host' => 'localhost',
    'port' => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  ];
  $config['system.file']['path']['temporary'] = '/tmp';
}

$config_directories = [];
$config_directories['sync'] = '../config/sync';

/**
 * Salt for one-time login links, cancel links, form tokens, etc.
 *
 * This variable will be set to a random value by the installer. All one-time
 * login links will be invalidated if the value is changed. Note that if your
 * site is deployed on a cluster of web servers, you must ensure that this
 * variable has the same value on each server.
 *
 * For enhanced security, you may set this variable to the contents of a file
 * outside your document root; you should also ensure that this file is not
 * stored with backups of your database.
 *
 * Example:
 * @code
 *   $settings['hash_salt'] = file_get_contents('/home/example/salt.txt');
 * @endcode
 */
$settings['hash_salt'] = 'duJm3kSZvLjExmtmkffDwuGB-67aVPeUCnq_NUkNd5LeQDL1XGHRNnkyfp01KuzGyeNrMedhJA';

/**
 * Access control for update.php script.
 *
 * If you are updating your Drupal installation using the update.php script but
 * are not logged in using either an account with the "Administer software
 * updates" permission or the site maintenance account (the account that was
 * created during installation), you will need to modify the access check
 * statement below. Change the FALSE to a TRUE to disable the access check.
 * After finishing the upgrade, be sure to open this file again and change the
 * TRUE back to a FALSE!
 */
$settings['update_free_access'] = FALSE;

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['install_profile'] = 'minimal';
