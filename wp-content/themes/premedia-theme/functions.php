<?php

declare(strict_types=1);

/**
 * Set up constants to avoid extra queries to DB
 * use as needed in functions.php and template files
 */
define('PDIR', get_template_directory_uri());
define('TDIR', get_bloginfo('stylesheet_directory'));
define('SITE', get_bloginfo('url'));

define('ROOT', realpath($_SERVER['DOCUMENT_ROOT']));
define('THEMEPATH', get_stylesheet_directory());


/**
 * Style vsn
 * used to juke browser cache
 */
global $style_vsn;
$style_vsn = '1.0.36';


/**
 * Vital functions and includes
 */
require_once(THEMEPATH . '/inc/accordion-faq-schema.php');
require_once(THEMEPATH . '/inc/disable-comments.php');
require_once(THEMEPATH . '/inc/remove-wordpress-cruft.php');
require_once(THEMEPATH . '/inc/simple-shortcodes.php'); /* includes query functions and references to template partials       */
require_once(THEMEPATH . '/inc/shortcode-map.php');



/**
 * For debugging
 * output all script handles
 */

/* add_action( 'wp_print_scripts', 'inspect_scripts', 99 );
/*
function inspect_scripts()
{
    if (!is_admin()) {
        global $wp_scripts;
        foreach($wp_scripts->queue as $handle) :
            echo $handle . ' | ';
        endforeach;
    }
} */


/**
 * D/enqueue styles + scripts
 *
 * Syntax: wp_register_style( $handle, $src, $deps, $ver, $media );
 * https://developer.wordpress.org/reference/functions/wp_enqueue_style/
 *
 * wp_register_script( $handle, $src, $deps, $ver, $args );
 * https://developer.wordpress.org/reference/functions/wp_register_script/
 */

add_action('wp_enqueue_scripts', 'enqueue_css_js', 100);

function enqueue_css_js()
{
    global $style_vsn;

    wp_register_style('main', TDIR.'/assets/css/style.css', '', $style_vsn);
    wp_enqueue_style('main');

    wp_register_script('skiplink', TDIR.'/assets/js/skiplink.js', '', '1.0.1', true);
    wp_enqueue_script('skiplink');

}
