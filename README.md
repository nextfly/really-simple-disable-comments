# Really Simple Disable Comments

[![WordPress](https://img.shields.io/wordpress/v/really-simple-disable-comments.svg)](https://wordpress.org/plugins/really-simple-disable-comments/)
[![PHP](https://img.shields.io/badge/php-%3E%3D7.0-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.

## Description

Really Simple Disable Comments is a lightweight plugin that completely disables WordPress comments functionality with a single activation. No configuration needed!

### Features

* Disables comments on all post types
* Removes comment-related UI elements
* Disables trackbacks and pingbacks
* Removes comment-related admin menu items and dashboard widgets
* Hides comment counts from dashboard "At a Glance" widget
* Hides "Recent Comments" section from dashboard Activity widget
* Disables all comment-related Gutenberg blocks
* Clean and efficient code with no settings required

### What Gets Disabled?

* Comment forms and displays
* Admin menu items and dashboard widgets
* Comment-related Gutenberg blocks
* Trackbacks and pingbacks
* Comment-related UI elements in themes

### Developer Friendly

The plugin includes various filters and actions for developers to customize its behavior:

* `rsdc_post_type` - Filter the post type before removing comment support
* `rsdc_comments_status` - Filter the comments status
* `rsdc_hide_existing_comments` - Filter the hidden comments array
* `rsdc_hide_ui_styles` - Filter the CSS used to hide comment UI elements
* `rsdc_block_editor_settings` - Filter the block editor settings
* `rsdc_allowed_blocks` - Filter the allowed Gutenberg blocks

## Installation

1. Upload the plugin files to the `/wp-content/plugins/really-simple-disable-comments` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. That's it! No configuration needed.

## Frequently Asked Questions

### Does this plugin remove existing comments?

No, this plugin only hides existing comments and prevents new comments. It does not delete any data from your database.

### Will this plugin disable comments on specific post types only?

No, this plugin is designed to completely disable comments across all post types for simplicity.

### Does this affect my site's performance?

No, the plugin is very lightweight and only adds the necessary hooks to disable comment functionality.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and release notes.

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Author

[NEXTFLY® Web Design](https://www.nextflywebdesign.com/)
