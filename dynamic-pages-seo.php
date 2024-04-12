<?php

/**
 * Plugin Name: Dynamic Pages Creator with SEO
 * Description: Automatically generates web pages based on predefined URL slugs and dynamically assigns SEO meta tags to each page, tailored to its slug, to improve search engine visibility.
 * Version: 1.0
 * Author: Hans Steffens & Marketing Done Right LLC
 * Author URI: https://marketingdr.co
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

 // Set up admin menu
 add_action('admin_menu', 'dynamic_pages_creator_menu');

function dynamic_pages_creator_menu() {
    add_menu_page(
        'Dynamic Pages Creator',     // Page title
        'Dynamic Pages Creator',     // Menu title
        'manage_options',            // Capability required to see this option
        'dynamic-pages-creator',     // Menu slug, used for the URL
        'dynamic_pages_creator_settings_page', // Function that outputs the menu page
        'dashicons-admin-generic',   // Icon URL
        20                            // Position in the menu
    );
}

// Set up the settings page
function dynamic_pages_creator_settings_page() {
    ?>
    <div class="wrap">
        <h2>Dynamic Pages Creator</h2>
        <p>Use this page to create dynamic pages with SEO meta tags.</p>
        <form method="post" action="options.php">
            <?php
            settings_fields('dynamic-pages-creator-options');
            do_settings_sections('dynamic-pages-creator');
            submit_button('Create Pages');
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'dynamic_pages_creator_settings_init');

function dynamic_pages_creator_settings_init() {
    register_setting('dynamic_pages_creator_options', 'dynamic_pages_creator_slugs', 'sanitize_text_field');

    add_settings_section(
        'dynamic_pages_creator_main', 
        'Settings', 
        'dynamic_pages_creator_settings_section_callback', 
        'dynamic-pages-creator'
    );

    add_settings_field(
        'dynamic_pages_creator_slug_field', 
        'Page Slugs (comma-separated)', 
        'dynamic_pages_creator_slug_field_callback', 
        'dynamic-pages-creator', 
        'dynamic_pages_creator_main'
    );
}

function dynamic_pages_creator_settings_section_callback() {
    echo 'Enter the slugs for the pages you wish to create, separated by commas.';
}

function dynamic_pages_creator_slug_field_callback() {
    $slugs = get_option('dynamic_pages_creator_slugs');
    echo "<input type='text' id='dynamic_pages_creator_slug_field' name='dynamic_pages_creator_slugs' value='' placeholder='Enter slugs separated by commas' style='width: 100%;'>";
}

