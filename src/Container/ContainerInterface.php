<?php

declare(strict_types=1);

namespace Acme\Container;

interface ContainerInterface
{
    public function get(string $id): mixed;
    public function has(string $id): bool;
    public function set(string $id, mixed $value): void;
}