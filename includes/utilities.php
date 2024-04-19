<?php
// Ensures that the file is not accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles utility functionality for the Dynamic Pages Creator plugin.
 */

class DPC_Utilities {
    public function __construct() {
        add_shortcode('keyword', array($this, 'handle_keyword_shortcode'));
    }

    /**
     * Handles the [keyword default="..."] shortcode.
     * 
     * @param array $atts Attributes from the shortcode.
     * @return string The keyword or the default text if a keyword is not available.
     */
    public function handle_keyword_shortcode($atts) {
        // Get the attributes and set default values.
        $attributes = shortcode_atts(array(
            'default' => 'Default Keyword' // Default value if 'default' attribute not provided.
        ), $atts);

        // Check if the current page is created by the plugin and has a keyword.
        $page_id = get_the_ID();
        $existing_pages_ids = get_option('dynamic_pages_creator_existing_pages_ids', []);
        
        // If the current page ID is in the array of pages created by the plugin, use its kyeword/title.
        if (array_key_exists($page_id, $existing_pages_ids)) {
            return get_the_title($page_id);
        } else {
            // Otherwise, return the default value.
            return esc_html($attributes['default']);
        }
    }
}

// Initialize the utilities class.
new DPC_Utilities();
