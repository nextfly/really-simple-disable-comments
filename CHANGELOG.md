# Changelog

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
