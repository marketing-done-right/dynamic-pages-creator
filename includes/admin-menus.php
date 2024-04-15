<?php
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
            'dashicons-admin-generic', // Icon
            20 // Position
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
        
        add_settings_field('dynamic_pages_creator_title_field', 'Page Titles (comma-separated)', array($this, 'title_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');
        add_settings_field('dynamic_pages_creator_parent_field', 'Parent Page', array($this, 'parent_field_callback'), 'dynamic-pages-creator', 'dynamic_pages_creator_main');
    
        // SEO Settings
        register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_title_template');
        register_setting('dynamic_pages_creator_seo_settings', 'seo_meta_description_template');
    
        add_settings_section('seo_settings_section', 'SEO Meta Settings', array($this, 'seo_settings_section_callback'), 'dynamic_pages_creator_seo_settings');
        add_settings_field('seo_meta_title_field', 'SEO Meta Title Template', array($this, 'seo_meta_title_field_callback'), 'dynamic_pages_creator_seo_settings', 'seo_settings_section');
        add_settings_field('seo_meta_description_field', 'SEO Meta Description Template', array($this, 'seo_meta_description_field_callback'), 'dynamic_pages_creator_seo_settings', 'seo_settings_section');

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
        wp_localize_script('dpc-admin-js', 'dpcData', array(
            'clearFields' => $shouldClearFields ? 'true' : 'false'
        ));
    
        wp_enqueue_style('dpc-admin-css', plugins_url('css/admin-style.css', __FILE__));
    }

    // Validation functions for settings fields
    public function validate_options($inputs) {
        $inputs['page_titles'] = sanitize_text_field($inputs['page_titles']);
        $inputs['parent'] = absint($inputs['parent']);
        return $inputs;
    }

    // Callback functions for settings fields
    public function main_settings_section_callback() {
        echo 'Enter the titles for the pages you wish to create, separated by commas. For example, "Home, About Us, Contact".';
    }
    
    public function title_field_callback() {
        $options = get_option('dynamic_pages_creator_options');
        echo "<input type='text' id='dynamic_pages_creator_title_field' name='dynamic_pages_creator_options[page_titles]' value='" . esc_attr($options['page_titles'] ?? '') . "' style='width: 100%;'>";
    }

    public function parent_field_callback() {
        $options = get_option('dynamic_pages_creator_options');
        $pages = get_pages();
        echo '<select id="dynamic_pages_creator_parent_field" name="dynamic_pages_creator_options[parent]">';
        echo '<option value="0">No parent</option>';
        foreach ($pages as $page) {
            $selected = ($options['parent'] == $page->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
    }

    public function seo_settings_section_callback() {
        echo '<p>Enter the template for SEO meta tags. Use [title] as a placeholder to insert the page title.</p>';
    }
    
    public function seo_meta_title_field_callback() {
        $title_template = get_option('seo_meta_title_template', '[title] | Your Site Name');
        echo "<input type='text' id='seo_meta_title_template' name='seo_meta_title_template' value='" . esc_attr($title_template) . "' style='width: 100%;'>";
    }
    
    public function seo_meta_description_field_callback() {
        $description_template = get_option('seo_meta_description_template', 'Learn more about [title] on our site.');
        echo "<textarea id='seo_meta_description_template' name='seo_meta_description_template' rows='5' style='width: 100%;'>" . esc_textarea($description_template) . "</textarea>";
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