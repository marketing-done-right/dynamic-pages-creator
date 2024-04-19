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

## Usage

### Creating Pages
Navigate to the 'Dynamic Pages Creator' menu in the admin dashboard. Here, you can create new pages by entering keywords, choosing a parent page, and optionally selecting an existing draft page as a template. This replicates the selected draft's content, structure, and SEO settings onto the new page.

### Using the Shortcode
You can dynamically insert page keywords into your content using:
```plaintext
[keyword default="Default Keyword"]
```

If the page was not created via the plugin, "Default Keyword" will be displayed.

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

Distributed under the GPL v3 License. See [LICENSE](LICENSE.md) for more information.

[^1]: This plugin is not officially associated with Yoast SEO or any other third-party plugin unless otherwise specified.
