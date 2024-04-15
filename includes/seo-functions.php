<?php
class DPC_SEO_Functions {
    public function __construct() {
        add_action('template_redirect', [$this, 'initialize_seo_tags']);
    }

    public function initialize_seo_tags() {
        // initialize SEO tags for pages
        if (defined('WPSEO_VERSION')) {
            add_filter('wpseo_metadesc', [$this, 'seo_meta_description']);
            add_filter('wpseo_title', [$this, 'seo_meta_title']);
        } else {
            add_action('wp_head', [$this, 'fallback_seo_meta_tags']);
        }
    }

    public function seo_meta_description($description) {
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

    public function seo_meta_title($title) {
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

    public function fallback_seo_meta_tags() {
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
}
