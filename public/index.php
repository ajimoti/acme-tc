<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Acme\Container\Container;
use Acme\Container\ServiceFactory;
use Acme\Application\BasketApplication;

$container = new Container();
$serviceFactory = new ServiceFactory($container);
$application = new BasketApplication($serviceFactory);

$application->runDemo();