<?php
use App\Core\Router;

/** @var Router $router */

// Auth Routes
$router->get('/login', 'App\Controllers\Foundation\AuthController', 'index');
$router->post('/login', 'App\Controllers\Foundation\AuthController', 'login');
$router->get('/logout', 'App\Controllers\Foundation\AuthController', 'logout');

// Dashboard Route
$router->get('/', 'App\Controllers\Foundation\DashboardController', 'index');

// Users Routes (RBAC)
$router->get('/users', 'App\Controllers\Foundation\UserController', 'index');
$router->get('/users/create', 'App\Controllers\Foundation\UserController', 'create');
$router->post('/users/store', 'App\Controllers\Foundation\UserController', 'store');
$router->post('/users/delete/{id}', 'App\Controllers\Foundation\UserController', 'delete');

// Product Routes (PIM)
$router->get('/products', 'App\Controllers\PIM\ProductController', 'index');
$router->get('/products/create', 'App\Controllers\PIM\ProductController', 'create');
$router->post('/products/store', 'App\Controllers\PIM\ProductController', 'store');
$router->get('/products/edit/{id}', 'App\Controllers\PIM\ProductController', 'edit');
$router->post('/products/update/{id}', 'App\Controllers\PIM\ProductController', 'update');
$router->post('/products/delete/{id}', 'App\Controllers\PIM\ProductController', 'delete');

// Inventory Routes (WMS)
$router->get('/inventory', 'App\Controllers\WMS\InventoryController', 'index');
$router->get('/inventory/receive', 'App\Controllers\WMS\InventoryController', 'receive');
$router->post('/inventory/store', 'App\Controllers\WMS\InventoryController', 'store');

// Customer Routes (CRM)
$router->get('/customers', 'App\Controllers\CRM\CustomerController', 'index');
$router->get('/customers/create', 'App\Controllers\CRM\CustomerController', 'create');
$router->post('/customers/store', 'App\Controllers\CRM\CustomerController', 'store');

// Sales Routes
$router->get('/orders', 'App\Controllers\Sales\OrderController', 'index');
$router->get('/orders/create', 'App\Controllers\Sales\OrderController', 'create');
$router->post('/orders/store', 'App\Controllers\Sales\OrderController', 'store');
