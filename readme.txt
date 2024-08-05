=== Dynamic Pages Creator with SEO ===
Contributors: Marketing Done Right, LLC
Tags: seo, pages, dynamic
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.3.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Dynamic Pages Creator with SEO is a WordPress plugin that automates the creation of web pages based on predefined keywords and dynamically assigns SEO meta tags to enhance search engine visibility.

== Description ==

This plugin simplifies the management of content creation and SEO optimization by automatically generating pages with specified keywords and SEO tags, making it ideal for marketing campaigns, SEO strategies, and content management.

== Installation ==

1. Upload the plugin files to your `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the plugin's settings page to configure and start creating pages.

== Changelog ==

= 1.3.0 =
* Enqueued Select2 CSS and JS for improved dropdowns in the admin pages.
* Enhanced `Draft Page Template` and `Parent Page` dropdowns with Select2 for better user experience.
* Updated Quick Edit functionality to utilize Select2 for dropdowns, ensuring reinitialization when Quick Edit is opened.

= 1.2.0 =
* Custom slug formatting feature: Users can now specify custom slugs for pages using a `[keyword]` placeholder. This allows for more flexible and meaningful URL structures directly from the plugin settings.
* Improved explanations in the settings interface to help users understand the difference between "Global" and "Default" SEO settings.

= 1.1.1 =
* SEO template selection feature for individual pages created by the Dynamic Pages Creator plugin. Users can now choose between using the plugin's global SEO settings or the default settings provided by their theme or another SEO plugin.
* Fixed PHP warning related to attempting to access array offsets on a value of type bool.
* Issues with transient and clearing logic for page creation, ensuring that settings and metadata are preserved correctly.
* Deletion logic to accurately remove entries from the Created Pages list when pages are deleted.
* Ensured that default plugin options are correctly initialized upon plugin activation.

= 1.1.0 =
* Added the ability to select an existing draft as a template for creating new pages, enabling users to replicate page structure and content including custom fields and meta data.
* Enhanced page duplication process to include all associated meta data and custom fields, ensuring comprehensive page clones.

= 1.0.0 =
* Initial release of the Dynamic Pages Creator with SEO.
* Ability to create dynamic pages from a list of keywords through the WordPress admin panel.
* Automatic SEO meta tag generation based on page keywords.
* Shortcode support for dynamically inserting page keywords into content, with a default option.
* Integration with Yoast SEO plugin for enhanced SEO features.
* Security practices implemented to prevent unauthorized access and data validation.
* Admin menus for managing plugin settings and viewing created pages.
* Utility functions to support the main features.
* Modular structure for easy maintenance and updates:
  - `admin-menus.php` for handling admin dashboard interactions.
  - `page-management.php` for managing page creation and deletion.
  - `seo-functions.php` for handling SEO related functionality.
  - `utilities.php` for common helper functions used across the plugin.

== Frequently Asked Questions ==

= How do I change SEO settings for a created page? =
Navigate to the page editor where you'll find the 'SEO Settings Override' box. Select your preferred SEO settings for the page.

= What happens if I deactivate the plugin? =
Deactivating the plugin will stop new pages from being created and managed by it, but existing pages will remain intact.

= How do I create a draft template? =
Navigate to the 'Dynamic Pages Creator' menu in the admin dashboard. Select an existing draft as a template when creating new pages. This replicates the selected draft's content, structure, and SEO settings onto the new page.

= How do I use the Quick Edit feature? =
In the 'View Created Pages' section, you can quickly edit page details such as SEO settings, status, and parent page using the Quick Edit option available for each item. The filters available help you navigate and manage the pages efficiently.

== Upgrade Notice ==

= 1.3.0 =
Enhance your user experience with new features and automatic updates.