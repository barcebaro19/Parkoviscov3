<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Ruta principal - mostrar landing (Quintanares)
$routes->get('/', static function () {
    return redirect()->to('/quintanares.php');
});
$routes->get('index.php', static function () {
    return redirect()->to('/quintanares.php');
});

// Compatibilidad: si intentan abrir páginas públicas por /index.php/<archivo>
$routes->get('index.php/Administrador1.php', static function () {
    return redirect()->to('/Administrador1.php');
});
$routes->get('index.php/vigilante.php', static function () {
    return redirect()->to('/vigilante.php');
});
$routes->get('index.php/usuario.php', static function () {
    return redirect()->to('/usuario.php');
});

// Compatibilidad: cuando entran por /index.php/<archivo>.php el Router ve solo '<archivo>.php'
$routes->get('Administrador1.php', static function () {
    return redirect()->to('/Administrador1.php');
});
$routes->get('vigilante.php', static function () {
    return redirect()->to('/vigilante.php');
});
$routes->get('usuario.php', static function () {
    return redirect()->to('/usuario.php');
});

// Debug: forzar escritura en logs
$routes->get('/log-test', 'Home::logTest');

// Rutas de autenticación
$routes->get('/login', 'Auth::login');
$routes->get('/auth/login.php', 'Auth::login');
$routes->get('/index.php/auth/login.php', 'Auth::login');
$routes->post('/auth/authenticate', 'Auth::authenticate');
$routes->post('index.php/auth/authenticate', 'Auth::authenticate');
$routes->post('/auth/reset-password', 'Auth::resetPassword');
$routes->post('index.php/auth/reset-password', 'Auth::resetPassword');
$routes->get('/auth/logout', 'Auth::logout');
$routes->get('index.php/auth/logout', 'Auth::logout');

// Rutas del administrador
$routes->get('/administrador', 'Administrador::index');
$routes->get('/administrador/usuarios', 'Administrador::usuarios');
$routes->get('/administrador/crear-usuario', 'Administrador::crearUsuario');
$routes->post('/administrador/crear-usuario', 'Administrador::crearUsuario');
$routes->get('/administrador/eliminar-usuario/(:num)', 'Administrador::eliminarUsuario/$1');

// Rutas del vigilante
$routes->get('/vigilante', 'Vigilante::index');
$routes->get('/vigilante/control-acceso', 'Vigilante::controlAcceso');
$routes->post('/vigilante/validar-qr', 'Vigilante::validarQR');

// Rutas del propietario
$routes->get('/propietario', 'Propietario::index');
$routes->get('/propietario/mis-vehiculos', 'Propietario::misVehiculos');
$routes->get('/propietario/registrar-visitante', 'Propietario::registrarVisitante');
