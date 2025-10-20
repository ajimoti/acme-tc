<?php

declare(strict_types=1);

namespace Acme\Tests\Model;

use PHPUnit\Framework\TestCase;
use Acme\Model\Product;
use Acme\Value\Money;

final class ProductTest extends TestCase
{
    public function testProductCreation(): void
    {
        $price = Money::fromCents(3295);
        $product = new Product('R01', 'Red Widget', $price);
        
        $this->assertEquals('R01', $product->code());
        $this->assertEquals('Red Widget', $product->name());
        $this->assertEquals($price, $product->price());
    }

    public function testProductWithDifferentPrice(): void
    {
        $price = Money::fromCents(795);
        $product = new Product('B01', 'Blue Widget', $price);
        
        $this->assertEquals('B01', $product->code());
        $this->assertEquals('Blue Widget', $product->name());
        $this->assertEquals($price, $product->price());
        $this->assertEquals('$7.95', $product->price()->format());
    }

    public function testProductWithZeroPrice(): void
    {
        $price = Money::zero();
        $product = new Product('FREE', 'Free Item', $price);
        
        $this->assertEquals('FREE', $product->code());
        $this->assertEquals('Free Item', $product->name());
        $this->assertEquals($price, $product->price());
        $this->assertEquals('$0.00', $product->price()->format());
    }

    public function testProductWithHighPrice(): void
    {
        $price = Money::fromCents(999999);
        $product = new Product('EXP', 'Expensive Item', $price);
        
        $this->assertEquals('EXP', $product->code());
        $this->assertEquals('Expensive Item', $product->name());
        $this->assertEquals($price, $product->price());
        $this->assertEquals('$9999.99', $product->price()->format());
    }
}
