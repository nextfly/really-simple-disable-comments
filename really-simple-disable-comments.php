<?php
/**
 * Plugin Name: Really Simple Disable Comments
 * Description: Effortlessly disable all comments and trackback functionality across your entire WordPress site by activating this plugin.
 * Version: 0.1.0
 * Author: NEXTFLY® Web Design
 * Author URI: https://www.nextflywebdesign.com/
 * License: GPL v2 or later
 * Text Domain: really-simple-disable-comments
 * 
 * This plugin completely disables WordPress comments functionality including:
 * - Comment forms and displays
 * - Admin menu items and dashboard widgets
 * - Comment-related Gutenberg blocks
 * - Trackbacks and pingbacks
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class that handles disabling WordPress comments functionality
 * 
 * Uses singleton pattern to ensure only one instance runs during request lifecycle
 * 
 * @since 0.1.0
 */
class ReallySimpleDisableComments {
    // Singleton instance
    private static $instance = null;
    
    // Private constructor to prevent direct creation
    private function __construct() {
        $this->init_hooks();
    }
    
    // Singleton pattern implementation
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Initialize all hooks
    private function init_hooks() {
        // Disable comment support
        add_action('init', [$this, 'disable_comments_post_types']);
        
        // Frontend filters
        add_filter('comments_open', [$this, 'disable_comments_status'], 20, 2);
        add_filter('pings_open', [$this, 'disable_comments_status'], 20, 2);
        add_filter('trackback_status', [$this, 'disable_comments_status'], 20, 2);
        add_filter('comments_array', [$this, 'disable_comments_hide_existing'], 10, 2);
        
        // Admin-related actions
        add_action('admin_menu', [$this, 'disable_comments_admin_menu']);
        add_action('admin_init', [$this, 'disable_comments_admin_redirect']);
        add_action('admin_init', [$this, 'disable_comments_dashboard']);
        add_action('wp_before_admin_bar_render', [$this, 'disable_comments_admin_bar']);
        
        // Frontend UI
        add_action('wp_head', [$this, 'disable_comments_hide_ui']);
        
        // Disable Gutenberg block comments
        add_action('init', [$this, 'disable_block_comments']);
    }
    
    /**
     * Disable support for comments and trackbacks in post types
     * 
     * @since 0.1.0
     * @filter rsdc_post_type Filters the post type before removing comment support
     * @action rsdc_after_disable_comments_post_types Fires after comment support is removed
     * @return void
     */
    public function disable_comments_post_types() {
        foreach (get_post_types() as $post_type) {
            $post_type = apply_filters('rsdc_post_type', $post_type);
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
            remove_post_type_support($post_type, 'pingbacks');
        }
        do_action('rsdc_after_disable_comments_post_types');
    }
    
    /**
     * Close comments on the front-end
     * @return bool
     * @since 0.1.0
     */
    public function disable_comments_status() {
        $status = false;
        return apply_filters('rsdc_comments_status', $status);
    }
    
    /**
     * Hide existing comments from display
     * 
     * @param array $comments Array of comment objects to filter
     * @return array Empty array to remove all comments
     * @since 0.1.0
     * @filter rsdc_hide_existing_comments
     */
    public function disable_comments_hide_existing($comments) {
        $comments = [];
        return apply_filters('rsdc_hide_existing_comments', $comments);
    }
    
    /**
     * Remove comments page from admin menu
     * @return void
     * @since 0.1.0
     */
    public function disable_comments_admin_menu() {
        remove_menu_page('edit-comments.php');
    }
    
    /**
     * Redirect any user trying to access comments page
     * @return void
     * @since 0.1.0
     */
    public function disable_comments_admin_redirect() {
        global $pagenow;
        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }
    }
    
    /**
     * Remove comments dashboard widgets
     * @return void
     * @since 0.1.0
     */
    public function disable_comments_dashboard() {
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
    }
    
    /**
     * Remove from admin bar
     * @return void
     * @since 0.1.0
     */
    public function disable_comments_admin_bar() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }
    
    /**
     * Hide comments UI elements
     * @return void
     * @since 0.1.0
     */
    public function disable_comments_hide_ui() {
        $styles = apply_filters('rsdc_hide_ui_styles', '
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
        ');
        
        do_action('rsdc_before_hide_ui');
        echo '<style>' . $styles . '</style>';
        do_action('rsdc_after_hide_ui');
    }
    
    /**
     * Disable Gutenberg block comments
     * @return void
     * @since 0.1.0
     */
    public function disable_block_comments() {
        add_filter('block_editor_settings_all', function($settings) {
            $settings['__experimentalDisablePostFormats'] = true;
            $settings['enableComments'] = false;
            $settings['commentStatus'] = false;
            return apply_filters('rsdc_block_editor_settings', $settings);
        });

        // Remove comment blocks from inserter
        add_filter('allowed_block_types_all', function($allowed_blocks) {
            if (!is_array($allowed_blocks)) {
                return $allowed_blocks;
            }
            
            $blocks_to_remove = [
                'core/comments',
                'core/comments-query-loop',
                'core/comments-title',
                'core/comment-author-name',
                'core/comment-content',
                'core/comment-date',
                'core/comment-edit-link',
                'core/comment-reply-link',
                'core/comment-template',
                'core/comments-pagination',
                'core/comments-pagination-next',
                'core/comments-pagination-previous',
                'core/comments-pagination-numbers',
                'core/post-comments-form',
                'core/latest-comments'
            ];
            
            foreach ($blocks_to_remove as $block) {
                $key = array_search($block, $allowed_blocks);
                if ($key !== false) {
                    unset($allowed_blocks[$key]);
                }
            }
            
            return apply_filters('rsdc_allowed_blocks', $allowed_blocks);
        });
    }
}

/**
 * Initialize the plugin
 * @return void
 * @since 0.1.0
 */
function initialize_disable_comments_plugin() {
    ReallySimpleDisableComments::get_instance();
}
add_action('plugins_loaded', 'initialize_disable_comments_plugin'); 