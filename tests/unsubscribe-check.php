<?php

declare(strict_types=1);

/**
 * Guest unsubscribe + sending handoff guard.
 *
 * Run: php tests/unsubscribe-check.php
 *
 * Fails if the "leave this waitlist" token accepts a tampered token, another
 * subscription's token or another address, if opening the link (GET) deletes
 * anything, if the confirm POST stops deleting, if an invalid link answers
 * differently for a missing row than for a wrong token, if the per-IP limit
 * stops applying, or if the engine stops handing each mail to the configured
 * sender while keeping the hooks Plogins Waitlist PRO relies on
 * (notifySubscribers on both stock actions, `plogins_waitlist_should_notify`).
 */

define('ABSPATH', __DIR__ . '/');
define('MINUTE_IN_SECONDS', 60);

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

// --- WordPress / WooCommerce stubs -----------------------------------------

$GLOBALS['actions'] = [];
$GLOBALS['filters_applied'] = [];
$GLOBALS['transients'] = [];
$GLOBALS['mails'] = [];

function add_action(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    $GLOBALS['actions'][] = [$hook, $callback];

    return true;
}

function add_filter(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    return add_action($hook, $callback, $priority, $args);
}

function apply_filters(string $hook, $value, ...$rest)
{
    $GLOBALS['filters_applied'][] = $hook;

    return $hook === 'plogins_waitlist_should_notify' ? ($GLOBALS['should_notify'] ?? true) : $value;
}

function did_action(string $hook): int
{
    return 0;
}

function wp_schedule_single_event(int $timestamp, string $hook, array $args = []): bool
{
    return true;
}

function wp_mail($to, $subject, $message): bool
{
    $GLOBALS['mails'][] = (string) $to;

    return true;
}

function get_permalink($id = 0): string
{
    return 'https://example.test/?p=' . (int) $id;
}

function wp_salt(string $scheme = 'auth'): string
{
    return 'test-salt';
}

function home_url(string $path = ''): string
{
    return 'https://example.test' . $path;
}

function add_query_arg(array $args, string $url): string
{
    return $url . '?' . http_build_query($args);
}

function absint($value): int
{
    return abs((int) $value);
}

function wp_unslash($value)
{
    return $value;
}

function sanitize_text_field(string $value): string
{
    return trim($value);
}

function sanitize_key(string $value): string
{
    return strtolower($value);
}

function __(string $text, string $domain = ''): string
{
    return $text;
}

function esc_html__(string $text, string $domain = ''): string
{
    return $text;
}

function esc_html(string $text): string
{
    return $text;
}

function esc_url(string $url): string
{
    return $url;
}

function wp_kses(string $html, array $allowed): string
{
    return $html;
}

function get_option(string $name, $default = false)
{
    return $default;
}

function get_transient(string $key)
{
    return $GLOBALS['transients'][$key] ?? false;
}

function set_transient(string $key, $value, int $ttl): bool
{
    $GLOBALS['transients'][$key] = $value;

    return true;
}

final class WpDie extends RuntimeException
{
    public function __construct(public string $body, public int $status)
    {
        parent::__construct($body);
    }
}

function wp_die($message = '', $title = '', $args = []): void
{
    throw new WpDie((string) $message, (int) ($args['response'] ?? 500));
}

function wc_get_product($id = false)
{
    return $GLOBALS['product'] ?? null;
}

class WC_Product
{
    public function __construct(private int $id, private int $parentId = 0)
    {
    }

    public function get_id(): int
    {
        return $this->id;
    }

    public function get_parent_id(): int
    {
        return $this->parentId;
    }

    public function get_name(): string
    {
        return 'Test product';
    }
}

/** Minimal wpdb: prepare() keeps the id, get_row()/query() act on $rows. */
class wpdb
{
    public string $prefix = 'wp_';

    /** @var array<int, object> */
    public array $rows = [];

    public function prepare(string $sql, ...$args): string
    {
        return $sql . '#' . (int) end($args);
    }

    public function get_row(string $sql): ?object
    {
        return $this->rows[(int) substr((string) strrchr($sql, '#'), 1)] ?? null;
    }

    public function query(string $sql): int
    {
        $id = (int) substr((string) strrchr($sql, '#'), 1);

        if (! isset($this->rows[$id])) {
            return 0;
        }

        unset($this->rows[$id]);

        return 1;
    }
}

require_once __DIR__ . '/../src/Contract/HasHooks.php';
require_once __DIR__ . '/../src/Model/WaitlistSubscription.php';
require_once __DIR__ . '/../lib/storefront-kit/Support/Formatter.php';
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistRepository.php';
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistEngine.php';
require_once __DIR__ . '/../src/Repository/WaitlistRepository.php';
require_once __DIR__ . '/../src/Service/GuestUnsubscribe.php';

use Waitlist\Repository\WaitlistRepository;
use Waitlist\Service\GuestUnsubscribe;
use Waitlist\Vendor\StorefrontKit\Waitlist\WaitlistEngine;

// --- Signature --------------------------------------------------------------

$token = GuestUnsubscribe::token(7, 'Ann@Example.test');

check('a valid token is accepted', GuestUnsubscribe::isValid(7, 'ann@example.test', $token));
check('a tampered token is refused', ! GuestUnsubscribe::isValid(7, 'ann@example.test', substr($token, 0, -1) . (substr($token, -1) === 'a' ? 'b' : 'a')));
check('the token of another subscription is refused', ! GuestUnsubscribe::isValid(8, 'ann@example.test', $token));
check('the token for another address is refused', ! GuestUnsubscribe::isValid(7, 'bob@example.test', $token));
check('an empty token is refused', ! GuestUnsubscribe::isValid(7, 'ann@example.test', ''));
check('the link carries id and token, not the address', str_contains(GuestUnsubscribe::url(7, 'ann@example.test'), 'restock_leave=7')
    && str_contains(GuestUnsubscribe::url(7, 'ann@example.test'), $token)
    && ! str_contains(GuestUnsubscribe::url(7, 'ann@example.test'), 'ann'));

// --- Handler ----------------------------------------------------------------

$wpdb = new wpdb();
$row = static fn (int $id, string $email): object => (object) [
    'id' => $id, 'product_id' => 5, 'email' => $email, 'user_id' => null,
    'notified' => 1, 'created_at' => '2026-10-01 10:00:00', 'notified_at' => '2026-10-02 10:00:00',
];
$wpdb->rows = [7 => $row(7, 'ann@example.test'), 8 => $row(8, 'bob@example.test')];
$service = new GuestUnsubscribe(new WaitlistRepository($wpdb));

function visit(GuestUnsubscribe $service, string $method, int $id, string $token, string $ip = '10.0.0.1'): WpDie
{
    $_GET = [GuestUnsubscribe::ID_ARG => (string) $id, GuestUnsubscribe::TOKEN_ARG => $token];
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REMOTE_ADDR'] = $ip;

    try {
        $service->handle();
    } catch (WpDie $die) {
        return $die;
    }

    return new WpDie('no response', 0);
}

$open = visit($service, 'GET', 7, $token);
check('opening the link deletes nothing', isset($wpdb->rows[7]));
check('opening the link shows a confirm button that posts back', str_contains($open->body, 'method="post"') && $open->status === 200);

$tampered = visit($service, 'POST', 7, str_repeat('0', 64));
check('a tampered link deletes nothing', isset($wpdb->rows[7]));

$other = visit($service, 'POST', 8, $token);
check('one subscription\'s token cannot remove another', isset($wpdb->rows[8]));

$missing = visit($service, 'POST', 999, $token);
check('a missing row and a wrong token get the same answer', $missing->body === $tampered->body && $missing->status === $tampered->status && $other->body === $tampered->body);

$done = visit($service, 'POST', 7, $token);
check('the confirm POST removes the subscription', ! isset($wpdb->rows[7]) && $done->body !== $tampered->body);
check('the other subscription is untouched', isset($wpdb->rows[8]));

$again = visit($service, 'POST', 7, $token);
check('a second click says nothing about the address', $again->body === $tampered->body);

$GLOBALS['transients'] = [];
for ($i = 0; $i < 20; ++$i) {
    visit($service, 'GET', 999, 'x', '10.0.0.9');
}
check('the 21st attempt from one IP is refused', visit($service, 'GET', 999, 'x', '10.0.0.9')->status === 429);
check('another IP is not affected', visit($service, 'GET', 999, 'x', '10.0.0.10')->status === 200);

// --- Engine handoff and PRO-facing hooks -------------------------------------

final class OneRowRepository implements Waitlist\Vendor\StorefrontKit\Waitlist\WaitlistRepository
{
    /** @var list<int> */
    public array $notified = [];

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
        return $afterId === 0 ? [Waitlist\Model\WaitlistSubscription::fromRow((object) [
            'id' => 3, 'product_id' => $productId, 'email' => 'cy@example.test', 'user_id' => null,
            'notified' => 0, 'created_at' => '2026-10-01 10:00:00', 'notified_at' => null,
        ])] : [];
    }

    public function markNotified(int $id): void
    {
        $this->notified[] = $id;
    }
}

$sent = [];
$repo = new OneRowRepository();
$engine = new WaitlistEngine(
    repository: $repo,
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
    sendNotification: static function (int $id, string $email, WC_Product $product, int $target) use (&$sent): bool {
        $sent[] = [$id, $email, $product->get_id(), $target];

        return true;
    },
);

$GLOBALS['actions'] = [];
$engine->registerHooks();
$hooked = static fn (string $hook): bool => (bool) array_filter(
    $GLOBALS['actions'],
    static fn (array $a): bool => $a[0] === $hook && is_array($a[1]) && $a[1][0] === $engine && $a[1][1] === 'notifySubscribers',
);
check('notifySubscribers is still on woocommerce_product_set_stock_status (PRO suppressor)', $hooked('woocommerce_product_set_stock_status'));
check('notifySubscribers is still on woocommerce_variation_set_stock_status (PRO suppressor)', $hooked('woocommerce_variation_set_stock_status'));

$GLOBALS['product'] = new WC_Product(11, 10);
$GLOBALS['should_notify'] = false;
$GLOBALS['filters_applied'] = [];
$engine->notifySubscribers(11, 'instock', $GLOBALS['product']);
check('plogins_waitlist_should_notify is still asked (PRO segmentation)', in_array('plogins_waitlist_should_notify', $GLOBALS['filters_applied'], true));
$GLOBALS['should_notify'] = true;

$engine->runNotifyBatch(11, 10);
check('each mail goes to the configured sender with id, email, product and target', $sent === [[3, 'cy@example.test', 11, 10]]);
check('the plain wp_mail path is not used alongside it', $GLOBALS['mails'] === []);
check('a sent mail marks the row notified', $repo->notified === [3]);

echo $failures === 0 ? "\nPASS  unsubscribe\n" : "\n{$failures} FAILED\n";
exit($failures === 0 ? 0 : 1);
