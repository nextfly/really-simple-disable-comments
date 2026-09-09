<?php
/**
 * Plugin Name: Really Simple Disable Comments
 * Plugin URI: https://github.com/nextfly/really-simple-disable-comments
 * Description: Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.
 * Version: 0.5.0
 * Author: NEXTFLY® Web Design
 * Author URI: https://www.nextflywebdesign.com/
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * Text Domain: really-simple-disable-comments
 *
 * @category Core
 * @package  Nextfly\ReallySimpleDisableComments
 * @license  GPL v2 or later
 * @link     https://www.nextflywebdesign.com/
 *
 *
 * This plugin completely disables WordPress comments functionality including:
 * - Comment forms and displays
 * - Comment block output on block themes
 * - Comment feeds and their autodiscovery links
 * - Admin menu items and dashboard widgets
 * - Comment-related Gutenberg blocks
 * - Trackbacks and pingbacks
 */

defined('ABSPATH') || exit;

// Define the plugin version.
if (!defined('RSDC_VERSION')) {
    define('RSDC_VERSION', '0.5.0');
}

/**
 * Main plugin class that handles disabling WordPress comments functionality
 *
 * Uses singleton pattern to ensure only one instance runs during request lifecycle
 *
 * @category Core
 * @package  Nextfly\ReallySimpleDisableComments
 * @license  GPL v2 or later
 * @since    0.1.0
 * @link     https://www.nextflywebdesign.com/
 */
class ReallySimpleDisableComments
{
    /**
     * Singleton instance.
     *
     * @var self|null
     */
    private static $instance = null;

    /**
     * Private constructor to prevent direct creation.
     *
     * @since 0.1.0
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Get the singleton instance.
     *
     * @since  0.1.0
     * @return self
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize all hooks.
     *
     * @since 0.1.0
     * @return void
     */
    private function init_hooks()
    {
        // Disable comment support.
        add_action('init', array( $this, 'disable_comments_post_types' ));

        // Block direct comment submission and REST/XML-RPC comment endpoints.
        add_action('pre_comment_on_post', array( $this, 'disable_comments_block_submission' ));
        add_action('rest_api_init', array( $this, 'disable_comments_rest_post_fields' ));
        add_filter('rest_endpoints', array( $this, 'disable_comments_rest_endpoints' ));
        add_filter('rest_request_before_callbacks', array( $this, 'disable_comments_rest_gate' ), 10, 3);
        add_filter('xmlrpc_methods', array( $this, 'disable_comments_xmlrpc_pingback' ));
        add_filter('wp_headers', array( $this, 'disable_comments_remove_pingback_header' ));

        // Comment feeds.
        add_filter('feed_links_show_comments_feed', array( $this, 'disable_comments_feed_links' ));
        add_filter('feed_links_extra_show_post_comments_feed', array( $this, 'disable_comments_feed_links' ));
        add_action('template_redirect', array( $this, 'disable_comments_block_feed' ), 1);

        // Frontend filters.
        add_filter('comments_open', array( $this, 'disable_comments_status' ), 20, 2);
        add_filter('pings_open', array( $this, 'disable_comments_status' ), 20, 2);
        add_filter('comments_array', array( $this, 'disable_comments_hide_existing' ), 10, 2);

        // Admin-related actions.
        add_action('admin_menu', array( $this, 'disable_comments_admin_menu' ));
        add_action('admin_init', array( $this, 'disable_comments_admin_redirect' ));
        add_action('admin_init', array( $this, 'disable_comments_dashboard' ));
        add_action('add_meta_boxes', array( $this, 'disable_comments_metaboxes' ), 100);
        add_action('wp_before_admin_bar_render', array( $this, 'disable_comments_admin_bar' ));
        add_action('admin_enqueue_scripts', array( $this, 'disable_comments_admin_assets' ));
        add_filter('the_comments', array( $this, 'disable_dashboard_recent_comments' ), 10, 2);

        // Frontend UI.
        add_action('wp_enqueue_scripts', array( $this, 'disable_comments_hide_ui' ));

        // Disable Gutenberg block comments.
        add_action('init', array( $this, 'disable_block_comments' ));
        add_filter('register_block_type_args', array( $this, 'disable_comment_block_inserter' ), 10, 2);
        add_filter('render_block', array( $this, 'disable_comments_render_block' ), 10, 2);
    }

    /**
     * Get the list of comment-related block types.
     *
     * Includes the legacy `core/post-comments` block, which WordPress still
     * registers as a deprecated alias, so pre-6.1 content is covered too.
     *
     * The list is filtered once and then cached for the rest of the request,
     * because `disable_comments_render_block()` consults it for every block on
     * every page. Register `rsdc_comment_block_types` early, on `plugins_loaded`
     * or on `init` before priority 10, so it is in place for the first lookup.
     *
     * @return array
     * @since   0.3.0
     * @version 0.5.0
     * @filter  rsdc_comment_block_types Filters the comment-related block types.
     */
    private function get_comment_block_types()
    {
        static $block_types = null;

        if (null !== $block_types) {
            return $block_types;
        }

        $defaults = array(
            'core/comments',
            'core/comments-query-loop',
            'core/comments-title',
            'core/post-comments',
            'core/post-comments-form',
            'core/post-comments-link',
            'core/post-comments-count',
            'core/post-comment',
            'core/comment-author-name',
            'core/comment-author-avatar',
            'core/comment-content',
            'core/comment-date',
            'core/comment-edit-link',
            'core/comment-reply-link',
            'core/comment-template',
            'core/comments-pagination',
            'core/comments-pagination-next',
            'core/comments-pagination-previous',
            'core/comments-pagination-numbers',
            'core/latest-comments',
        );

        $filtered = apply_filters('rsdc_comment_block_types', $defaults);

        $block_types = is_array($filtered) ? $filtered : $defaults;

        return $block_types;
    }

    /**
     * Disable support for comments and trackbacks in post types.
     *
     * @since  0.1.0
     * @filter rsdc_post_type Filters the post type before removing comment support.
     * @action rsdc_after_disable_comments_post_types Fires after comment support is removed.
     * @return void
     */
    public function disable_comments_post_types()
    {
        foreach (get_post_types() as $post_type) {
            $post_type = apply_filters('rsdc_post_type', $post_type);
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
        do_action('rsdc_after_disable_comments_post_types');
    }

    /**
     * Register per-post-type REST response filters on rest_api_init.
     *
     * Loops over every post type exposed in the REST API and attaches
     * `disable_comments_rest_post_response` so that comment/ping status fields
     * and the replies link are normalized for all of them.
     *
     * @since  0.4.0
     * @action rest_api_init
     * @return void
     */
    public function disable_comments_rest_post_fields()
    {
        foreach (get_post_types(array( 'show_in_rest' => true )) as $post_type) {
            add_filter(
                "rest_prepare_{$post_type}",
                array( $this, 'disable_comments_rest_post_response' ),
                10,
                3
            );
        }
    }

    /**
     * Normalize comment/ping status fields and remove the replies link in REST responses.
     *
     * Sets `comment_status` and `ping_status` to `"closed"` and removes the
     * `replies` HAL link so post objects do not advertise comment availability
     * even though comments are blocked everywhere else.
     *
     * @param  WP_REST_Response $response The REST response object.
     * @param  WP_Post          $post     The post object.
     * @param  WP_REST_Request  $request  The REST request.
     * @return WP_REST_Response
     * @since  0.4.0
     * @filter rest_prepare_{$post_type}
     * @filter rsdc_rest_post_response Allows developers to modify the response after normalization.
     */
    public function disable_comments_rest_post_response($response, $post, $request)
    {
        $data = $response->get_data();

        if (isset($data['comment_status'])) {
            $data['comment_status'] = 'closed';
        }

        if (isset($data['ping_status'])) {
            $data['ping_status'] = 'closed';
        }

        $response->set_data($data);
        $response->remove_link('replies');

        return apply_filters('rsdc_rest_post_response', $response, $post, $request);
    }

    /**
     * Block direct comment submission via wp-comments-post.php.
     *
     * Hooked to `pre_comment_on_post`, which fires inside
     * `wp_handle_comment_submission()` before any comment is saved. This
     * provides defense-in-depth alongside the `comments_open` filter.
     *
     * @since  0.4.0
     * @action pre_comment_on_post
     * @filter rsdc_block_comment_submission Allows opting out of the block.
     * @return void
     */
    public function disable_comments_block_submission()
    {
        if (! apply_filters('rsdc_block_comment_submission', true)) {
            return;
        }

        wp_die(
            esc_html__('Comments are disabled on this site.', 'really-simple-disable-comments'),
            esc_html__('Forbidden', 'really-simple-disable-comments'),
            array( 'response' => 403 )
        );
    }

    /**
     * Remove comment-related REST API endpoints.
     *
     * WordPress 7.1 serves editorial Notes through the same controller as
     * public comments, so the routes are left registered by default and
     * policed by `disable_comments_rest_gate()` instead. When editorial Notes
     * are switched off via `rsdc_allow_editorial_notes`, the routes are
     * unregistered outright, as they were before 0.5.0.
     *
     * @param  array $endpoints Registered REST API endpoints.
     * @return array
     * @since  0.4.0
     * @version 0.5.0
     * @filter rest_endpoints
     * @filter rsdc_rest_endpoints Allows developers to modify the endpoint list after removal.
     */
    public function disable_comments_rest_endpoints($endpoints)
    {
        if (! $this->allow_editorial_notes()) {
            unset($endpoints['/wp/v2/comments']);

            if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
                unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
            }
        }

        return apply_filters('rsdc_rest_endpoints', $endpoints);
    }

    /**
     * Allow only editorial Note traffic through the comments REST route.
     *
     * WordPress 7.1 added editorial Notes, stored as comments with
     * `comment_type` of `note` and served through the same REST controller as
     * public comments. This gate keeps the route available for Notes while
     * returning the same `rest_no_route` 404 that earlier versions returned by
     * unregistering the route entirely.
     *
     * Requests are denied by default: the collection route must ask for
     * `type=note` explicitly (core defaults that parameter to `comment`), and
     * single-item requests must resolve to a comment whose type is `note`.
     *
     * @param  WP_REST_Response|WP_HTTP_Response|WP_Error|mixed $response Current response.
     * @param  array                                            $handler  Matched route handler.
     * @param  WP_REST_Request                                  $request  Current request.
     * @return WP_REST_Response|WP_HTTP_Response|WP_Error|mixed
     * @since  0.5.0
     * @filter rest_request_before_callbacks
     */
    public function disable_comments_rest_gate($response, $handler, $request)
    {
        if (is_wp_error($response)) {
            return $response;
        }

        if (! $this->allow_editorial_notes()) {
            return $response;
        }

        if (! $request instanceof WP_REST_Request) {
            return $response;
        }

        if (! preg_match('#^/wp/v2/comments(?:/(\d+))?$#i', $request->get_route(), $matches)) {
            return $response;
        }

        $comment_id = isset($matches[1]) ? (int) $matches[1] : 0;

        if ($this->is_editorial_note_request($request, $comment_id)) {
            return $response;
        }

        return new WP_Error(
            'rest_no_route',
            __('No route was found matching the URL and request method.', 'really-simple-disable-comments'),
            array( 'status' => 404 )
        );
    }

    /**
     * Determine whether a comments REST request targets an editorial Note.
     *
     * @param  WP_REST_Request $request    Current request.
     * @param  int             $comment_id Comment ID from the route, or 0 for the collection route.
     * @return bool
     * @since  0.5.0
     */
    private function is_editorial_note_request($request, $comment_id)
    {
        if ($comment_id > 0) {
            $comment = get_comment($comment_id);

            return ($comment instanceof WP_Comment) && 'note' === $comment->comment_type;
        }

        return 'note' === $request->get_param('type');
    }

    /**
     * Whether editorial Notes are allowed through the comments REST route.
     *
     * @return bool
     * @since  0.5.0
     * @filter rsdc_allow_editorial_notes Set to false to restore pre-0.5.0 behavior.
     */
    private function allow_editorial_notes()
    {
        return (bool) apply_filters('rsdc_allow_editorial_notes', true);
    }

    /**
     * Remove XML-RPC pingback methods.
     *
     * Unsets `pingback.ping` and `pingback.extensions.getPingbacks` while
     * leaving all other XML-RPC methods intact.
     *
     * @param  array $methods Registered XML-RPC methods.
     * @return array
     * @since  0.4.0
     * @filter xmlrpc_methods
     * @filter rsdc_xmlrpc_methods Allows developers to adjust the method list after removal.
     */
    public function disable_comments_xmlrpc_pingback($methods)
    {
        unset($methods['pingback.ping'], $methods['pingback.extensions.getPingbacks']);

        return apply_filters('rsdc_xmlrpc_methods', $methods);
    }

    /**
     * Remove the X-Pingback header from responses.
     *
     * Strips the autodiscovery hint so clients cannot detect the XML-RPC
     * endpoint via the response header.
     *
     * @param  array $headers HTTP response headers.
     * @return array
     * @since  0.4.0
     * @filter wp_headers
     */
    public function disable_comments_remove_pingback_header($headers)
    {
        if (isset($headers['X-Pingback'])) {
            unset($headers['X-Pingback']);
        }

        return $headers;
    }

    /**
     * Hide comment feed autodiscovery links.
     *
     * Covers both the site-wide comments feed link emitted by `feed_links()`
     * and the per-post comments feed link emitted by `feed_links_extra()`.
     * Core defaults the second filter to the result of the first, but a theme
     * or plugin can set them independently, so both are hooked.
     *
     * @param  bool $show Whether core intends to show the link.
     * @return bool
     * @since  0.5.0
     * @filter feed_links_show_comments_feed
     * @filter feed_links_extra_show_post_comments_feed
     */
    public function disable_comments_feed_links($show)
    {
        if (! $this->disable_comment_feeds()) {
            return $show;
        }

        return false;
    }

    /**
     * Return 404 for comment feeds.
     *
     * Removing the autodiscovery links is not enough on its own: the feed URLs
     * are guessable and may already be indexed, and the comment feed template
     * queries comments directly rather than honoring `comments_open`.
     *
     * @return void
     * @since  0.5.0
     * @action template_redirect
     */
    public function disable_comments_block_feed()
    {
        if (! $this->disable_comment_feeds()) {
            return;
        }

        if (! is_comment_feed()) {
            return;
        }

        global $wp_query;

        if ($wp_query instanceof WP_Query) {
            $wp_query->set_404();
        }

        status_header(404);
        nocache_headers();
        exit;
    }

    /**
     * Whether comment feeds should be disabled.
     *
     * @return bool
     * @since  0.5.0
     * @filter rsdc_disable_comment_feeds Set to false to leave comment feeds alone.
     */
    private function disable_comment_feeds()
    {
        return (bool) apply_filters('rsdc_disable_comment_feeds', true);
    }

    /**
     * Close comments on the front-end
     *
     * @return bool
     * @since  0.1.0
     */
    public function disable_comments_status()
    {
        $status = false;
        return apply_filters('rsdc_comments_status', $status);
    }

    /**
     * Hide existing comments from display
     *
     * @param  array $comments Array of comment objects to filter.
     * @return array Empty array to remove all comments
     * @since  0.1.0
     * @filter rsdc_hide_existing_comments
     */
    public function disable_comments_hide_existing($comments)
    {
        $comments = array();
        return apply_filters('rsdc_hide_existing_comments', $comments);
    }

    /**
     * Remove comments page from admin menu
     *
     * @return void
     * @since  0.1.0
     */
    public function disable_comments_admin_menu()
    {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Redirect any user trying to access comments page
     *
     * @return void
     * @since  0.1.0
     */
    public function disable_comments_admin_redirect()
    {
        global $pagenow;
        if ($pagenow === 'edit-comments.php') {
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    /**
     * Remove comments dashboard widgets
     *
     * @return void
     * @since  0.1.0
     */
    public function disable_comments_dashboard()
    {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }

    /**
     * Remove comment-related metaboxes from classic edit screens.
     *
     * @param  string $post_type Current post type.
     * @return void
     * @since   0.3.0
     */
    public function disable_comments_metaboxes($post_type)
    {
        $metaboxes = array(
            'commentsdiv',
            'commentstatusdiv',
            'trackbacksdiv',
        );

        $contexts = array(
            'normal',
            'advanced',
            'side',
        );

        foreach ($metaboxes as $metabox) {
            foreach ($contexts as $context) {
                remove_meta_box($metabox, $post_type, $context);
            }
        }
    }

    /**
     * Remove from admin bar
     *
     * @return void
     * @since  0.1.0
     */
    public function disable_comments_admin_bar()
    {
        global $wp_admin_bar;

        if (! $wp_admin_bar) {
            return;
        }

        $wp_admin_bar->remove_menu('comments');
    }

    /**
     * Enqueue admin assets to suppress comment-related UI on the dashboard.
     *
     * Registers inline CSS and a footer script on the dashboard to hide and
     * remove comment count DOM nodes from the At a Glance widget.
     *
     * @param  string $hook_suffix The current admin page hook suffix.
     * @return void
     * @since  0.3.0
     */
    public function disable_comments_admin_assets($hook_suffix)
    {
        if ('index.php' !== $hook_suffix) {
            return;
        }

        $css = '/* Hide comment counts from At a Glance widget */
        #dashboard_right_now .comment-count,
        #dashboard_right_now .comment-mod-count,
        #dashboard_right_now a[href*="edit-comments.php"] {
            display: none !important;
        }';

        wp_register_style('rsdc-admin', false, array(), RSDC_VERSION);
        wp_add_inline_style('rsdc-admin', $css);
        wp_enqueue_style('rsdc-admin');

        $script = '(function () {
            var widget = document.getElementById("dashboard_right_now");
            if (!widget) { return; }
            var commentLinks = widget.querySelectorAll(".comment-count, .comment-mod-count, a[href*=\"edit-comments.php\"]");
            commentLinks.forEach(function (element) {
                var listItem = element.closest("li");
                if (listItem && widget.contains(listItem)) {
                    listItem.remove();
                    return;
                }
                element.remove();
            });
        }());';

        wp_register_script('rsdc-admin-dom', false, array(), RSDC_VERSION, array('in_footer' => true));
        wp_add_inline_script('rsdc-admin-dom', $script);
        wp_enqueue_script('rsdc-admin-dom');
    }

    /**
     * Disable recent comments from dashboard Activity widget
     *
     * @param  array            $comments Array of comment objects.
     * @param  WP_Comment_Query $query Comment query object.
     * @return array Empty array if dashboard context, original comments otherwise
     * @since  0.2.0
     */
    public function disable_dashboard_recent_comments($comments, $query)
    {
        // First check if we're on the dashboard screen.
        if (is_admin() && function_exists('get_current_screen')) {
            $screen = get_current_screen();
            if ($screen && $screen->id === 'dashboard') {
                return array();
            }
        }
        return $comments;
    }

    /**
     * Hide comments UI elements
     *
     * @return void
     * @since  0.1.0
     */
    public function disable_comments_hide_ui()
    {
        // Register the base style handle.
        wp_register_style(
            'really-simple-disable-comments',
            false, // No source file needed since we're only using inline styles.
            array(),
            RSDC_VERSION
        );

        // Add our inline styles.
        $styles = apply_filters(
            'rsdc_hide_ui_styles',
            '
            .post-comments, 
            .entry-comments, 
            .comments-link, 
            .comments-area,
            .wp-block-comments,
            .wp-block-latest-comments,
            .wp-block-post-comments,
            .wp-block-comments-query-loop,
            .wp-block-post-comments-form { 
                display: none !important; 
            }
        '
        );

        wp_add_inline_style('really-simple-disable-comments', $styles);
        wp_enqueue_style('really-simple-disable-comments');
    }

    /**
     * Return empty output for comment-related blocks.
     *
     * Block themes never call `comments_template()`, so the `comments_array`
     * filter never runs for them, and `comments_open()` returning false does
     * not stop WordPress rendering pre-existing comments. Without this, the
     * comment markup is merely hidden with CSS while commenter names, comment
     * text and avatar URLs still ship in the page source.
     *
     * Reuses `get_comment_block_types()` so this stays in sync with the
     * inserter filter.
     *
     * @param  string $block_content Rendered block HTML.
     * @param  array  $block         Parsed block.
     * @return string
     * @since  0.5.0
     * @filter render_block
     */
    public function disable_comments_render_block($block_content, $block)
    {
        if (! isset($block['blockName'])) {
            return $block_content;
        }

        if (! in_array($block['blockName'], $this->get_comment_block_types(), true)) {
            return $block_content;
        }

        if (! $this->disable_comment_block_output()) {
            return $block_content;
        }

        return '';
    }

    /**
     * Whether comment block output should be suppressed.
     *
     * @return bool
     * @since  0.5.0
     * @filter rsdc_disable_comment_block_output Set to false to let comment blocks render.
     */
    private function disable_comment_block_output()
    {
        return (bool) apply_filters('rsdc_disable_comment_block_output', true);
    }

    /**
     * Hide comment-related blocks from the inserter at registration time.
     *
     * @param  array  $args Block type registration arguments.
     * @param  string $block_type Block type name.
     * @return array
     * @since   0.3.0
     */
    public function disable_comment_block_inserter($args, $block_type)
    {
        if (! in_array($block_type, $this->get_comment_block_types(), true)) {
            return $args;
        }

        if (! isset($args['supports']) || ! is_array($args['supports'])) {
            $args['supports'] = array();
        }

        $args['supports']['inserter'] = false;

        return $args;
    }

    /**
     * Disable Gutenberg block comments
     *
     * @return void
     * @since  0.1.0
     * @version 0.3.0
     */
    public function disable_block_comments()
    {
        add_filter(
            'block_editor_settings_all',
            function ($settings) {
                $settings['__experimentalDisablePostFormats'] = true;
                $settings['enableComments']                   = false;
                $settings['commentStatus']                    = false;
                return apply_filters('rsdc_block_editor_settings', $settings);
            }
        );

        // Remove comment blocks from inserter.
        add_filter(
            'allowed_block_types_all',
            function ($allowed_blocks) {
                if (false === $allowed_blocks) {
                    return $allowed_blocks;
                }

                if (true === $allowed_blocks) {
                    if (class_exists('WP_Block_Type_Registry')) {
                        $allowed_blocks = array_keys(\WP_Block_Type_Registry::get_instance()->get_all_registered());
                    } else {
                        return $allowed_blocks; // Fallback for very old setups.
                    }
                }

                if (! is_array($allowed_blocks)) {
                    return $allowed_blocks;
                }

                foreach ($this->get_comment_block_types() as $block) {
                    $key = array_search($block, $allowed_blocks, true);
                    if (false !== $key) {
                        unset($allowed_blocks[ $key ]);
                    }
                }

                return apply_filters('rsdc_allowed_blocks', $allowed_blocks);
            }
        );
    }
}

/**
 * Initialize the plugin
 *
 * @return void
 * @since  0.1.0
 */
function Rsdc_Initialize_Disable_Comments_plugin()
{
    ReallySimpleDisableComments::get_instance();
}
add_action('plugins_loaded', 'Rsdc_Initialize_Disable_Comments_plugin');
