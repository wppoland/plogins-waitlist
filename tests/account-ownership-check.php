<?php

declare(strict_types=1);

/**
 * Account ownership guard, from the IDOR report of 2026-10-05.
 *
 * Run: php tests/account-ownership-check.php
 *
 * My Account > Waitlists listed and deleted rows matching the user_id OR the
 * account email. WooCommerce does not verify an account email, so a customer
 * could set it to a guest's address and list or delete that guest's pending
 * signups. Fails if either query goes back to matching on email.
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

final class RecordingWpdb
{
    public string $prefix = 'wp_';

    /** @var list<array{0: string, 1: array<int, mixed>}> */
    public array $prepared = [];

    public function prepare(string $query, ...$args): string
    {
        $this->prepared[] = [$query, $args];

        return $query;
    }

    public function get_results(string $query): array
    {
        return [];
    }

    public function query(string $query): int
    {
        return 1;
    }
}

class_alias(RecordingWpdb::class, 'wpdb');
require_once __DIR__ . '/../lib/storefront-kit/Waitlist/WaitlistRepository.php';
require_once __DIR__ . '/../src/Model/WaitlistSubscription.php';
require_once __DIR__ . '/../src/Repository/WaitlistRepository.php';

$wpdb       = new RecordingWpdb();
$repository = new Waitlist\Repository\WaitlistRepository($wpdb);

$repository->findActiveForAccount(3);
$repository->deleteForAccountOwner(11, 3);

foreach (['list' => 0, 'delete' => 1] as $label => $i) {
    [$sql, $args] = $wpdb->prepared[$i];
    check("{$label}: scoped by user_id", str_contains($sql, 'user_id = %d'));
    check("{$label}: never matches on email", ! str_contains($sql, 'email'));
    check("{$label}: user id is bound", in_array(3, $args, true));
}

foreach (['findActiveForAccount', 'deleteForAccountOwner'] as $method) {
    $params = array_map(
        static fn (ReflectionParameter $p): string => $p->getName(),
        (new ReflectionMethod(Waitlist\Repository\WaitlistRepository::class, $method))->getParameters(),
    );
    check("{$method} takes no email", ! in_array('email', $params, true));
}

$service = (string) file_get_contents(__DIR__ . '/../src/Service/WaitlistService.php');
check('service passes no account email to either query', preg_match('/(findActiveForAccount|deleteForAccountOwner)\([^;]*user_email/', $service) === 0);

echo $failures === 0 ? "PASS  account ownership\n" : "{$failures} failure(s)\n";
exit($failures === 0 ? 0 : 1);
