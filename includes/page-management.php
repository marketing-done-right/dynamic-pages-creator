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
        $titles = isset($options['page_titles']) ? $options['page_titles'] : '';
        $parent_id = isset($options['parent']) ? intval($options['parent']) : 0;

        if (empty($titles)) {
            add_settings_error(
                'dynamic_pages_creator_options',
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
                    'dynamic_pages_creator_options',
                    'dynamic_pages_creator_empty_title',
                    'Error: Empty titles are not allowed. Please enter valid titles to create pages.',
                    'error'
                );
                continue;
            }

            $slug = sanitize_title($title);
            if (!get_page_by_path($slug, OBJECT, 'page') && !array_key_exists($slug, $existing_pages_ids)) {
                $page_id = wp_insert_post([
                    'post_title'    => $title,
                    'post_content'  => 'This is an automatically generated page for ' . $title,
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_name'     => $slug,
                    'post_parent'   => $parent_id
                ]);

                if ($page_id && !is_wp_error($page_id)) {
                    $existing_pages_ids[$page_id] = ['date' => $timestamp, 'title' => $title, 'slug' => $slug];
                    $created_pages[] = $title;
                } else {
                    $errors[] = $title;
                }
            } else {
                $errors[] = $title . ' (already exists)';
            }
        }

        update_option('dynamic_pages_creator_existing_pages_ids', $existing_pages_ids);

        // Store success and error messages in transients
        if (!empty($created_pages)) {
            set_transient('dpc_page_creation_success', 'Successfully created pages for the following titles: ' . implode(', ', $created_pages), 30);
        }

        if (!empty($errors)) {
            set_transient('dpc_page_creation_errors', $errors, 30);
        }

        // Assuming pages were created, set a flag
        $shouldClearFields = !empty($created_pages);

        // Store this flag in an option to use it later when enqueuing scripts
        update_option('dpc_should_clear_fields', $shouldClearFields);
    
        if (!empty($created_pages) || !empty($errors)) {
            // Redirect to avoid form resubmission issues
            wp_redirect(menu_page_url('dynamic-pages-creator', false));
            exit;
        }
    }

    // Function to display settings errors after redirect
    public function display_settings_errors() {
        if ($message = get_transient('dpc_page_creation_success')) {
            add_settings_error('dynamic_pages_creator_options', 'dynamic_pages_creator_page_titles_success', $message, 'updated');
            delete_transient('dpc_page_creation_success');
        }
        if ($errors = get_transient('dpc_page_creation_errors')) {
            foreach ($errors as $error) {
                add_settings_error('dynamic_pages_creator_options', 'dynamic_pages_creator_page_titles_error', $error, 'error');
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
