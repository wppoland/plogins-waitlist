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
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Restock Subscribers', 'restock'); ?></h1>

            <p>
                <a href="<?php echo esc_url($exportUrl); ?>" class="button">
                    <?php esc_html_e('Export CSV', 'restock'); ?>
                </a>
            </p>

            <?php if (empty($rows)) : ?>
                <p><?php esc_html_e('No subscribers yet.', 'restock'); ?></p>
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
                                    <?php
                                    echo $sub->notified
                                        ? esc_html__('Yes', 'restock')
                                        : esc_html__('No', 'restock');
                                    ?>
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
