<?php
/**
 * Restrict the Locations template to a single page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get the page currently using the locations template
 *
 * @since 1.0.0
 * @return int|null Page ID or null if none found
 */
function dbllc_get_locations_template_page_id() {
    static $page_id = null;
    
    if ( null !== $page_id ) {
        return $page_id;
    }
    
    $cached = get_transient( 'dbllc_locations_template_page' );
    if ( false !== $cached ) {
        $page_id = (int) $cached;
        return $page_id;
    }
    
    $pages = get_posts(
        array(
            'post_type'      => 'page',
            'posts_per_page' => 1,
            'post_status'    => 'any',
            'meta_key'       => '_wp_page_template',
            'meta_value'     => 'locations',
        )
    );
    
    if ( ! empty( $pages ) ) {
        $page_id = $pages[0]->ID;
        set_transient( 'dbllc_locations_template_page', $page_id, DAY_IN_SECONDS );
        return $page_id;
    }
    
    return null;
}


/**
 * Bust locations template cache when any page is saved
 *
 * @since 1.0.0
 * @param int     $post_id Post ID
 * @param WP_Post $post    Post object
 * @return void
 */
add_action( 'save_post_page', 'dbllc_bust_locations_template_cache', 10, 2 );

function dbllc_bust_locations_template_cache( $post_id, $post ) {
    // Don't run on autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check permissions
    if ( ! current_user_can( 'edit_page', $post_id ) ) {
        return;
    }
    
    // Bust cache when any page template changes
    delete_transient( 'dbllc_locations_template_page' );
}

/**
 * Helper function to get locations page ID
 */
function dbllc_get_locations_page_id() {
    $page_id = dbllc_get_locations_template_page_id();
    
    // Fallback to hard-coded ID if template not found
    if ( null === $page_id ) {
        // Try to find by slug as last resort
        $page = get_page_by_path( 'locations' );
        if ( $page ) {
            return $page->ID;
        }
        // Ultimate fallback
        return 13;
    }
    
    return $page_id;
}