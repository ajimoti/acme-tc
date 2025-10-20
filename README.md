# Acme Widget Co - Basket System

A proof of concept for Acme Widget Co's sales system with support for products, delivery charge rules, and special offers.

## Overview

This system implements a flexible basket that can handle:
- Product catalogue management
- Dynamic delivery charges based on basket total
- Special offers (e.g., "buy one get one half price")

## Project Structure

```
src/
├── Application/
│   └── BasketApplication.php                 # Demo application runner
├── Basket/
│   └── Basket.php                            # Main basket domain object
├── Catalogue/
│   ├── CatalogueInterface.php                # Product catalogue interface
│   └── ProductCatalogue.php                  # In-memory product catalogue
├── Container/
│   ├── ContainerInterface.php                # Minimal DI container contract
│   ├── Container.php                         # Simple DI container + registrations
│   └── ServiceFactory.php                    # Factory for creating services (e.g., Basket)
├── DeliveryRules/
│   ├── DeliveryRuleInterface.php             # Strategy interface for delivery rules
│   ├── DeliveryChargeRule.php                # Tiered delivery rule
│   ├── DeliveryChargeRuleCollection.php      # Collection orchestrating delivery rules
│   └── FreeDeliveryRule.php                  # Free delivery over threshold
├── Offers/
│   ├── OfferInterface.php                    # Strategy interface for offers
│   ├── OfferRuleCollection.php               # Collection orchestrating offer strategies
│   ├── BuyOneGetOneHalfPriceOffer.php        # "Buy one, get the second half price"
├── Model/
│   └── Product.php                           # Product entity
└── Value/
    └── Money.php                             # Immutable money value object

public/
└── index.php                                 # Entry point running BasketApplication

tests/
├── Basket/
├── Catalogue/
├── DeliveryRules/
├── Offers/
├── Model/
└── Value/

Dockerfile                                     # PHP 8.2 CLI image
docker-compose.yml                              # app/test/stan/dev services
composer.json                                   # dependencies + scripts
phpunit.xml                                     # PHPUnit configuration
run-tests.sh                                    # Convenience script for tests + stan
```

## Features

### 1. Product Catalogue
The system supports three products:
- **Red Widget** (R01): $32.95
- **Green Widget** (G01): $24.95
- **Blue Widget** (B01): $7.95

### 2. Delivery Charges
Delivery costs are tiered based on basket total:
- Orders under $50: $4.95 delivery
- Orders under $90: $2.95 delivery
- Orders $90 or more: FREE delivery

### 3. Special Offers
Currently implemented: **"Buy One Red Widget, Get the Second Half Price"**
- Applies to Red Widgets (R01)
- For every 2 red widgets, the second one is 50% off
- Example: 3 red widgets = 1st full price + 2nd half price + 3rd full price

## Installation

```bash
composer install
```

## Usage

### Basic Example

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Acme\Basket\Basket;
use Acme\Catalogue\ProductCatalogue;
use Acme\Basket\DeliveryChargeRuleCollection;
use Acme\Basket\DeliveryChargeRule;
use Acme\Basket\OfferRuleCollection;
use Acme\Basket\BuyOneGetOneHalfPriceOffer;
use Acme\Model\Product;
use Acme\Value\Money;

// Setup products
$products = new ProductCatalogue(
    new Product('R01', 'Red Widget', Money::fromCents(3295)),
    new Product('G01', 'Green Widget', Money::fromCents(2495)),
    new Product('B01', 'Blue Widget', Money::fromCents(795)),
);

// Setup delivery charge rules
$deliveryChargeRules = new DeliveryChargeRuleCollection(
    new DeliveryChargeRule(Money::fromCents(5000), Money::fromCents(495)),
    new DeliveryChargeRule(Money::fromCents(9000), Money::fromCents(295)),
);

// Setup offer rules
$offerRules = new OfferRuleCollection(
    new BuyOneGetOneHalfPriceOffer('R01'),
);

// Create basket and add items
$basket = new Basket($products, $deliveryChargeRules, $offerRules);
$basket->add('R01')->add('R01');

// Get total
echo $basket->total()->format(); // Output: $54.37
```

## Test Results

The implementation has been tested with the following scenarios:

| Products | Expected | Actual | Status |
|----------|----------|--------|--------|
| B01, G01 | $37.85 | $37.85 | Pass |
| R01, R01 | $54.37 | $54.37 | Pass |
| R01, G01 | $60.85 | $60.85 | Pass |
| B01, B01, R01, R01, R01 | $98.27 | $98.27 | Pass |

### Calculation Breakdown

**Test 1: B01, G01**
- Blue Widget: $7.95
- Green Widget: $24.95
- Subtotal: $32.90
- Delivery: $4.95 (under $50)
- **Total: $37.85**

**Test 2: R01, R01**
- Red Widget: $32.95
- Red Widget (50% off): $16.48
- Subtotal: $49.43
- Delivery: $4.95 (under $50)
- **Total: $54.37**

**Test 3: R01, G01**
- Red Widget: $32.95
- Green Widget: $24.95
- Subtotal: $57.90
- Delivery: $2.95 (under $90)
- **Total: $60.85**

**Test 4: B01, B01, R01, R01, R01**
- Blue Widget: $7.95
- Blue Widget: $7.95
- Red Widget: $32.95
- Red Widget (50% off): $16.48
- Red Widget: $32.95
- Subtotal: $98.28
- Delivery: $0.00 (over $90)
- **Total: $98.27**

## Local Setup

Prerequisites:
- PHP 8.2+
- Composer

Install dependencies:
```bash
composer install
```

Run the demo app:
```bash
php public/index.php
```

Run the test suite:
```bash
composer test
```

Run static analysis:
```bash
composer stan
```

## Docker

This project includes a Dockerfile and docker-compose.yml to run the app, tests, and static analysis in containers.

Build images:
```bash
docker compose build
```

Run tests in Docker:
```bash
docker compose run --rm test
```

Run PHPStan in Docker:
```bash
docker compose run --rm stan
```

Run the application in Docker (serves public/index.php on port 8000):
```bash
docker compose up app
# Visit http://localhost:8000
```

Development mode (mounts your working directory and serves):
```bash
docker compose up dev
```

Stop containers:
```bash
docker compose down
```

## Design Decisions & Assumptions

### 1. Architecture
- **Value Object Pattern**: `Money` class handles all monetary operations with cent precision to avoid floating-point errors
- **Interface-Driven Design**: `OfferInterface` and `DeliveryRuleInterface` allow for easy extension of new offers and rules
- **Collection Pattern**: `OfferRuleCollection` and `DeliveryChargeRuleCollection` manage multiple rules efficiently
- **Immutability**: All value objects are immutable, preventing unintended side effects

### 2. Money Handling
- All prices are stored in cents (integers) to avoid floating-point precision issues
- Rounding is performed using PHP_ROUND_HALF_UP for consistent results
- Division operations include a half-up flag for proper rounding

### 3. Offer Application Logic
- **Order of Operations**:
  1. Calculate subtotal from all products
  2. Apply offers (subtract discounts)
  3. Apply delivery charges based on discounted subtotal
- **"Buy One Get One Half Price" Logic**:
  - Counts matching product codes in the basket
  - For every pair (2 items), applies 50% discount to one item
  - Odd numbers (e.g., 3 items) = 1 pair discounted + 1 full price

### 4. Extensibility
The system is designed to be easily extended:
- **New Products**: Simply add to the `ProductCatalogue`
- **New Delivery Rules**: Implement `DeliveryRuleInterface` and add to collection
- **New Offers**: Implement `OfferInterface` (e.g., `BuyOneGetOneHalfPriceOffer`)
- **Multiple Offers**: The `OfferRuleCollection` can handle multiple simultaneous offers

### 5. Assumptions
- Offers are applied before delivery charges (as this benefits the customer)
- Multiple offers can stack (total discount is sum of all applicable offers)
- Delivery rules are checked in order and first matching rule applies
- Product codes are case-sensitive
- All prices are in USD
- Invalid product codes will throw exceptions (handled by `ProductCatalogue`)


## Author
John Ajimoti

