<?php

declare(strict_types=1);

/**
 * Storefront wiring guard, from the live review of 2026-10-02.
 *
 * Run: php tests/storefront-wiring-test.php
 *
 * Three shopper-facing controls were inert on a real site while every gate
 * stayed green:
 * - variations: the form only appeared when the availability text contained
 *   the English words "out of stock", and never for a variation on backorder;
 * - the shortcode off a product page printed a form with no script;
 * - "Leave waitlist" in My Account never loaded its script, and its handler
 *   read the nonce from a field the script does not send.
 *
 * Plain PHP, same as tests/batching-test.php.
 */

define('ABSPATH', __DIR__ . '/');

$failures = 0;

function check(string $label, bool $ok): void
{
    global $failures;

    if (! $ok) {
        ++$failures;
        echo "FAIL  {$label}\n";
        return;
    }

    echo "ok    {$label}\n";
}

$GLOBALS['enqueued'] = [];

function add_action(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function add_filter(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function is_product(): bool
{
    return false;
}

function wp_enqueue_style(string $handle, ...$rest): void
{
    $GLOBALS['enqueued'][] = 'style:' . $handle;
}

function wp_enqueue_script(string $handle, ...$rest): void
{
    $GLOBALS['enqueued'][] = 'script:' . $handle;
}

function wp_localize_script(string $handle, string $name, array $data): bool
{
    $GLOBALS['enqueued'][] = 'config:' . $name;

    return true;
}

function admin_url(string $path = ''): string
{
    return 'https://example.test/wp-admin/' . $path;
}

function wp_create_nonce($action = -1): string
{
    return 'nonce';
}

class WC_Product
{
    public function __construct(private string $status, private string $type = 'variation')
    {
    }

    public function is_type(string $type): bool
    {
        return $this->type === $type;
    }

    public function is_in_stock(): bool
    {
        return $this->status !== 'outofstock';
    }

    public function get_stock_status(): string
    {
        return $this->status;
    }
}

require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistRepository.php';
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistEngine.php';

use WPPoland\StorefrontKit\Waitlist\WaitlistEngine;

$repository = new class implements WPPoland\StorefrontKit\Waitlist\WaitlistRepository {
    public function subscribe(int $productId, string $email, ?int $userId): int
    {
        return 0;
    }

    public function findPendingByProduct(int $productId): iterable
    {
        return [];
    }

    public function findPendingBatch(int $productId, int $limit, string $afterCreatedAt = '', int $afterId = 0): iterable
    {
        return [];
    }

    public function markNotified(int $id): void
    {
    }
};

$engine = new WaitlistEngine(
    repository: $repository,
    ajaxAction: 'restock_waitlist_subscribe',
    nonceAction: 'restock_waitlist',
    scriptObjectName: 'restockWaitlist',
    assetHandle: 'restock-waitlist',
    styleUrl: '',
    scriptUrl: '',
    version: '0',
    templateName: 'single-product/waitlist-form',
    defaultMessages: [],
    isEnabled: static fn (): bool => true,
    settings: static fn (): array => [],
    renderTemplate: static function (string $template, array $data): void {
    },
);

$parent = new WC_Product('instock', 'variable');
$flag = static fn (string $status): mixed => method_exists($engine, 'flagVariation')
    ? ($engine->flagVariation([], $parent, new WC_Product($status))['restock_waitlistable'] ?? null)
    : null;

check('out-of-stock variation takes signups', $flag('outofstock') === true);
check('variation on backorder takes signups, as the handler does', $flag('onbackorder') === true);
check('in-stock variation does not', $flag('instock') === false);

if (method_exists($engine, 'enqueueFormAssets')) {
    $engine->enqueueFormAssets();
}
check(
    'form assets load off a product page',
    in_array('script:restock-waitlist', $GLOBALS['enqueued'], true) && in_array('config:restockWaitlist', $GLOBALS['enqueued'], true),
);

$js = (string) file_get_contents(__DIR__ . '/../assets/js/waitlist.js');
check('script reads the server flag', str_contains($js, 'restock_waitlistable'));
check('script does not match translated availability text', ! str_contains(strtolower($js), "'out of stock'"));
check('unsubscribe request sends the nonce as `nonce`', (bool) preg_match('/unsubscribeAction,\s*nonce: config\.nonce/', $js));

$service = (string) file_get_contents(__DIR__ . '/../src/Service/WaitlistService.php');
check('unsubscribe handler reads the nonce from `nonce`', str_contains($service, "check_ajax_referer('restock_waitlist', 'nonce')"));
check('account assets are not gated on is_wc_endpoint_url()', ! str_contains($service, '! is_wc_endpoint_url('));
check('shortcode loads the form assets', (bool) preg_match('/function renderShortcode.*?enqueueFormAssets\(\)/s', $service));

echo $failures === 0 ? "\nAll checks passed.\n" : "\n{$failures} check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
