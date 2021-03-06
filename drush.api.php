<?php

/**
 * Adjust the contents of a site alias.
 */
function hook_drush_sitealias_alter(&$alias_record) {
  // If the alias is "remote", but the remote site is
  // the system this command is running on, convert the
  // alias record to a local alias.
  if (isset($alias_record['remote-host'])) {
    $uname = php_uname('n');
    if ($alias_record['remote-host'] == $uname) {
      unset($alias_record['remote-host']);
      unset($alias_record['remote-user']);
    }
  }
}

/**
 * Take action after a project has been downloaded.
 */
function hook_drush_pm_post_download($project, $release) {

}

/**
 * Adjust the location a project should be copied to after being downloaded.
 *
 * See @pm_drush_pm_download_destination_alter().
 */
function hook_drush_pm_download_destination_alter(&$project, $release) {
  if ($some_condition) {
    $project['project_install_location'] = '/path/to/install/to/' . $project['project_dir'];
  }
}

/**
 * Automatically download project dependencies at pm-enable time.
 *
 * Use a pre-pm_enable hook to download before your module is enabled,
 * or a post-pm_enable hook (drush_hook_post_pm_enable) to run after
 * your module is enabled.
 *
 * Your hook will be called every time pm-enable is executed; you should
 * only download dependencies when your module is being enabled.  Respect
 * the --skip flag, and take no action if it is present.
 */
function drush_hook_pre_pm_enable() {
  // Get the list of modules being enabled; only download dependencies if our
  // module name appears in the list.
  $modules = drush_get_context('PM_ENABLE_MODULES');
  if (in_array('hook', $modules) && !drush_get_option('skip')) {
    $url = 'http://server.com/path/MyLibraryName.tgz';
    $path = drush_get_context('DRUSH_DRUPAL_ROOT');
    drush_include_engine('drupal', 'environment');
    if (drush_module_exists('libraries')) {
      $path .= '/' . libraries_get_path('MyLibraryName') . '/MyLibraryName.tgz';
    }
    else {
      $path .= '/' . drupal_get_path('module', 'hook') . '/MyLibraryName.tgz';
    }
    drush_download_file($url, $path) && drush_tarball_extract($path);
  }
}

/**
 * Sql-sanitize example.
 *
 * These plugins sanitize the DB, usually removing personal information.
 *
 * @see \Drush\Commands\sql\SqlSanitizePluginInterface
 */
function sanitize() {}
function messages() {}

/**
 * Add help components to a command.
 */
function hook_drush_help_alter(&$command) {
  if ($command['command'] == 'sql-sync') {
    $command['options']['myoption'] = "Description of modification of sql-sync done by hook";
    $command['sub-options']['sanitize']['my-sanitize-option'] = "Description of sanitization option added by hook (grouped with --sanitize option)";
  }
  if ($command['command'] == 'global-options') {
    // Recommended: don't show global hook options in brief global options help.
    if ($command['#brief'] === FALSE) {
      $command['options']['myglobaloption'] = 'Description of option used globally in all commands (e.g. in a commandfile init hook)';
    }
  }
}

/*
 * Storage filters alter the .yml files on disk after a config-export or before
 * a config-import. See `drush topic docs-config-filter` and config_drush_storage_filters().
 */
function hook_drush_storage_filters() {
  $result = array();
  $module_adjustments = drush_get_option('skip-modules');
  if (!empty($module_adjustments)) {
    if (is_string($module_adjustments)) {
      $module_adjustments = explode(',', $module_adjustments);
    }
    $result[] = new CoreExtensionFilter($module_adjustments);
  }
  return $result;
}

/**
 * @} End of "addtogroup hooks".
 */
