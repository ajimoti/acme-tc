<?php

declare(strict_types=1);

namespace Acme\DeliveryRules;

use Acme\Value\Money;

final class FreeDeliveryRule implements DeliveryRuleInterface
{
    public function __construct(
        private readonly Money $threshold
    ) {}

    public function apply(Money $total): Money
    {
        if ($total->greaterOrEqual($this->threshold)) {
            return $total; // Free delivery
        }
        
        return $total; // No delivery charge applied
    }

    public function underThreshold(): Money
    {
        return $this->threshold;
    }

    public function deliveryCost(): Money
    {
        return Money::zero();
    }
}
