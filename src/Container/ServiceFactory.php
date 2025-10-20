<?php

declare(strict_types=1);

namespace Acme\Container;

use Acme\Basket\Basket;
use Acme\Catalogue\CatalogueInterface;
use Acme\DeliveryRules\DeliveryChargeRuleCollection;
use Acme\Offers\OfferRuleCollection;

final class ServiceFactory
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function createBasket(): Basket
    {
        $factory = $this->container->get('basket_factory');
        
        if (is_callable($factory)) {
            return $factory();
        }
        
        throw new \RuntimeException('Basket factory is not callable');
    }

    public function getCatalogue(): CatalogueInterface
    {
        return $this->container->get('products');
    }

    public function getDeliveryRules(): DeliveryChargeRuleCollection
    {
        return $this->container->get('delivery_rules');
    }

    public function getOfferRules(): OfferRuleCollection
    {
        return $this->container->get('offer_rules');
    }
}
