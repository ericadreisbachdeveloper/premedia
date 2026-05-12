<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly


/**
 * Add an optional aria-label meta box to Gutenberg native button blocks
 * 
 * @since 1.0.0
 * @return void
 */
add_action( 'enqueue_block_editor_assets', 'add_button_aria_label_control_dbllc' );

function add_button_aria_label_control_dbllc() {
    wp_enqueue_script(
        'button-aria-label-control',
        TDIR . '/assets/js/button-aria-label.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-hooks' ),
        filemtime( TDIR . '/assets/js/button-aria-label.js' )
    );
}


add_filter( 'render_block', 'add_aria_label_to_button_dbllc', 10, 2 );

function add_aria_label_to_button_dbllc( $block_content, $block ) {
    if ( $block['blockName'] !== 'core/button' ) {
        return $block_content;
    }

    if ( ! empty( $block['attrs']['ariaLabel'] ) ) {
        $aria_label = esc_attr( $block['attrs']['ariaLabel'] );
        // Add aria-label to the <a> tag
        $block_content = preg_replace(
            '/(<a\s[^>]*class="wp-block-button__link[^"]*")/',
            '$1 aria-label="' . $aria_label . '"',
            $block_content
        );
    }

    return $block_content;
}
