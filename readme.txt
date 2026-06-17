=== Really Simple Disable Comments ===
Contributors: nextfly
Tags: comments, disable comments, disable trackbacks, disable pingbacks
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.

== Description ==

Really Simple Disable Comments is a lightweight plugin that completely disables WordPress comments functionality with a single activation. No configuration needed!

= Features =

* Disables comments on all post types
* Removes comment-related UI elements
* Disables trackbacks and pingbacks
* Blocks direct comment submission to wp-comments-post.php (403 response)
* Removes comment REST API endpoints (/wp/v2/comments)
* Disables XML-RPC pingback methods
* Strips X-Pingback response header
* Removes comment-related admin menu items and dashboard widgets
* Hides comment counts from dashboard "At a Glance" widget
* Hides "Recent Comments" section from dashboard Activity widget
* Disables all comment-related Gutenberg blocks
* Clean and efficient code with no settings required

= What Gets Disabled? =

* Comment forms and displays
* Direct comment submission via wp-comments-post.php
* Comment REST API endpoints (/wp/v2/comments)
* XML-RPC pingback methods
* X-Pingback response header
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
* `rsdc_block_comment_submission` - Control whether direct comment submission is blocked (return false to allow)
* `rsdc_rest_endpoints` - Filter the REST endpoints array after comment endpoints are removed
* `rsdc_xmlrpc_methods` - Filter the XML-RPC methods array after pingback methods are removed

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

= 0.4.0 =
* Block direct comment submission to wp-comments-post.php with a 403 response
* Remove /wp/v2/comments and /wp/v2/comments/<id> REST API endpoints
* Remove XML-RPC pingback.ping and pingback.extensions.getPingbacks methods
* Strip X-Pingback response header to remove pingback autodiscovery
* Added developer filters: rsdc_block_comment_submission, rsdc_rest_endpoints, rsdc_xmlrpc_methods

= 0.3.0 =
* Added WordPress 7.0 compatibility updates for comment-related block inserter removal
* Hardened dashboard comment count cleanup for the refreshed WordPress 7.0 admin UI
* Removed classic editor comment-related metaboxes so Screen Options cannot restore them on edit screens

= 0.2.1 =
* Changed `wp_redirect()` to `wp_safe_redirect()` for better security when redirecting from comments admin page

= 0.2.0 =
* Added hiding of comment counts from "At a Glance" dashboard widget
* Added hiding of "Recent Comments" section from Activity dashboard widget

= 0.1.0 =
* Initial release

== Upgrade Notice ==

= 0.4.0 =
Hardens comment blocking: direct POST submissions, REST API endpoints, and XML-RPC pingbacks are now all blocked at the server level.

= 0.3.0 =
* WordPress 7.0 compatibility update with improved comment block and metabox removal and enhanced dashboard cleanup.

= 0.2.1 =
* Minor security enhancement. No user action required.

= 0.2.0 =
* Enhanced dashboard functionality - now hides comment counts and recent comments from dashboard widgets.

= 0.1.0 =
* Initial release of Really Simple Disable Comments 