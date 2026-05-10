<?php

if (!defined('ABSPATH')) {
    exit;
}    // Exit if accessed directly


/**
 * Obfuscate email addresses
 */
add_action('template_redirect', 'start_email_obfuscation_buffer_dbllc');

function start_email_obfuscation_buffer_dbllc()
{
    ob_start('obfuscate_all_emails_dbllc');
}


add_action('shutdown', 'end_email_obfuscation_buffer_dbllc', 0);

function end_email_obfuscation_buffer_dbllc()
{
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
}

function obfuscate_all_emails_dbllc($html)
{
    // Match emails in mailto links
    $html = preg_replace_callback(
        '/href=["\']mailto:([^"\']+)["\']/',
        function ($matches) {
            $email = $matches[1];
            $encoded = '';
            for ($i = 0; $i < strlen($email); $i++) {
                $encoded .= '&#' . ord($email[$i]) . ';';
            }
            return 'href="mailto:' . $encoded . '"';
        },
        $html
    );

    // Match plain email text
    $html = preg_replace_callback(
        '/([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
        function ($matches) {
            // Don't encode if it's already inside an HTML tag
            return '&#' . implode(';&#', array_map('ord', str_split($matches[1]))) . ';';
        },
        $html
    );

    return $html;
}
