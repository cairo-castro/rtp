<?php
// Test script to verify gerencia route functionality

// Define required constants
define('APP_ROOT', __DIR__);
define('BASE_URL', '/');
define('PUBLIC_PATH', __DIR__ . '/public');

// Include autoloader and session
require_once __DIR__ . '/src/config/session.php';

// Set up test environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/gerencia';

try {
    // Include necessary classes
    require_once __DIR__ . '/src/core/Controller.php';
    require_once __DIR__ . '/src/core/Router.php';
    require_once __DIR__ . '/src/core/ErrorHandler.php';
    require_once __DIR__ . '/src/helpers/relatorio_helpers.php';
    require_once __DIR__ . '/src/models/RelatorioModel.php';
    require_once __DIR__ . '/src/controllers/RelatorioController.php';

    // Test router
    $router = new Router();
    echo "Router created successfully.\n";
    
    // Test route existence
    $routes = require __DIR__ . '/src/config/routes.php';
    var_dump($routes);
    
    if (isset($routes['GET']['/gerencia'])) {
        echo "Route /gerencia exists in routes.php\n";
        
        // Test controller instantiation
        $controller = new RelatorioController();
        echo "RelatorioController created successfully.\n";
        
        // Test method existence
        if (method_exists($controller, 'gerencia')) {
            echo "gerencia method exists in RelatorioController.\n";
            
            // Test method call (without actually executing to avoid output)
            $reflection = new ReflectionMethod($controller, 'gerencia');
            echo "gerencia method is accessible: " . ($reflection->isPublic() ? "YES" : "NO") . "\n";
        } else {
            echo "ERROR: gerencia method does not exist in RelatorioController.\n";
        }
    } else {
        echo "ERROR: Route /gerencia does not exist in routes.php\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
