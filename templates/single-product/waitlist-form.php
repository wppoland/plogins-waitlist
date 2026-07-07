<?php
/**
 * Waitlist form.
 *
 * @var \WC_Product          $restock_product
 * @var array<string, mixed> $restock_settings
 * @var string               $restock_email
 *
 * @package Waitlist/Templates
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$restock_heading = (string) ($restock_settings['title'] ?? '');
$restock_intro   = (string) ($restock_settings['intro_text'] ?? '');
$restock_show_heading = ! empty($restock_settings['show_title']) && $restock_heading !== '';
$restock_show_intro   = ! empty($restock_settings['show_intro']) && $restock_intro !== '';

$restock_email_label = (string) ($restock_settings['email_label'] ?? __('Email address', 'plogins-waitlist'));
$restock_button_text = (string) ($restock_settings['button_text'] ?? __('Join Waitlist', 'plogins-waitlist'));
$restock_is_variable = $restock_product->is_type('variable');
$restock_variation_prompt = (string) ($restock_settings['variation_prompt_text'] ?? __('Select options above, then join the waitlist when that variation is unavailable.', 'plogins-waitlist'));

$restock_waiting_count = isset($restock_waiting_count) ? (int) $restock_waiting_count : 0;
$restock_show_social_proof = ! empty($restock_settings['show_social_proof']) && $restock_waiting_count > 0;
$restock_social_proof_custom = (string) ($restock_settings['social_proof_text'] ?? '');
if ($restock_show_social_proof) {
    $restock_social_proof_line = $restock_social_proof_custom !== ''
        ? str_replace('{count}', number_format_i18n($restock_waiting_count), $restock_social_proof_custom)
        : sprintf(
            /* translators: %s: number of shoppers already on the waitlist. */
            _n(
                '%s shopper is already waiting for this item.',
                '%s shoppers are already waiting for this item.',
                $restock_waiting_count,
                'plogins-waitlist'
            ),
            number_format_i18n($restock_waiting_count)
        );
}
?>
<div
    class="restock-waitlist"
    data-restock-waitlist
    data-restock-variable="<?php echo $restock_is_variable ? '1' : '0'; ?>"
    <?php if ($restock_is_variable) : ?>
        hidden
        data-restock-parent-id="<?php echo esc_attr((string) $restock_product->get_id()); ?>"
    <?php endif; ?>
>
    <?php if ($restock_show_heading) : ?>
        <h3 class="restock-waitlist__heading"><?php echo esc_html($restock_heading); ?></h3>
    <?php endif; ?>
    <?php if ($restock_show_intro) : ?>
        <p class="restock-waitlist__intro"><?php echo esc_html($restock_intro); ?></p>
    <?php endif; ?>
    <?php if ($restock_show_social_proof) : ?>
        <p class="restock-waitlist__social-proof" data-restock-social-proof>
            <span class="restock-waitlist__social-proof-dot" aria-hidden="true"></span>
            <?php echo esc_html($restock_social_proof_line); ?>
        </p>
    <?php endif; ?>
    <?php if ($restock_is_variable) : ?>
        <p class="restock-waitlist__variation-prompt"><?php echo esc_html($restock_variation_prompt); ?></p>
    <?php endif; ?>
    <form class="restock-waitlist-form" novalidate>
        <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $restock_product->get_id()); ?>" data-restock-product-id />
        <label>
            <span class="screen-reader-text"><?php echo esc_html($restock_email_label); ?></span>
            <input
                type="email"
                name="email"
                value="<?php echo esc_attr($restock_email); ?>"
                placeholder="<?php echo esc_attr((string) ($restock_settings['email_placeholder'] ?? __('Your email address', 'plogins-waitlist'))); ?>"
                autocomplete="email"
                inputmode="email"
                required
            />
        </label>
        <label class="restock-waitlist__privacy">
            <input type="checkbox" name="privacy" value="1" required />
            <span><?php echo esc_html((string) ($restock_settings['privacy_label'] ?? __('I consent to receiving back-in-stock notifications.', 'plogins-waitlist'))); ?></span>
        </label>
        <button type="submit" class="button alt" data-busy-label="<?php echo esc_attr__('Sending…', 'plogins-waitlist'); ?>"><?php echo esc_html($restock_button_text); ?></button>
        <p class="restock-waitlist__message" data-restock-waitlist-message hidden></p>
    </form>
</div>
