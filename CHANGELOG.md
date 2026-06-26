# Changelog

## 0.4.0
* Block direct comment submission to `wp-comments-post.php` with a 403 response via the `pre_comment_on_post` action
* Remove `/wp/v2/comments` and `/wp/v2/comments/<id>` REST API endpoints
* Normalize `comment_status` and `ping_status` to `"closed"` in post REST API responses
* Remove the `replies` HAL link from post REST API responses
* Remove XML-RPC `pingback.ping` and `pingback.extensions.getPingbacks` methods
* Strip the `X-Pingback` response header to remove pingback autodiscovery
* Added developer filters: `rsdc_block_comment_submission`, `rsdc_rest_endpoints`, `rsdc_xmlrpc_methods`, `rsdc_rest_post_response`

## 0.3.0
* Added WordPress 7.0 compatibility updates for comment-related block inserter removal
* Hardened dashboard comment count cleanup for the refreshed WordPress 7.0 admin UI
* Raised the minimum supported WordPress version to 5.8 to match the current block editor hooks in use
* Removed classic editor comment-related metaboxes so Screen Options cannot restore them on edit screens

## 0.2.1
* Changed `wp_redirect()` to `wp_safe_redirect()` for better security when redirecting from comments admin page

## 0.2.0

* Added hiding of comment counts from "At a Glance" dashboard widget
* Added hiding of "Recent Comments" section from Activity dashboard widget

## 0.1.0

* Initial release
