<?php

/**
 * Plugin Name: Schema FAQ Block
 * Description: Gutenberg FAQ block with Schema.org JSON-LD markup.
 * Version: 1.0.0
 */

function schema_faq_block_init()
{
    register_block_type(__DIR__ . '/build');
}
add_action('init', 'schema_faq_block_init');
