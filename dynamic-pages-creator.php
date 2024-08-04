<?php

/**
 * Plugin Name: Dynamic Pages Creator with SEO
 * Description: Automatically generates web pages based on predefined page keywords and dynamically assigns SEO meta tags to each page.
 * Version: 1.3.0
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
require_once(DPC_PATH . 'includes/class-dpc-created-pages-list.php');
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
    // Schedule cleanup if not already scheduled
    if (!wp_next_scheduled('dpc_verify_pages_ids_event')) {
        wp_schedule_event(time(), 'daily', 'dpc_verify_pages_ids_event');
    }

    // Initialize default options
    $default_options = array(
        'seo_template' => 'default',  // Ensure 'default' is default unless specified otherwise
        'parent' => 0,
        'page_template' => 0
    );

    // Get current settings and merge with defaults if not already set
    $options = get_option('dynamic_pages_creator_options', []);
    $options = array_merge($default_options, $options); // Defaults are overridden by existing settings
    update_option('dynamic_pages_creator_options', $options);
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

// Hook the function to 'admin_init'
add_action('admin_init', 'handle_page_actions');
function handle_page_actions() {
    if (isset($_REQUEST['page'], $_REQUEST['action'], $_REQUEST['post'], $_REQUEST['_wpnonce']) && $_REQUEST['page'] == 'dynamic-pages-view-pages') {
        $post_id = intval($_REQUEST['post']);
        $action = $_REQUEST['action'];

        // Verify nonce
        if (!wp_verify_nonce($_REQUEST['_wpnonce'], $action . '-post_' . $post_id)) {
            wp_die('Nonce verification failed, action not allowed.', 'Nonce Verification Failed', ['response' => 403]);
        }

        if (!current_user_can('edit_post', $post_id)) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        switch ($action) {
            case 'trash':
                wp_trash_post($post_id);
                break;
            case 'restore':
                wp_untrash_post($post_id);
                break;
            case 'delete':
                wp_delete_post($post_id, true);
                break;
        }

        // After performing the action, redirecting to the plugin page helps avoid re-executing the action if the user refreshes the page.
        wp_redirect(admin_url('admin.php?page=dynamic-pages-view-pages'));
        exit;
    }
}

function dpc_get_post_counts_by_ids() {
    global $wpdb;
    $ids = array_keys(get_option('dynamic_pages_creator_existing_pages_ids', []));
    if (empty($ids)) {
        return ['all' => 0, 'publish' => 0, 'draft' => 0, 'trash' => 0];
    }

    // Properly prepare the statement with placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $sql = "SELECT post_status, COUNT(1) as num_posts FROM $wpdb->posts WHERE ID IN ($placeholders) GROUP BY post_status";
    $prepared_sql = $wpdb->prepare($sql, $ids); // Safely prepare the SQL with the array of IDs
    $results = $wpdb->get_results($prepared_sql, OBJECT_K);

    $counts = [
        'publish' => $results['publish']->num_posts ?? 0,
        'draft'   => $results['draft']->num_posts ?? 0,
        'trash'   => $results['trash']->num_posts ?? 0
    ];
    $counts['all'] = $counts['publish'] + $counts['draft'];  // Exclude trash from 'all'

    return $counts;
}

add_action('wp_ajax_save_quick_edit', 'handle_quick_edit_save');
function handle_quick_edit_save() {
    // Check the nonce and then proceed if it's valid
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'quick_edit_action')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
        return;
    }

    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $title = sanitize_text_field($_POST['title']);
    $slug = sanitize_title($_POST['slug']);
    $parent = intval($_POST['parent']);
    $status = sanitize_text_field($_POST['status']);
    $seo_template = sanitize_text_field($_POST['seo_template']);
    update_post_meta($post_id, '_dpc_seo_override', $seo_template);

    // Update the post
    wp_update_post(array(
        'ID' => $post_id,
        'post_title' => $title,
        'post_name' => $slug,
        'post_parent' => $parent,
        'post_status' => $status
    ));
    // Determine the label for the status if needed
    $status_label = $status === 'draft' ? ' — Draft' : '';
    // Get the updated counts
    $counts = dpc_get_post_counts_by_ids();
    // Return success with title and slug to update on the client side
    wp_send_json_success(array(
        'title' => $title, 
        'slug' => $slug, 
        'parent' => $parent, 
        'status' => $status, 
        'status_label' => $status_label,
        'counts' => $counts,
        'seo_template' => $seo_template === 'global' ? 'Global' : 'Default'
    ));
}
