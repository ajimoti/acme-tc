<?php

declare(strict_types=1);

namespace Acme\Tests\Value;

use PHPUnit\Framework\TestCase;
use Acme\Value\Money;

final class MoneyTest extends TestCase
{
    public function testZero(): void
    {
        $money = Money::zero();
        
        $this->assertEquals(0, $money->cents());
        $this->assertEquals('USD', $money->currency());
        $this->assertEquals('$0.00', $money->format());
    }

    public function testFromCents(): void
    {
        $money = Money::fromCents(3295);
        
        $this->assertEquals(3295, $money->cents());
        $this->assertEquals('USD', $money->currency());
        $this->assertEquals('$32.95', $money->format());
    }

    public function testFromFloat(): void
    {
        $money = Money::fromFloat(32.95);
        
        $this->assertEquals(3295, $money->cents());
        $this->assertEquals('$32.95', $money->format());
    }

    public function testFromFloatWithRounding(): void
    {
        $money = Money::fromFloat(32.945);
        
        $this->assertEquals(3295, $money->cents());
        $this->assertEquals('$32.95', $money->format());
    }

    public function testAdd(): void
    {
        $money1 = Money::fromCents(1000);
        $money2 = Money::fromCents(500);
        
        $result = $money1->add($money2);
        
        $this->assertEquals(1500, $result->cents());
        $this->assertEquals('$15.00', $result->format());
    }

    public function testSub(): void
    {
        $money1 = Money::fromCents(1000);
        $money2 = Money::fromCents(300);
        
        $result = $money1->sub($money2);
        
        $this->assertEquals(700, $result->cents());
        $this->assertEquals('$7.00', $result->format());
    }

    public function testMul(): void
    {
        $money = Money::fromCents(500);
        
        $result = $money->mul(3);
        
        $this->assertEquals(1500, $result->cents());
        $this->assertEquals('$15.00', $result->format());
    }

    public function testDiv(): void
    {
        $money = Money::fromCents(1000);
        
        $result = $money->div(2);
        
        $this->assertEquals(500, $result->cents());
        $this->assertEquals('$5.00', $result->format());
    }

    public function testDivWithRounding(): void
    {
        $money = Money::fromCents(1001);
        
        $result = $money->div(2);
        
        $this->assertEquals(501, $result->cents());
        $this->assertEquals('$5.01', $result->format());
    }

    public function testDivByZero(): void
    {
        $money = Money::fromCents(1000);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        
        $money->div(0);
    }

    public function testLessThan(): void
    {
        $money1 = Money::fromCents(500);
        $money2 = Money::fromCents(1000);
        
        $this->assertTrue($money1->lessThan($money2));
        $this->assertFalse($money2->lessThan($money1));
        $this->assertFalse($money1->lessThan($money1));
    }

    public function testGreaterOrEqual(): void
    {
        $money1 = Money::fromCents(1000);
        $money2 = Money::fromCents(500);
        $money3 = Money::fromCents(1000);
        
        $this->assertTrue($money1->greaterOrEqual($money2));
        $this->assertTrue($money1->greaterOrEqual($money3));
        $this->assertFalse($money2->greaterOrEqual($money1));
    }

    public function testCurrencyMismatch(): void
    {
        $usd = Money::fromCents(1000, 'USD');
        $eur = Money::fromCents(1000, 'EUR');
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency mismatch');
        
        $usd->add($eur);
    }

    public function testFormat(): void
    {
        $money = Money::fromCents(123456);
        
        $this->assertEquals('$1234.56', $money->format());
    }

    public function testFormatWithSmallAmount(): void
    {
        $money = Money::fromCents(5);
        
        $this->assertEquals('$0.05', $money->format());
    }
}
