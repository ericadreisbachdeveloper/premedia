<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Build clinical site and physician HTML for the Markdown Mirror plugin.
 *
 * Reads from the $clinical_site_info global, which is populated by
 * mdm_load_clinical_site_info() in shortcode-map.php. Called directly
 * by mdm_append_locations_markdown() in the Markdown Mirror plugin.
 *
 * @since 2.0.0
 * @return string  HTML string ready for conversion to Markdown, or '' if
 *                 no clinical site data is available.
 */
function mdm_build_locations_html(): string {
    global $clinical_site_info;

    if ( empty( $clinical_site_info ) ) {
        return '';
    }

    $html = '<h1>Study Sites and Clinicians</h1>';

    foreach ( $clinical_site_info as $site ) {
        $html .= '<h2>' . esc_html( $site['site_name'] ) . '</h2>';
        $html .= '<p>' . esc_html( $site['display_city'] ) . ', ' . esc_html( $site['state'] ) . '</p>';

        if ( ! empty( $site['physicians'] ) ) {
            foreach ( $site['physicians'] as $physician ) {
                $html .= '<p>' . esc_html( $physician['name'] );

                // Omit placeholder stethoscope images; include real physician photos.
                if ( ! str_contains( $physician['img_src'], 'stethoscope' ) ) {
                    $html .= '<img src="' . esc_url( $physician['img_src'] ) . '" alt="photo of ' . esc_attr( $physician['name'] ) . '">';
                }

                $html .= '</p>';
            }
        }
    }

    return $html;
}
