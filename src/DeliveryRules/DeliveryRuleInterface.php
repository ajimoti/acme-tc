<?php

declare(strict_types=1);

namespace Acme\DeliveryRules;

use Acme\Value\Money;

interface DeliveryRuleInterface {
    public function apply(Money $total): Money;
    public function underThreshold(): Money;
    public function deliveryCost(): Money;
}