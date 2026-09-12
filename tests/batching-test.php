<?php

declare(strict_types=1);

/**
 * Batching guard.
 *
 * Run: php tests/batching-test.php
 *
 * Fails if the restock mailing goes back to sending inside the request that
 * changed the stock, if a batch ignores its size, if the walk loses or repeats
 * a subscriber, if a failed send makes it loop forever, or if the admin list
 * queries drop their LIMIT.
 *
 * Plain PHP on purpose: this plugin has no test framework, and adding one to
 * protect five assertions would cost more than it guards.
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

// --- WordPress / WooCommerce stubs -----------------------------------------

$GLOBALS['scheduled'] = [];
$GLOBALS['mails']     = [];
$GLOBALS['mail_ok']   = true;

function add_action(string $hook, $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function apply_filters(string $hook, $value, ...$rest)
{
    return $value;
}

function wp_schedule_single_event(int $timestamp, string $hook, array $args = [])
{
    $GLOBALS['scheduled'][] = $args;

    return true;
}

function wp_mail($to, $subject, $message): bool
{
    $GLOBALS['mails'][] = (string) $to;

    return (bool) $GLOBALS['mail_ok'];
}

function get_permalink($id = 0): string
{
    return 'https://example.test/?p=' . (int) $id;
}

function wc_get_product($id = false)
{
    return $GLOBALS['product'];
}

function current_time(string $type, $gmt = 0): string
{
    return gmdate('Y-m-d H:i:s');
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

require_once __DIR__ . '/../lib/storefront-kit/Support/Formatter.php';
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistRepository.php';
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistEngine.php';
require_once __DIR__ . '/../src/Model/WaitlistSubscription.php';

use Waitlist\Model\WaitlistSubscription;
use WPPoland\StorefrontKit\Waitlist\WaitlistEngine;

final class FakeRepository implements WPPoland\StorefrontKit\Waitlist\WaitlistRepository
{
    /** @var list<WaitlistSubscription> */
    public array $rows = [];

    /** @var list<int> */
    public array $notified = [];

    public function subscribe(int $productId, string $email, ?int $userId): int
    {
        return 0;
    }

    public function findPendingByProduct(int $productId): iterable
    {
        return $this->pending($productId);
    }

    public function findPendingBatch(int $productId, int $limit, string $afterCreatedAt = '', int $afterId = 0): iterable
    {
        $batch = [];

        foreach ($this->pending($productId) as $row) {
            $createdAt = $row->createdAt->format('Y-m-d H:i:s');

            if ($afterCreatedAt !== '') {
                if ($createdAt < $afterCreatedAt) {
                    continue;
                }
                if ($createdAt === $afterCreatedAt && $row->id <= $afterId) {
                    continue;
                }
            }

            $batch[] = $row;

            if (count($batch) >= $limit) {
                break;
            }
        }

        return $batch;
    }

    public function markNotified(int $id): void
    {
        $this->notified[] = $id;
    }

    /** @return list<WaitlistSubscription> */
    private function pending(int $productId): array
    {
        $rows = array_values(array_filter(
            $this->rows,
            fn (WaitlistSubscription $row): bool => $row->productId === $productId
                && ! in_array($row->id, $this->notified, true),
        ));

        // Mirror the SQL: ORDER BY created_at ASC, id ASC.
        usort($rows, static function (WaitlistSubscription $a, WaitlistSubscription $b): int {
            return [$a->createdAt->format('Y-m-d H:i:s'), $a->id] <=> [$b->createdAt->format('Y-m-d H:i:s'), $b->id];
        });

        return $rows;
    }
}

function make_engine(FakeRepository $repository): WaitlistEngine
{
    return new WaitlistEngine(
        repository: $repository,
        ajaxAction: 'restock_waitlist_subscribe',
        nonceAction: 'restock_waitlist',
        scriptObjectName: 'restockWaitlist',
        assetHandle: 'restock-waitlist',
        styleUrl: '',
        scriptUrl: '',
        version: '0',
        templateName: 'single-product/waitlist-form',
        defaultMessages: [
            'notify_subject' => 'Back in stock - {product_name}',
            'notify_intro' => '{product_name} is back.',
            'notify_outro' => 'Ignore this mail to stop.',
        ],
        isEnabled: static fn (): bool => true,
        settings: static fn (): array => [],
        renderTemplate: static function (string $template, array $data): void {
        },
    );
}

function seed(FakeRepository $repository, int $productId, int $count): void
{
    for ($i = 1; $i <= $count; $i++) {
        $repository->rows[] = new WaitlistSubscription(
            id: $i,
            productId: $productId,
            email: 'shopper' . $i . '@example.test',
            userId: null,
            notified: false,
            // Deliberately coarse: ten rows share each created_at, which is
            // what an offset walk gets wrong once rows leave the result set,
            // and what a cursor on created_at alone would skip past.
            createdAt: new DateTimeImmutable(sprintf('2026-01-01 10:%02d:00', intdiv($i - 1, 10))),
            notifiedAt: null,
        );
    }
}

/**
 * Drain the scheduled queue, returning how many mails each run sent.
 *
 * @return list<int>
 */
function drain(WaitlistEngine $engine, int $maxRuns = 40): array
{
    $perRun = [];

    while ($GLOBALS['scheduled'] !== []) {
        if (count($perRun) >= $maxRuns) {
            return $perRun;
        }

        $args   = array_shift($GLOBALS['scheduled']);
        $before = count($GLOBALS['mails']);
        $engine->runNotifyBatch(...$args);
        $perRun[] = count($GLOBALS['mails']) - $before;
    }

    return $perRun;
}

// --- 1. a restock queues the mailing, it does not send it -------------------

$repository        = new FakeRepository();
$GLOBALS['product'] = new WC_Product(7);
seed($repository, 7, 120);
$engine = make_engine($repository);

$engine->notifySubscribers(7, 'instock', $GLOBALS['product']);

check('a restock sends no mail inside the stock-change request', $GLOBALS['mails'] === []);
check('a restock queues exactly one batch job', count($GLOBALS['scheduled']) === 1);

$perRun = drain($engine);

check('120 subscribers drain in batches of 50', $perRun === [50, 50, 20]);
check('every subscriber was mailed exactly once', count($GLOBALS['mails']) === 120
    && count(array_unique($GLOBALS['mails'])) === 120);
check('mails keep the waiting order', $GLOBALS['mails'][0] === 'shopper1@example.test'
    && $GLOBALS['mails'][119] === 'shopper120@example.test');
check('the last batch queues nothing further', $GLOBALS['scheduled'] === []);

// --- 2. a failing mailer must not loop forever ------------------------------

$repository         = new FakeRepository();
$GLOBALS['product']  = new WC_Product(7);
$GLOBALS['mails']    = [];
$GLOBALS['mail_ok']  = false;
seed($repository, 7, 120);
$engine = make_engine($repository);

$engine->notifySubscribers(7, 'instock', $GLOBALS['product']);
$perRun = drain($engine);

check('a failing mailer still terminates', count($perRun) === 3 && $GLOBALS['scheduled'] === []);
check('a failing mailer retries nobody twice', count($GLOBALS['mails']) === 120);

$GLOBALS['mail_ok'] = true;

// --- 3. the admin list queries carry a LIMIT --------------------------------

final class RecordingWpdb
{
    public string $prefix = 'wp_';

    /** @var list<string> */
    public array $queries = [];

    public function prepare(string $query, ...$args): string
    {
        return $query;
    }

    public function get_results(string $query): array
    {
        $this->queries[] = $query;

        return [];
    }

    public function get_row(string $query)
    {
        $this->queries[] = $query;

        return null;
    }

    public function esc_like(string $text): string
    {
        return $text;
    }
}

class_alias(RecordingWpdb::class, 'wpdb');
require_once __DIR__ . '/../src/Repository/WaitlistRepository.php';

$wpdb       = new RecordingWpdb();
$repository = new Waitlist\Repository\WaitlistRepository($wpdb);

$repository->findAll(50, 0);
$repository->search('shopper', 50, 50);
$repository->findPendingBatch(7, 50);
$repository->findPendingByProduct(7, 500, 0);

foreach ($wpdb->queries as $query) {
    check('bounded: ' . substr($query, 0, 60), str_contains($query, 'LIMIT'));
}

check('four list queries were checked', count($wpdb->queries) === 4);

echo $failures === 0 ? "\nPASS\n" : "\n{$failures} FAILED\n";
exit($failures === 0 ? 0 : 1);
