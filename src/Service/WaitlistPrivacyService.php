<?php

declare(strict_types=1);

namespace Waitlist\Service;

use Waitlist\Contract\HasHooks;
use Waitlist\Model\WaitlistSubscription;
use Waitlist\Repository\WaitlistRepository;
use WP_User;

defined('ABSPATH') || exit;

/**
 * Wires WordPress Personal Data Exporters and Erasers for Waitlist subscriptions.
 */
final class WaitlistPrivacyService implements HasHooks
{
    private const PAGE_SIZE = 100;

    public function __construct(
        private readonly WaitlistRepository $repository,
    ) {
    }

    public function registerHooks(): void
    {
        add_filter('wp_privacy_personal_data_exporters', [$this, 'registerExporters']);
        add_filter('wp_privacy_personal_data_erasers', [$this, 'registerErasers']);
    }

    /**
     * @param array<string, array<string, mixed>> $exporters
     * @return array<string, array<string, mixed>>
     */
    public function registerExporters(array $exporters): array
    {
        $exporters['restock-waitlist'] = [
            'exporter_friendly_name' => __('Restock Waitlist Subscriptions', 'restock'),
            'callback'               => [$this, 'exportWaitlist'],
        ];

        return $exporters;
    }

    /**
     * @param array<string, array<string, mixed>> $erasers
     * @return array<string, array<string, mixed>>
     */
    public function registerErasers(array $erasers): array
    {
        $erasers['restock-waitlist'] = [
            'eraser_friendly_name' => __('Restock Waitlist Subscriptions', 'restock'),
            'callback'             => [$this, 'eraseWaitlist'],
        ];

        return $erasers;
    }

    /**
     * @return array{data: list<array<string, mixed>>, done: bool}
     */
    public function exportWaitlist(string $email, int $page = 1): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * self::PAGE_SIZE;

        $items = [];
        $emailRows = $this->repository->findByEmail($email, self::PAGE_SIZE, $offset);

        foreach ($emailRows as $sub) {
            $items[] = $this->formatSubscription($sub);
        }

        $user = get_user_by('email', $email);
        $userCount = 0;
        if ($user instanceof WP_User) {
            $userRows = $this->repository->findByUser((int) $user->ID, self::PAGE_SIZE, $offset);
            $userCount = count($userRows);
            foreach ($userRows as $sub) {
                if (strtolower($sub->email) === strtolower($email)) {
                    continue;
                }
                $items[] = $this->formatSubscription($sub);
            }
        }

        $done = count($emailRows) < self::PAGE_SIZE && $userCount < self::PAGE_SIZE;

        return [
            'data' => $items,
            'done' => $done,
        ];
    }

    /**
     * @return array{items_removed: int, items_retained: int, messages: list<string>, done: bool}
     */
    public function eraseWaitlist(string $email, int $page = 1): array
    {
        $removed = $this->repository->deleteByEmail($email);

        $user = get_user_by('email', $email);
        if ($user instanceof WP_User) {
            $removed += $this->repository->deleteByUser((int) $user->ID);
        }

        return [
            'items_removed'  => $removed,
            'items_retained' => 0,
            'messages'       => [],
            'done'           => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatSubscription(WaitlistSubscription $sub): array
    {
        $product = function_exists('wc_get_product') ? wc_get_product($sub->productId) : null;
        $productName = $product ? $product->get_name() : sprintf(__('Product #%d', 'restock'), $sub->productId);

        return [
            'group_id'    => 'restock-waitlist',
            'group_label' => __('Restock Waitlist Subscriptions', 'restock'),
            'item_id'     => 'waitlist-' . $sub->id,
            'data'        => [
                ['name' => __('Product ID', 'restock'), 'value' => (string) $sub->productId],
                ['name' => __('Product', 'restock'), 'value' => $productName],
                ['name' => __('Subscribed At', 'restock'), 'value' => (string) $sub->createdAt],
                ['name' => __('Notified', 'restock'), 'value' => $sub->notified ? __('Yes', 'restock') : __('No', 'restock')],
                ['name' => __('Notified At', 'restock'), 'value' => $sub->notifiedAt ?? '—'],
            ],
        ];
    }
}
