<?php

declare(strict_types=1);

namespace Waitlist;

defined('ABSPATH') || exit;

use InvalidArgumentException;

/**
 * Lightweight dependency-injection container. Singleton + factory bindings,
 * no external dependencies.
 */
final class Container
{
    /** @var array<class-string, callable> */
    private array $factories = [];

    /** @var array<class-string, object> */
    private array $singletons = [];

    /** @var array<class-string, true> */
    private array $shared = [];

    /**
     * @template T of object
     * @param class-string<T> $id
     * @param callable(): T $factory
     */
    public function singleton(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        $this->shared[$id] = true;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @param callable(): T $factory
     */
    public function bind(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->shared[$id]);
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @return T
     */
    public function get(string $id): object
    {
        if (isset($this->singletons[$id])) {
            /** @var T */
            return $this->singletons[$id];
        }

        if (! isset($this->factories[$id])) {
            throw new InvalidArgumentException(
                sprintf('Service "%s" is not registered in the container.', esc_html($id)),
            );
        }

        $instance = ($this->factories[$id])();

        if (isset($this->shared[$id])) {
            $this->singletons[$id] = $instance;
        }

        /** @var T */
        return $instance;
    }

    /**
     * @param class-string $id
     */
    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || isset($this->singletons[$id]);
    }
}
