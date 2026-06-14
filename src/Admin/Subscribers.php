<?php

declare(strict_types=1);

namespace Restock\Admin;

defined('ABSPATH') || exit;

use Restock\Contract\HasHooks;
use Restock\Repository\WaitlistRepository;

/**
 * Admin page for viewing and exporting waitlist subscribers.
 *
 * Registered as WooCommerce → Restock → Subscribers.
 */
final class Subscribers implements HasHooks
{
    private const PAGE = 'restock-subscribers';
    private const NONCE_EXPORT = 'restock_export_subscribers';

    public function __construct(
        private readonly WaitlistRepository $repository,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'maybeExportCsv']);
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'restock-settings',
            __('Restock Subscribers', 'restock'),
            __('Subscribers', 'restock'),
            'manage_woocommerce',
            self::PAGE,
            [$this, 'renderPage'],
        );
    }

    public function maybeExportCsv(): void
    {
        if (
            ! isset($_GET['restock_export']) ||
            ! isset($_GET['_wpnonce']) ||
            ! current_user_can('manage_woocommerce')
        ) {
            return;
        }

        if (! wp_verify_nonce(sanitize_key($_GET['_wpnonce']), self::NONCE_EXPORT)) {
            wp_die(esc_html__('Security check failed.', 'restock'));
        }

        $productId = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
        $rows = $productId > 0
            ? $this->repository->findPendingByProduct($productId)
            : $this->repository->findAll();

        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="restock-subscribers.csv"');

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- Writing CSV to php://output; WP_Filesystem is for files, not the output stream.
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }

        fputcsv($out, ['ID', 'Product ID', 'Email', 'User ID', 'Notified', 'Created At', 'Notified At']);

        foreach ($rows as $sub) {
            fputcsv($out, [
                $sub->id,
                $sub->productId,
                $sub->email,
                $sub->userId ?? '',
                $sub->notified ? 'yes' : 'no',
                $sub->createdAt->format('Y-m-d H:i:s'),
                $sub->notifiedAt !== null ? $sub->notifiedAt->format('Y-m-d H:i:s') : '',
            ]);
        }

        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Writing CSV to php://output; WP_Filesystem is for files, not the output stream.
        fclose($out);
        exit;
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $productId = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $rows = $productId > 0
            ? $this->repository->findPendingByProduct($productId)
            : $this->repository->findAll();

        $exportUrl = wp_nonce_url(
            add_query_arg(
                [
                    'restock_export' => '1',
                    'product_id'     => $productId ?: '',
                ],
                admin_url('admin.php?page=' . self::PAGE),
            ),
            self::NONCE_EXPORT,
        );

        // Summary stats computed from the already-fetched rows (no extra query).
        $total    = count($rows);
        $pending  = 0;
        $notified = 0;
        foreach ($rows as $sub) {
            if ($sub->notified) {
                ++$notified;
            } else {
                ++$pending;
            }
        }

        $filteredProduct = $productId > 0 ? wc_get_product($productId) : null;
        ?>
        <div class="wrap restock-admin">
            <h1><?php esc_html_e('Restock Subscribers', 'restock'); ?></h1>

            <p class="restock-admin__lead">
                <?php esc_html_e('Everyone who asked to be notified when an out-of-stock product returns. When you restock a product, pending subscribers are emailed automatically and move to "Notified".', 'restock'); ?>
            </p>

            <?php if ($filteredProduct instanceof \WC_Product) : ?>
                <div class="notice notice-info inline">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: product name. */
                            esc_html__('Showing waiting subscribers for: %s', 'restock'),
                            '<strong>' . esc_html($filteredProduct->get_name()) . '</strong>',
                        );
                        ?>
                        &nbsp;
                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE)); ?>">
                            <?php esc_html_e('Show all subscribers', 'restock'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (! empty($rows)) : ?>
                <div class="restock-subscribers__stats">
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($total)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Total', 'restock'); ?></span>
                    </div>
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($pending)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Waiting', 'restock'); ?></span>
                    </div>
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($notified)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Notified', 'restock'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="restock-subscribers__toolbar">
                <a href="<?php echo esc_url($exportUrl); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download" aria-hidden="true" style="vertical-align:text-top;"></span>
                    <?php esc_html_e('Export CSV', 'restock'); ?>
                </a>
            </div>

            <?php if (empty($rows)) : ?>
                <div class="restock-empty">
                    <div class="restock-empty__icon" aria-hidden="true">&#128235;</div>
                    <h2 class="restock-empty__title"><?php esc_html_e('No subscribers yet', 'restock'); ?></h2>
                    <p class="restock-empty__text">
                        <?php esc_html_e('When a product is out of stock, shoppers can join its waitlist from the product page. Their requests will appear here, and they will be emailed automatically as soon as you restock.', 'restock'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=restock-settings')); ?>" class="button button-primary">
                            <?php esc_html_e('Review waitlist settings', 'restock'); ?>
                        </a>
                    </p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'restock'); ?></th>
                            <th><?php esc_html_e('Product', 'restock'); ?></th>
                            <th><?php esc_html_e('Email', 'restock'); ?></th>
                            <th><?php esc_html_e('Notified', 'restock'); ?></th>
                            <th><?php esc_html_e('Subscribed', 'restock'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $sub) : ?>
                            <tr>
                                <td><?php echo esc_html((string) $sub->id); ?></td>
                                <td>
                                    <?php
                                    $product = wc_get_product($sub->productId);
                                    if ($product instanceof \WC_Product) {
                                        printf(
                                            '<a href="%s">%s</a>',
                                            esc_url((string) get_edit_post_link($sub->productId)),
                                            esc_html($product->get_name()),
                                        );
                                    } else {
                                        echo esc_html((string) $sub->productId);
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html($sub->email); ?></td>
                                <td>
                                    <?php if ($sub->notified) : ?>
                                        <span class="restock-badge restock-badge--yes"><?php esc_html_e('Notified', 'restock'); ?></span>
                                    <?php else : ?>
                                        <span class="restock-badge restock-badge--no"><?php esc_html_e('Waiting', 'restock'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($sub->createdAt->format('Y-m-d H:i')); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}
