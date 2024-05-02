<?php

/**
 * Plugin Name: Dynamic Pages Creator with SEO
 * Description: Automatically generates web pages based on predefined page keywords and dynamically assigns SEO meta tags to each page.
 * Version: 1.1.0
 * Author: Hans Steffens & Marketing Done Right LLC
 * Author URI: https://marketingdr.co
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 /*
    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

    Copyright 2019-2024 Marketng Done Right, LLC.
*/

defined('ABSPATH') or die('Direct script access disallowed.');

define('DPC_PATH', plugin_dir_path(__FILE__));

require_once(DPC_PATH . 'includes/admin-menus.php');
require_once(DPC_PATH . 'includes/page-management.php');
require_once(DPC_PATH . 'includes/seo-functions.php');
require_once(DPC_PATH . 'includes/utilities.php');

function dpc_init()
{
    new DPC_Admin_Menus();
    new DPC_Page_Management();
    new DPC_SEO_Functions();
    new DPC_Utilities();
}

add_action('plugins_loaded', 'dpc_init');

// Schedule the cleanup event on plugin activation
register_activation_hook(__FILE__, 'dpc_activate');

function dpc_activate() {
    // Ensure the scheduled cleanup is set
    if (!wp_next_scheduled('dpc_verify_pages_ids_event')) {
        wp_schedule_event(time(), 'daily', 'dpc_verify_pages_ids_event');
    }

    // Initialize default options
    $default_options = array(
        'default_seo_setting' => 'global',
        'parent' => 0,
        'page_template' => 0
    );

    if (!get_option('dynamic_pages_creator_options')) {
        update_option('dynamic_pages_creator_options', $default_options);
    } else {
        $existing_options = get_option('dynamic_pages_creator_options');
        $updated_options = array_merge($default_options, $existing_options);
        update_option('dynamic_pages_creator_options', $updated_options);
    }
}

// Hook into the event
add_action('dpc_verify_pages_ids_event', 'dpc_run_scheduled_cleanup');

function dpc_run_scheduled_cleanup() {
    $page_management = new DPC_Page_Management();
    $page_management->verify_existing_pages_ids();
}

// Optional: Clear scheduled event and cleanup options on plugin deactivation
register_deactivation_hook(__FILE__, 'dpc_deactivate');

function dpc_deactivate() {
    $timestamp = wp_next_scheduled('dpc_verify_pages_ids_event');
    wp_unschedule_event($timestamp, 'dpc_verify_pages_ids_event');
    // clear the plugin's main option
    delete_option('dynamic_pages_creator_options');
}