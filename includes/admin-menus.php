<?php

 // Ensures that the file is not accessed directly.
 if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles all admin menu related functionality for the Dynamic Pages Creator plugin.
 */

class DPC_Admin_Menus {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menus'));
        add_action('admin_init', array($this, 'register_settings_and_fields'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));
    }

    public function add_menus() {
        add_menu_page(
            'Dynamic Pages Creator', // Page title
            'Dynamic Pages Creator', // Menu title
            'manage_options', // Capability
            'dynamic-pages-creator', // Menu slug
            array($this, 'render_main_page'), // Function callback
            'dashicons-welcome-add-page', // Icon
            20 // Position
        );

        // Add the first submenu page, which is usually the same as the main menu page but with a different title
        add_submenu_page(
            'dynamic-pages-creator', // Parent slug
            'Create Pages', // Page title
            'Create Pages', // Menu title
            'manage_options', // Capability
            'dynamic-pages-creator', // Menu slug, same as the main menu slug
            array($this, 'render_main_page') // Function callback, same as the main menu callback
        );

        add_submenu_page(
            'dynamic-pages-creator', // Parent slug
            'SEO Settings', // Page title
            'SEO Settings', // Menu title
            'manage_options', // Capability
            'dynamic-pages-creator-seo', // Menu slug
            array($this, 'render_seo_settings_page') // Function callback
        );

        add_submenu_page(
            'dynamic-pages-creator', // Parent slug
            'View Created Pages', // Page title
            'View Created Pages', // Menu title
            'manage_options', // Capability
            'dynamic-pages-view-pages', // Menu slug
            array($this, 'render_view_pages_page') // Function callback
        );
    }

    public function register_settings_and_fields() {
        register_setting('dynamic_pages_creator_options', 'dynamic_pages_creator_options', array($this, 'validate_options'));
        add_settings_section('dynamic_pages_creator_main', 'Settings', array($this, 'main_settings_section_callback'), 'dynamic-pages-creator');
        
        add_settings_field('dynamic_pages_creator_keyword_field', 'Page Keywords (comma-separated)', array($this, 'keyword_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');
        add_settings_field('dynamic_pages_creator_parent_field', 'Parent Page', array($this, 'parent_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');

        // Field for custom slug format
        add_settings_field('dynamic_pages_creator_slug_format', 'Custom Slug Format', array($this, 'slug_format_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');
    
        // SEO Settings
        register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_title_template');
        register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_description_template');
    
        add_settings_section('seo_settings_section', 'SEO Meta Settings', array($this, 'seo_settings_section_callback'), 'dynamic_pages_creator_seo_settings');
        add_settings_field('seo_meta_title_field', 'SEO Meta Title Template', array($this, 'seo_meta_title_field_callback'), 'dynamic_pages_creator_seo_settings', 'seo_settings_section');
        add_settings_field('seo_meta_description_field', 'SEO Meta Description Template', array($this, 'seo_meta_description_field_callback'), 'dynamic_pages_creator_seo_settings', 'seo_settings_section');
        add_settings_field('dynamic_pages_creator_draft_page_field', 'Draft Page Template', array($this, 'dynamic_pages_creator_draft_page_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');

        // Check if the settings were saved and add an updated message
        if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
            add_settings_error(
                'dynamic_pages_creator_seo_settings',
                'seo_settings_updated',
                'SEO settings updated successfully.',
                'updated'
            );
        }

        // Add a new field for choosing SEO template
        add_settings_field(
            'seo_template', // ID
            'SEO Template', // Title
            array($this, 'seo_template_field_callback'), // Callback
            'dynamic-pages-creator', // Page
            'dynamic_pages_creator_main' // Section
        );
        
    }

    // Enqueue scripts and styles
    public function enqueue_scripts_and_styles() {
        $script_version = '1.3.0'; // Update the version number to bust the cache
        wp_enqueue_script('dpc-admin-js', plugins_url('js/admin-scripts.js', __FILE__), array('jquery'), $script_version, true);
        $shouldClearFields = get_option('dpc_should_clear_fields', false);
        // Pass the flag to JavaScript
        wp_localize_script('dpc-admin-js', 'dpcData', array(
            'clearFields' => $shouldClearFields ? 'true' : 'false'
        ));

        // Reset the flag after passing it to JavaScript
        if ($shouldClearFields) {
            update_option('dpc_should_clear_fields', false);
        }
        $style_version = '1.2.0'; // Update the version number to bust the cache
        wp_enqueue_style('dpc-admin-css', plugins_url('css/admin-style.css', __FILE__) , array(), $style_version );

        // Enqueue Select2 CSS
        wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css');
    
        // Enqueue Select2 JS
        wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js', array('jquery'), null, true);
    }

    // Validation functions for settings fields
    public function validate_options($inputs) {
    error_log('Received form inputs: ' . print_r($inputs, true));  // Log the received inputs

    $new_input = [];
    $new_input['page_keywords'] = isset($inputs['page_keywords']) ? sanitize_text_field($inputs['page_keywords']) : '';
    $new_input['parent'] = isset($inputs['parent']) ? absint($inputs['parent']) : 0;
    $new_input['page_template'] = isset($inputs['page_template']) ? absint($inputs['page_template']) : 0;
    $new_input['seo_template'] = in_array($inputs['seo_template'], ['global', 'default']) ? $inputs['seo_template'] : 'default';
    
    // Validate custom slug format
    $slug_format = sanitize_text_field($inputs['slug_format']);
    if (!empty($slug_format) && strpos($slug_format, '[keyword]') === false) {
        add_settings_error(
            'dynamic_pages_creator_options', 
            'invalid_slug_format', 
            'Invalid slug format: Missing [keyword] placeholder. Please include [keyword] in your custom slug format.',
            'error'
        );
        $slug_format = ''; // Consider whether to reset the format if invalid or keep the old one
    }
    $new_input['slug_format'] = $slug_format;  // Save the validated format

    error_log('Sanitized inputs: ' . print_r($new_input, true));  // Log the sanitized inputs

    return $new_input;
}

    // Callback functions for settings fields
    public function main_settings_section_callback() {
        echo 'Enter the keywords for the pages you wish to create, separated by commas. For example, "Chicago, New York, Cleveland".';
    }
    
    public function keyword_field_callback() {
        $shouldClearFields = get_transient('dpc_clear_fields');
        $options = get_option('dynamic_pages_creator_options');
        $value = $shouldClearFields ? '' : esc_attr($options['page_keywords'] ?? '');
        echo "<input type='text' id='dynamic_pages_creator_keyword_field' name='dynamic_pages_creator_options[page_keywords]' value='" . esc_attr($value) . "' style='width: 100%;' autocomplete='off'>";

        // Delete the transient so it does not affect future loads
        delete_transient('dpc_clear_fields');

    }

    public function parent_field_callback() {
        $options = get_option('dynamic_pages_creator_options', []);  // Default to an empty array if the option doesn't exist
        $pages = get_pages();
        echo '<select class="dpc-select2" id="dynamic_pages_creator_parent_field" name="dynamic_pages_creator_options[parent]">';
        echo '<option value="0">Main Page (no parent)</option>';
        foreach ($pages as $page) {
            // Check if options and parent are set and not false
            $selected = (is_array($options) && isset($options['parent']) && $options['parent'] == $page->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . esc_attr($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    public function seo_settings_section_callback() {
        echo '<p>Enter the template for SEO meta tags. Use [keyword] as a placeholder to insert the page keyword.</p>';
    }
    
    public function seo_meta_title_field_callback() {
        $title_template = get_option('seo_meta_title_template', '[keyword] | Your Site Name');
        echo "<input type='text' id='seo_meta_title_template' name='seo_meta_title_template' value='" . esc_attr($title_template) . "' style='width: 100%;'>";
    }
    
    public function seo_meta_description_field_callback() {
        $description_template = get_option('seo_meta_description_template', 'Learn more about [keyword] on our site.');
        echo "<textarea id='seo_meta_description_template' name='seo_meta_description_template' rows='5' style='width: 100%;'>" . esc_textarea($description_template) . "</textarea>";
    }

    public function seo_template_field_callback() {
        $options = get_option('dynamic_pages_creator_options');
        $template = $options['seo_template'] ?? 'default'; // Default to `default` if not set
        // HTML form inputs
        echo '<p>Select how SEO settings should be applied to pages:</p>';
        echo '<label><input type="radio" name="dynamic_pages_creator_options[seo_template]" value="global" ' . checked($template, 'global', false) . '> <strong>Global:</strong> Apply the SEO settings defined in the plugin\'s SEO Settings panel to this page.</label><br>';
        echo '<label><input type="radio" name="dynamic_pages_creator_options[seo_template]" value="default" ' . checked($template, 'default', false) . '> <strong>Default:</strong> Use the SEO settings from your WordPress theme or another SEO plugin that may be active.</label><br>';
        echo '<p style="font-size: small; color: #666;">Note: The "Default" setting allows the page to inherit SEO settings from other plugins (like Yoast) or the theme, bypassing the plugin\'s SEO configuration.</p>';
    }

    public function slug_format_field_callback() {
        $options = get_option('dynamic_pages_creator_options');
        $slug_format = isset($options['slug_format']) ? esc_attr($options['slug_format']) : '';
        echo "<input style='width:50%;' type='text' id='dynamic_pages_creator_slug_format' name='dynamic_pages_creator_options[slug_format]' value='" . esc_attr($slug_format) . "' />";
        echo "<p>Enter a custom slug format using [keyword] to include the keyword dynamically.</p>";
        echo "<p style='font-size: small; color: #666;'>Example: my-[keyword]-page. Leave empty for default slug behavior.</p>";
    }

    public function dynamic_pages_creator_draft_page_field_callback() {
        $args = array(
            'post_type'      => 'page',
            'post_status'    => 'draft',
            'posts_per_page' => -1
        );
        $draft_pages = get_posts($args);
        $selected_template = get_option('dynamic_pages_creator_page_template');
    
        echo '<select class="dpc-select2" id="dynamic_pages_creator_template_field" name="dynamic_pages_creator_options[page_template]">';
        echo '<option value="">Select a Draft Page</option>';
        foreach ($draft_pages as $page) {
            $selected = selected($selected_template, $page->ID, false);
            echo '<option value="' . esc_attr($page->ID) . '"' . esc_attr($selected) . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }    

    // Render functions for settings fields

    public function render_main_page() {
        include 'views/main_settings_page.php';
        wp_nonce_field('dynamic_pages_creator_save_settings', 'dynamic_pages_creator_nonce');
    }

    public function render_seo_settings_page() {
        include 'views/seo_settings_page.php';
    }

    public function render_view_pages_page() {
        include 'views/view_pages_page.php';
    }

}