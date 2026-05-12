<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly


/**
 * Output copyright symbol
 *
 * Usage: [copyright]
 *
 * @since 1.0.0
 * @return string Copyright symbol HTML entity
 */
add_shortcode( 'copyright', 'copyright_sign' );

function copyright_sign() {
    $current_year = date( 'Y' );

    return '&copy;' . $current_year;
}
