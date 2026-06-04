<?php

/**
 * Disable comments in WordPress back end 
 *
 * Removes all comment functionality from WordPress including:
 * - Comment forms and submission
 * - Admin menu items and metaboxes
 * - RSS feeds
 * - Widgets
 * - Scripts
 *
 * Also tarpits comment submission attempts by holding the connection
 * open for 30 seconds before returning an empty response, wasting
 * bot and spammer resources.
 *
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Tarpit comment submission attempts
 *
 * Intercepts POST requests to wp-comments-post.php as early as possible,
 * holds the connection open for 30 seconds, then exits with an empty 200.
 * This wastes bot/spammer time and resources without revealing the block.
 *
 * Requires max_execution_time >= 30 (or 0 for unlimited).
 * Run `php -i | grep max_execution_time` to verify.
 *
 * @since 1.1.0
 * @return void
 */
add_action( 'init', 'dbllc_tarpit_comment_submissions', 1 );

function dbllc_tarpit_comment_submissions() {
    if (
        isset( $_SERVER['REQUEST_METHOD'] ) &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset( $_SERVER['SCRIPT_NAME'] ) &&
        str_contains( $_SERVER['SCRIPT_NAME'], 'wp-comments-post.php' )
    ) {
        // Ensure PHP won't terminate the sleep early
        set_time_limit( 90 );

        // Hold the connection open for 30 seconds
        sleep( 30 );

        // Return empty 200 — indistinguishable from previous behavior
        http_response_code( 200 );
        exit;
    }
}


add_filter('rest_endpoints', function ($endpoints) {
    if (isset($endpoints['/wp/v2/comments'])) {
        unset($endpoints['/wp/v2/comments']);
    }
    if (isset($endpoints['/wp/v2/comments/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/comments/(?P<id>[\d]+)']);
    }
    return $endpoints;
});


/**
 * Disable comments feed in header
 */
add_filter( 'feed_links_show_comments_feed', '__return_false' );

/**
 * Remove comments from admin menu
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_menu', 'dbllc_remove_comments_menu' );

function dbllc_remove_comments_menu() {
    remove_menu_page( 'edit-comments.php' );
}

/**
 * Remove comments from admin bar
 *
 * @since 1.0.0
 * @param WP_Admin_Bar $wp_admin_bar Admin bar object
 * @return void
 */
add_action( 'admin_bar_menu', 'dbllc_remove_comments_admin_bar', 999 );

function dbllc_remove_comments_admin_bar( $wp_admin_bar ) {
    $wp_admin_bar->remove_node( 'comments' );
}

/**
 * Close comments on frontend globally
 */
add_filter( 'comments_open', '__return_false', 20, 2 );
add_filter( 'pings_open', '__return_false', 20, 2 );

/**
 * Remove comment support from all post types
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', 'dbllc_remove_comment_support' );

function dbllc_remove_comment_support() {
    $post_types = get_post_types();

    foreach ( $post_types as $post_type ) {
        if ( post_type_supports( $post_type, 'comments' ) ) {
            remove_post_type_support( $post_type, 'comments' );
            remove_post_type_support( $post_type, 'trackbacks' );
        }
    }
}

/**
 * Hide existing comments from display
 */
add_filter( 'comments_array', '__return_empty_array', 10, 2 );

/**
 * Remove comment-related queries from being executed
 */
add_filter( 'comment_feed_query', '__return_empty_array' );

/**
 * Remove the X-Pingback HTTP header
 *
 * @since 1.0.0
 * @param array $headers HTTP headers
 * @return array Modified headers without X-Pingback
 */
add_filter( 'wp_headers', 'dbllc_remove_pingback_header' );

function dbllc_remove_pingback_header( $headers ) {
    unset( $headers['X-Pingback'] );
    return $headers;
}

/**
 * Remove comment RSS feed links from head
 */
remove_action( 'wp_head', 'feed_links_extra', 3 );


/**
 * Remove comment metaboxes from post edit screens
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_init', 'dbllc_remove_comment_metaboxes' );

function dbllc_remove_comment_metaboxes() {
    remove_meta_box( 'commentsdiv', 'post', 'normal' );
    remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
    remove_meta_box( 'commentsdiv', 'page', 'normal' );
    remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
}

/**
 * Remove comments widget
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'widgets_init', 'dbllc_remove_comments_widget' );

function dbllc_remove_comments_widget() {
    unregister_widget( 'WP_Widget_Recent_Comments' );
}

/**
 * Redirect comment feed page requests to homepage
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', 'dbllc_redirect_comment_feeds' );

function dbllc_redirect_comment_feeds() {
    if ( is_comment_feed() ) {
        wp_safe_redirect( home_url(), 301 );
        exit;
    }
}

/**
 * Remove comment reply script
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'dbllc_remove_comment_reply_script' );

function dbllc_remove_comment_reply_script() {
    wp_dequeue_script( 'comment-reply' );
}

/**
 * Close comments on all existing posts and pages (one-time bulk update)
 *
 * Uncomment and run once to close comments on all existing content.
 * The option flag prevents this from running multiple times.
 *
 * @since 1.0.0
 * @return void
 */
/*
add_action( 'wp_loaded', 'dbllc_bulk_close_comments' );

function dbllc_bulk_close_comments() {
    // Only run this once after adding the function
    if ( get_option( 'dbllc_comments_disabled_bulk_update' ) ) {
        return;
    }

    global $wpdb;
    $wpdb->query( "UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed'" );
    update_option( 'dbllc_comments_disabled_bulk_update', true );
}
*/