<?php

declare(strict_types=1);

namespace Acme\Tests\Basket;

use PHPUnit\Framework\TestCase;
use Acme\Basket\Basket;
use Acme\Catalogue\ProductCatalogue;
use Acme\DeliveryRules\DeliveryChargeRuleCollection;
use Acme\DeliveryRules\DeliveryChargeRule;
use Acme\Offers\OfferRuleCollection;
use Acme\Offers\BuyOneGetOneHalfPriceOffer;
use Acme\Model\Product;
use Acme\Value\Money;

final class BasketTest extends TestCase
{
    private ProductCatalogue $catalogue;
    private DeliveryChargeRuleCollection $deliveryRules;
    private OfferRuleCollection $offerRules;

    protected function setUp(): void
    {
        $this->catalogue = new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
        );

        $this->deliveryRules = new DeliveryChargeRuleCollection(
            new DeliveryChargeRule(Money::fromCents(5000), Money::fromCents(495)),
            new DeliveryChargeRule(Money::fromCents(9000), Money::fromCents(295)),
        );

        $this->offerRules = new OfferRuleCollection(
            new BuyOneGetOneHalfPriceOffer('R01'),
        );
    }

    public function testBasketCreation(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        
        $this->assertInstanceOf(Basket::class, $basket);
    }

    public function testAddSingleItem(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket->add('R01');
        
        $total = $basket->total();
        
        // $32.95 + $4.95 delivery (under $50)
        $this->assertEquals(3790, $total->cents());
        $this->assertEquals('$37.90', $total->format());
    }

    public function testAddMultipleItems(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket->add('R01')->add('G01');
        
        $total = $basket->total();
        
        // $32.95 + $24.95 = $57.90 + $2.95 delivery (under $90)
        $this->assertEquals(6085, $total->cents());
        $this->assertEquals('$60.85', $total->format());
    }

    public function testAddWithOffer(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket->add('R01')->add('R01');
        
        $total = $basket->total();
        
        // $32.95 + $16.48 (50% off) = $49.42 + $4.95 delivery (under $50)
        $this->assertEquals(5437, $total->cents());
        $this->assertEquals('$54.37', $total->format());
    }

    public function testAddWithFreeDelivery(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket->add('R01')->add('R01')->add('R01')->add('G01')->add('G01');
        
        $total = $basket->total();
        
        // $32.95 + $16.48 (50% off) + $32.95 + $24.95 + $24.95 = $132.28
        // Over $90, so free delivery
        $this->assertEquals(13227, $total->cents());
        $this->assertEquals('$132.27', $total->format());
    }

    public function testAddInvalidProduct(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found: INVALID');
        
        $basket->add('INVALID');
    }

    public function testEmptyBasket(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        
        $total = $basket->total();
        
        // $0.00 + $4.95 delivery (under $50)
        $this->assertEquals(495, $total->cents());
        $this->assertEquals('$4.95', $total->format());
    }

    public function testBasketWithNoOffers(): void
    {
        $noOfferRules = new OfferRuleCollection();
        $basket = new Basket($this->catalogue, $this->deliveryRules, $noOfferRules);
        $basket->add('R01')->add('R01');
        
        $total = $basket->total();
        
        // $32.95 + $32.95 = $65.90 + $2.95 delivery (under $90)
        $this->assertEquals(6885, $total->cents());
        $this->assertEquals('$68.85', $total->format());
    }

    public function testBasketWithNoDeliveryRules(): void
    {
        $noDeliveryRules = new DeliveryChargeRuleCollection();
        $basket = new Basket($this->catalogue, $noDeliveryRules, $this->offerRules);
        $basket->add('R01')->add('R01');
        
        $total = $basket->total();
        
        // $32.95 + $16.48 (50% off) = $49.42 (no delivery charge)
        $this->assertEquals(4942, $total->cents());
        $this->assertEquals('$49.42', $total->format());
    }

    public function testBasketWithNoRules(): void
    {
        $noDeliveryRules = new DeliveryChargeRuleCollection();
        $noOfferRules = new OfferRuleCollection();
        $basket = new Basket($this->catalogue, $noDeliveryRules, $noOfferRules);
        $basket->add('R01')->add('G01');
        
        $total = $basket->total();
        
        // $32.95 + $24.95 = $57.90 (no delivery charge, no offers)
        $this->assertEquals(5790, $total->cents());
        $this->assertEquals('$57.90', $total->format());
    }

    public function testRealWorldScenarios(): void
    {
        // Test 1: B01, G01
        $basket1 = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket1->add('B01')->add('G01');
        $total1 = $basket1->total();
        $this->assertEquals(3785, $total1->cents()); // $37.85

        // Test 2: R01, R01
        $basket2 = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket2->add('R01')->add('R01');
        $total2 = $basket2->total();
        $this->assertEquals(5437, $total2->cents()); // $54.37

        // Test 3: R01, G01
        $basket3 = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket3->add('R01')->add('G01');
        $total3 = $basket3->total();
        $this->assertEquals(6085, $total3->cents()); // $60.85

        // Test 4: B01, B01, R01, R01, R01
        $basket4 = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket4->add('B01')->add('B01')->add('R01')->add('R01')->add('R01');
        $total4 = $basket4->total();
        $this->assertEquals(9827, $total4->cents()); // $98.27
    }

    public function testBasketWithMultipleOffers(): void
    {
        $multipleOffers = new OfferRuleCollection(
            new BuyOneGetOneHalfPriceOffer('R01'),
            new BuyOneGetOneHalfPriceOffer('G01')
        );
        
        $basket = new Basket($this->catalogue, $this->deliveryRules, $multipleOffers);
        $basket->add('R01')->add('R01')->add('G01')->add('G01');
        
        $total = $basket->total();
        
        // $32.95 + $16.48 (50% off) + $24.95 + $12.48 (50% off) = $86.86
        // Under $90, so $2.95 delivery
        $this->assertEquals(8979, $total->cents());
        $this->assertEquals('$89.79', $total->format());
    }

    public function testBasketWithComplexOfferScenario(): void
    {
        $basket = new Basket($this->catalogue, $this->deliveryRules, $this->offerRules);
        $basket->add('R01')->add('R01')->add('R01')->add('R01')->add('R01');
        
        $total = $basket->total();
        
        // 5 R01 items: 2 pairs = 2 discounted items
        // $32.95 + $16.48 + $32.95 + $16.48 + $32.95 = $131.81
        // Over $90, so free delivery
        $this->assertEquals(13179, $total->cents());
        $this->assertEquals('$131.79', $total->format());
    }
}
