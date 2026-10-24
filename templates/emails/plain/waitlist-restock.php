<?php
/**
 * Back-in-stock email (plain text).
 *
 * Override by copying to yourtheme/woocommerce/emails/plain/waitlist-restock.php.
 *
 * @var string           $email_heading
 * @var \WC_Product|null $product
 * @var string           $product_url
 * @var string           $intro
 * @var string           $outro
 * @var string           $unsubscribe_url
 * @var string           $additional_content
 *
 * @package Waitlist/Templates
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

echo esc_html(wp_strip_all_tags($email_heading)) . "\n\n";
echo esc_html($intro) . "\n\n";

if ($product instanceof \WC_Product) {
    echo esc_html($product->get_name()) . "\n";
    echo esc_url_raw($product_url) . "\n\n";
}

if ($outro !== '') {
    echo esc_html($outro) . "\n\n";
}

if ($additional_content) {
    echo esc_html(wp_strip_all_tags(wptexturize($additional_content))) . "\n\n";
}

if ($unsubscribe_url !== '') {
    /* translators: %s: link that removes the address from this waitlist. */
    echo esc_html(sprintf(__('Leave this waitlist: %s', 'plogins-waitlist'), esc_url_raw($unsubscribe_url))) . "\n\n";
}

echo esc_html(wp_strip_all_tags(preg_replace('/<br\s*\/?>/i', "\n", wptexturize((string) apply_filters('woocommerce_email_footer_text', get_option('woocommerce_email_footer_text'))))));
