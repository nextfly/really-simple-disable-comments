=== Really Simple Disable Comments ===
Contributors: nextfly
Tags: comments, disable comments, disable trackbacks, disable pingbacks
Requires at least: 5.0
Tested up to: 6.7.1
Stable tag: 0.1.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.

== Description ==

Really Simple Disable Comments is a lightweight plugin that completely disables WordPress comments functionality with a single activation. No configuration needed!

= Features =

* Disables comments on all post types
* Removes comment-related UI elements
* Disables trackbacks and pingbacks
* Removes comment-related admin menu items and dashboard widgets
* Disables all comment-related Gutenberg blocks
* Clean and efficient code with no settings required

= What Gets Disabled? =

* Comment forms and displays
* Admin menu items and dashboard widgets
* Comment-related Gutenberg blocks
* Trackbacks and pingbacks
* Comment-related UI elements in themes

= Developer Friendly =

The plugin includes various filters and actions for developers to customize its behavior:

* `rsdc_post_type` - Filter the post type before removing comment support
* `rsdc_comments_status` - Filter the comments status
* `rsdc_hide_existing_comments` - Filter the hidden comments array
* `rsdc_hide_ui_styles` - Filter the CSS used to hide comment UI elements
* `rsdc_block_editor_settings` - Filter the block editor settings
* `rsdc_allowed_blocks` - Filter the allowed Gutenberg blocks

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/really-simple-disable-comments` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. That's it! No configuration needed.

== Frequently Asked Questions ==

= Does this plugin remove existing comments? =

No, this plugin only hides existing comments and prevents new comments. It does not delete any data from your database.

= Will this plugin disable comments on specific post types only? =

No, this plugin is designed to completely disable comments across all post types for simplicity.

= Does this affect my site's performance? =

No, the plugin is very lightweight and only adds the necessary hooks to disable comment functionality.

== Changelog ==

= 0.1.0 =
* Initial release

== Upgrade Notice ==

= 0.1.0 =
Initial release of Really Simple Disable Comments 