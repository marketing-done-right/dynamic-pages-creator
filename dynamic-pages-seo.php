<?php

/**
 * Plugin Name: Dynamic Pages Creator with SEO
 * Description: Automatically generates web pages based on predefined page titles and dynamically assigns SEO meta tags to each page, tailored to its automatically generated slug, to improve search engine visibility.
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
        'Dynamic Pages Creator', // Page title
        'Dynamic Pages Creator', // Menu title
        'manage_options', // Capability required to see this option
        'dynamic-pages-creator', // Menu slug, used for the URL
        'dynamic_pages_creator_settings_page', // Function that outputs the menu page
        'dashicons-admin-generic', // Icon URL
        20 // Position in the menu
    );
}

function dynamic_pages_creator_check_submission() {
    if (isset($_POST['option_page']) && $_POST['option_page'] == 'dynamic_pages_creator_options' && current_user_can('manage_options')) {
        if (isset($_POST['dynamic_pages_creator_options'])) {
            dynamic_pages_creator_create_pages($_POST['dynamic_pages_creator_options']);
        }
    }
}
add_action('admin_init', 'dynamic_pages_creator_check_submission');

/**
 * Output the settings page for the plugin
 */
function dynamic_pages_creator_settings_page() {
    ?>
    <div class="wrap">
        <h2>Dynamic Pages Creator</h2>
        <p>Use this page to create dynamic pages with SEO meta tags based on page titles.</p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('dynamic_pages_creator_options');
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
    register_setting('dynamic_pages_creator_options', 'dynamic_pages_creator_options', 'dynamic_pages_creator_options_validate');

    add_settings_section(
        'dynamic_pages_creator_main',
        'Settings',
        'dynamic_pages_creator_settings_section_callback',
        'dynamic-pages-creator'
    );

    add_settings_field(
        'dynamic_pages_creator_title_field',
        'Page Titles (comma-separated)',
        'dynamic_pages_creator_title_field_callback',
        'dynamic-pages-creator',
        'dynamic_pages_creator_main'
    );

    add_settings_field(
        'dynamic_pages_creator_parent_field',
        'Parent Page',
        'dynamic_pages_creator_parent_field_callback',
        'dynamic-pages-creator',
        'dynamic_pages_creator_main'
    );
}

/**
 * Validate the input for the titles field
 */
function dynamic_pages_creator_options_validate($input) {
    $new_input = array();
    $new_input['parent'] = absint($input['parent']);
    $new_input['page_titles'] = sanitize_text_field($input['page_titles']);
    return $new_input;
}

/**
 * Output the settings section description
 */
function dynamic_pages_creator_settings_section_callback() {
    echo 'Enter the titles for the pages you wish to create, separated by commas. For example, "Home, About Us, Contact".';
}

/**
 * Output the input field for the titles
 */
function dynamic_pages_creator_title_field_callback() {
    echo "<input type='text' id='dynamic_pages_creator_title_field' name='dynamic_pages_creator_options[page_titles]' value='' placeholder='Enter page titles separated by commas' style='width: 100%;'>";
}

/**
 * Output the parent page dropdown
 */
function dynamic_pages_creator_parent_field_callback() {
    $pages = get_pages();
    $selected_parent = get_option('dynamic_pages_creator_parent');
    echo '<select id="dynamic_pages_creator_parent_field" name="dynamic_pages_creator_options[parent]">';
    echo '<option value="0"' . (!$selected_parent ? ' selected="selected"' : '') . '>Main Page (no parent)</option>';
    foreach ($pages as $page) {
        echo '<option value="' . esc_attr($page->ID) . '"' . ($selected_parent == $page->ID ? ' selected="selected"' : '') . '>' . esc_html($page->post_title) . '</option>';
    }
    echo '</select>';
}

/**
 * Create pages based on the titles provided in the input field
 */
function dynamic_pages_creator_create_pages($options) {
    $titles = isset($options['page_titles']) ? $options['page_titles'] : '';
    $parent_id = isset($options['parent']) ? intval($options['parent']) : 0;

    if (empty($titles)) {
        add_settings_error(
            'dynamic_pages_creator_page_titles',
            'dynamic_pages_creator_page_titles_error',
            'Error: No page titles provided. Please enter some page titles to create pages.',
            'error'
        );
        return '';
    }

    $titles_array = explode(',', $titles);
    $created_pages = [];
    $errors = [];
    $existing_pages = get_option('dynamic_pages_creator_existing_slugs', []);
    $timestamp = current_time('mysql');

    foreach ($titles_array as $title) {

        $title = trim($title);
        if (empty($title)) {
            add_settings_error(
                'dynamic_pages_creator_page_titles',
                'dynamic_pages_creator_empty_title',
                'Error: Empty titles are not allowed. Please enter valid titles to create pages.',
                'error'
            );
            continue;
        }
        
        $slug = sanitize_title($title);
        
        if (!get_page_by_path($slug, OBJECT, 'page') && !isset($existing_pages[$slug])) {
            $page_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => 'This is the automatically generated page for ' . $title,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_name' => $slug,
                'post_parent' => $parent_id
            ]);

            if (!is_wp_error($page_id)) {
                $created_pages[] = $title;
                $existing_pages[$slug] = ['date' => $timestamp];
            } else {
                $errors[] = $title;
            }
        } else {
            $errors[] = $title . ' (already exists)';
        }
    }

    update_option('dynamic_pages_creator_existing_slugs', $existing_pages);

    if (!empty($created_pages)) {
        add_settings_error(
            'dynamic_pages_creator_page_titles',
            'dynamic_pages_creator_page_titles_success',
            'Successfully created pages for the following titles: ' . implode(', ', $created_pages),
            'updated'
        );
    }

    if (!empty($errors)) {
        add_settings_error(
            'dynamic_pages_creator_page_titles',
            'dynamic_pages_creator_page_titles_error',
            'Failed to create pages for the following titles: ' . implode(', ', $errors),
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
