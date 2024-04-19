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
