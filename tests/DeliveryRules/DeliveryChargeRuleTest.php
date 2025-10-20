<?php

declare(strict_types=1);

namespace Acme\Tests\DeliveryRules;

use PHPUnit\Framework\TestCase;
use Acme\DeliveryRules\DeliveryChargeRule;
use Acme\Value\Money;

final class DeliveryChargeRuleTest extends TestCase
{
    public function testDeliveryChargeRuleCreation(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        
        $this->assertEquals(5000, $rule->underThreshold()->cents());
        $this->assertEquals(495, $rule->deliveryCost()->cents());
    }

    public function testApplyWhenTotalIsUnderThreshold(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        
        $total = Money::fromCents(3000); // $30.00
        $result = $rule->apply($total);
        
        $this->assertEquals(3495, $result->cents()); // $30.00 + $4.95
        $this->assertEquals('$34.95', $result->format());
    }

    public function testApplyWhenTotalEqualsThreshold(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        
        $total = Money::fromCents(5000); // Exactly $50.00
        $result = $rule->apply($total);
        
        // Should NOT apply delivery charge when total equals threshold
        $this->assertEquals(5000, $result->cents());
        $this->assertEquals('$50.00', $result->format());
    }

    public function testApplyWhenTotalIsOverThreshold(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        
        $total = Money::fromCents(6000); // $60.00
        $result = $rule->apply($total);
        
        // Should NOT apply delivery charge when total is over threshold
        $this->assertEquals(6000, $result->cents());
        $this->assertEquals('$60.00', $result->format());
    }

    public function testApplyWithZeroDeliveryCost(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(9000), // Under $90
            Money::zero()           // Free delivery
        );
        
        $total = Money::fromCents(5000); // $50.00
        $result = $rule->apply($total);
        
        $this->assertEquals(5000, $result->cents());
        $this->assertEquals('$50.00', $result->format());
    }

    public function testApplyWithHighDeliveryCost(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(1000), // Under $10
            Money::fromCents(2000)  // $20.00 delivery
        );
        
        $total = Money::fromCents(500); // $5.00
        $result = $rule->apply($total);
        
        $this->assertEquals(2500, $result->cents()); // $5.00 + $20.00
        $this->assertEquals('$25.00', $result->format());
    }

    public function testApplyWithZeroTotal(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(1000), // Under $10
            Money::fromCents(500)   // $5.00 delivery
        );
        
        $total = Money::zero();
        $result = $rule->apply($total);
        
        $this->assertEquals(500, $result->cents());
        $this->assertEquals('$5.00', $result->format());
    }
}
