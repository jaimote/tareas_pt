<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


$routes->get('tasks', 'TasksController::index');
$routes->get('tasks/(:num)', 'TasksController::show/$1');
$routes->post('tasks', 'TasksController::create');
$routes->put('tasks/(:num)', 'TasksController::update/$1');
$routes->delete('tasks/(:num)', 'TasksController::delete/$1');

