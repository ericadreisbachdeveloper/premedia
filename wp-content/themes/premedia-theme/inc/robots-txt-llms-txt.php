<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


add_filter('robots_txt', 'custom_robots_txt_dbllc', 10, 2);

function custom_robots_txt_dbllc($output, $public)
{
    // Only apply if site is public
    // i.e. "Discourage search engines from indexing this site" unchecked under
    // Settings > Reading
    if ('1' != $public) {
        return $output;
    }

    $custom = $output;

    $custom .= "
# Block markdown files from traditional search engines to avoid duplicate content signal

User-agent: Bingbot
Disallow: /*.md$

User-agent: Googlebot
Disallow: /*.md$


# Block aggressive AI scrapers entirely

User-agent: Amazonbot
Disallow: /

User-agent: Bytespider
Disallow: /

User-agent: cohere-ai
Disallow: /

User-agent: FacebookBot
Disallow: /

User-agent: GPTBot
Disallow: /

User-agent: Meta-ExternalAgent
Disallow: /


# Allow specific AI crawlers with more ethical use patterns

User-agent: anthropic-ai
Allow: /

User-agent: Applebot-Extended
Allow: /

User-agent: ChatGPT-User
Allow: /

User-agent: ClaudeBot
Allow: /

User-agent: Claude-Web
Allow: /

User-agent: Google-Extended
Allow: /

User-agent: PerplexityBot
Allow: /
";

    return $custom;
}




add_action('template_redirect', 'extend_rank_math_llms_txt_dbllc', 1);
function extend_rank_math_llms_txt_dbllc()
{

    // Only handle llms.txt requests
    if ($_SERVER['REQUEST_URI'] !== '/llms.txt') {
        return;
    }

    // Start output buffering to catch Rank Math's output
    ob_start('append_markdown_to_llms_txt_dbllc');
}

function append_markdown_to_llms_txt_dbllc($buffer)
{
    // Add markdown section to the end of llms.txt
    $markdown_section = "\n## Markdown Versions\n";

    $pages = get_posts(['post_type' => 'page', 'numberposts' => -1, 'post_status' => 'publish']);
    foreach ($pages as $page) {

        // Skip if Rank Math is active and has marked this page as noindex
        if (function_exists('rank_math')) {
            $robots_meta = get_post_meta($page->ID, 'rank_math_robots', true);
            if (is_array($robots_meta) && in_array('noindex', $robots_meta)) {
                continue;
            }
        }

        $url = untrailingslashit(get_permalink($page->ID)) . '.md';
        $markdown_section .= "- [" . get_the_title($page->ID) . "](" . $url . ")\n";
    }

    return $buffer . $markdown_section;
}
