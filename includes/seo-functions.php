<?php

// Ensures that the file is not accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles SEO related functionality for the Dynamic Pages Creator plugin.
 */

class DPC_SEO_Functions {
    public function __construct() {
        add_action('template_redirect', [$this, 'initialize_seo_tags']);
        add_action('add_meta_boxes', [$this, 'add_seo_override_meta_box']);
        add_action('save_post', [$this, 'save_seo_override_meta_box'], 10, 2);
    }

    public function initialize_seo_tags() {
        // initialize SEO tags for pages
        if (defined('WPSEO_VERSION')) {
            add_filter('wpseo_metadesc', [$this, 'seo_meta_description']);
            add_filter('wpseo_title', [$this, 'seo_meta_title']);
            add_filter('wpseo_opengraph_title', [$this, 'seo_meta_title']);
            add_filter('wpseo_opengraph_desc', [$this, 'seo_meta_description']);
            add_filter('wpseo_twitter_title', [$this, 'seo_meta_title']);
            add_filter('wpseo_twitter_description', [$this, 'seo_meta_description']);
        } else {
            add_action('wp_head', [$this, 'fallback_seo_meta_tags']);
        }
    }

    public function add_seo_override_meta_box() {
        $screens = ['page']; // This can be expanded to other post types.
        foreach ($screens as $screen) {
            add_meta_box(
                'dpc_seo_override',
                'SEO Settings Override',
                [$this, 'render_seo_override_meta_box'],
                $screen,
                'side',
                'high'
            );
        }
    }

    public function render_seo_override_meta_box($post) {
        // Check if it's a plugin-created page
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        if (!isset($existing_pages_ids[$post->ID])) {
            echo 'This SEO setting is only applicable to pages created by the Dynamic Pages Creator plugin.';
            return;
        }

        // Security field
        wp_nonce_field('dpc_seo_override_nonce_action', 'dpc_seo_override_nonce');

        // Get the current setting
        $current_setting = get_post_meta($post->ID, '_dpc_seo_override', true) ?? 'default';
        //error_log('SEO Override setting for post ' . $post->ID . ': ' . $current_setting);

        // HTML for the meta box
        ?>
        <p>
            <label><input type="radio" name="dpc_seo_override" value="global" <?php checked($current_setting, 'global'); ?>> Use Global SEO Settings</label><br>
            <label><input type="radio" name="dpc_seo_override" value="default" <?php checked($current_setting, 'default'); ?>> Use Default SEO Settings</label>
        </p>
        <?php
    }

    public function save_seo_override_meta_box($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['dpc_seo_override_nonce']) || !wp_verify_nonce($_POST['dpc_seo_override_nonce'], 'dpc_seo_override_nonce_action')) {
            return;
        }

        // Check user permissions
        if ('page' === $post->post_type && !current_user_can('edit_page', $post_id)) {
            return;
        }

        // Update the meta field in the database
        if (isset($_POST['dpc_seo_override'])) {
            update_post_meta($post_id, '_dpc_seo_override', sanitize_text_field($_POST['dpc_seo_override']));
        }
    }

    public function seo_meta_description($description) {
        if (is_singular('page')) {
            $page_id = get_the_ID();
            $seo_template = get_post_meta($page_id, '_dpc_seo_override', true) ?? 'default';
            //error_log('Current SEO template for page ' . $page_id . ': ' . $seo_template);
            if ($seo_template == 'global') {
                $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
                if (isset($existing_pages_ids[$page_id])) {
                    $seo_template_setting = get_option('seo_meta_description_template', 'Learn more about [keyword] on our site.');
                    return str_replace('[keyword]', get_the_title(), $seo_template_setting);
                }
            } else {
                // Return default or another plugin's description
                return $description;
            }
        }
        return $description;
    }

    public function seo_meta_title($keyword) {
        if (is_singular('page')) {
            $page_id = get_the_ID();
            $seo_template = get_post_meta($page_id, '_dpc_seo_override', true) ?? 'default';
            if ($seo_template == 'global') {
                $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
                if (isset($existing_pages_ids[$page_id])) {
                    $seo_template_setting = get_option('seo_meta_title_template', '[keyword] | Your Site Name');
                    return str_replace('[keyword]', get_the_title(), $seo_template_setting);
                }
            } else {
                // Return default or another plugin's keyword
                return $keyword;
            }
        }
        return $keyword;
    }

    public function fallback_seo_meta_tags() {
        if (is_singular('page')) {
            $page_id = get_the_ID();
            $seo_template = get_post_meta($page_id, '_dpc_seo_override', true) ?? 'default';
            if ($seo_template == 'global') {
                $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
                if (isset($existing_pages_ids[$page_id])) {
                    $seo_title = str_replace('[keyword]', get_the_title(), get_option('seo_meta_title_template', '[keyword] | Your Site Name'));
                    echo '<title>' . esc_html($seo_title) . '</title>';
                    $seo_description = str_replace('[keyword]', get_the_title(), get_option('seo_meta_description_template', 'Learn more about [keyword] on our site.'));
                    echo '<meta name="description" content="' . esc_attr($seo_description) . '">';
                }
            } else {
                // If 'default' or another setting, let WordPress handle the head tags normally
                // This could involve removing actions added by this plugin or simply doing nothing here
                do_action('dpc_custom_seo_fallback', $page_id);
            }
        }
    }
}
