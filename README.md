# Dynamic Pages Creator with SEO

Dynamic Pages Creator with SEO is a WordPress plugin that automates the creation of web pages based on predefined keywords and dynamically assigns SEO meta tags to enhance search engine visibility.

## Authors

- [Hans Steffens](https://hanscode.io/)
- Marketing Done Right, LLC - [Visit us](https://marketingdr.co)

## Description

This plugin simplifies the management of content creation and SEO optimization by automatically generating pages with specified keywords and SEO tags, making it ideal for marketing campaigns, SEO strategies, and content management.

## Key Features

- **Automatic Page Creation**: Generate pages from a list of keywords provided through the plugin's settings page.
- **SEO Enhancement**: Automatically apply SEO meta tags based on the page keywords.
- **SEO Settings Control**: Users can choose between global SEO settings provided by the plugin or default settings from their existing theme or other SEO plugins. This setting is available for each page created through the Dynamic Pages Creator, allowing for more granular control over SEO.
- **Custom URL Slug Formatting**: To enhance SEO and maintain brand consistency across your site, the Dynamic Pages Creator now supports custom slug formatting. When creating pages, you can specify a slug template using the `[keyword]` placeholder. For example, setting the slug format to `my-[keyword]-page` and using the keyword `Miami` will generate the slug `my-miami-page`. This feature is optional; if no custom format is specified, the plugin will use the default slug derived from the page keyword.
- **Draft Template Selection**: Users can select an existing draft page as a template when creating new pages, allowing the duplication of page structure, content, custom fields, and meta data.
- **Shortcode Support**: Use the `[keyword default="text"]` shortcode to insert keywords dynamically in page content.
- **Yoast SEO Compatibility**: Works seamlessly with Yoast SEO to enhance your site's SEO capabilities. [^1]

## Security Practices

Our plugin adheres to the best security practices, including:
- Checking user capabilities to prevent unauthorized actions.
- Sanitizing and validating input data to avoid SQL injection and XSS attacks.
- Direct script access denial by defining 'ABSPATH'.

## Installation

1. Download the plugin from our [GitHub repository](https://github.com/hanscode/dynamic-pages-creator).
2. Upload the plugin files to your `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Use the plugin's settings page to configure and start creating pages.

## Configuration
After installation, navigate to the 'Dynamic Pages Creator' menu in your WordPress admin panel to configure the plugin:
- Set page keywords, parent page, and template preferences.
- Choose the SEO settings for each page individually or apply global settings.

## Usage

### Creating Pages
Navigate to the 'Dynamic Pages Creator' menu in the admin dashboard. Here, you can create new pages by entering keywords, choosing a parent page, and optionally selecting an existing draft page as a template. This replicates the selected draft's content, structure, and SEO settings onto the new page.

### Using the Shortcode
You can dynamically insert page keywords into your content using:
```plaintext
[keyword default="Default Keyword"]
```

If the page was not created via the plugin, "Default Keyword" will be displayed.

## Frequently Asked Questions
**Q: How do I change SEO settings for a created page?**
**A:** Navigate to the page editor where you'll find the 'SEO Settings Override' box. Select your preferred SEO settings for the page.

**Q: What happens if I deactivate the plugin?**
**A:** Deactivating the plugin will stop new pages from being created and managed by it, but existing pages will remain intact.

## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are greatly appreciated.

1. Fork the Project
2. Create your Feature Branch (git checkout -b feature/AmazingFeature)
3. Commit your Changes (git commit -m 'Add some AmazingFeature')
4. Push to the Branch (git push origin feature/AmazingFeature)
5. Open a Pull Request

## Changelog
See the [CHANGELOG](CHANGELOG.md) for a full list of changes and additions.

## License

Distributed under the GPL v3 License. See [LICENSE](LICENSE) for more information.

[^1]: This plugin is not officially associated with Yoast SEO or any other third-party plugin unless otherwise specified.
