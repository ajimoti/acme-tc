<?php

declare(strict_types=1);

namespace Acme\Offers;

use Acme\Value\Money;
use Acme\Model\Product;

interface OfferInterface
{
    /**
     * Apply the offer to the basket items and return the total discount
     * 
     * @param array<Product> $items
     * @return Money
     */
    public function apply(array $items): Money;
}

