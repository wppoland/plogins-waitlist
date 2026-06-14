<?php
/**
 * My Account waitlists table.
 *
 * @var list<\Restock\Model\WaitlistSubscription> $restock_subscriptions
 * @var array<string, mixed>                  $restock_settings
 *
 * @package Restock/Templates
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$restock_title = (string) ($restock_settings['account_title'] ?? __('My waitlists', 'restock'));
$restock_intro = (string) ($restock_settings['account_intro_text'] ?? '');
$restock_empty = (string) ($restock_settings['account_empty_text'] ?? __('You are not on any waitlists yet.', 'restock'));
$restock_unsubscribe_label = (string) ($restock_settings['unsubscribe_button_text'] ?? __('Leave waitlist', 'restock'));

$restock_stock_labels = [
    'instock'     => __('In stock', 'restock'),
    'outofstock' => __('Out of stock', 'restock'),
    'onbackorder' => __('On backorder', 'restock'),
];
?>
<div class="restock-account-waitlists">
    <h2><?php echo esc_html($restock_title); ?></h2>

    <?php if ($restock_intro !== '') : ?>
        <p class="restock-account-waitlists__intro"><?php echo esc_html($restock_intro); ?></p>
    <?php endif; ?>

    <?php if ($restock_subscriptions === []) : ?>
        <p class="restock-account-waitlists__empty"><?php echo esc_html($restock_empty); ?></p>
    <?php else : ?>
        <table class="shop_table restock-account-waitlists__table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Product', 'restock'); ?></th>
                    <th><?php esc_html_e('Stock', 'restock'); ?></th>
                    <th><?php esc_html_e('Subscribed', 'restock'); ?></th>
                    <th><span class="screen-reader-text"><?php esc_html_e('Actions', 'restock'); ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($restock_subscriptions as $restock_subscription) : ?>
                    <?php
                    $restock_product = wc_get_product($restock_subscription->productId);

                    if (! $restock_product instanceof \WC_Product) {
                        continue;
                    }

                    $restock_stock = $restock_product->get_stock_status();
                    $restock_stock_label = $restock_stock_labels[$restock_stock] ?? ucfirst(str_replace('_', ' ', $restock_stock));
                    ?>
                    <tr data-restock-subscription-row="<?php echo esc_attr((string) $restock_subscription->id); ?>">
                        <td>
                            <a href="<?php echo esc_url(get_permalink($restock_subscription->productId) ?: ''); ?>">
                                <?php echo esc_html($restock_product->get_name()); ?>
                            </a>
                        </td>
                        <td><?php echo esc_html($restock_stock_label); ?></td>
                        <td>
                            <time datetime="<?php echo esc_attr($restock_subscription->createdAt->format(DATE_ATOM)); ?>">
                                <?php echo esc_html(wp_date(get_option('date_format', 'F j, Y'), $restock_subscription->createdAt->getTimestamp())); ?>
                            </time>
                        </td>
                        <td>
                            <button
                                type="button"
                                class="button restock-account-waitlists__unsubscribe"
                                data-restock-unsubscribe
                                data-subscription-id="<?php echo esc_attr((string) $restock_subscription->id); ?>"
                            >
                                <?php echo esc_html($restock_unsubscribe_label); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="restock-account-waitlists__message" data-restock-account-message hidden role="status" aria-live="polite"></p>
    <?php endif; ?>
</div>
