<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}    // Exit if accessed directly



/**
 * Global location metadata for <head>
 */



/**
 * 1. Add <meta name="geo.placename" content="...">
 *    for improved surfaceability in searches of "achalasia study near me"
 */


// 1a. Generate unique array of City, State pairs from Locations page ACF repeater
function premedia_get_geo_placenames() {
    $cached = get_transient( 'premedia_geo_placenames' );
    if ( $cached !== false ) {
        return $cached;
    }

    $clinic_sites = array();

    // Allow graceful failure if ACF is disabled
    if ( function_exists( 'get_field' ) ) {
        $clinic_sites = get_field( 'sites', 13 );
    } else {
        $clinic_sites = array();
    }

    if ( empty( $clinic_sites ) ) {
        return '';
    }

    // Initialize $placenames array
    $placenames = array();

    foreach ( $clinic_sites as $site ) {
        $display_city = trim( $site['display_city'] ?? '' );
        $state        = trim( $site['state'] ?? '' );

        if ( $display_city && $state ) {
            $placenames[] = "$display_city, $state";
        }
    }

    // Uniqueness of places
    $placenames = array_unique( $placenames );
    // Alpha sort places by city
    sort( $placenames );

    // Separate with a semicolon
    $result = implode( '; ', $placenames );

    // Set a cookie looking for whether to use cached or just-now-updated list
    // 0 = no expiration, persists until explicitly deleted
    set_transient( 'premedia_geo_placenames', $result, 0 );

    return $result;
}



// 1b. Output City, State pairs as <meta> tag in <head>
add_action( 'wp_head', 'premedia_geo_meta_tag' );

function premedia_geo_meta_tag() {
    $placenames = premedia_get_geo_placenames();

    if ( ! empty( $placenames ) ) {
        echo '<meta name="geo.placename" content="' . esc_attr( $placenames ) . '">' . "\n";
    }
}



/**
 * 2. Generate MedicalOrganization parent Schema including alpha-sorted list of states for areaServed
 */


// 2a. Generate alpha-sorted list of states and parent MedicalOrganization Schema
function generate_parent_schema() {

    $cached = get_transient( 'parent_schema_transient' );

    if ( $cached !== false ) {
        return $cached;
    }

    $medical_organization_schema = '';

    // Allow graceful failure if ACF is disabled
    if ( function_exists( 'get_field' ) ) {
        $clinic_sites = get_field( 'sites', 13 );
    } else {
        $clinic_sites = array();
    }

    if ( empty( $clinic_sites ) ) {
        return '';
    }

    $state_arr = array();

    foreach ( $clinic_sites as $site ) {
        $state_arr [] = $site['state'];
    }

    $state_arr = array_unique( $state_arr );
    sort( $state_arr );

    $area_served = '';

    foreach ( $state_arr as $state_key ) {
        $area_served .= '{   
                "@type": "State", 
                "name": "' . $state_key . '"
                }';
        if ( $state_key !== end( $state_arr ) ) {
            $area_served .= ',
                    ';
        }
    }

    // Sites and physicians for LLMs and robots to cache/catch
    $medical_organization_schema = '<script type="application/ld+json">{
    "@context": "https://schema.org",
    "@type": ["MedicalOrganization", "MedicalTrial"], 
    "conditionOrDisease": [
        {
            "@type": "MedicalCondition",
            "name": "Achalasia",
            "signOrSymptom": ["Dysphagia", "Difficulty swallowing", "Regurgitation", "Chest pain"]
        }
    ],
    "eligibilityCriteria": "Adults experiencing difficulty swallowing due to achalasia or related esophageal motility disorders",
    "keywords": "achalasia, dysphagia, difficulty swallowing, POEM, esophageal motility disorder, swallowing problems",
    "@id": "https://premediatrial.com/#organization",
    "name": "PREMEDIA Clinical Trial - Precision Medicine in Achalasia",
    "url": "https://premediatrial.com",
    "description": "The PREcision MEDicine In Achalasia (PREMEDIA) study is the largest and most rigorous multicenter evaluation of achalasia treatment to date.",
    "medicalSpecialty": "Gastroenterologic",
    "sameAs": [
        "https://clinicaltrials.gov/study/NCT07293650",
        "https://clinicaltrials.gov/study/NCT07293689"
    ],
    "identifier": [
        {"@type": "PropertyValue", "name": "ClinicalTrials.gov ID", "value": "NCT07293650"},            
        {"@type": "PropertyValue", "name": "ClinicalTrials.gov ID", "value": "NCT07293689"}
    ]';

    if ( ! empty( $clinic_sites ) ) {
        $medical_organization_schema .= ',
        "areaServed": [' . $area_served .
        ']';
    }

    $medical_organization_schema .= '};  
    </script>';

    set_transient( 'parent_schema_transient', $medical_organization_schema, 0 );

    return $medical_organization_schema;
}


// 2b. Output parent Schema in <head> of all pages
add_action( 'wp_head', 'output_parent_schema' );

function output_parent_schema() {
    $parent_schema = generate_parent_schema();

    if ( ! empty( $parent_schema ) ) {
        echo wp_json_encode( $parent_schema );
    }
}



/**
 *  3. Bust cached transients when Locations page is saved
 *
 *     NOTE from Claude:
 *     One transient per function, but a single shared bust function that clears both.
 */
add_action( 'acf/save_post', 'premedia_bust_locations_cache' );

function premedia_bust_locations_cache( $post_id ) {
    if ( (int) $post_id === 13 ) {
        delete_transient( 'premedia_geo_placenames' );
        delete_transient( 'parent_schema_transient' );
    }
}


// NOTE from Claude:
// If you ever need to force a rebuild outside of a save — for example
// after a server migration or database restore — you can either
// temporarily remove the transient via WP CLI
// $ wp transient delete premedia_geo_placenames
// $ wp transient delete parent_schema_transient
// or save the locations page once to trigger the bust and rebuild.



/**
 *  4. Map state names to postal abbreviations
 *    for use in inc/shortcode-map.php
 */
function state_abbreviation( $state_name ) {
    $states = array(
        'Alabama'              => 'AL',
		'Alaska'               => 'AK',
        'Arizona'              => 'AZ',
		'Arkansas'             => 'AR',
        'California'           => 'CA',
		'Colorado'             => 'CO',
        'Connecticut'          => 'CT',
		'Delaware'             => 'DE',
        'Florida'              => 'FL',
		'Georgia'              => 'GA',
        'Hawaii'               => 'HI',
		'Idaho'                => 'ID',
        'Illinois'             => 'IL',
		'Indiana'              => 'IN',
        'Iowa'                 => 'IA',
		'Kansas'               => 'KS',
        'Kentucky'             => 'KY',
		'Louisiana'            => 'LA',
        'Maine'                => 'ME',
		'Maryland'             => 'MD',
        'Massachusetts'        => 'MA',
		'Michigan'             => 'MI',
        'Minnesota'            => 'MN',
		'Mississippi'          => 'MS',
        'Missouri'             => 'MO',
		'Montana'              => 'MT',
        'Nebraska'             => 'NE',
		'Nevada'               => 'NV',
        'New Hampshire'        => 'NH',
		'New Jersey'           => 'NJ',
        'New Mexico'           => 'NM',
		'New York'             => 'NY',
        'North Carolina'       => 'NC',
		'North Dakota'         => 'ND',
        'Ohio'                 => 'OH',
		'Oklahoma'             => 'OK',
        'Oregon'               => 'OR',
		'Pennsylvania'         => 'PA',
        'Rhode Island'         => 'RI',
		'South Carolina'       => 'SC',
        'South Dakota'         => 'SD',
		'Tennessee'            => 'TN',
        'Texas'                => 'TX',
		'Utah'                 => 'UT',
        'Vermont'              => 'VT',
		'Virginia'             => 'VA',
        'Washington'           => 'WA',
		'West Virginia'        => 'WV',
        'Wisconsin'            => 'WI',
		'Wyoming'              => 'WY',
        'District of Columbia' => 'DC',
    );

    $state_name = trim( $state_name );
    return $states[ $state_name ] ?? $state_name;
}
