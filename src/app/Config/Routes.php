<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->resource('clientes');
/** É o equivalente a escrever:
 * $routes->get('clientes/new',             'Clientes::new');
 * $routes->post('clientes',                'Clientes::create');
 * $routes->get('clientes',                 'Clientes::index');
 * $routes->get('clientes/(:segment)',      'Clientes::show/$1');
 * $routes->get('clientes/(:segment)/edit', 'Clientes::edit/$1');
 * $routes->put('clientes/(:segment)',      'Clientes::update/$1');
 * $routes->patch('clientes/(:segment)',    'Clientes::update/$1');
 * $routes->delete('clientes/(:segment)',   'Clientes::delete/$1');
 */
$routes->resource('fornecedores');
$routes->resource('produtos');
$routes->resource('compras');
$routes->resource('carrinhos');
$routes->resource('pagamentos');