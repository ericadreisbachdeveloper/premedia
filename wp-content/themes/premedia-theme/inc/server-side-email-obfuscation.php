<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/**
 * Output buffer: rewrites mailto links for CSS display + JS onclick assembly.
 * - Anchor text replaced by CSS ::before content (split across two data attributes)
 * - mailto: assembled only on click
 */
add_action('template_redirect', 'obf_mailto_start');

function obf_mailto_start()
{
    ob_start('obf_mailto_rewrite');
}

function obf_mailto_rewrite($html)
{
    $pattern = '/<a\s([^>]*)href=["\']mailto:([a-zA-Z0-9._%+\-]+)@([a-zA-Z0-9.\-]+\.[a-zA-Z]{2,})["\']([^>]*)>(.*?)<\/a>/is';

    return preg_replace_callback($pattern, function ($matches) {
        $before_href = $matches[1];
        $user        = $matches[2];
        $domain      = $matches[3];
        $after_href  = $matches[4];

        // Reverse both parts
        $user_r   = strrev($user);
        $domain_r = strrev($domain);

        // Strip any existing onclick
        $other_attrs = preg_replace('/onclick=["\'][^"\']*["\']/i', '', $before_href . $after_href);

        // Split domain at the TLD boundary for CSS reassembly
        // e.g. "northwestern.edu" -> data-d="ude.nretsewhtron"
        // CSS will display: attr(data-u) + "@" + attr(data-d) -- but reversed,
        // so we store them reversed and use JS to reverse on click.
        // For CSS we store the display-friendly (non-reversed) halves separately.
        $at_pos    = strpos($domain, '.');
        $domain_a  = substr($domain, 0, $at_pos);  // e.g. "northwestern"
        $domain_b  = substr($domain, $at_pos);      // e.g. ".edu"

        return sprintf(
            '<a %s aria-label="Email ' . esc_attr($user) . '" href="#" 
        data-u="%s" 
        data-d="%s" 
        data-da="%s" 
        data-db="%s" 
        class="obf-mailto" 
        onclick="return obfMailto(this)"></a>',
            trim($other_attrs),
            esc_attr($user_r),       // reversed user, for JS
            esc_attr($domain_r),     // reversed domain, for JS
            esc_attr($user),         // plain user, for CSS  e.g. "premedia"
            esc_attr('@' . $domain)  // @ + full domain, for CSS  e.g. "@northwestern.edu"
        );
    }, $html);
}

/**
 * Inject CSS and JS into footer.
 */
add_action('wp_footer', function () { ?>

<?php
/*
 * Reassemble display from two separate attributes.
 * No single attribute contains the full address.
 * data-da = "premedia"
 * data-db = "@northwestern.edu"  <- note the @ is on the domain side
 */
    ?>
<style>
a.obf-mailto::before {
    content: attr(data-da) attr(data-db);
    cursor: pointer;
}
</style>

<script>
function obfMailto(el) {
    // Reverse stored parts to get real address, set href, follow link
    var u = el.dataset.u.split('').reverse().join('');
    var d = el.dataset.d.split('').reverse().join('');
    el.href = 'mailto:' + u + '@' + d;
    return true;
}
</script>

<?php });
