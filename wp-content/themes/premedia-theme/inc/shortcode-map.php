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

    wp_enqueue_script(
        'map-js',
        TDIR . '/assets/js/zz-dev/map.js',
        '',
        '1.0.1',
        true
    );


    global $post;

    $post_id = $post->ID;


    // save string to output buffer
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

                    $clinical_site_info[$site['slug']]['physicians'] = [
                        'name' => $physician['physician'],
                        'institution' => $physician['institution'],
                        'img_src' => $physician['photo']
                    ];

                }

            }
        }

        print_r($clinical_site_info);


        // Loop through a given distributor's regions
        foreach ($row['regions'] ?? array() as $region) {

            $region_distributors[$region][] = [
                'name' => $dist['name'],
                'contact_info' => $dist['contact_info'],
            ];
        }



        //        return $region_distributors;



        foreach ($rows as $row) {
            $map_output .= '<div class="map-pin" style="top: ' . $row['vertical'] . '%; right: ' . $row['horizontal'] . '%;">';
            $map_output .= '<div role="button" aria-pressed="false" tabindex="0" id="' . $row['slug'] . '" class="map-pin-img" src="' . TDIR . '/assets/img/map-pin-48.svg" aria-label="' . $row['site_name'] . ', ' . $row['city_state'] . ' physicians"></div>';
            $map_output .= '</div>';
        }

        $map_output .= '<div id="data-modal" class="modal main_color" role="dialog" aria-modal="true" aria-labelledby="modal-data-heading" style="display: none;">
            <div class="modal-content" id="region-data">
                <div id="clinical-site-location"> </div>
                <div id="clinical-site-physicians"> </div>
                <button class="close-data" data-close-modal data-modal-type="data-modal" aria-label="Close dialog">&times;</button>
            </div>
        </div>';

    }


    $map_output .= '</div>';


    return $map_output;

    // clear output buffer
    return ob_get_clean();
}
