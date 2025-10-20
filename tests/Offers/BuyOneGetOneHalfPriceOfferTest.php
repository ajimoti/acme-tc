<?php

declare(strict_types=1);

namespace Acme\Tests\Offers;

use PHPUnit\Framework\TestCase;
use Acme\Offers\BuyOneGetOneHalfPriceOffer;
use Acme\Model\Product;
use Acme\Value\Money;

final class BuyOneGetOneHalfPriceOfferTest extends TestCase
{
    private BuyOneGetOneHalfPriceOffer $offer;

    protected function setUp(): void
    {
        $this->offer = new BuyOneGetOneHalfPriceOffer('R01');
    }

    public function testOfferCreation(): void
    {
        $this->assertInstanceOf(BuyOneGetOneHalfPriceOffer::class, $this->offer);
    }

    public function testApplyWithNoItems(): void
    {
        $items = [];
        $discount = $this->offer->apply($items);
        
        $this->assertEquals(0, $discount->cents());
        $this->assertEquals('$0.00', $discount->format());
    }

    public function testApplyWithOneItem(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        $this->assertEquals(0, $discount->cents());
        $this->assertEquals('$0.00', $discount->format());
    }

    public function testApplyWithTwoItems(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        // Second item gets 50% off
        $expectedDiscount = 3295 / 2; // $16.475 -> $16.48
        $this->assertEquals(1648, $discount->cents());
        $this->assertEquals('$16.48', $discount->format());
    }

    public function testApplyWithThreeItems(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        // One pair: second item gets 50% off
        $expectedDiscount = 3295 / 2;
        $this->assertEquals(1648, $discount->cents());
        $this->assertEquals('$16.48', $discount->format());
    }

    public function testApplyWithFourItems(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        // Two pairs: second and fourth items get 50% off
        $expectedDiscount = (3295 / 2) * 2;
        $this->assertEquals(3296, $discount->cents());
        $this->assertEquals('$32.96', $discount->format());
    }

    public function testApplyWithFiveItems(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        // Two pairs: second and fourth items get 50% off
        $expectedDiscount = (3295 / 2) * 2;
        $this->assertEquals(3296, $discount->cents());
        $this->assertEquals('$32.96', $discount->format());
    }

    public function testApplyWithMixedProducts(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $this->offer->apply($items);
        
        // Two R01 items = one pair, so one gets 50% off
        $this->assertEquals(1648, $discount->cents());
        $this->assertEquals('$16.48', $discount->format());
    }

    public function testApplyWithTwoRedWidgetsAndOtherProducts(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('B01', 'Blue Widget', Money::fromCents(795))
        ];
        
        $discount = $this->offer->apply($items);
        
        // One R01 pair: second R01 gets 50% off
        $expectedDiscount = 3295 / 2;
        $this->assertEquals(1648, $discount->cents());
        $this->assertEquals('$16.48', $discount->format());
    }

    public function testApplyWithDifferentProductCode(): void
    {
        $greenOffer = new BuyOneGetOneHalfPriceOffer('G01');
        
        $items = [
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('G01', 'Green Widget', Money::fromCents(2495))
        ];
        
        $discount = $greenOffer->apply($items);
        
        // Second G01 gets 50% off
        $expectedDiscount = 2495 / 2;
        $this->assertEquals(1248, $discount->cents());
        $this->assertEquals('$12.48', $discount->format());
    }

    public function testApplyWithBlueWidgets(): void
    {
        $blueOffer = new BuyOneGetOneHalfPriceOffer('B01');
        
        $items = [
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
            new Product('B01', 'Blue Widget', Money::fromCents(795))
        ];
        
        $discount = $blueOffer->apply($items);
        
        // Second B01 gets 50% off
        $expectedDiscount = 795 / 2;
        $this->assertEquals(398, $discount->cents());
        $this->assertEquals('$3.98', $discount->format());
    }

    public function testApplyWithOddPricing(): void
    {
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(1001)), // $10.01
            new Product('R01', 'Red Widget', Money::fromCents(1001))
        ];
        
        $discount = $this->offer->apply($items);
        
        // Second item gets 50% off: $10.01 / 2 = $5.005 -> $5.01
        $this->assertEquals(501, $discount->cents());
        $this->assertEquals('$5.01', $discount->format());
    }
}
