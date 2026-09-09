=== Really Simple Disable Comments ===
Contributors: nextfly
Tags: comments, disable comments, disable trackbacks, disable pingbacks
Requires at least: 5.8
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.5.0
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
* Blocks the comment REST API endpoints (/wp/v2/comments), while keeping WordPress 7.1 editorial Notes working
* Stops comment blocks rendering at all on block themes, so comment text never reaches the page source
* Disables comment feeds and removes their autodiscovery links
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
* Comment REST API reads and writes (/wp/v2/comments), except WordPress 7.1 editorial Notes
* Comment block output on block themes (markup is removed, not just hidden)
* Comment feeds (/comments/feed/ and per-post comment feeds) and their <head> links
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
* `rsdc_rest_endpoints` - Filter the REST endpoints array during REST bootstrap (comment endpoints are only unset when `rsdc_allow_editorial_notes` returns false)
* `rsdc_xmlrpc_methods` - Filter the XML-RPC methods array after pingback methods are removed
* `rsdc_rest_post_response` - Filter the normalized WP_REST_Response for post objects
* `rsdc_allow_editorial_notes` - Return false to unregister the comment REST routes entirely, as in 0.4.0 (also disables WordPress 7.1 editorial Notes)
* `rsdc_disable_comment_feeds` - Return false to leave comment feeds and their autodiscovery links alone
* `rsdc_disable_comment_block_output` - Return false to let comment-related blocks render their markup again
* `rsdc_comment_block_types` - Filter the list of comment-related block types the plugin hides and suppresses (register it on `plugins_loaded` so it applies before the first lookup)

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

= Does this break WordPress 7.1 editorial Notes? =

No. Editorial Notes are private annotations collaborators leave on a post, and WordPress serves them over the same REST route as public comments. The plugin keeps that route available for Notes only, so Notes keep working while public comment data stays blocked. Use the `rsdc_allow_editorial_notes` filter to turn Notes off as well. The route itself stays registered, so it is still listed at `/wp-json/wp/v2` and still answers `OPTIONS` with the comment schema; every request that is not Note traffic gets the same `404 rest_no_route` as before.

= What happens to my comment feeds? =

`/comments/feed/` and per-post comment feeds return 404, and their autodiscovery links are removed from the page head. Anyone still subscribed to a comment feed will stop receiving it. Your main content feed at `/feed/` is untouched. Use the `rsdc_disable_comment_feeds` filter to keep comment feeds enabled.

= My theme's comment markup used to be in the page source. Where did it go? =

On block themes the plugin now stops comment blocks producing output at all, rather than only hiding them with CSS. Commenter names, comment text and avatar URLs no longer ship with the page, so scrapers and crawlers cannot read them. Use the `rsdc_disable_comment_block_output` filter to restore the old behavior.

== Changelog ==

= 0.5.0 =
* Enqueue the front-end hide styles on wp_enqueue_scripts so they print inside <head> instead of after the page content
* Stop comment-related blocks rendering at all, so commenter names, comment text and avatar URLs no longer appear in the page source on block themes
* Comment feeds (/comments/feed/ and per-post comment feeds) now return 404, and their autodiscovery links are removed from <head>
* Keep /wp/v2/comments registered so WordPress 7.1 editorial Notes keep working, while all other comment REST reads and writes still return 404 rest_no_route
* Added developer filters: rsdc_allow_editorial_notes, rsdc_disable_comment_feeds, rsdc_disable_comment_block_output, rsdc_comment_block_types

= 0.4.0 =
* Block direct comment submission to wp-comments-post.php with a 403 response
* Remove /wp/v2/comments and /wp/v2/comments/<id> REST API endpoints
* Normalize comment_status and ping_status to "closed" in post REST responses
* Remove the replies HAL link from post REST responses
* Remove XML-RPC pingback.ping and pingback.extensions.getPingbacks methods
* Strip X-Pingback response header to remove pingback autodiscovery
* Added developer filters: rsdc_block_comment_submission, rsdc_rest_endpoints, rsdc_xmlrpc_methods, rsdc_rest_post_response

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

= 0.5.0 =
Comment feeds now return 404 and comment markup is no longer output on block themes. WordPress 7.1 editorial Notes keep working. Three new filters let you switch each behavior off.

= 0.4.0 =
Hardens comment blocking: direct POST submissions, REST API endpoints (including post response fields), and XML-RPC pingbacks are now all blocked at the server level.

= 0.3.0 =
* WordPress 7.0 compatibility update with improved comment block and metabox removal and enhanced dashboard cleanup.

= 0.2.1 =
* Minor security enhancement. No user action required.

= 0.2.0 =
* Enhanced dashboard functionality - now hides comment counts and recent comments from dashboard widgets.

= 0.1.0 =
* Initial release of Really Simple Disable Comments 