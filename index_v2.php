<?php
declare(strict_types=1);

/**
 * DMS Enterprise Front Controller (v2)
 * All requests should be routed through here.
 */
session_start();

// Basic PSR-4 Autoloader mapping App\ to current directory
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

use App\Core\Router;

$router = new Router();

// Load Routes
if (file_exists(__DIR__ . '/routes.php')) {
    require_once __DIR__ . '/routes.php';
} else {
    // Example default fallback
    $router->get('/', 'App\Controllers\Foundation\DashboardController', 'index');
}

// Request parsing
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Adjust base path if app is running in a subdirectory
// Adjust base path if app is running in a subdirectory
$basePath1 = '/dms/index_v2.php';
$basePath2 = '/dms';

if (strpos($requestUri, $basePath1) === 0) {
    $requestUri = substr($requestUri, strlen($basePath1));
} elseif (strpos($requestUri, $basePath2) === 0) {
    $requestUri = substr($requestUri, strlen($basePath2));
}
if ($requestUri === '') {
    $requestUri = '/';
}

$router->dispatch($requestMethod, $requestUri);
