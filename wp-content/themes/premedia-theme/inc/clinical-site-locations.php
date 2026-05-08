<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly



/**
 * Get list of unique City, State pairs to add to <meta name="geo.placename" content="...">
 * for improved surfaceability in searches of "achalasia study near me"
 */



// Function to Ggnerate unique array of City, State pairs
// from Locations page ACF repeater
function premedia_get_geo_placenames()
{
    $cached = get_transient('premedia_geo_placenames');
    if ($cached !== false) {
        return $cached;
    }

    $clinic_sites = array();

    // Allow graceful failure if ACF is disabled
    if (function_exists('get_field')) {
        $clinic_sites = get_field('sites', 13);
    } else {
        $clinic_sites = array();
    }

    if (empty($clinic_sites)) {
        return '';
    }

    // Initialize $placenames array
    $placenames = [];

    foreach ($clinic_sites as $site) {
        $display_city  = trim($site['display_city'] ?? '');
        $state = trim($site['state'] ?? '');

        if ($display_city && $state) {
            $placenames[] = "$display_city, $state";
        }
    }

    // Uniqueness of places
    $placenames = array_unique($placenames);
    // Alpha sort places by city
    sort($placenames);

    // Separate with a semicolon
    $result = implode('; ', $placenames);

    // Set a cookie looking for whether to use cached or just-now-updated list
    // 0 = no expiration, persists until explicitly deleted
    set_transient('premedia_geo_placenames', $result, 0);

    return $result;
}



// Run function defined above
// and output as <meta> tag in <head> of all pages
add_action('wp_head', 'premedia_geo_meta_tag');

function premedia_geo_meta_tag()
{
    $placenames = premedia_get_geo_placenames();

    if (! empty($placenames)) {
        echo '<meta name="geo.placename" content="' . esc_attr($placenames) . '">' . "\n";
    }
}



// Bust cached transient when Locations page is saved
add_action('acf/save_post', 'premedia_bust_geo_cache');

function premedia_bust_geo_cache($post_id)
{
    if ((int) $post_id === 13) {
        delete_transient('premedia_geo_placenames');
    }
}



// NOTE from Claude:
// If you ever need to force a rebuild outside of a save — for example
// after a server migration or database restore — you can either
// temporarily remove the transient via WP CLI (wp transient delete
// premedia_geo_placenames) or save the locations page once to trigger the
// bust and rebuild.



// For use in inc/shortcode-map.php
function state_abbreviation($state_name)
{
    $states = [
        'Alabama'        => 'AL', 'Alaska'         => 'AK',
        'Arizona'        => 'AZ', 'Arkansas'       => 'AR',
        'California'     => 'CA', 'Colorado'       => 'CO',
        'Connecticut'    => 'CT', 'Delaware'       => 'DE',
        'Florida'        => 'FL', 'Georgia'        => 'GA',
        'Hawaii'         => 'HI', 'Idaho'          => 'ID',
        'Illinois'       => 'IL', 'Indiana'        => 'IN',
        'Iowa'           => 'IA', 'Kansas'         => 'KS',
        'Kentucky'       => 'KY', 'Louisiana'      => 'LA',
        'Maine'          => 'ME', 'Maryland'       => 'MD',
        'Massachusetts'  => 'MA', 'Michigan'       => 'MI',
        'Minnesota'      => 'MN', 'Mississippi'    => 'MS',
        'Missouri'       => 'MO', 'Montana'        => 'MT',
        'Nebraska'       => 'NE', 'Nevada'         => 'NV',
        'New Hampshire'  => 'NH', 'New Jersey'     => 'NJ',
        'New Mexico'     => 'NM', 'New York'       => 'NY',
        'North Carolina' => 'NC', 'North Dakota'   => 'ND',
        'Ohio'           => 'OH', 'Oklahoma'       => 'OK',
        'Oregon'         => 'OR', 'Pennsylvania'   => 'PA',
        'Rhode Island'   => 'RI', 'South Carolina' => 'SC',
        'South Dakota'   => 'SD', 'Tennessee'      => 'TN',
        'Texas'          => 'TX', 'Utah'           => 'UT',
        'Vermont'        => 'VT', 'Virginia'       => 'VA',
        'Washington'     => 'WA', 'West Virginia'  => 'WV',
        'Wisconsin'      => 'WI', 'Wyoming'        => 'WY',
        'District of Columbia' => 'DC',
    ];

    $state_name = trim($state_name);
    return $states[ $state_name ] ?? $state_name;
}
