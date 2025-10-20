<?php

declare(strict_types=1);

namespace Acme\Tests\Catalogue;

use PHPUnit\Framework\TestCase;
use Acme\Catalogue\ProductCatalogue;
use Acme\Model\Product;
use Acme\Value\Money;

final class ProductCatalogueTest extends TestCase
{
    private ProductCatalogue $catalogue;

    protected function setUp(): void
    {
        $this->catalogue = new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
        );
    }

    public function testGetExistingProduct(): void
    {
        $product = $this->catalogue->getProduct('R01');
        
        $this->assertEquals('R01', $product->code());
        $this->assertEquals('Red Widget', $product->name());
        $this->assertEquals('$32.95', $product->price()->format());
    }

    public function testGetAnotherExistingProduct(): void
    {
        $product = $this->catalogue->getProduct('B01');
        
        $this->assertEquals('B01', $product->code());
        $this->assertEquals('Blue Widget', $product->name());
        $this->assertEquals('$7.95', $product->price()->format());
    }

    public function testGetNonExistentProduct(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found: INVALID');
        
        $this->catalogue->getProduct('INVALID');
    }

    public function testGetProductWithEmptyCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found: ');
        
        $this->catalogue->getProduct('');
    }

    public function testGetProductWithNullCode(): void
    {
        $this->expectException(\TypeError::class);
        
        // This will fail at the type level, not at runtime
        $this->catalogue->getProduct(null);
    }

    public function testCaseSensitiveProductCodes(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found: r01');
        
        $this->catalogue->getProduct('r01');
    }

    public function testGetAllProducts(): void
    {
        $products = $this->catalogue->getAllProducts();
        
        $this->assertCount(3, $products);
        $this->assertArrayHasKey('R01', $products);
        $this->assertArrayHasKey('G01', $products);
        $this->assertArrayHasKey('B01', $products);
    }

    public function testProductCatalogueWithSingleProduct(): void
    {
        $singleProductCatalogue = new ProductCatalogue(
            new Product('SINGLE', 'Single Product', Money::fromCents(1000))
        );
        
        $product = $singleProductCatalogue->getProduct('SINGLE');
        $this->assertEquals('SINGLE', $product->code());
        $this->assertEquals('Single Product', $product->name());
        $this->assertEquals('$10.00', $product->price()->format());
    }

    public function testProductCatalogueWithNoProducts(): void
    {
        $emptyCatalogue = new ProductCatalogue();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product not found: ANY');
        
        $emptyCatalogue->getProduct('ANY');
    }
}
