<?php
/**
 * Bootstrap da aplicação RTP Hospital
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */

// Iniciar sessão de forma segura
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
session_start();

// Definir constantes fundamentais
define('APP_ROOT', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('BASE_URL', '/');

// Carregar configurações da aplicação
require_once APP_ROOT . '/src/config/app.php';

// Aplicar headers de segurança
applySecurityHeaders();

// Autoload otimizado com cache
spl_autoload_register(function ($class) {
    static $classMap = [];
    
    if (isset($classMap[$class])) {
        require_once $classMap[$class];
        return;
    }
    
    $paths = [
        APP_ROOT . '/src/core/',
        APP_ROOT . '/src/controllers/',
        APP_ROOT . '/src/models/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            $classMap[$class] = $file;
            require_once $file;
            return;
        }
    }
});

// Carregar dependências essenciais
require_once APP_ROOT . '/src/core/ErrorHandler.php';
require_once APP_ROOT . '/src/config/database.php';
require_once APP_ROOT . '/src/helpers/relatorio_helpers.php';

// Configurar tratamento de erros
ErrorHandler::register();

// Rate limiting básico
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$requestCount = $_SESSION['requests'][$clientIp] ?? 0;

if ($requestCount > RATE_LIMIT_REQUESTS) {
    http_response_code(429);
    header('Retry-After: 3600');
    exit('Rate limit exceeded. Try again later.');
}

$_SESSION['requests'][$clientIp] = $requestCount + 1;

try {
    // Verificar se banco está acessível
    if (!testDatabaseConnection()) {
        throw new Exception('Banco de dados indisponível', 503);
    }
    
    // Inicializar e executar roteador
    $router = new Router();
    $router->dispatch();
    
} catch (Exception $e) {
    ErrorHandler::handleException($e);
}
