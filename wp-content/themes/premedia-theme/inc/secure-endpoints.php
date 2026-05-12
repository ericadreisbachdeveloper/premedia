<?php
/**
 * REST API Security - Restrict and disable unnecessary endpoints
 *
 * Prevents information disclosure through WordPress REST API:
 * - Blocks user enumeration via /wp-json/wp/v2/users
 * - Removes user data from post/comment author responses
 * - Disables REST API for non-authenticated users (optional)
 *
 * Note: REST API discovery links and oEmbed links are already removed
 * in /inc/remove-wordpress-cruft.php
 *
 * @package    PREMEDIA
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disable user endpoints for non-authenticated users
 *
 * Prevents user enumeration via /wp-json/wp/v2/users
 *
 * @since 1.0.0
 * @param array $endpoints Available REST API endpoints
 * @return array Modified endpoints
 */
add_filter( 'rest_endpoints', 'dbllc_disable_user_endpoints' );

function dbllc_disable_user_endpoints( $endpoints ) {
    // Only disable for non-authenticated users
    if ( ! is_user_logged_in() ) {
        if ( isset( $endpoints['/wp/v2/users'] ) ) {
            unset( $endpoints['/wp/v2/users'] );
        }
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
    }
    
    return $endpoints;
}

/**
 * Remove author/user data from REST API responses
 *
 * Prevents username exposure via post/comment author fields
 *
 * @since 1.0.0
 * @param WP_REST_Response $response REST API response object
 * @param WP_Post          $post     Post object
 * @param WP_REST_Request  $request  REST API request object
 * @return WP_REST_Response Modified response
 */
add_filter( 'rest_prepare_post', 'dbllc_remove_author_from_rest', 10, 3 );
add_filter( 'rest_prepare_page', 'dbllc_remove_author_from_rest', 10, 3 );

function dbllc_remove_author_from_rest( $response, $post, $request ) {
    // Only filter for non-authenticated users
    if ( ! is_user_logged_in() ) {
        $data = $response->get_data();
        
        // Remove author ID and link
        unset( $data['author'] );
        
        // Remove _links that expose author info
        if ( isset( $data['_links']['author'] ) ) {
            unset( $data['_links']['author'] );
        }
        
        $response->set_data( $data );
    }
    
    return $response;
}

/**
 * Disable application passwords endpoint
 *
 * Prevents brute force attacks on application password generation
 *
 * @since 1.0.0
 * @param array $endpoints Available REST API endpoints
 * @return array Modified endpoints
 */
add_filter( 'rest_endpoints', 'dbllc_disable_application_passwords_endpoint' );

function dbllc_disable_application_passwords_endpoint( $endpoints ) {
    if ( isset( $endpoints['/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<user_id>(?:[\d]+|me))/application-passwords/(?P<uuid>[\w\-]+)'] );
    }
    return $endpoints;
}

/**
 * Disable author archives to prevent username enumeration
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', 'dbllc_disable_author_archives' );

function dbllc_disable_author_archives() {
    if ( is_author() ) {
        wp_safe_redirect( home_url(), 301 );
        exit;
    }
}

/**
 * Block author query string enumeration
 *
 * Prevents /?author=1 style username discovery
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', 'dbllc_block_author_query' );

function dbllc_block_author_query() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading public URL parameter, no state changes
    if ( ! is_admin() && isset( $_GET['author'] ) && is_numeric( $_GET['author'] ) ) {
        wp_safe_redirect( home_url(), 301 );
        exit;
    }
}

/**
 * Remove username hints from login errors
 *
 * Prevents attackers from distinguishing between invalid username
 * and invalid password errors
 *
 * @since 1.0.0
 * @param string $error Login error message
 * @return string Generic error message
 */
add_filter( 'login_errors', 'dbllc_remove_login_username_hints' );

function dbllc_remove_login_username_hints( $error ) {
    // Return generic error for all login failures
    return 'Invalid credentials.';
}

/**
 * Restrict REST API to authenticated users only (OPTIONAL - COMMENTED OUT)
 *
 * Uncomment this to completely disable REST API for non-logged-in users.
 * WARNING: This will break some plugins and themes that use the REST API.
 *
 * @since 1.0.0
 * @param WP_Error|null|bool $result Error from another authentication handler
 * @return WP_Error|null|bool
 */
/*
add_filter( 'rest_authentication_errors', 'dbllc_restrict_rest_api_to_authenticated_users' );

function dbllc_restrict_rest_api_to_authenticated_users( $result ) {
    // If a previous authentication check was applied, pass that result along
    if ( true === $result || is_wp_error( $result ) ) {
        return $result;
    }

    // Restrict access to authenticated users only
    if ( ! is_user_logged_in() ) {
        return new WP_Error(
            'rest_not_logged_in',
            __( 'You are not currently logged in.' ),
            array( 'status' => 401 )
        );
    }

    return $result;
}
*/