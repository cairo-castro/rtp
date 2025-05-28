<?php

/**
 * Controller base simplificado para relatórios
 * 
 * @author Equipe EMSERH
 * @version 2.0.0 - Versão simplificada
 */
abstract class Controller {
    
    /**
     * Renderiza uma view com dados
     * 
     * @param string $view
     * @param array $data
     * @return void
     */
    protected function render($view, $data = []) {
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
     * Retorna dados em formato JSON
     * 
     * @param array $data
     * @param int $httpCode
     * @return void
     */
    protected function json($data, $httpCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($httpCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Redireciona para uma URL
     * 
     * @param string $url
     * @return void
     */
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
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
}
