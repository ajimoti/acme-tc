<?php

declare(strict_types=1);

namespace Acme\Catalogue;

use Acme\Model\Product;

final class ProductCatalogue implements CatalogueInterface {
    /** @var array<Product> */
    private array $products = [];

    public function __construct(Product ...$products)
    {
        foreach ($products as $product) {
            $this->products[$product->code()] = $product;
        }
    }

    public function add(Product $product): void
    {
        $this->products[$product->code()] = $product;
    }

    public function getProduct(string $productCode): Product
    {
        if (!isset($this->products[$productCode])) {
            throw new \InvalidArgumentException("Product not found: $productCode");
        }
        
        return $this->products[$productCode];
    }

    public function getAllProducts(): array
    {
        return $this->products;
    }
}