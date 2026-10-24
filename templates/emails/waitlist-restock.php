<?php
/**
 * Back-in-stock email (HTML).
 *
 * Override by copying to yourtheme/woocommerce/emails/waitlist-restock.php.
 *
 * @var string           $email_heading
 * @var \WC_Product|null $product
 * @var string           $product_url
 * @var string           $image_url
 * @var string           $intro
 * @var string           $outro
 * @var string           $unsubscribe_url
 * @var string           $additional_content
 * @var bool             $sent_to_admin
 * @var bool             $plain_text
 * @var \WC_Email        $email
 *
 * @package Waitlist/Templates
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

do_action('woocommerce_email_header', $email_heading, $email);
?>

<p><?php echo esc_html($intro); ?></p>

<?php if ($product instanceof \WC_Product) : ?>
    <table cellspacing="0" cellpadding="0" border="0" style="width: 100%; margin: 0 0 24px;" role="presentation">
        <tr>
            <?php if ($image_url !== '') : ?>
                <td style="width: 120px; padding: 0 16px 0 0; vertical-align: top;">
                    <a href="<?php echo esc_url($product_url); ?>"><img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" width="120" style="display: block; width: 120px; height: auto;" /></a>
                </td>
            <?php endif; ?>
            <td style="vertical-align: top;">
                <p style="margin: 0 0 12px; font-weight: bold;"><?php echo esc_html($product->get_name()); ?></p>
                <p style="margin: 0;"><a href="<?php echo esc_url($product_url); ?>"><?php esc_html_e('View product', 'plogins-waitlist'); ?></a></p>
            </td>
        </tr>
    </table>
<?php endif; ?>

<?php if ($outro !== '') : ?>
    <p><?php echo esc_html($outro); ?></p>
<?php endif; ?>

<?php if ($additional_content) : ?>
    <?php echo wp_kses_post(wpautop(wptexturize($additional_content))); ?>
<?php endif; ?>

<?php if ($unsubscribe_url !== '') : ?>
    <p style="font-size: 12px;"><a href="<?php echo esc_url($unsubscribe_url); ?>"><?php esc_html_e('Leave this waitlist', 'plogins-waitlist'); ?></a></p>
<?php endif; ?>

<?php
do_action('woocommerce_email_footer', $email);
