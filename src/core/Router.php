<?php

class Router {
    private $routes;
      public function __construct() {
        $this->routes = require APP_ROOT . '/src/config/routes.php';
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove BASE_URL from the path to get the route path
        $basePath = rtrim(BASE_URL, '/');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Ensure we have a clean path starting with /
        $path = '/' . ltrim($uri, '/');
        $path = rtrim($path, '/') ?: '/';
        
        // Verificar se é um arquivo estático (assets, favicon, etc.)
        if ($this->isStaticFile($path)) {
            $this->handleStaticFile($path);
            return;
        }
        
        // Validação de segurança básica
        if (!$this->isValidPath($path)) {
            throw new Exception('Caminho inválido', 400);
        }
        
        if (!isset($this->routes[$method][$path])) {
            throw new Exception('Rota não encontrada', 404);
        }
        
        $route = $this->routes[$method][$path];
        $controllerName = $route[0];
        $methodName = $route[1];
        
        // Validar controller
        if (!class_exists($controllerName)) {
            throw new Exception('Controller não encontrado', 404);
        }
        
        $controller = new $controllerName();
        
        if (!method_exists($controller, $methodName)) {
            throw new Exception('Método não encontrado', 404);
        }
        
        // Executar controller
        $controller->$methodName();
    }
    
    private function isStaticFile($path) {
        // Permitir assets e arquivos comuns
        return preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path) ||
               $path === '/favicon.ico';
    }
      private function handleStaticFile($path) {
        if ($path === '/favicon.ico') {
            // Se favicon não existe, retornar 204 (No Content) em vez de 404
            http_response_code(204);
            return;
        }
        
        $filePath = PUBLIC_PATH . $path;
        
        if (!file_exists($filePath)) {
            http_response_code(404);
            return;
        }
        
        // Determinar tipo MIME
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml'
        ];
        
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';
        
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }

    private function isValidPath($path) {
        // Prevenir directory traversal e caracteres perigosos
        return !preg_match('/\.\./', $path) && preg_match('/^[\/\w\.-]*$/', $path);
    }
}
