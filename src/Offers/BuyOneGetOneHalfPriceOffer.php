<?php

declare(strict_types=1);

namespace Acme\Offers;

use Acme\Value\Money;
use Acme\Model\Product;
use Acme\Offers\OfferInterface;

final class BuyOneGetOneHalfPriceOffer implements OfferInterface
{
    public function __construct(
        private readonly string $productCode
    ) {}

    /**
     * Apply the "buy one get one half price" offer
     * For every 2 items of the specified product, the second one is half price
     * 
     * @param array<Product> $items
     * @return Money
     */
    public function apply(array $items): Money
    {
        $matchingItems = array_filter($items, fn(Product $item) => $item->code() === $this->productCode);
        $count = count($matchingItems);

        if ($count < 2) {
            return Money::zero();
        }

        // Calculate number of pairs (every 2nd item gets 50% discount)
        $discountedItems = intdiv($count, 2);
        
        $firstItem = reset($matchingItems);
        if ($firstItem === false) {
            return Money::zero();
        }
        $itemPrice = $firstItem->price();
        
        $discountPerItem = $itemPrice->div(2);
        
        return $discountPerItem->mul($discountedItems);
    }
}

