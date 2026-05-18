<?php
/**
 * Plugin Name: Really Simple Disable Comments
 * Plugin URI: https://github.com/nextfly/really-simple-disable-comments
 * Description: Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.
 * Version: 0.3.0
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
 * - Admin menu items and dashboard widgets
 * - Comment-related Gutenberg blocks
 * - Trackbacks and pingbacks
 */

defined('ABSPATH') || exit;

// Define the plugin version.
if (!defined('RSDC_VERSION')) {
    define('RSDC_VERSION', '0.3.0');
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
        add_action('wp_head', array( $this, 'disable_comments_hide_ui' ));

        // Disable Gutenberg block comments.
        add_action('init', array( $this, 'disable_block_comments' ));
        add_filter('register_block_type_args', array( $this, 'disable_comment_block_inserter' ), 10, 2);
    }

    /**
     * Get the list of comment-related block types.
     *
     * @return array
     * @since   0.3.0
     */
    private function get_comment_block_types()
    {
        return array(
            'core/comments',
            'core/comments-query-loop',
            'core/comments-title',
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
