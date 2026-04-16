<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/**
 * Locations map shortcode
 * [map]
 *
 * Relies on Advanced Custom Fields 'sites' repeater field
 *
 */
add_shortcode('map', 'map_shortcode_fxn');

function map_shortcode_fxn()
{

    global $post;

    $post_id = $post->ID;

    // Save string to output buffer
    ob_start();




    $map_output = '';

    $map_output .= '<div class="map-container">';

    $map_output .= '<img class="us-map" src="' . TDIR . '/assets/img/us-map.svg" alt="United States map">';


    $rows = get_field('sites', $post_id);


    if (!empty($rows)) {

        // Initialize final array
        $clinical_site_info = array();

        // Loop through clinical sites to generate data array
        foreach ($rows as $site) {

            $clinical_site_info[$site['slug']] = [
                'site_name' => $site['site_name'],
                'city_state' => $site['city_state']
            ];

            if (!empty($site['physicians'])) {

                foreach ($site['physicians'] as $physician) {

                    $clinical_site_info[$site['slug']]['physicians'][] = [
                        'name' => $physician['physician'],
                        'institution' => $physician['institution'],
                        'img_src' => $physician['photo']
                    ];

                }

            }
        }


        foreach ($rows as $row) {
            $map_output .= '<div class="map-pin" style="top: ' . $row['vertical'] . '%; right: ' . $row['horizontal'] . '%;">';
            /*
            $map_output .= '<div role="button" aria-pressed="false" tabindex="0" id="' . $row['slug'] . '" class="map-pin-img" src="' . TDIR . '/assets/img/map-pin-48.svg" aria-label="' . $row['site_name'] . ', ' . $row['city_state'] . ' physicians"></div>';
            */

            $map_output .= '<svg class="s0 map-pin-svg" version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="48" height="48">
                <g>
                    <path class="map-pin-path s0" role="button" aria-pressed="false" tabindex="0" id="' . $row['slug'] . '" aria-label="' . $row['site_name'] . ', ' . $row['city_state'] . ' physicians" d="m23.97 0c-9.32 0-16.91 7.59-16.91 16.91 0 11.35 15.5 29.9 16.16 30.68 0.19 0.22 0.46 0.35 0.75 0.35q0 0 0 0c0.29 0 0.56-0.12 0.74-0.34 0.66-0.77 16.17-19.01 16.17-30.69 0-9.32-7.58-16.91-16.91-16.91z"/>
                    <path  class="s1" d="m23.97 0c-9.32 0-16.91 7.59-16.91 16.91 0 11.35 15.5 29.9 16.16 30.68 0.19 0.22 0.46 0.35 0.75 0.35q0 0 0 0c0.29 0 0.56-0.12 0.74-0.34 0.66-0.77 16.17-19.01 16.17-30.69 0-9.32-7.58-16.91-16.91-16.91zm0.01 45.43c-3.12-3.87-14.97-19.22-14.97-28.52 0-8.25 6.71-14.96 14.96-14.96 8.25 0 14.96 6.71 14.96 14.96 0 9.56-11.83 24.69-14.95 28.52z"/>
                    <path class="s1" d="m23.97 9.4c-3.96 0-7.19 3.23-7.19 7.19 0 3.97 3.23 7.19 7.19 7.19 3.97 0 7.19-3.22 7.19-7.19 0-3.96-3.22-7.19-7.19-7.19z"/>
                </g>
                </svg>';
            $map_output .= '</div>';
        }

        $map_output .= '<div id="data-modal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-data-heading" style="display: none;">
            <div class="modal-content" id="clinic-data">
                <div id="clinic-site-name"> </div>
                <div id="clinic-site-city-state"> </div>
                <div id="clinic-site-physicians"> </div>
                <button class="close-data" data-close-modal data-modal-type="data-modal" aria-label="Close dialog">&times;</button>
            </div>
        </div>';

    }


    $map_output .= '</div>';

    wp_enqueue_script(
        'map-js',
        TDIR . '/assets/js/zz-dev/map.js',
        '',
        '1.0.6',
        true
    );

    // Pass clinicData object to JavaScript
    wp_localize_script('map-js', 'clinicData', array(
        'clinical_site_info' => $clinical_site_info
    ));

    return $map_output;

    // clear output buffer
    return ob_get_clean();
}
