<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/**
 * Completely disable WordPress comments system and block endpoints
 */


// Disable comment feeds
add_filter('feed_links_show_comments_feed', '__return_false');


// Remove comments from admin bar
add_action('admin_bar_menu', function ($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}, 999);


// Remove comment support from all post types
add_action('init', function () {

    $post_types = get_post_types();

    foreach ($post_types as $post_type) {

        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }

    }

});


// Close comments on the frontend
add_filter('comments_open', '__return_false', 20, 2);
add_filter('pings_open', '__return_false', 20, 2);


// Hide existing comments
add_filter('comments_array', '__return_empty_array', 10, 2);


// Remove comment-related queries from being executed
add_filter('comment_feed_query', '__return_empty_array');

// Remove the X-Pingback HTTP header
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});


// Remove comment RSS feed links from head
remove_action('wp_head', 'feed_links_extra', 3);


// Block direct access to comment endpoints
add_action('init', function () {

    if (isset($_SERVER['REQUEST_URI']) &&

        (strpos($_SERVER['REQUEST_URI'], '/wp-comments-post.php') !== false ||
         strpos($_SERVER['REQUEST_URI'], '/xmlrpc.php') !== false)) {

        wp_die('Comments are disabled.', 'Comments Disabled', array('response' => 403));

    }

});


// Remove comment menu from admin
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});


// Remove comment metaboxes from post edit screens
add_action('admin_init', function () {
    remove_meta_box('commentsdiv', 'post', 'normal');
    remove_meta_box('commentstatusdiv', 'post', 'normal');
    remove_meta_box('commentsdiv', 'page', 'normal');
    remove_meta_box('commentstatusdiv', 'page', 'normal');
});


// Remove comments widget
add_action('widgets_init', function () {
    unregister_widget('WP_Widget_Recent_Comments');
});


// Redirect comment page requests
add_action('template_redirect', function () {
    if (is_comment_feed()) {
        wp_redirect(home_url(), 301);
        exit;
    }
});


// Remove comment reply script
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_script('comment-reply');
});

// Close comments on all existing posts and pages
/*
add_action('wp_loaded', function() {
    // Only run this once after adding the function
    if (!get_option('comments_disabled_bulk_update')) {
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->posts} SET comment_status = 'closed', ping_status = 'closed'");
        update_option('comments_disabled_bulk_update', true);
    }
});
*/
