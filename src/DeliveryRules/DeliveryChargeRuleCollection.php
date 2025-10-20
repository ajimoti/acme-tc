<?php 

declare(strict_types=1);

namespace Acme\DeliveryRules;

use Acme\Value\Money;

final class DeliveryChargeRuleCollection {
     /** @var array<DeliveryRuleInterface> */
     private array $rules = [];
    
     public function __construct(DeliveryRuleInterface ...$rules)
     {
         $this->rules = $rules;
     }
     
     public function add(DeliveryRuleInterface $rule): void
     {
         $this->rules[] = $rule;
     }
     
     /** @return array<DeliveryRuleInterface> */
     public function all(): array
     {
         return $this->rules;
     }

    public function apply(Money $total): Money
    {
        foreach ($this->rules as $rule) {
            if ($total->lessThan($rule->underThreshold())) {
                return $total->add($rule->deliveryCost());
            }
        }   

        // If no rule applies, return the total
        // This is the default case for free delivery
        return $total;
    }
}