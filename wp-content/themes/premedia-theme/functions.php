<?php
/**
 * Theme Functions
 *
 * @package    PREMEDIA
 * @author     erica dreisbach erica@ericadreisbach.com
 * @license    GPL-2.0+
 * @link       https://premediatrial.com
 * @since      1.0.0
 */
declare(strict_types=1);

/**
 * Set up constants to avoid extra queries to DB
 * use as needed in functions.php and template files
 */
define( 'PDIR', get_template_directory_uri() );
define( 'TDIR', get_bloginfo( 'stylesheet_directory' ) );
define( 'SITE', get_bloginfo( 'url' ) );

if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
    define( 'ROOT', sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) );
}
define( 'THEMEPATH', get_stylesheet_directory() );


/**
 * Style vsn
 * used to juke browser cache
 */
global $style_vsn;
$style_vsn = '1.0.60';


/**
 * Vital functions and includes
 */
require_once THEMEPATH . '/inc/accordion-faq-schema.php';
require_once THEMEPATH . '/inc/button-aria-label.php';
require_once THEMEPATH . '/inc/clinical-site-locations-schema.php';
require_once THEMEPATH . '/inc/disable-comments.php';
require_once THEMEPATH . '/inc/medicalcondition-schema.php';
require_once THEMEPATH . '/inc/remove-wordpress-cruft.php';
require_once THEMEPATH . '/inc/robots-txt-llms-txt.php';
require_once THEMEPATH . '/inc/server-side-email-obfuscation.php';
require_once THEMEPATH . '/inc/simple-shortcodes.php'; /* includes query functions and references to template partials       */
require_once THEMEPATH . '/inc/shortcode-map.php';
require_once THEMEPATH . '/inc/template-locations.php'; 



/**
 * For debugging
 * output all script handles
 */

/*
add_action( 'wp_print_scripts', 'inspect_scripts', 99 );
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

add_action( 'wp_enqueue_scripts', 'enqueue_css_js', 100 );

function enqueue_css_js() {
    global $style_vsn;

    wp_register_style( 'main', TDIR . '/assets/css/style.css', '', $style_vsn );
    wp_enqueue_style( 'main' );

    wp_register_script( 'scroll', TDIR . '/assets/js/scroll-min.js', '', '1.0.4', true );
    wp_enqueue_script( 'scroll' );

    wp_register_script( 'skiplink', TDIR . '/assets/js/skiplink-min.js', '', '1.0.4', true );
    wp_enqueue_script( 'skiplink' );
}



/**
 * Admin styles - as of 6 May 2026 adding .sr-only to Homepage Gutenberg editor ONLY
 */
add_action( 'enqueue_block_assets', 'customize_css_in_gutenberg_back_end' );

function customize_css_in_gutenberg_back_end() {
    if ( !is_admin() ) {
        return; 
    }

    global $post;

    // Only use this CSS on front page template editor
    if ( ! isset( $post->ID ) || (int) $post->ID !== (int) get_option( 'page_on_front' ) ) {
        return;
    }

    wp_enqueue_style(
        'admin-only',
        TDIR . '/assets/css/admin.css'
    );

}



/**
 * Add viewport detector for use with CSS animations entering and existing viewport
 */
add_action( 'wp_body_open', 'add_viewport_detector_dbllc' );

function add_viewport_detector_dbllc() {
    echo '<div class="-scroll" data-role="viewport-detector"></div>';
}



/**
 * Modify <head>
 * 1. Google Analytics
 * 2. Reference markdown mirrors - cf /plugins/markdown-mirror-dbllc
 */
add_action( 'wp_head', 'add_to_head_dbllc' );

function add_to_head_dbllc() {
    global $post;
    ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-8EX4823B06"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', 'G-8EX4823B06');
</script>

    <?php
    
    // Only add markdown link on singular posts/pages
    if ( ! is_singular() || ! isset( $post->post_name ) ) {
        return;
    }
    
    $slug = ( 'home' === $post->post_name ) ? 'index' : $post->post_name;
    ?>
<link rel="alternate" type="text/markdown" href="<?php echo esc_url( SITE . '/' . $slug . '.md' ); ?>">
    <?php
}


/**
 * Add Google Analytics conversion event upon successful WPForms submission
 */
add_action(
    'wp_footer',
    function () {
        if ( ! function_exists( 'wpforms' ) || ! wpforms()->frontend->forms ) {
            return;
        }
        ?>
<script>
window.addEventListener('wpformsAjaxSubmitSuccess', function (event) {
    gtag('event', 'form_submission', {
    event_category: 'WPForms',
    event_label: 'Contact Form',
    form_id: event.detail?.formId ?? 'unknown'
    });
});
</script>
        <?php
    }
);
