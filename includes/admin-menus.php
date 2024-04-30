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
        
    }

    // Enqueue scripts and styles
    public function enqueue_scripts_and_styles() {
        wp_enqueue_script('dpc-admin-js', plugins_url('js/admin-scripts.js', __FILE__), array('jquery'), null, true);
        $shouldClearFields = get_option('dpc_should_clear_fields', false);
        // Pass the flag to JavaScript
        wp_localize_script('dpc-admin-js', 'dpcData', array(
            'clearFields' => $shouldClearFields ? 'true' : 'false'
        ));

        // Batch processing script
        wp_enqueue_script('dpc-admin-batch-js', plugins_url('js/dpc-batch-process.js', __FILE__), ['jquery'], null, true);
        wp_localize_script('dpc-admin-batch-js', 'dpcAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dpc-ajax-nonce')
        ]);

        // Reset the flag after passing it to JavaScript
        if ($shouldClearFields) {
            update_option('dpc_should_clear_fields', false);
        }
    
        wp_enqueue_style('dpc-admin-css', plugins_url('css/admin-style.css', __FILE__));
    }

    // Validation functions for settings fields
    public function validate_options($inputs) {
        $new_input = [];
        $inputs['page_keywords'] = sanitize_text_field($inputs['page_keywords']);
        $inputs['parent'] = absint($inputs['parent']);
        $new_input['page_template'] = absint($inputs['page_template']);
        return $inputs;
    }

    // Callback functions for settings fields
    public function main_settings_section_callback() {
        echo 'Enter the keywords for the pages you wish to create, separated by commas. For example, "Chicago, New York, Cleveland".';
    }
    
    public function keyword_field_callback() {
        $shouldClearFields = get_transient('dpc_clear_fields');
        $options = get_option('dynamic_pages_creator_options');
        $value = $shouldClearFields ? '' : esc_attr($options['page_keywords'] ?? '');
        echo "<input type='text' id='dynamic_pages_creator_keyword_field' name='dynamic_pages_creator_options[page_keywords]' value='" . $value . "' style='width: 100%;' autocomplete='off'>";

        // Delete the transient so it does not affect future loads
        delete_transient('dpc_clear_fields');

    }

    public function parent_field_callback() {
        $options = get_option('dynamic_pages_creator_options');
        $pages = get_pages();
        echo '<select id="dynamic_pages_creator_parent_field" name="dynamic_pages_creator_options[parent]">';
        echo '<option value="0">Main Page (no parent)</option>';
        foreach ($pages as $page) {
            $selected = ($options['parent'] == $page->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
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

    public function dynamic_pages_creator_draft_page_field_callback() {
        $args = array(
            'post_type'      => 'page',
            'post_status'    => 'draft',
            'posts_per_page' => -1
        );
        $draft_pages = get_posts($args);
        $selected_template = get_option('dynamic_pages_creator_page_template');
    
        echo '<select id="dynamic_pages_creator_template_field" name="dynamic_pages_creator_options[page_template]">';
        echo '<option value="">Select a Draft Page</option>';
        foreach ($draft_pages as $page) {
            $selected = selected($selected_template, $page->ID, false);
            echo '<option value="' . esc_attr($page->ID) . '"' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }    

    // Render functions for settings fields

    public function render_main_page() {
        include 'views/main_settings_page.php';
    }

    public function render_seo_settings_page() {
        include 'views/seo_settings_page.php';
    }

    public function render_view_pages_page() {
        include 'views/view_pages_page.php';
    }

}