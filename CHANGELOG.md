# Changelog

All notable changes to the "Dynamic Pages Creator with SEO" plugin will be documented in this file.

## [1.3.0] - 2024-08-06

### Added
- Enqueued Select2 CSS and JS for improved dropdowns in the admin pages.
- Enhanced `Draft Page Template` and `Parent Page` dropdowns with Select2 for better user experience.
- Updated Quick Edit functionality to utilize Select2 for dropdowns, ensuring reinitialization when Quick Edit is opened.

## [1.2.0] - 2024-05-03

### Added
- Custom slug formatting feature: Users can now specify custom slugs for pages using a `[keyword]` placeholder. This allows for more flexible and meaningful URL structures directly from the plugin settings.

### Improved
- Improved explanations in the settings interface to help users understand the difference between "Global" and "Default" SEO settings.

## [1.1.1] - 2024-05-02

### Added
- SEO template selection feature for individual pages created by the Dynamic Pages Creator plugin. Users can now choose between using the plugin's global SEO settings or the default settings provided by their theme or another SEO plugin.

### Fixed
- PHP warning related to attempting to access array offsets on a value of type bool.
- Issues with transient and clearing logic for page creation, ensuring that settings and metadata are preserved correctly.
- Deletion logic to accurately remove entries from the Created Pages list when pages are deleted.

### Changed
- Ensured that default plugin options are correctly initialized upon plugin activation.

## [1.1.0] - 2024-04-20

### Added
- Added the ability to select an existing draft as a template for creating new pages, enabling users to replicate page structure and content including custom fields and meta data.

### Improved
- Enhanced page duplication process to include all associated meta data and custom fields, ensuring comprehensive page clones.

## [1.0.0] - 2024-04-15

### Added
- Initial release of the Dynamic Pages Creator with SEO.
- Ability to create dynamic pages from a list of keywords through the WordPress admin panel.
- Automatic SEO meta tag generation based on page keywords.
- Shortcode support for dynamically inserting page keywords into content, with a default option.
- Integration with Yoast SEO plugin for enhanced SEO features.
- Security practices implemented to prevent unauthorized access and data validation.
- Admin menus for managing plugin settings and viewing created pages.
- Utility functions to support the main features.
- Modular structure for easy maintenance and updates:
  - `admin-menus.php` for handling admin dashboard interactions.
  - `page-management.php` for managing page creation and deletion.
  - `seo-functions.php` for handling SEO related functionality.
  - `utilities.php` for common helper functions used across the plugin.

### Security
- Direct script access checks to ensure the plugin files cannot be accessed directly.
- User capability checks to ensure only authorized users can create or delete pages.
- Data sanitization and validation to prevent XSS attacks and SQL injection.

## [Unreleased]
- Planning to add more integrations and customizable features based on user feedback.

