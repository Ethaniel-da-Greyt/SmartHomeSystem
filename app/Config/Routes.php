<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/smarthome', 'SmartHome::index');           // Web UI
    $routes->get('/smarthome/setPower/(:num)', 'SmartHome::setPower/$1'); // Turn ON/OFF

    $routes->get('/dashboard', function () {
        return view('pages/dashboard');
    });

    $routes->get('/maintenance', function () {
        return view('pages/maintenance');
    });

    $routes->get('/remote-control', function () {
        return view('pages/remote-control');
    });

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