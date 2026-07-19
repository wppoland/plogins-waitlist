<?php

declare(strict_types=1);

namespace Waitlist\Admin;

defined('ABSPATH') || exit;

use Waitlist\Contract\HasHooks;
use Waitlist\Repository\WaitlistRepository;

/**
 * Admin page for viewing and exporting waitlist subscribers.
 *
 * Registered as WooCommerce → Waitlist → Subscribers.
 */
final class Subscribers implements HasHooks
{
    private const PAGE = 'restock-subscribers';
    private const NONCE_EXPORT = 'restock_export_subscribers';
    private const NONCE_DELETE = 'restock_delete_subscriber';

    public function __construct(
        private readonly WaitlistRepository $repository,
    ) {
    }

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_init', [$this, 'maybeExportCsv']);
        add_action('admin_init', [$this, 'maybeDelete']);
    }

    /**
     * Handle a single-subscriber delete from the list (nonce-protected).
     */
    public function maybeDelete(): void
    {
        if (
            ! isset($_GET['restock_delete'], $_GET['_wpnonce']) ||
            ! current_user_can('manage_woocommerce')
        ) {
            return;
        }

        $id = absint($_GET['restock_delete']);

        if (! wp_verify_nonce(sanitize_key($_GET['_wpnonce']), self::NONCE_DELETE . '_' . $id)) {
            wp_die(esc_html__('Security check failed.', 'plogins-waitlist'));
        }

        if ($id > 0) {
            $this->repository->deleteById($id);
        }

        wp_safe_redirect(
            add_query_arg(
                'restock_deleted',
                $id > 0 ? '1' : '0',
                admin_url('admin.php?page=' . self::PAGE),
            ),
        );
        exit;
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'restock-settings',
            __('Waitlist Subscribers', 'plogins-waitlist'),
            __('Subscribers', 'plogins-waitlist'),
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
            wp_die(esc_html__('Security check failed.', 'plogins-waitlist'));
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

        // Neutralize spreadsheet formula injection (CWE-1236): a cell starting
        // with = + - @ (or a control char) is evaluated by Excel/Sheets. The
        // email comes from an unauthenticated signup, so prefix such cells.
        $csvCell = static function ($value): string {
            $s = (string) $value;
            return $s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'" . $s : $s;
        };

        foreach ($rows as $sub) {
            fputcsv($out, array_map($csvCell, [
                $sub->id,
                $sub->productId,
                $sub->email,
                $sub->userId ?? '',
                $sub->notified ? 'yes' : 'no',
                $sub->createdAt->format('Y-m-d H:i:s'),
                $sub->notifiedAt !== null ? $sub->notifiedAt->format('Y-m-d H:i:s') : '',
            ]));
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

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only list filters (GET), no state change.
        $productId = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
        $search    = isset($_GET['s']) ? sanitize_text_field(wp_unslash((string) $_GET['s'])) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ($productId > 0) {
            $rows = $this->repository->findPendingByProduct($productId);
        } elseif ('' !== $search) {
            $rows = $this->repository->search($search);
        } else {
            $rows = $this->repository->findAll();
        }

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
            <h1><?php esc_html_e('Waitlist Subscribers', 'plogins-waitlist'); ?></h1>

            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only flash flag after a nonce-checked redirect.
            if (isset($_GET['restock_deleted']) && '1' === $_GET['restock_deleted']) :
                ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Subscriber removed from the waitlist.', 'plogins-waitlist'); ?></p></div>
            <?php endif; ?>

            <p class="restock-admin__lead">
                <?php esc_html_e('Everyone who asked to be notified when an out-of-stock product returns. When you restock a product, pending subscribers are emailed automatically and move to "Notified".', 'plogins-waitlist'); ?>
            </p>

            <?php if ($filteredProduct instanceof \WC_Product) : ?>
                <div class="notice notice-info inline">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: product name. */
                            esc_html__('Showing waiting subscribers for: %s', 'plogins-waitlist'),
                            '<strong>' . esc_html($filteredProduct->get_name()) . '</strong>',
                        );
                        ?>
                        &nbsp;
                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE)); ?>">
                            <?php esc_html_e('Show all subscribers', 'plogins-waitlist'); ?>
                        </a>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (! empty($rows)) : ?>
                <div class="restock-subscribers__stats">
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($total)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Total', 'plogins-waitlist'); ?></span>
                    </div>
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($pending)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Waiting', 'plogins-waitlist'); ?></span>
                    </div>
                    <div class="restock-stat">
                        <span class="restock-stat__value"><?php echo esc_html(number_format_i18n($notified)); ?></span>
                        <span class="restock-stat__label"><?php esc_html_e('Notified', 'plogins-waitlist'); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <div class="restock-subscribers__toolbar">
                <a href="<?php echo esc_url($exportUrl); ?>" class="button button-secondary">
                    <span class="dashicons dashicons-download" aria-hidden="true" style="vertical-align:text-top;"></span>
                    <?php esc_html_e('Export CSV', 'plogins-waitlist'); ?>
                </a>
                <?php if (0 === $productId) : ?>
                    <form method="get" class="restock-subscribers__search">
                        <input type="hidden" name="page" value="<?php echo esc_attr(self::PAGE); ?>" />
                        <label class="screen-reader-text" for="restock-subscriber-search"><?php esc_html_e('Search subscribers by email', 'plogins-waitlist'); ?></label>
                        <input type="search" id="restock-subscriber-search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php esc_attr_e('Search by email', 'plogins-waitlist'); ?>" />
                        <button type="submit" class="button"><?php esc_html_e('Search', 'plogins-waitlist'); ?></button>
                        <?php if ('' !== $search) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE)); ?>" class="button-link"><?php esc_html_e('Clear', 'plogins-waitlist'); ?></a>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($rows) && '' !== $search) : ?>
                <div class="restock-empty">
                    <h2 class="restock-empty__title"><?php esc_html_e('No matching subscribers', 'plogins-waitlist'); ?></h2>
                    <p class="restock-empty__text">
                        <?php
                        printf(
                            /* translators: %s: search term. */
                            esc_html__('No subscribers match "%s".', 'plogins-waitlist'),
                            esc_html($search),
                        );
                        ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::PAGE)); ?>" class="button button-secondary">
                            <?php esc_html_e('Show all subscribers', 'plogins-waitlist'); ?>
                        </a>
                    </p>
                </div>
            <?php elseif (empty($rows)) : ?>
                <div class="restock-empty">
                    <div class="restock-empty__icon" aria-hidden="true">&#128235;</div>
                    <h2 class="restock-empty__title"><?php esc_html_e('No subscribers yet', 'plogins-waitlist'); ?></h2>
                    <p class="restock-empty__text">
                        <?php esc_html_e('When a product is out of stock, shoppers can join its waitlist from the product page. Their requests will appear here, and they will be emailed automatically as soon as you restock.', 'plogins-waitlist'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=restock-settings')); ?>" class="button button-primary">
                            <?php esc_html_e('Review waitlist settings', 'plogins-waitlist'); ?>
                        </a>
                    </p>
                </div>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('ID', 'plogins-waitlist'); ?></th>
                            <th><?php esc_html_e('Product', 'plogins-waitlist'); ?></th>
                            <th><?php esc_html_e('Email', 'plogins-waitlist'); ?></th>
                            <th><?php esc_html_e('Notified', 'plogins-waitlist'); ?></th>
                            <th><?php esc_html_e('Subscribed', 'plogins-waitlist'); ?></th>
                            <th><?php esc_html_e('Actions', 'plogins-waitlist'); ?></th>
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
                                        <span class="restock-badge restock-badge--yes"><?php esc_html_e('Notified', 'plogins-waitlist'); ?></span>
                                    <?php else : ?>
                                        <span class="restock-badge restock-badge--no"><?php esc_html_e('Waiting', 'plogins-waitlist'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($sub->createdAt->format('Y-m-d H:i')); ?></td>
                                <td>
                                    <?php
                                    $deleteUrl = wp_nonce_url(
                                        add_query_arg('restock_delete', $sub->id, admin_url('admin.php?page=' . self::PAGE)),
                                        self::NONCE_DELETE . '_' . $sub->id,
                                    );
                                    ?>
                                    <a
                                        href="<?php echo esc_url($deleteUrl); ?>"
                                        class="restock-row-delete"
                                        onclick="return confirm('<?php echo esc_js(__('Remove this subscriber from the waitlist?', 'plogins-waitlist')); ?>');"
                                        aria-label="<?php echo esc_attr(sprintf(/* translators: %s: subscriber email. */ __('Remove %s', 'plogins-waitlist'), $sub->email)); ?>"
                                    >
                                        <?php esc_html_e('Remove', 'plogins-waitlist'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}
