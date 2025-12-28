<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/smarthome', 'SmartHome::index');           // Web UI
    $routes->get('/smarthome/setPower/(:num)', 'SmartHome::setPower/$1'); // Turn ON/OFF

    $routes->get('/dashboard', 'Home::dashboard');

    $routes->get('/maintenance', 'MaintenanceController::maintenance');

    $routes->get('/remote-control', 'Home::remote_control');

    $routes->get('/auth/logout', 'AuthController::logout');

});

$routes->group('', ['filter' => 'guest'], function ($routes) {
    $routes->get('/signup', function () {
        return view('signup');
    });

    $routes->get('/', 'AuthController::index'); //Login Page View
    $routes->post('/auth/signup', 'AuthController::signUp');
    $routes->post('/auth/login', 'AuthController::login');
});


// Smarthome API
// $routes->group('smarthome/api', function ($routes) {
//     $routes->get('device/state/(:any)', 'DeviceController::api_device_state/$1'); // GET device status
//     $routes->post('device/toggle', 'DeviceController::api_device_toggle');       // POST toggle state
// });

$routes->group('smarthome/api', function ($routes) {
    $routes->post('kwh', 'Smarthome::api_kwh');                     // ESP sends kWh
    $routes->get('device/state/(:any)', 'DeviceController::api_device_state/$1');
    $routes->post('device/toggle', 'DeviceController::api_device_toggle');
    $routes->post('api/faults', 'FaultController::api_store');
    $routes->get('faults', 'FaultController::index');
});



