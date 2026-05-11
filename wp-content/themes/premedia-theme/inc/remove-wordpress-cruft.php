<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/**
 * Add defer to non-admin scripts
 *
 * "The best thing to do to speed up your page loading when using scripts is to put them in the head
 * "and add a defer attribute to your script tag"
 *
 * ref: https://flaviocopes.com/javascript-async-defer/#just-tell-me-the-best-way
 */
add_filter('script_loader_tag', 'add_async_attribute', 100, 2);

function add_async_attribute($tag, $handle)
{
    if (!is_admin()) {
        $dontdefer = ['jquery-core', 'jquery-migrate', 'wp-i18n', 'wp-hooks'];

        if (!in_array($handle, $dontdefer, true)) {
            return str_replace(' src', ' defer="defer" src', $tag);
        }
    }

    return $tag;
}


/**
 * Remove type="text/javascript" and type="text/css"
 * no longer needed
 *
 * ref: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/style
 * ref: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script
 */
add_filter('style_loader_tag', 'remove_type_attr', 10, 2);
add_filter('script_loader_tag', 'remove_type_attr', 10, 2);

function remove_type_attr($tag, $handle)
{
    return preg_replace("/type=['\"]text\\/(javascript|css)['\"]/", '', $tag);
}


/**
 * Remove Gutenberg styling
 * ref: https://simplerevolutions.design/making-wordpress-faster-with-and-without-plugins/
 */
add_action('wp_enqueue_scripts', 'remove_gutenberg_styling', 100);

function remove_gutenberg_styling()
{

    //wp_dequeue_style('wp-block-library'); // External CSS
    wp_dequeue_style('wp-block-library-theme'); // Inline CSS

    /* Inline CSS necessary for back end Editor customization tools to work */
    //wp_dequeue_style('global-styles'); // Inline CSS

}


/**
 * Remove SVGs below <body> tag
 *
 * ref: https://simplerevolutions.design/making-wordpress-faster-with-and-without-plugins/
 */
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');


/**
 * Disable comments RSS
 *
 * ref: https://wordpress.stackexchange.com/questions/126174/disable-comments-feed-but-not-the-others/218786
 */
add_action('after_setup_theme', 'head_cleanup');

function head_cleanup(): void
{
    add_filter('feed_links_show_comments_feed', '__return_false');
}



/**
 * Remove Really Simple Discovery Link
 *
 * src: https://wpadminify.com/kb/how-to-remove-rsd-link-from-wordpress-header-source/
 */
remove_action('wp_head', 'rsd_link');



/**
 * Remove wlmanifest
 *
 * src: https://wpassist.me/how-to-remove-wlwmanifest-from-wordpress/
 */
remove_action('wp_head', 'wlwmanifest_link');



/**
 * Remove emoji support
 */
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');


/**
 * Remove unnecessary WordPress head tags and HTTP headers
 */
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_shortlink_wp_head');

remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('template_redirect', 'wp_shortlink_header', 11);


/**
 * Removes RSS feed endpoints
 */
// Remove ALL feed links from head - run later to override theme/plugins
add_action('after_setup_theme', 'disable_all_feeds_dbllc');
function disable_all_feeds_dbllc()
{
    // Remove default feed links
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);

    // Remove oEmbed links
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');

    // Redirect feed requests
    add_action('template_redirect', function () {
        if (is_feed()) {
            wp_redirect(home_url(), 301);
            exit;
        }
    }, 1);
}

// Nuclear option: remove any remaining feed links that slip through
add_action('wp_head', function () {
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
}, 1); // Very early priority

// If Rank Math is re-adding them, disable via filter
add_filter('rank_math/frontend/feed_link', '__return_false');



// Convert &nbsp; to space for use in meta descriptions
add_action('wp_head', 'clean_all_meta_descriptions_dbllc', 999);
function clean_all_meta_descriptions_dbllc()
{
    ob_start('filter_meta_description_output_dbllc');
}

function filter_meta_description_output_dbllc($buffer)
{
    // Match any meta description tag
    $buffer = preg_replace_callback(
        '/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']\s*\/?>/i',
        function ($matches) {
            $content = $matches[1];

            // Convert &nbsp; to regular space
            $content = str_replace('&nbsp;', ' ', $content);

            // Handle actual non-breaking space character
            $content = str_replace("\xc2\xa0", ' ', $content);

            // Clean up double spaces
            $content = preg_replace('/\s+/', ' ', $content);

            // Trim
            $content = trim($content);

            return '<meta name="description" content="' . $content . '" />';
        },
        $buffer
    );

    return $buffer;
}

// Also filter at common plugin hooks
add_filter('rank_math/frontend/description', 'clean_meta_content_dbllc');
add_filter('rank_math/opengraph/facebook/og_description', 'clean_meta_content_dbllc');
add_filter('rank_math/opengraph/twitter/twitter_description', 'clean_meta_content_dbllc');
add_filter('wpseo_metadesc', 'clean_meta_content_dbllc'); // Yoast
add_filter('aioseop_description', 'clean_meta_content_dbllc'); // All in One SEO
add_filter('wp_trim_words', 'clean_meta_content_dbllc'); // WordPress core

function clean_meta_content_dbllc($content)
{
    if (empty($content)) {
        return $content;
    }

    // Convert &nbsp; to regular space
    $content = str_replace('&nbsp;', ' ', $content);

    // Handle actual non-breaking space character
    $content = str_replace("\xc2\xa0", ' ', $content);

    // Clean up double spaces
    $content = preg_replace('/\s+/', ' ', $content);

    return trim($content);
}


/**
 * Add fetchpriority="high" to the logo
 */
add_filter('render_block', 'add_fetchpriority_to_logo_dbllc', 10, 2);

function add_fetchpriority_to_logo_dbllc($block_content, $block)
{
    // Only target image blocks
    if ($block['blockName'] !== 'core/image') {
        return $block_content;
    }

    // Only target image ID 358 (logo)
    if (isset($block['attrs']['id']) && $block['attrs']['id'] == 358) {
        $block_content = preg_replace(
            '/<img\s/',
            '<img fetchpriority="high" ',
            $block_content,
            1
        );
    }

    return $block_content;
}
