<?php

/**
 * Controller base com funcionalidades de segurança e utilitários
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */
abstract class Controller {
    
    /**
     * Renderiza uma view com dados de forma segura
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function render($view, $data = []) {
        // Validar nome da view para prevenir directory traversal
        if (!$this->isValidViewName($view)) {
            throw new Exception('Nome de view inválido', 400);
        }
        
        // Sanitizar dados de saída
        $data = $this->sanitizeOutput($data);
        extract($data);
        
        ob_start();
        $viewPath = APP_ROOT . "/src/views/{$view}.php";
        
        if (!file_exists($viewPath)) {
            throw new Exception('View não encontrada: ' . $view, 404);
        }
        
        require $viewPath;
        $content = ob_get_clean();
        
        require APP_ROOT . '/src/views/layouts/main.php';
    }
    
    /**
     * Retorna dados em formato JSON com headers de segurança
     * 
     * @param array $data
     * @param int $httpCode
     * @return void
     */
    protected function json($data, $httpCode = 200) {
        // Headers de segurança para API
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redireciona para uma URL de forma segura
     * 
     * @param string $url
     * @return void
     */
    protected function redirect($url) {
        // Validar URL para prevenir redirecionamento aberto
        if (!$this->isValidRedirectUrl($url)) {
            throw new Exception('URL de redirecionamento inválida', 400);
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Sanitiza entrada de dados
     * 
     * @param mixed $input
     * @return mixed
     */
    protected function sanitize($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitize'], $input);
        }
        
        if (is_string($input)) {
            $input = trim($input);
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        }
        
        return $input;
    }
      /**
     * Trata erros de forma consistente
     * 
     * @param string $message
     * @param int $code
     * @return void
     */
    protected function handleError($message, $code = 500) {
        // Log do erro
        error_log("Controller Error [{$code}]: {$message}");
        
        // Se for requisição AJAX/API, retornar JSON
        if ($this->isAjaxRequest()) {
            $this->json([
                'error' => true,
                'message' => 'Ocorreu um erro interno. Tente novamente.',
                'code' => $code
            ], $code);
        }
        
        // Redirecionar para página de erro ou mostrar mensagem
        throw new Exception($message, $code);
    }
    
    /**
     * Valida proteção CSRF para requisições que modificam dados
     * 
     * @param bool $required Se a proteção é obrigatória
     * @throws Exception
     * @return void
     */
    protected function validateCsrf($required = true): void {
        if (!$required) {
            return;
        }
        
        try {
            CsrfProtection::protect();
        } catch (Exception $e) {
            $this->handleError('Token de segurança inválido', 403);
        }
    }
    
    /**
     * Verifica se é uma requisição AJAX
     * 
     * @return bool
     */
    protected function isAjaxRequest(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Valida se o método HTTP está correto
     * 
     * @param string $expectedMethod
     * @return bool
     */
    protected function validateHttpMethod($expectedMethod): bool {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($expectedMethod);
    }
    
    /**
     * Valida nome da view para prevenir directory traversal
     * 
     * @param string $viewName
     * @return bool
     */
    private function isValidViewName($viewName): bool {
        return !preg_match('/\.\./', $viewName) && preg_match('/^[a-zA-Z0-9_\/\-]+$/', $viewName);
    }
    
    /**
     * Valida URL de redirecionamento
     * 
     * @param string $url
     * @return bool
     */
    private function isValidRedirectUrl($url): bool {
        // Permitir apenas URLs internas ou relativas
        return strpos($url, '/') === 0 && !preg_match('/^\/\//', $url);
    }
    
    /**
     * Sanitiza dados de saída recursivamente
     * 
     * @param mixed $data
     * @return mixed
     */
    private function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
}
