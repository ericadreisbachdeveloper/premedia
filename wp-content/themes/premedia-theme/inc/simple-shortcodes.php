<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly


/**
 * Simple shortcodes that don't warrant a standalone PHP file
 */


/**
 * Shortcode output (c)CURRENT YEAR
 * [copyright]
 */
add_shortcode( 'copyright', 'copyright_sign' );

function copyright_sign() {
    $current_year = date( 'Y' );

    return '&copy;' . $current_year;
}
