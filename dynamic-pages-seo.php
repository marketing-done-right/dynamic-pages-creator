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

// Add a Submenu for SEO Settings
function dynamic_pages_creator_add_seo_settings_menu() {
    add_submenu_page(
        'dynamic-pages-creator', // parent slug
        'SEO Settings', // page title
        'SEO Settings', // menu title
        'manage_options', // capability
        'dynamic-pages-creator-seo', // menu slug
        'dynamic_pages_creator_seo_settings_page' // function that displays the settings page
    );
}
add_action('admin_menu', 'dynamic_pages_creator_add_seo_settings_menu');

// Function to render the SEO settings page
function dynamic_pages_creator_seo_settings_page() {
    ?>
    <div class="wrap">
        <h2>SEO Settings for Dynamic Pages</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('dynamic_pages_creator_seo_settings');
            do_settings_sections('dynamic_pages_creator_seo_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the settings for the SEO settings page
function dynamic_pages_creator_register_seo_settings() {
    register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_title_template');
    register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_description_template');

    add_settings_section(
        'seo_settings_section', 
        'SEO Meta Settings', 
        'dynamic_pages_creator_seo_settings_section_callback', 
        'dynamic_pages_creator_seo_settings'
    );

    add_settings_field(
        'seo_meta_title_field', 
        'SEO Meta Title Template', 
        'dynamic_pages_creator_seo_meta_title_field_callback', 
        'dynamic_pages_creator_seo_settings', 
        'seo_settings_section'
    );

    add_settings_field(
        'seo_meta_description_field', 
        'SEO Meta Description Template', 
        'dynamic_pages_creator_seo_meta_description_field_callback', 
        'dynamic_pages_creator_seo_settings', 
        'seo_settings_section'
    );
}
add_action('admin_init', 'dynamic_pages_creator_register_seo_settings');

// Callback function to output the section description
function dynamic_pages_creator_seo_settings_section_callback() {
    echo '<p>Enter the template for SEO meta tags. Use [title] as a placeholder to insert the page title.</p>';
}

function dynamic_pages_creator_seo_meta_title_field_callback() {
    // Fetch the title template option and ensure it's treated as a string.
    $title_template = get_option('seo_meta_title_template', '[title] | Your Site Name');
    if (is_array($title_template)) {
        $title_template = implode(" ", $title_template);
    }
    echo "<input type='text' id='seo_meta_title_template' name='seo_meta_title_template' value='" . esc_attr($title_template) . "' style='width: 100%;'>";
}

function dynamic_pages_creator_seo_meta_description_field_callback() {
    // Fetch the description template option and ensure it's treated as a string.
    $description_template = get_option('seo_meta_description_template', 'Learn more about [title] on our site.');
    if (is_array($description_template)) {
        $description_template = implode(" ", $description_template); // Example fallback, should be adjusted based on expected structure.
    }
    echo "<textarea id='seo_meta_description_template' name='seo_meta_description_template' rows='5' style='width: 100%;'>" . esc_textarea($description_template) . "</textarea>";
}


// Initialize SEO tags - This should be outside any specific function and called on every page load
add_action('template_redirect', 'initialize_page_seo_tags');

function initialize_page_seo_tags() {
    if (defined('WPSEO_VERSION')) {
        add_filter('wpseo_metadesc', 'dynamic_page_seo_meta_description');
        add_filter('wpseo_title', 'dynamic_page_seo_title');
    } else {
        add_action('wp_head', 'fallback_page_seo_meta_tags');
    }
}

function dynamic_page_seo_meta_description($description) {
    if (is_singular('page')) {
        $page_id = get_the_ID();
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        if (isset($existing_pages_ids[$page_id])) {
            $seo_template = get_option('seo_meta_description_template', 'Learn more about [title] on our site.');
            return str_replace('[title]', get_the_title(), $seo_template);
        }
    }
    return $description;
}

function dynamic_page_seo_title($title) {
    if (is_singular('page')) {
        $page_id = get_the_ID();
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        if (isset($existing_pages_ids[$page_id])) {
            $seo_template = get_option('seo_meta_title_template', '[title] | Your Site Name');
            return str_replace('[title]', get_the_title(), $seo_template);
        }
    }
    return $title;
}

function fallback_page_seo_meta_tags() {
    if (is_singular('page')) {
        $page_id = get_the_ID();
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        if (isset($existing_pages_ids[$page_id])) {
            $seo_title = str_replace('[title]', get_the_title(), get_option('seo_meta_title_template', '[title] | Your Site Name'));
            echo '<title>' . esc_html($seo_title) . '</title>';
            $seo_description = str_replace('[title]', get_the_title(), get_option('seo_meta_description_template', 'Learn more about [title] on our site.'));
            echo '<meta name="description" content="' . esc_attr($seo_description) . '">';
        }
    }
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
    $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
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
        $page_exists = get_page_by_path($slug, OBJECT, 'page');

        if (!$page_exists && !array_key_exists($slug, $existing_pages_ids)) {
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
                $existing_pages_ids[$page_id] = ['date' => $timestamp, 'title' => $title, 'slug' => $slug];
            } else {
                $errors[] = $title;
            }
        } else {
            $errors[] = $title . ' (already exists)';
        }
    }

    update_option('dynamic_pages_creator_existing_pages_ids', $existing_pages_ids);

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

// Hook to delete pages from the list when they are deleted
add_action('before_delete_post', 'dynamic_pages_creator_handle_delete'); 
// Function to handle the deletion of pages
function dynamic_pages_creator_handle_delete($post_id) {
    $created_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
    if (array_key_exists($post_id, $created_pages_ids)) {
        unset($created_pages_ids[$post_id]);
        update_option('dynamic_pages_creator_existing_pages_ids', $created_pages_ids);
    }
}

// Add a Submenu for Viewing Created Pages
add_action('admin_menu', 'dynamic_pages_creator_add_view_pages_submenu');
function dynamic_pages_creator_add_view_pages_submenu() {
    add_submenu_page(
        'dynamic-pages-creator',  // parent_slug
        'View Created Pages',     // page_title
        'View Created Pages',     // menu_title
        'manage_options',         // capability
        'dynamic-pages-view-pages', // menu_slug
        'dynamic_pages_creator_view_pages_page' // function that will render the page
    );
}

/**
 * Render the page for viewing created pages
 */
function dynamic_pages_creator_view_pages_page() {
    $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
    ?>
    <div class="wrap">
        <h1>Created Pages</h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Page Title</th>
                    <th>Slug</th>
                    <th>Date Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($existing_pages_ids)): ?>
                    <tr>
                        <td colspan="3">No pages have been created yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($existing_pages_ids as $id => $info): ?>
                        <tr>
                            <td><?php echo esc_html(get_the_title($id)); ?></td>
                            <td><?php echo esc_html(get_post_field('post_name', $id)); ?></td>
                            <td><?php echo esc_html($info['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
