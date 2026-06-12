<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly


/**
 * Register custom block metadata for FAQ schema
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', 'dbllc_register_faq_schema_metadata' );

function dbllc_register_faq_schema_metadata() {
    register_block_type_from_metadata(
        __DIR__ . '/blocks/accordion-faq-schema'
    );
}


/**
 * Add FAQ schema JSON-LD to accordion blocks
 *
 * @since 1.0.0
 * @param string $block_content Block HTML content
 * @param array  $block         Block data
 * @return string Modified block content with FAQ schema
 */
add_filter( 'render_block', 'dbllc_add_faq_schema_to_accordion', 10, 2 );

function dbllc_add_faq_schema_to_accordion( $block_content, $block ) {
    if ( 'core/accordion' !== $block['blockName'] ) {
        return $block_content;
    }

    if ( empty( $block['attrs']['enableFaqSchema'] ) ) {
        return $block_content;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $dom->loadHTML( '<?xml encoding="utf-8" ?>' . $block_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();

    $xpath = new DOMXPath( $dom );

    // Add FAQPage schema to the wrapper
    $wrapper = $xpath->query( '//div[contains(@class,"wp-block-accordion")]' )->item( 0 );
    if ( $wrapper ) {
        $wrapper->setAttribute( 'itemscope', '' );
        $wrapper->setAttribute( 'itemtype', 'https://schema.org/FAQPage' );
    }

    // Each accordion item = a Question
    $items = $xpath->query( '//div[contains(@class,"wp-block-accordion-item")]' );
    foreach ( $items as $item ) {
        $item->setAttribute( 'itemscope', '' );
        $item->setAttribute( 'itemprop', 'mainEntity' );
        $item->setAttribute( 'itemtype', 'https://schema.org/Question' );

        // Question text — the toggle title span
        $title = $xpath->query( './/span[contains(@class,"wp-block-accordion-heading__toggle-title")]', $item )->item( 0 );
        if ( $title ) {
            $title->setAttribute( 'itemprop', 'name' );
        }

        // Answer panel — wrap contents with Answer scope
        $panel = $xpath->query( './/div[contains(@class,"wp-block-accordion-panel")]', $item )->item( 0 );
        if ( $panel ) {
            $panel->setAttribute( 'itemscope', '' );
            $panel->setAttribute( 'itemprop', 'acceptedAnswer' );
            $panel->setAttribute( 'itemtype', 'https://schema.org/Answer' );

            // Collect all child nodes (p, ul, ol, div, etc.)
            $children = iterator_to_array( $panel->childNodes );
            $eligible = array_filter(
                $children,
                fn ( $node ) =>
                XML_ELEMENT_NODE === $node->nodeType &&
                in_array( $node->nodeName, array( 'p', 'ul', 'ol', 'div', 'h4', 'h5', 'h6' ), true )
            );

            if ( ! empty( $eligible ) ) {
                // Create a wrapper div with itemprop="text"
                $wrapper = $dom->createElement( 'div' );
                $wrapper->setAttribute( 'itemprop', 'text' );

                // Insert wrapper before the first eligible child
                $first = reset( $eligible );
                $panel->insertBefore( $wrapper, $first );

                // Move all eligible nodes into the wrapper
                foreach ( $eligible as $node ) {
                    $wrapper->appendChild( $node );
                }
            }
        }
    }

    $block_content = $dom->saveHTML();

    return $block_content;

}
