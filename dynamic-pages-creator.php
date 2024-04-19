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
