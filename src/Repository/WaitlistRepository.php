<?php

declare(strict_types=1);

namespace Restock\Repository;

defined('ABSPATH') || exit;

use Restock\Model\WaitlistSubscription;
use wpdb;

/**
 * Data access for product waitlist subscriptions.
 */
final class WaitlistRepository implements \WPPoland\StorefrontKit\Waitlist\WaitlistRepository
{
    public function __construct(
        private readonly wpdb $wpdb,
    ) {
    }

    public function tableName(): string
    {
        return $this->wpdb->prefix . 'restock_waitlist';
    }

    public function subscribe(int $productId, string $email, ?int $userId): int
    {
        return $this->subscribeWithVariation($productId, 0, $email, $userId);
    }

    public function subscribeWithVariation(int $productId, int $variationId, string $email, ?int $userId): int
    {
        $existing = $this->findByProductVariationAndEmail($productId, $variationId, $email);

        if ($existing !== null) {
            if ($existing->notified) {
                $this->wpdb->update(
                    $this->tableName(),
                    [
                        'user_id' => $userId,
                        'notified' => 0,
                        'created_at' => current_time('mysql', true),
                        'notified_at' => null,
                    ],
                    ['id' => $existing->id],
                    ['%d', '%d', '%s', '%s'],
                    ['%d'],
                );
            }

            return $existing->id;
        }

        $this->wpdb->insert(
            $this->tableName(),
            [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'email' => $email,
                'user_id' => $userId,
                'notified' => 0,
                'created_at' => current_time('mysql', true),
            ],
            ['%d', '%d', '%s', '%d', '%d', '%s'],
        );

        return (int) $this->wpdb->insert_id;
    }

    public function findByProductAndEmail(int $productId, string $email): ?WaitlistSubscription
    {
        return $this->findByProductVariationAndEmail($productId, 0, $email);
    }

    public function findByProductVariationAndEmail(int $productId, int $variationId, string $email): ?WaitlistSubscription
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND variation_id = %d AND email = %s LIMIT 1',
                $this->tableName(),
                $productId,
                $variationId,
                $email,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return $row !== null ? WaitlistSubscription::fromRow($row) : null;
    }

    /**
     * @return list<WaitlistSubscription>
     */
    public function findPendingByProduct(int $productId): array
    {
        return $this->findPendingByProductVariation($productId, 0);
    }

    /**
     * @return list<WaitlistSubscription>
     */
    public function findPendingByProductVariation(int $productId, int $variationId): array
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND variation_id = %d AND notified = 0 ORDER BY created_at ASC',
                $this->tableName(),
                $productId,
                $variationId,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    /**
     * Pending subscriptions to notify when a product or variation returns to stock.
     *
     * @return list<WaitlistSubscription>
     */
    public function findPendingForRestock(\WC_Product $product): array
    {
        if ($product->is_type('variation')) {
            return $this->findPendingByProductVariation($product->get_parent_id(), $product->get_id());
        }

        return $this->findPendingByProduct($product->get_id());
    }

    public function markNotified(int $id): void
    {
        $this->wpdb->update(
            $this->tableName(),
            [
                'notified' => 1,
                'notified_at' => current_time('mysql', true),
            ],
            ['id' => $id],
            ['%d', '%s'],
            ['%d'],
        );
    }

    /**
     * Return all subscriptions ordered newest first.
     *
     * Used by the admin subscriber list page only.
     *
     * @return list<\Restock\Model\WaitlistSubscription>
     */
    public function findAll(): array
    {
        $restock_table = $this->tableName();

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Own plugin table; table name from $wpdb->prefix, cannot be parameterised.
        $rows = $this->wpdb->get_results(
            "SELECT * FROM {$restock_table} ORDER BY created_at DESC", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): \Restock\Model\WaitlistSubscription => \Restock\Model\WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }
}
