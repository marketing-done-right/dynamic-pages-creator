# Changelog

All notable changes to the "Dynamic Pages Creator with SEO" plugin will be documented in this file.

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

