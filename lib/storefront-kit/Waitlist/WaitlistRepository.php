<?php

declare(strict_types=1);

namespace WPPoland\StorefrontKit\Waitlist;

interface WaitlistRepository
{
    public function subscribe(int $productId, string $email, ?int $userId): int;

    /**
     * @return iterable<object{id:int,email:string}>
     */
    public function findPendingByProduct(int $productId): iterable;

    /**
     * One batch of pending subscribers after the given (created_at, id) cursor,
     * in the same order as findPendingByProduct().
     *
     * @return iterable<object{id:int,email:string,createdAt:\DateTimeImmutable}>
     */
    public function findPendingBatch(int $productId, int $limit, string $afterCreatedAt = '', int $afterId = 0): iterable;

    public function markNotified(int $id): void;
}
