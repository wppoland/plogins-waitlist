<?php
/**
 * Waitlist form.
 *
 * @var \WC_Product          $restock_product
 * @var array<string, mixed> $restock_settings
 * @var string               $restock_email
 *
 * @package Restock/Templates
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$restock_heading = (string) ($restock_settings['title'] ?? '');
$restock_intro   = (string) ($restock_settings['intro_text'] ?? '');
$restock_show_heading = ! empty($restock_settings['show_title']) && $restock_heading !== '';
$restock_show_intro   = ! empty($restock_settings['show_intro']) && $restock_intro !== '';

$restock_email_label = (string) ($restock_settings['email_label'] ?? __('Email address', 'restock'));
$restock_button_text = (string) ($restock_settings['button_text'] ?? __('Join Waitlist', 'restock'));
?>
<div class="restock-waitlist" data-restock-waitlist>
    <?php if ($restock_show_heading) : ?>
        <h3 class="restock-waitlist__heading"><?php echo esc_html($restock_heading); ?></h3>
    <?php endif; ?>
    <?php if ($restock_show_intro) : ?>
        <p class="restock-waitlist__intro"><?php echo esc_html($restock_intro); ?></p>
    <?php endif; ?>
    <form class="restock-waitlist-form" novalidate>
        <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $restock_product->get_id()); ?>" />
        <label>
            <span class="screen-reader-text"><?php echo esc_html($restock_email_label); ?></span>
            <input
                type="email"
                name="email"
                value="<?php echo esc_attr($restock_email); ?>"
                placeholder="<?php echo esc_attr((string) ($restock_settings['email_placeholder'] ?? __('Your email address', 'restock'))); ?>"
                autocomplete="email"
                inputmode="email"
                required
            />
        </label>
        <label class="restock-waitlist__privacy">
            <input type="checkbox" name="privacy" value="1" required />
            <span><?php echo esc_html((string) ($restock_settings['privacy_label'] ?? __('I consent to receiving back-in-stock notifications.', 'restock'))); ?></span>
        </label>
        <button type="submit" class="button alt" data-busy-label="<?php echo esc_attr__('Sending…', 'restock'); ?>"><?php echo esc_html($restock_button_text); ?></button>
        <p class="restock-waitlist__message" data-restock-waitlist-message hidden></p>
    </form>
</div>
