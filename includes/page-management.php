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
        add_action('wp_ajax_process_batch_creation', array($this, 'handle_batch_creation'));
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
        $keywords = isset($options['page_keywords']) ? explode(',', $options['page_keywords']) : [];
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
    
        // Ensure that the keywords are not just an array of empty strings
        $keywords = array_filter(array_map('trim', $keywords));
        if (empty($keywords)) {
            add_settings_error(
                'dynamic_pages_creator_options',
                'dynamic_pages_creator_page_keywords_error',
                'Error: No valid keywords provided. Please enter some valid keywords.',
                'error'
            );
            return '';
        }
    
        // Set transients for batch processing
        set_transient('dpc_batch_keywords', $keywords, HOUR_IN_SECONDS * 2); // Extended time for safety
        set_transient('dpc_batch_parent_id', $parent_id, HOUR_IN_SECONDS * 2);
        set_transient('dpc_batch_template_id', $template_id, HOUR_IN_SECONDS * 2);
        set_transient('dpc_batch_index', 0, HOUR_IN_SECONDS * 2);
    
        wp_redirect(admin_url('admin.php?page=dynamic-pages-creator&batch_processing=1'));
        exit;
    }     

    public function create_single_page($keyword, $parent_id, $template_id, $timestamp, &$existing_pages_ids) {
        $slug = sanitize_title($keyword);
        if (!get_page_by_path($slug, OBJECT, 'page') && !isset($existing_pages_ids[$slug])) {
            $page_data = [
                'post_title'    => $keyword,
                'post_content'  => 'Automatically generated content.',
                'post_status'   => 'publish',
                'post_type'     => 'page',
                'post_name'     => $slug,
                'post_parent'   => $parent_id
            ];
    
            if ($template_id > 0 && function_exists('duplicate_post_create_duplicate')) {
                $template_post = get_post($template_id);
                if ($template_post && $template_post->post_status === 'draft') {
                    $new_page_id = duplicate_post_create_duplicate($template_post, 'publish', $parent_id);
                }
            } else {
                $new_page_id = wp_insert_post($page_data);
            }
    
            if (!is_wp_error($new_page_id)) {
                // Add the timestamp to the existing pages IDs tracking array
                $existing_pages_ids[$new_page_id] = [
                    'date' => $timestamp,  // Save the creation timestamp
                    'title' => $keyword,
                    'slug' => $slug
                ];
                return $new_page_id;
            }
        } else {
            return new WP_Error('page_exists', __('Page already exists'));
        }
    }    

    public function handle_batch_creation() {
        $batch_index = intval(get_transient('dpc_batch_index'));
        $keywords = get_transient('dpc_batch_keywords');
        $parent_id = get_transient('dpc_batch_parent_id');
        $template_id = get_transient('dpc_batch_template_id');
    
        // Check if keywords is an array
        if (!is_array($keywords)) {
            wp_send_json_error(['message' => 'Keywords data is missing or invalid']);
            return;
        }
    
        $total_keywords = count($keywords);
        $batch_size = 5; // Adjust batch size as necessary
    
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        $timestamp = current_time('mysql');
        $created_pages = [];
        $errors = [];
    
        $batch_keywords = array_slice($keywords, $batch_index * $batch_size, $batch_size);
        foreach ($batch_keywords as $keyword) {
            $result = $this->create_single_page(trim($keyword), $parent_id, $template_id, $timestamp, $existing_pages_ids);
            if (is_wp_error($result)) {
                $errors[] = $keyword;
            } else {
                $created_pages[] = $keyword;
                $existing_pages_ids[$result] = ['date' => $timestamp, 'title' => $keyword, 'slug' => sanitize_title($keyword)];
            }
        }
    
        $progress = ($batch_index * $batch_size) / $total_keywords * 100; // Calculate progress in percentage
    
        update_option('dynamic_pages_creator_existing_pages_ids', $existing_pages_ids);
    
        if (!empty($created_pages)) {
            set_transient('dpc_page_creation_success', 'Successfully created pages for the following keywords: ' . implode(', ', $created_pages), 30);
        }
    
        if (!empty($errors)) {
            set_transient('dpc_page_creation_errors', $errors, 30);
        }
    
        $batch_index++;
        if ($batch_index * $batch_size < $total_keywords) {
            set_transient('dpc_batch_index', $batch_index, HOUR_IN_SECONDS);
            wp_send_json_success([
                'continue' => true,
                'progress' => $progress
            ]);
        } else {
            wp_send_json_success([
                'continue' => false,
                'progress' => 100
            ]);
            // Clean up after batch processing
            delete_transient('dpc_batch_keywords');
            delete_transient('dpc_batch_parent_id');
            delete_transient('dpc_batch_template_id');
            delete_transient('dpc_batch_index');
    
            // Final redirection to avoid resubmission
            wp_redirect(admin_url('admin.php?page=dynamic-pages-creator&batch_complete=1'));
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
