<?php

declare(strict_types=1);

namespace Acme\Tests\DeliveryRules;

use PHPUnit\Framework\TestCase;
use Acme\DeliveryRules\DeliveryChargeRuleCollection;
use Acme\DeliveryRules\DeliveryChargeRule;
use Acme\Value\Money;

final class DeliveryChargeRuleCollectionTest extends TestCase
{
    public function testDeliveryChargeRuleCollectionCreation(): void
    {
        $rule1 = new DeliveryChargeRule(Money::fromCents(5000), Money::fromCents(495));
        $rule2 = new DeliveryChargeRule(Money::fromCents(9000), Money::fromCents(295));
        
        $collection = new DeliveryChargeRuleCollection($rule1, $rule2);
        
        $this->assertCount(2, $collection->all());
    }

    public function testApplyWithSingleRule(): void
    {
        $rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        
        $collection = new DeliveryChargeRuleCollection($rule);
        
        $total = Money::fromCents(3000); // $30.00
        $result = $collection->apply($total);
        
        $this->assertEquals(3495, $result->cents()); // $30.00 + $4.95
        $this->assertEquals('$34.95', $result->format());
    }

    public function testApplyWithMultipleRulesFirstMatch(): void
    {
        $rule1 = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        $rule2 = new DeliveryChargeRule(
            Money::fromCents(9000), // Under $90
            Money::fromCents(295)   // $2.95 delivery
        );
        
        $collection = new DeliveryChargeRuleCollection($rule1, $rule2);
        
        $total = Money::fromCents(3000); // $30.00
        $result = $collection->apply($total);
        
        // Should apply first rule (under $50)
        $this->assertEquals(3495, $result->cents()); // $30.00 + $4.95
        $this->assertEquals('$34.95', $result->format());
    }

    public function testApplyWithMultipleRulesSecondMatch(): void
    {
        $rule1 = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        $rule2 = new DeliveryChargeRule(
            Money::fromCents(9000), // Under $90
            Money::fromCents(295)   // $2.95 delivery
        );
        
        $collection = new DeliveryChargeRuleCollection($rule1, $rule2);
        
        $total = Money::fromCents(6000); // $60.00
        $result = $collection->apply($total);
        
        // Should apply second rule (under $90)
        $this->assertEquals(6295, $result->cents()); // $60.00 + $2.95
        $this->assertEquals('$62.95', $result->format());
    }

    public function testApplyWithMultipleRulesNoMatch(): void
    {
        $rule1 = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        $rule2 = new DeliveryChargeRule(
            Money::fromCents(9000), // Under $90
            Money::fromCents(295)   // $2.95 delivery
        );
        
        $collection = new DeliveryChargeRuleCollection($rule1, $rule2);
        
        $total = Money::fromCents(10000); // $100.00
        $result = $collection->apply($total);
        
        // Should not apply any delivery charge (free delivery)
        $this->assertEquals(10000, $result->cents());
        $this->assertEquals('$100.00', $result->format());
    }

    public function testApplyWithEmptyCollection(): void
    {
        $collection = new DeliveryChargeRuleCollection();
        
        $total = Money::fromCents(1000);
        $result = $collection->apply($total);
        
        // Should return original total (free delivery)
        $this->assertEquals(1000, $result->cents());
        $this->assertEquals('$10.00', $result->format());
    }

    public function testAddRule(): void
    {
        $rule1 = new DeliveryChargeRule(Money::fromCents(5000), Money::fromCents(495));
        $collection = new DeliveryChargeRuleCollection($rule1);
        
        $this->assertCount(1, $collection->all());
        
        $rule2 = new DeliveryChargeRule(Money::fromCents(9000), Money::fromCents(295));
        $collection->add($rule2);
        
        $this->assertCount(2, $collection->all());
    }

    public function testRealWorldScenario(): void
    {
        // Simulate the actual delivery rules from the requirements
        $under50Rule = new DeliveryChargeRule(
            Money::fromCents(5000), // Under $50
            Money::fromCents(495)   // $4.95 delivery
        );
        $under90Rule = new DeliveryChargeRule(
            Money::fromCents(9000), // Under $90
            Money::fromCents(295)   // $2.95 delivery
        );
        
        $collection = new DeliveryChargeRuleCollection($under50Rule, $under90Rule);
        
        // Test under $50
        $total1 = Money::fromCents(3295); // $32.95
        $result1 = $collection->apply($total1);
        $this->assertEquals(3790, $result1->cents()); // $32.95 + $4.95
        
        // Test under $90
        $total2 = Money::fromCents(6000); // $60.00
        $result2 = $collection->apply($total2);
        $this->assertEquals(6295, $result2->cents()); // $60.00 + $2.95
        
        // Test over $90 (free delivery)
        $total3 = Money::fromCents(10000); // $100.00
        $result3 = $collection->apply($total3);
        $this->assertEquals(10000, $result3->cents()); // $100.00 (no delivery charge)
    }
}
