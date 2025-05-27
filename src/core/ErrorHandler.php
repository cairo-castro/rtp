<?php

/**
 * Tratamento centralizado de erros com segurança
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */
class ErrorHandler {
    
    private static $logFile = null;
    
    /**
     * Registra os handlers de erro
     */
    public static function register() {
        self::$logFile = APP_ROOT . '/logs/error.log';
        
        // Criar diretório de logs se não existir
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
        
        // Configurar exibição de erros baseado no ambiente
        self::configureErrorDisplay();
    }
    
    /**
     * Trata exceções não capturadas
     * 
     * @param Throwable $exception
     */
    public static function handleException($exception) {
        $code = $exception->getCode() ?: 500;
        
        // Log do erro
        self::logError($exception->getMessage(), $exception->getFile(), $exception->getLine(), $exception->getTraceAsString());
        
        // Enviar código HTTP apropriado
        http_response_code($code);
        
        // Adicionar headers de segurança
        self::addSecurityHeaders();
        
        if ($code === 404) {
            self::render404();
        } else {
            self::renderError('Erro interno do servidor', $code);
        }
    }
    
    /**
     * Trata erros PHP
     * 
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @return bool
     * @throws ErrorException
     */
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        // Log do erro
        self::logError($message, $file, $line);
        
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    
    /**
     * Trata erros fatais
     */
    public static function handleShutdown() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError($error['message'], $error['file'], $error['line']);
            self::renderError('Erro fatal do sistema', 500);
        }
    }
    
    /**
     * Configura exibição de erros baseado no ambiente
     */
    private static function configureErrorDisplay() {
        $isProduction = self::isProduction();
        
        if ($isProduction) {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        } else {
            ini_set('display_errors', '1');
            ini_set('log_errors', '1');
            error_reporting(E_ALL);
        }
    }
    
    /**
     * Adiciona headers de segurança
     */
    private static function addSecurityHeaders() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
        }
    }
    
    /**
     * Faz log do erro de forma segura
     * 
     * @param string $message
     * @param string $file
     * @param int $line
     * @param string $trace
     */
    private static function logError($message, $file = '', $line = 0, $trace = '') {
        $timestamp = date('Y-m-d H:i:s');
        $ip = self::getClientIp();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        $logMessage = "[{$timestamp}] ERROR: {$message}\n";
        $logMessage .= "File: {$file}:{$line}\n";
        $logMessage .= "IP: {$ip}\n";
        $logMessage .= "User-Agent: " . substr($userAgent, 0, 200) . "\n";
        $logMessage .= "Request: {$requestUri}\n";
        
        if ($trace) {
            $logMessage .= "Trace: {$trace}\n";
        }
        
        $logMessage .= str_repeat('-', 80) . "\n";
        
        // Escrever no log de forma segura
        if (self::$logFile) {
            error_log($logMessage, 3, self::$logFile);
        }
        
        // Também usar o log padrão do PHP
        error_log("RTP Error: {$message} in {$file}:{$line}");
    }
      /**
     * Obtém IP do cliente de forma segura
     * 
     * @return string
     */
    private static function getClientIp(): string {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Se for uma lista de IPs, pegar o primeiro
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Validar se é um IP válido
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Verifica se está em ambiente de produção
     * 
     * @return bool
     */
    private static function isProduction(): bool {
        $env = getenv('APP_ENV') ?: ($_SERVER['APP_ENV'] ?? 'production');
        return $env === 'production';
    }
    
    /**
     * Renderiza página 404
     */
    private static function render404() {
        self::addSecurityHeaders();
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página não encontrada - RTP Hospital</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background-color: #f8f9fa;
            color: #333;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #e74c3c; 
            margin-bottom: 20px;
        }
        p { 
            color: #666; 
            margin-bottom: 30px;
        }
        a { 
            color: #3498db; 
            text-decoration: none;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            display: inline-block;
        }
        a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404 - Página não encontrada</h1>
        <p>A página solicitada não foi encontrada no sistema.</p>
        <a href="/">Voltar ao Dashboard</a>
    </div>
</body>
</html>';
    }
    
    /**
     * Renderiza página de erro genérica
     * 
     * @param string $message
     * @param int $code
     */
    private static function renderError($message, $code) {
        self::addSecurityHeaders();
        
        // Em produção, não mostrar detalhes do erro
        if (self::isProduction()) {
            $message = 'Ocorreu um erro interno. Nossa equipe foi notificada.';
        }
        
        echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro ' . $code . ' - RTP Hospital</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            text-align: center; 
            padding: 50px; 
            background-color: #f8f9fa;
            color: #333;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #e74c3c; 
            margin-bottom: 20px;
        }
        p { 
            color: #666; 
            margin-bottom: 30px;
        }
        a { 
            color: #3498db; 
            text-decoration: none;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border-radius: 4px;
            display: inline-block;
        }
        a:hover {
            background-color: #2980b9;
        }
        .error-code {
            font-size: 0.9em;
            color: #999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Erro ' . $code . '</h1>
        <p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>
        <a href="/">Voltar ao Dashboard</a>
        <div class="error-code">Código do erro: ' . $code . '</div>
    </div>
</body>
</html>';
    }
}
