<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly



/**
 * Add MedicalWebPage adn MedicalCondition Schema to Home and For Patients
 */
add_action('wp_head', 'add_symptom_schema_dbllc');

function add_symptom_schema_dbllc()
{
    // Only on homepage or relevant pages
    if (!is_front_page() && !is_page('for-patients')) {
        return;
    }
    ?>

    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "MedicalWebPage",
        "about": {
            "@type": "MedicalCondition",
            "name": "Achalasia",
            "alternateName": ["Esophageal Achalasia", "Primary Achalasia"],
            "signOrSymptom": [
                {
                    "@type": "MedicalSymptom",
                    "name": "Dysphagia"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Difficulty swallowing"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Trouble swallowing solid foods"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Trouble swallowing liquids"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Regurgitation of food"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Chest pain"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Heartburn"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Weight loss"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Food getting stuck in throat"
                },
                {
                    "@type": "MedicalSymptom",
                    "name": "Coughing at night"
                }
            ],
            "possibleTreatment": {
                "@type": "MedicalTherapy",
                "name": "Peroral Endoscopic Myotomy",
                "alternateName": "POEM"
            }
        }
    }
    </script>
    <?php
}
