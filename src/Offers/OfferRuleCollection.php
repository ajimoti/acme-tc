<?php

declare(strict_types=1);

namespace Acme\Offers;

use Acme\Value\Money;
use Acme\Model\Product;

final class OfferRuleCollection
{
    /** @var array<OfferInterface> */
    private array $offers = [];

    public function __construct(OfferInterface ...$offers)
    {
        $this->offers = $offers;
    }

    public function add(OfferInterface $offer): void
    {
        $this->offers[] = $offer;
    }

    /** @return array<OfferInterface> */
    public function all(): array
    {
        return $this->offers;
    }

    /**
     * Apply all offers and return the total discount
     * 
     * @param array<Product> $items
     * @return Money
     */
    public function apply(array $items): Money
    {
        $totalDiscount = Money::zero();

        foreach ($this->offers as $offer) {
            $discount = $offer->apply($items);
            $totalDiscount = $totalDiscount->add($discount);
        }

        return $totalDiscount;
    }
}

