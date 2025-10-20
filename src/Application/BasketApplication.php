<?php

declare(strict_types=1);

namespace Acme\Application;

use Acme\Basket\Basket;
use Acme\Container\ServiceFactory;

final class BasketApplication
{
    public function __construct(
        private readonly ServiceFactory $serviceFactory
    ) {}

    public function createBasket(): Basket
    {
        return $this->serviceFactory->createBasket();
    }

    public function runDemo(): void
    {
        echo "================================================\n";
        echo "Test 1: B01, G01\n";
        $basket = $this->createBasket();
        $basket->add('B01')->add('G01');
        echo "Total price: " . $basket->total()->format();
        echo "\n";

        echo "================================================\n";
        echo "Test 2: R01, R01\n";
        $basket = $this->createBasket();
        $basket->add('R01')->add('R01');
        echo "Total price: " . $basket->total()->format();
        echo "\n";

        echo "================================================\n";
        echo "Test 3: R01, G01\n";
        $basket = $this->createBasket();
        $basket->add('R01')->add('G01');
        echo "Total price: " . $basket->total()->format();
        echo "\n";

        echo "================================================\n";
        echo "Test 4: B01, B01, R01, R01, R01\n";
        $basket = $this->createBasket();
        $basket->add('B01')->add('B01')->add('R01')->add('R01')->add('R01');
        echo "Total price: " . $basket->total()->format();
        echo "\n";
    }
}
