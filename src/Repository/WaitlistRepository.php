<?php

declare(strict_types=1);

namespace Waitlist\Repository;

defined('ABSPATH') || exit;

use Waitlist\Model\WaitlistSubscription;
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
        $existing = $this->findByProductAndEmail($productId, $email);

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
                'email' => $email,
                'user_id' => $userId,
                'notified' => 0,
                'created_at' => current_time('mysql', true),
            ],
            ['%d', '%s', '%d', '%d', '%s'],
        );

        return (int) $this->wpdb->insert_id;
    }

    public function findByProductAndEmail(int $productId, string $email): ?WaitlistSubscription
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND email = %s LIMIT 1',
                $this->tableName(),
                $productId,
                $email,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return $row !== null ? WaitlistSubscription::fromRow($row) : null;
    }

    /**
     * Pending subscribers for a product.
     *
     * `$limit` of 0 keeps the historical unbounded result, which the PRO add-on
     * still relies on. Every caller in this plugin passes a limit.
     *
     * @return list<WaitlistSubscription>
     */
    public function findPendingByProduct(int $productId, int $limit = 0, int $offset = 0): array
    {
        $limit  = max(0, $limit);
        $offset = max(0, $offset);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $sql = $limit > 0
            ? $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND notified = 0 ORDER BY created_at ASC, id ASC LIMIT %d OFFSET %d',
                $this->tableName(),
                $productId,
                $limit,
                $offset,
            )
            : $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND notified = 0 ORDER BY created_at ASC',
                $this->tableName(),
                $productId,
            );

        $rows = $this->wpdb->get_results($sql);
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    /**
     * One batch of pending subscribers, in the same order as
     * findPendingByProduct(), starting after the given cursor.
     *
     * The cursor is (created_at, id) rather than an offset on purpose: the send
     * marks rows notified as it goes, so they leave the result set while the
     * walk is still running, and an offset would skip the rows that slid down.
     * A row whose mail failed stays pending but is still behind the cursor, so
     * the walk cannot loop on it.
     *
     * @return list<WaitlistSubscription>
     */
    public function findPendingBatch(int $productId, int $limit, string $afterCreatedAt = '', int $afterId = 0): array
    {
        $limit          = max(1, $limit);
        $afterCreatedAt = $afterCreatedAt !== '' ? $afterCreatedAt : '1000-01-01 00:00:00';
        $afterId        = max(0, $afterId);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE product_id = %d AND notified = 0'
                . ' AND (created_at > %s OR (created_at = %s AND id > %d))'
                . ' ORDER BY created_at ASC, id ASC LIMIT %d',
                $this->tableName(),
                $productId,
                $afterCreatedAt,
                $afterCreatedAt,
                $afterId,
                $limit,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
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
     * One page of subscriptions, newest first.
     *
     * Used by the admin subscriber list page and the CSV export only.
     *
     * @return list<\Waitlist\Model\WaitlistSubscription>
     */
    public function findAll(int $limit, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d',
                $this->tableName(),
                $limit,
                $offset,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): \Waitlist\Model\WaitlistSubscription => \Waitlist\Model\WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    /**
     * Subscriptions whose email matches the search term, newest first.
     *
     * Used by the admin subscriber list page only.
     *
     * @return list<WaitlistSubscription>
     */
    public function search(string $term, int $limit, int $offset = 0): array
    {
        $like   = '%' . $this->wpdb->esc_like($term) . '%';
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE email LIKE %s ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d',
                $this->tableName(),
                $like,
                $limit,
                $offset,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    /**
     * Active (not yet notified) subscriptions for a logged-in customer.
     *
     * @return list<WaitlistSubscription>
     */
    public function findActiveForAccount(int $userId, string $email): array
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE notified = 0 AND (user_id = %d OR email = %s) ORDER BY created_at DESC',
                $this->tableName(),
                $userId,
                $email,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    public function deleteForAccountOwner(int $subscriptionId, int $userId, string $email): bool
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $deleted = $this->wpdb->query(
            $this->wpdb->prepare(
                'DELETE FROM %i WHERE id = %d AND notified = 0 AND (user_id = %d OR email = %s)',
                $this->tableName(),
                $subscriptionId,
                $userId,
                $email,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return is_int($deleted) && $deleted > 0;
    }

    /**
     * Row counts for the admin list, so the summary and the pager describe the
     * whole filtered set rather than the page that happens to be on screen.
     *
     * @return array{total:int,pending:int,notified:int}
     */
    public function countFiltered(int $productId, string $search): array
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        if ($productId > 0) {
            $sql = $this->wpdb->prepare(
                'SELECT COUNT(*) AS total, SUM(notified = 0) AS pending FROM %i WHERE product_id = %d AND notified = 0',
                $this->tableName(),
                $productId,
            );
        } elseif ($search !== '') {
            $sql = $this->wpdb->prepare(
                'SELECT COUNT(*) AS total, SUM(notified = 0) AS pending FROM %i WHERE email LIKE %s',
                $this->tableName(),
                '%' . $this->wpdb->esc_like($search) . '%',
            );
        } else {
            $sql = $this->wpdb->prepare(
                'SELECT COUNT(*) AS total, SUM(notified = 0) AS pending FROM %i',
                $this->tableName(),
            );
        }

        $row = $this->wpdb->get_row($sql);
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        $total   = is_object($row) ? (int) ($row->total ?? 0) : 0;
        $pending = is_object($row) ? (int) ($row->pending ?? 0) : 0;

        return [
            'total' => $total,
            'pending' => $pending,
            'notified' => max(0, $total - $pending),
        ];
    }

    /**
     * Count pending (not-yet-notified) subscribers for a product.
     *
     * Drives the "N people are waiting" social-proof line on the form.
     */
    public function countPending(int $productId): int
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                'SELECT COUNT(*) FROM %i WHERE product_id = %d AND notified = 0',
                $this->tableName(),
                $productId,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return (int) $count;
    }

    /**
     * Delete a single subscription by its id (admin action).
     */
    public function deleteById(int $subscriptionId): bool
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin table, statement prepared with placeholders.
        $deleted = $this->wpdb->query(
            $this->wpdb->prepare(
                'DELETE FROM %i WHERE id = %d',
                $this->tableName(),
                $subscriptionId,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return is_int($deleted) && $deleted > 0;
    }

    /**
     * @return list<WaitlistSubscription>
     */
    public function findByEmail(string $email, int $limit = 100, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE email = %s ORDER BY id ASC LIMIT %d OFFSET %d',
                $this->tableName(),
                $email,
                $limit,
                $offset,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    /**
     * @return list<WaitlistSubscription>
     */
    public function findByUser(int $userId, int $limit = 100, int $offset = 0): array
    {
        $limit  = max(1, $limit);
        $offset = max(0, $offset);

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                'SELECT * FROM %i WHERE user_id = %d ORDER BY id ASC LIMIT %d OFFSET %d',
                $this->tableName(),
                $userId,
                $limit,
                $offset,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return array_map(
            static fn (object $row): WaitlistSubscription => WaitlistSubscription::fromRow($row),
            is_array($rows) ? $rows : [],
        );
    }

    public function deleteByEmail(string $email): int
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $deleted = $this->wpdb->query(
            $this->wpdb->prepare(
                'DELETE FROM %i WHERE email = %s',
                $this->tableName(),
                $email,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return is_int($deleted) ? $deleted : 0;
    }

    public function deleteByUser(int $userId): int
    {
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter
        $deleted = $this->wpdb->query(
            $this->wpdb->prepare(
                'DELETE FROM %i WHERE user_id = %d',
                $this->tableName(),
                $userId,
            ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,PluginCheck.Security.DirectDB.UnescapedDBParameter

        return is_int($deleted) ? $deleted : 0;
    }
}
