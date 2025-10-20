<?php

declare(strict_types=1);

namespace Acme\Model;

use Acme\Value\Money;

final class Product
{
    public function __construct(
        private string $code,
        private string $name,
        private Money $price,
    ) {}

    public function code(): string { return $this->code; }
    public function name(): string { return $this->name; }
    public function price(): Money { return $this->price; }
}
