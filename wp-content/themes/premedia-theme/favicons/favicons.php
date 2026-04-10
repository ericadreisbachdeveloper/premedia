<?php if (!defined('ABSPATH')) {
    exit;
} ?>


<?php
/*
 * ref: https://dev.to/masakudamatsu/favicon-nightmare-how-to-maintain-sanity-3al7
 *
 * Commands below are from within theme directory
 *
 *
 *
 * 1 of 5 - favicon.svg
 * $ cp favicons/favicon.svg ../../../favicon.svg
 *
 *
 *
 * 2 of 5 - favicon.ico
 * generate from  rsvg-convert and convert
 *
 * $ rsvg-convert -w 48 -h 48 ./favicons/favicon.svg -o ./favicons/favicon-48.png
 * $ rsvg-convert -w 32 -h 32 ./favicons/favicon.svg -o ./favicons/favicon-32.png
 * $ magick ./favicons/favicon-48.png ./favicons/favicon-32.png ../../../favicon.ico

 * x $ magick -compress lossless -density 300 -define icon:auto-resize=256,96,48 -background none favicons/favicon.svg ../../../favicon.ico
 * ref: https://stackoverflow.com/a/34958537
 * "Note the sizes="48x48" attribute. This is a trick to fool the latest versions of Chromium browsers
 * (Chrome, Edge, Opera, etc.) so that they will pick the SVG favicon rather than the .ico version of the favicon.
 *
 *
 *
 *
 * 3 of 5 - apple-touch-icon.png
 * 180x180
 * used for Windows Tiles
 * $ mv favicons/apple-touch-icon.png ../../../apple-touch-icon.png
 *
 * !! NOTE: WordPress generates this from Settings > General > Site Icon
 *
 *
 *
 * 4 of 5 - android-chrome-192x192.png
 * $ mv favicons/android-chrome-192x192.png ../../../android-chrome-192x192.png
 *
 *  !! NOTE: WordPress generates a 300x300.png sizes 192x192 from Settings > General > Site Icon
 *  but /doesn't/ generate site.webmanifest
 *
 *
 *
 * 5 of 5 - android-chrome-512x512.png
 * $ mv favicons/android-chrome-512x512.png ../../../android-chrome-512x512.png
 *
 * $ mv favicons/site.webmanifest ../../../site.webmanifest
 *
 *
 *
 * !! NOTE: WordPress generates a 300x300.png from Settings > General > Site Icon
 * for IE 10 Metrotile - good!
 * https://www.deviantart.com/dakirby309/art/Modern-UI-Tiles-Icon-Set-616-Tiles-376111513
 * <meta name="msapplication-TileImage"
 */

?>

<link rel="icon" href="<?php echo(esc_url(SITE.'/favicon.ico')); ?>" sizes="48x48">
<link rel="icon" href="<?php echo(esc_url(SITE.'/favicon.svg')); ?>" sizes="any">
<link rel="apple-touch-icon" href="<?php echo(esc_url(SITE.'/apple-touch-icon.png')); ?>">


<?php
/*
 * ref: https://developer.mozilla.org/en-US/docs/Web/Manifest/
 *
 * ref: https://developer.mozilla.org/en-US/docs/Web/Manifest/theme_color
 * Recommended: a DARK color
 *
 * "The theme_color member is used to specify the default color for your web application's user interface.
 * "This color may be applied to various browser UI elements, such as the toolbar, address bar, and status bar.
 * "It can be particularly noticeable in contexts like the task switcher or when the app is added to the home screen."
 *
 * ref: https://developer.mozilla.org/en-US/docs/Web/Manifest/background_color
 * Recommended: a DARK, BRAND color
 *
 * "The background_color manifest member is used to specify an initial background color for your web application.
 * "This color appears in the application window before your application's stylesheets have loaded."
 *
 * ref: https://miro.medium.com/v2/resize:fit:1400/1*k07wulFO297SXn7EZ-Xaow.png
 */
?>
<link rel="manifest" href="<?php echo(esc_url(SITE.'/site.webmanifest')); ?>">