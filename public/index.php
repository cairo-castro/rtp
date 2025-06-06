<?php
// Final production-ready index.php for Hostinger server
// This file should be placed directly in /rtp_teste/ folder

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define paths for server structure
define('APP_ROOT', dirname(__DIR__)); // Parent directory of public
define('PUBLIC_PATH', __DIR__ . '/assets');
define('BASE_URL', '/');

// Load configuration (includes database.php and helpers)
require_once APP_ROOT . '/src/config/app.php';

// Enhanced autoloader for production
spl_autoload_register(function ($class) {
    $directories = [
        APP_ROOT . '/src/',
        APP_ROOT . '/src/controllers/',
        APP_ROOT . '/src/core/',
        APP_ROOT . '/src/models/',
        APP_ROOT . '/src/helpers/'
    ];
    
    foreach ($directories as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Try with namespace replacement
    $file = APP_ROOT . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize error handler
try {
    ErrorHandler::register();
} catch (Exception $e) {
    die('Error handler initialization failed: ' . $e->getMessage());
}

// Initialize and dispatch router
try {
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    ErrorHandler::handleException($e);
}
