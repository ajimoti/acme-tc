<?php 

declare(strict_types=1);

namespace Acme\Basket;

use Acme\Value\Money;
use Acme\Catalogue\CatalogueInterface;
use Acme\Offers\OfferRuleCollection;
use Acme\DeliveryRules\DeliveryChargeRuleCollection;

final class Basket {
    /** @var array<\Acme\Model\Product> */
    private array $items = [];

    public function __construct(
        private readonly CatalogueInterface $catalogue,
        private readonly DeliveryChargeRuleCollection $deliveryChargeRules,
        private readonly OfferRuleCollection $offerRules,
    )
    {
    }

    public function add(string $productCode): self 
    {
        $product = $this->catalogue->getProduct($productCode);

        $this->items[] = $product;
        
        return $this;
    }

    public function total(): Money
    {
        $total = Money::zero();
        foreach ($this->items as $item) {
            $total = $total->add($item->price());
        }

        // Apply offers (subtract discounts)
        $discount = $this->offerRules->apply($this->items);
        $total = $total->sub($discount);

        // Apply delivery charges
        return $this->deliveryChargeRules->apply($total);
    }
}