<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/*
 * Shortcode output (c)YYYY
 * [copyright]
 */
add_shortcode('map', 'map_function');

function map_function()
{

    global $post;

    $post_id = $post->ID;


    // save string to output buffer
    ob_start();

    $map_output = '';

    $map_output .= '<div class="map-container">';

    $map_output .= '<img class="us-map" src="' . TDIR . '/assets/img/us-map.svg" alt="United States map">';



    $rows = get_field('sites', $post_id);

    if (!empty($rows)) {

        foreach ($rows as $row) {
            $map_output .= '<div class="map-pin" style="top: ' . $row['vertical'] . '%; right: ' . $row['horizontal'] . '%;">';
            $map_output .= '<img class="map-pin-img" src="' . TDIR . '/assets/img/map-pin-48.svg" alt="' . $row['site_name'] . '">';
            $map_output .= '</div>';
        }

    }





    $map_output .= '</div>';


    return $map_output;

    // clear output buffer
    return ob_get_clean();
}
