<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('/smarthome', 'SmartHome::index');           // Web UI
$routes->get('/smarthome/setPower/(:num)', 'SmartHome::setPower/$1'); // Turn ON/OFF

