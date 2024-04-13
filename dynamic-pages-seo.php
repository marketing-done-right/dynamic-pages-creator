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


 /**
  * Add the plugin menu to the admin dashboard
  */
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

/**
 * Output the settings page for the plugin
 */
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

/**
 * Register the settings for the plugin
 */
add_action('admin_init', 'dynamic_pages_creator_settings_init');

function dynamic_pages_creator_settings_init() {
    register_setting('dynamic_pages_creator_options', 'dynamic_pages_creator_slugs', 'dynamic_pages_creator_create_pages');

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

/**
 * Output the settings section description
 */
function dynamic_pages_creator_settings_section_callback() {
    echo 'Enter the slugs for the pages you wish to create, separated by commas.';
}

/**
 * Output the input field for the slugs
 */
function dynamic_pages_creator_slug_field_callback() {
    echo "<input type='text' id='dynamic_pages_creator_slug_field' name='dynamic_pages_creator_slugs' value='' placeholder='Enter slugs separated by commas' style='width: 100%;'>";
}  

/**
 * Create pages based on the slugs provided in the input field
 * 
 * @param string $input The input from the settings field
 * @return string The input field value
 */

function dynamic_pages_creator_create_pages($input) {
    if (empty($input)) {
        add_settings_error(
            'dynamic_pages_creator_slugs',
            'dynamic_pages_creator_slugs_error',
            'Error: No slugs provided. Please enter some slugs to create pages.',
            'error'
        );
        return $input;
    }

    // Sanitize and split input
    $raw_slugs = explode(',', sanitize_text_field($input));
    $slugs = array_map('sanitize_title', $raw_slugs); // Sanitize each slug to ensure it's in a valid format

    $created_pages = [];
    $errors = [];
    $existing_pages = get_option('dynamic_pages_creator_existing_slugs', []);
    $timestamp = current_time('mysql');

    foreach ($slugs as $index => $slug) {
        // Skip empty slugs
        if (empty($slug)) {
            $errors[] = $raw_slugs[$index] . ' (invalid slug)';
            continue;
        }
        
        // Check if the page already exists
        if (!get_page_by_path($slug, OBJECT, 'page') && !in_array($slug, $existing_pages)) {
            $page_id = wp_insert_post([
                'post_title'  => ucwords(str_replace('-', ' ', $slug)),
                'post_content' => 'This is the automatically generated page for ' . $slug,
                'post_status' => 'publish',
                'post_type'   => 'page',
                'post_name'   => $slug
            ]);

            if (!is_wp_error($page_id)) {
                $created_pages[] = $slug;
                $existing_pages[$slug] = ['date' => $timestamp]; // Add slug to existing pages list and store the creation date.
            } else {
                $errors[] = $slug;
            }
        } else {
            $errors[] = $slug . ' (already exists)';
        }
    }
    
    // Update the list of existing pages
    update_option('dynamic_pages_creator_existing_slugs', $existing_pages);

    // Display a success message if pages were created
    if (!empty($created_pages)) {
        add_settings_error(
            'dynamic_pages_creator_slugs',
            'dynamic_pages_creator_slugs_success',
            'Successfully created pages for the following slugs: ' . implode(', ', $created_pages),
            'updated'
        );
    }
    // Display an error message if pages failed to create
    if (!empty($errors)) {
        add_settings_error(
            'dynamic_pages_creator_slugs',
            'dynamic_pages_creator_slugs_error',
            'Failed to create pages for the following slugs: ' . implode(', ', $errors),
            'error'
        );
    }

    return ''; // Clear the input field after processing
}

// Add a Submenu for Viewing Slugs
add_action('admin_menu', 'dynamic_pages_creator_add_view_slugs_submenu');
function dynamic_pages_creator_add_view_slugs_submenu() {
    add_submenu_page(
        'dynamic-pages-creator',  // parent_slug
        'View Created Slugs',     // page_title
        'View Created Slugs',     // menu_title
        'manage_options',         // capability
        'dynamic-pages-view-slugs', // menu_slug
        'dynamic_pages_creator_view_slugs_page' // function that will render the page
    );
}

/**
 * Render the slugs page for viewing created slugs
 */
function dynamic_pages_creator_view_slugs_page() {
    $existing_slugs = get_option('dynamic_pages_creator_existing_slugs', []);
    ?>
    <div class="wrap">
        <h1>Created Pages Slugs</h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Slug</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($existing_slugs)): ?>
                    <tr>
                        <td colspan="2">No slugs have been created yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($existing_slugs as $slug => $info): ?>
                        <tr>
                            <td><?php echo esc_html($slug); ?></td>
                            <td><?php echo esc_html($info['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
