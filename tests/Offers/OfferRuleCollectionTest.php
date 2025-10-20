<?php

declare(strict_types=1);

namespace Acme\Tests\Offers;

use PHPUnit\Framework\TestCase;
use Acme\Offers\OfferRuleCollection;
use Acme\Offers\BuyOneGetOneHalfPriceOffer;
use Acme\Model\Product;
use Acme\Value\Money;

final class OfferRuleCollectionTest extends TestCase
{
    public function testOfferRuleCollectionCreation(): void
    {
        $offer1 = new BuyOneGetOneHalfPriceOffer('R01');
        $offer2 = new BuyOneGetOneHalfPriceOffer('G01');
        
        $collection = new OfferRuleCollection($offer1, $offer2);
        
        $this->assertCount(2, $collection->all());
    }

    public function testApplyWithNoOffers(): void
    {
        $collection = new OfferRuleCollection();
        
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $collection->apply($items);
        
        $this->assertEquals(0, $discount->cents());
        $this->assertEquals('$0.00', $discount->format());
    }

    public function testApplyWithSingleOffer(): void
    {
        $offer = new BuyOneGetOneHalfPriceOffer('R01');
        $collection = new OfferRuleCollection($offer);
        
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $collection->apply($items);
        
        // Second R01 gets 50% off
        $expectedDiscount = 3295 / 2;
        $this->assertEquals(1648, $discount->cents());
        $this->assertEquals('$16.48', $discount->format());
    }

    public function testApplyWithMultipleOffers(): void
    {
        $redOffer = new BuyOneGetOneHalfPriceOffer('R01');
        $greenOffer = new BuyOneGetOneHalfPriceOffer('G01');
        $collection = new OfferRuleCollection($redOffer, $greenOffer);
        
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('G01', 'Green Widget', Money::fromCents(2495))
        ];
        
        $discount = $collection->apply($items);
        
        // Second R01 gets 50% off + Second G01 gets 50% off
        $expectedDiscount = (3295 / 2) + (2495 / 2);
        $this->assertEquals(2896, $discount->cents());
        $this->assertEquals('$28.96', $discount->format());
    }

    public function testApplyWithMultipleOffersForSameProduct(): void
    {
        $offer1 = new BuyOneGetOneHalfPriceOffer('R01');
        $offer2 = new BuyOneGetOneHalfPriceOffer('R01');
        $collection = new OfferRuleCollection($offer1, $offer2);
        
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295))
        ];
        
        $discount = $collection->apply($items);
        
        // Both offers apply to the same items, so discount is doubled
        $expectedDiscount = (3295 / 2) * 2;
        $this->assertEquals(3296, $discount->cents());
        $this->assertEquals('$32.96', $discount->format());
    }

    public function testApplyWithNoMatchingItems(): void
    {
        $offer = new BuyOneGetOneHalfPriceOffer('R01');
        $collection = new OfferRuleCollection($offer);
        
        $items = [
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795))
        ];
        
        $discount = $collection->apply($items);
        
        $this->assertEquals(0, $discount->cents());
        $this->assertEquals('$0.00', $discount->format());
    }

    public function testAddOffer(): void
    {
        $offer1 = new BuyOneGetOneHalfPriceOffer('R01');
        $collection = new OfferRuleCollection($offer1);
        
        $this->assertCount(1, $collection->all());
        
        $offer2 = new BuyOneGetOneHalfPriceOffer('G01');
        $collection->add($offer2);
        
        $this->assertCount(2, $collection->all());
    }

    public function testApplyWithEmptyItems(): void
    {
        $offer = new BuyOneGetOneHalfPriceOffer('R01');
        $collection = new OfferRuleCollection($offer);
        
        $discount = $collection->apply([]);
        
        $this->assertEquals(0, $discount->cents());
        $this->assertEquals('$0.00', $discount->format());
    }

    public function testApplyWithComplexScenario(): void
    {
        $redOffer = new BuyOneGetOneHalfPriceOffer('R01');
        $blueOffer = new BuyOneGetOneHalfPriceOffer('B01');
        $collection = new OfferRuleCollection($redOffer, $blueOffer);
        
        $items = [
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
            new Product('G01', 'Green Widget', Money::fromCents(2495))
        ];
        
        $discount = $collection->apply($items);
        
        // R01: 3 items = 1 pair, so 1 discounted
        // B01: 2 items = 1 pair, so 1 discounted
        $expectedDiscount = (3295 / 2) + (795 / 2);
        $this->assertEquals(2046, $discount->cents());
        $this->assertEquals('$20.46', $discount->format());
    }
}
