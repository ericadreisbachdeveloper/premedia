<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly


/**
 * Outout clinical sites and physicians for markdown files 
 * cf /plugins/markdown-mirror-dblls
 *
 *
 * @since 1.0.0
 * @return string Clinical site and physicial HTML
 */


// Sites and physicians for markdown to cache/catch
global $clinical_site_info; 

if ( ! empty( $clinical_site_info ) ) {
    
    $clinics_for_markdown = '';
    
    $clinics_for_markdown .= '<h1>Study Sites and Clinicians</h1>';

    foreach ( $clinical_site_info as $site ) {
        $clinics_for_markdown .= '<h2>' . $site['site_name'] . '</h2>';
        $clinics_for_markdown .= '<p>' . $site['display_city'] . ', ' . $site['state'] . '</p>';

        if ( ! empty( $site['physicians'] ) ) {
            foreach ( $site['physicians'] as $physician ) {
                $clinics_for_markdown .= '<p>' . $physician['name'];
                if ( ! str_contains( $physician['img_src'], 'stethoscope' ) ) {
                    $clinics_for_markdown .= '<img src="' . $physician['img_src'] . '" alt="photo of ' . $physician['name'] . '"></p>';
                } else {
                    $clinics_for_markdown .= '</p>';
                }
            }
        }
    }

    return $$clinics_for_markdown; 
}
// END Sites and physicians for markdown