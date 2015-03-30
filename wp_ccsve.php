<?php
/*
Plugin Name: Custom CSV Export Plugin
Description: A plugin to export WP site content into a CSV file.
Version: .3
Author: Ethan Hinson
Author URI: http://www.bluetentmarketing.com
License: GPL2
*/
/*
Copyright 2013  Ethan Hinson  (email : ethan@bluetent.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if(!class_exists('WP_CCSVE'))
{
  class WP_CCSVE
  {
    /**
     * Construct the plugin object
     */
    public function __construct()
    {
          // Initialize Settings
      require_once(sprintf("%s/settings.php", dirname(__FILE__)));
      require_once(sprintf("%s/functions/exporter.php", dirname(__FILE__)));
      add_action('init', 'ccsve_export');
      $WP_CCSVE_Settings = new WP_CCSVE_Settings();

        } // END public function __construct

    /**
     * Activate the plugin
     */
    public static function activate()
    {
      // Do nothing
    } // END public static function activate

    /**
     * Deactivate the plugin
     */
    public static function deactivate()
    {

    }
  }
}

if(class_exists('WP_CCSVE'))
{
  // Installation and uninstallation hooks
  register_activation_hook(__FILE__, array('WP_CCSVE', 'activate'));
  register_deactivation_hook(__FILE__, array('WP_CCSVE', 'deactivate'));

  // instantiate the plugin class
  $wp_ccsve = new WP_CCSVE();

    // Add a link to the settings page onto the plugin page
  if(isset($wp_plugin_template))
  {
        // Add the settings link to the plugins page
    function plugin_settings_link($links)
    {
      $settings_link = '<a href="options-general.php?page=wp_plugin_template">Settings</a>';
      array_unshift($links, $settings_link);
      return $links;
    }

    $plugin = plugin_basename(__FILE__);
    add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
  }
}
