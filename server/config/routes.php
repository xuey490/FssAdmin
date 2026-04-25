<?php
// config/routes.php
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('test_circuit', new Route('/test/circuit', [
    '_controller' => 'App\Controllers\Test::circuitAction'
]));

$routes->add('test_healthy', new Route('/test/healthy', [
    '_controller' => 'App\Controllers\Test::healthyAction'
]));

return $routes;