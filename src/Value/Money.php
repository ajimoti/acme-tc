<?php

declare(strict_types=1);

namespace Acme\Value;

final class Money
{
    private int $cents;
    private string $currency;

    private function __construct(int $cents, string $currency = 'USD')
    {
        $this->cents = $cents;
        $this->currency = $currency;
    }

    public static function zero(string $currency = 'USD'): self
    {
        return new self(0, $currency);
    }

    public static function fromCents(int $cents, string $currency = 'USD'): self
    {
        return new self($cents, $currency);
    }

    public static function fromFloat(float $amount, string $currency = 'USD'): self
    {
        $cents = (int) round($amount * 100, 0, PHP_ROUND_HALF_UP);
        return new self($cents, $currency);
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->cents + $other->cents, $this->currency);
    }

    public function sub(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->cents - $other->cents, $this->currency);
    }

    public function mul(int $multiplier): self
    {
        return new self($this->cents * $multiplier, $this->currency);
    }

    public function div(int $divisor, bool $halfUp = true): self
    {
        if ($divisor === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }

        if ($halfUp) {
            $cents = intdiv($this->cents * 2 + ($divisor), 2 * $divisor); // emulate half-up
            return new self($cents, $this->currency);
        }

        return new self(intdiv($this->cents, $divisor), $this->currency);
    }

    public function lessThan(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->cents < $other->cents;
    }

    public function greaterOrEqual(self $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->cents >= $other->cents;
    }

    public function format(): string
    {
        return '$' . number_format($this->cents / 100, 2, '.', '');
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch');
        }
    }
}
