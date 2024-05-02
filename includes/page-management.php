<?php

 // Ensures that the file is not accessed directly.
 if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles page management functionality for the Dynamic Pages Creator plugin.
 */

class DPC_Page_Management {
    public function __construct() {
        add_action('admin_init', array($this, 'check_page_submission'));
        add_action('before_delete_post', array($this, 'handle_page_deletion'));
        add_action('admin_notices', array($this, 'display_settings_errors'));
        add_action('wp_loaded', array($this, 'verify_existing_pages_ids'));
    }

    public function verify_existing_pages_ids() {
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        $valid_ids = [];

        foreach ($existing_pages_ids as $page_id => $info) {
            if (get_post_status($page_id)) {  // Checks if the post exists and is not trashed.
                $valid_ids[$page_id] = $info;
            } else {
                error_log("Page ID $page_id does not exist and was removed from existing pages IDs.");
            }
        }

        update_option('dynamic_pages_creator_existing_pages_ids', $valid_ids);
    }

    public function check_page_submission() {
        // Check if the form submission is intended for our plugin's page creation settings
        if (isset($_POST['option_page']) && $_POST['option_page'] == 'dynamic_pages_creator_options') {
            // Ensure that the current user has the capability to manage options
            if (current_user_can('manage_options')) {
                // Check if our specific settings data has been posted
                if (isset($_POST['dynamic_pages_creator_options'])) {
                    $this->create_pages($_POST['dynamic_pages_creator_options']);
                }
            } else {
                // Optionally handle the case where the user does not have permission
                wp_die('You do not have sufficient permissions to access this page.');
            }
        }
    }
    

    public function create_pages($options) {
        error_log('Received options: ' . print_r($options, true));  // Log the received options at the start
    
        $keywords = isset($options['page_keywords']) ? $options['page_keywords'] : '';
        $parent_id = isset($options['parent']) ? intval($options['parent']) : 0;
        $template_id = isset($options['page_template']) ? intval($options['page_template']) : 0;
    
        if (empty($keywords)) {
            add_settings_error(
                'dynamic_pages_creator_options',
                'dynamic_pages_creator_page_keywords_error',
                'Error: No page keywords provided. Please enter some page keywords to create pages.',
                'error'
            );
            return '';
        }
    
        $keywords_array = explode(',', $keywords);
        $created_pages = [];
        $errors = [];
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        error_log('Existing pages IDs at start: ' . print_r($existing_pages_ids, true));  // Log the existing pages IDs at the start
    
        $timestamp = current_time('mysql');
    
        foreach ($keywords_array as $keyword) {
            $keyword = trim($keyword);
            if (empty($keyword)) {
                add_settings_error(
                    'dynamic_pages_creator_options',
                    'dynamic_pages_creator_empty_keyword',
                    'Error: Empty keywords are not allowed. Please enter valid keywords to create pages.',
                    'error'
                );
                continue;
            }
    
            $slug = sanitize_title($keyword);
            if (!get_page_by_path($slug, OBJECT, 'page') && !array_key_exists($slug, $existing_pages_ids)) {
                if ($template_id > 0 && function_exists('duplicate_post_create_duplicate')) {
                    $template_post = get_post($template_id);
                    if ($template_post && $template_post->post_status === 'draft') {
                        $new_post_id = duplicate_post_create_duplicate($template_post, 'publish', $parent_id);
                        wp_update_post([
                            'ID'          => $new_post_id,
                            'post_title'  => $keyword,
                            'post_name'   => $slug,
                            'post_status' => 'publish',  // Ensure the status is set to publish
                        ]);
                        $page_id = $new_post_id;
                    }
                } else {
                    $page_data = [
                        'post_title'    => $keyword,
                        'post_content'  => 'This is an automatically generated page using the default page template.',
                        'post_status'   => 'publish',
                        'post_type'     => 'page',
                        'post_name'     => $slug,
                        'post_parent'   => $parent_id
                    ];
                    $page_id = wp_insert_post($page_data);
                }
    
                if ($page_id && !is_wp_error($page_id)) {
                    $existing_pages_ids[$page_id] = ['date' => $timestamp, 'title' => $keyword, 'slug' => $slug];
                    $created_pages[] = $keyword;
                } else {
                    $errors[] = $keyword;
                }
            } else {
                $errors[] = $keyword . ' (already exists)';
            }
        }
    
        error_log('Updated existing pages IDs before saving: ' . print_r($existing_pages_ids, true));  // Log before updating option
    
        if (!empty($created_pages)) {
            update_option('dynamic_pages_creator_existing_pages_ids', $existing_pages_ids);
            set_transient('dpc_page_creation_success', 'Successfully created pages for the following keywords: ' . implode(', ', $created_pages), 30);
        }
    
        if (!empty($errors)) {
            set_transient('dpc_page_creation_errors', $errors, 30);
        }
    
        $shouldClearFields = !empty($created_pages);
        update_option('dpc_should_clear_fields', $shouldClearFields);
    
        if (!empty($created_pages) || !empty($errors)) {
            wp_redirect(menu_page_url('dynamic-pages-creator', false));
            exit;
        }
    }    

    // Function to display settings errors after redirect
    public function display_settings_errors() {
        if ($message = get_transient('dpc_page_creation_success')) {
            add_settings_error('dynamic_pages_creator_options', 'dynamic_pages_creator_page_keywords_success', $message, 'updated');
            delete_transient('dpc_page_creation_success');
        }
        if ($errors = get_transient('dpc_page_creation_errors')) {
            foreach ($errors as $error) {
                add_settings_error('dynamic_pages_creator_options', 'dynamic_pages_creator_page_keywords_error', $error, 'error');
            }
            delete_transient('dpc_page_creation_errors');
        }
    }

    public function handle_page_deletion($post_id) {
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        if (array_key_exists($post_id, $existing_pages_ids)) {
            unset($existing_pages_ids[$post_id]);
            update_option('dynamic_pages_creator_existing_pages_ids', $existing_pages_ids);
        }
    }
}
