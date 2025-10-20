<?php

declare(strict_types=1);

namespace Acme\Container;

use Acme\Basket\Basket;
use Acme\Catalogue\CatalogueInterface;
use Acme\Catalogue\ProductCatalogue;
use Acme\DeliveryRules\DeliveryChargeRuleCollection;
use Acme\DeliveryRules\DeliveryChargeRule;
use Acme\Offers\OfferRuleCollection;
use Acme\Offers\BuyOneGetOneHalfPriceOffer;
use Acme\Model\Product;
use Acme\Value\Money;

final class Container implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $services = [];

    public function __construct()
    {
        $this->registerServices();
    }

    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new \InvalidArgumentException("Service not found: $id");
        }

        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, mixed $value): void
    {
        $this->services[$id] = $value;
    }

    private function registerServices(): void
    {
        // Register products
        $this->services['products'] = new ProductCatalogue(
            new Product('R01', 'Red Widget', Money::fromCents(3295)),
            new Product('G01', 'Green Widget', Money::fromCents(2495)),
            new Product('B01', 'Blue Widget', Money::fromCents(795)),
        );

        // Register delivery rules
        $this->services['delivery_rules'] = new DeliveryChargeRuleCollection(
            new DeliveryChargeRule(Money::fromCents(5000), Money::fromCents(495)),
            new DeliveryChargeRule(Money::fromCents(9000), Money::fromCents(295)),
        );

        // Register offer rules
        $this->services['offer_rules'] = new OfferRuleCollection(
            new BuyOneGetOneHalfPriceOffer('R01'),
        );

        // Register basket factory
        $this->services['basket_factory'] = function (): Basket {
            return new Basket(
                $this->get('products'),
                $this->get('delivery_rules'),
                $this->get('offer_rules')
            );
        };
    }
}
