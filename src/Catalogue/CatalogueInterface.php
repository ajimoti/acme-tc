<?php

declare(strict_types=1);

namespace Acme\Catalogue;

use Acme\Model\Product;

interface CatalogueInterface {
    public function add(Product $product): void;
    public function getProduct(string $productCode): Product;
    public function getAllProducts(): array;
}