<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/login', 'Auth::login');
$routes->post('/auth/validate', 'Auth::validate');
$routes->get('/logout', 'Auth::logout');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/admin', 'Administrador::index');
$routes->get('/usuario', 'Usuario::index');
$routes->get('/vigilante', 'Vigilante::index');

// API routes
$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('estadisticas', 'api_estadisticas_simple::index');
    $routes->get('reservas', 'api_reservas::index');
    $routes->post('pagos', 'pagos_api::index');
});

$routes->setAutoRoute(true);
