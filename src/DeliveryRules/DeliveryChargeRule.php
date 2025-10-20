<?php 

declare(strict_types=1);

namespace Acme\DeliveryRules;

use Acme\Value\Money;

final class DeliveryChargeRule implements DeliveryRuleInterface {

    public function __construct(
        protected Money $underThreshold,
        protected Money $deliveryCost,
    )
    {}

    public function apply(Money $total): Money
    {
        if ($total->lessThan($this->underThreshold)) {
            return $total->add($this->deliveryCost);
        }

        return $total;
    }

    public function underThreshold(): Money
    {
        return $this->underThreshold;
    }

    public function deliveryCost(): Money
    {
        return $this->deliveryCost;
    }
}