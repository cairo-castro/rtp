<?php

/**
 * Sistema de proteção CSRF (Cross-Site Request Forgery)
 * 
 * @author Equipe EMSERH
 * @version 1.0.0
 */
class CsrfProtection {
    
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_EXPIRY_KEY = 'csrf_token_expiry';
    private const TOKEN_LIFETIME = 3600; // 1 hora
    
    /**
     * Gera um novo token CSRF
     * 
     * @return string
     */
    public static function generateToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::TOKEN_EXPIRY_KEY] = time() + self::TOKEN_LIFETIME;
        
        return $token;
    }
    
    /**
     * Obtém o token CSRF atual da sessão
     * 
     * @return string|null
     */
    public static function getToken(): ?string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar se o token expirou
        if (isset($_SESSION[self::TOKEN_EXPIRY_KEY]) && 
            $_SESSION[self::TOKEN_EXPIRY_KEY] < time()) {
            self::clearToken();
            return null;
        }
        
        return $_SESSION[self::TOKEN_KEY] ?? null;
    }
    
    /**
     * Valida um token CSRF
     * 
     * @param string $token
     * @return bool
     */
    public static function validateToken(string $token): bool {
        if (empty($token)) {
            return false;
        }
        
        $sessionToken = self::getToken();
        if (!$sessionToken) {
            return false;
        }
        
        // Comparação timing-safe para prevenir timing attacks
        return hash_equals($sessionToken, $token);
    }
    
    /**
     * Valida token a partir da requisição
     * 
     * @param string $method Método HTTP (GET, POST, etc.)
     * @return bool
     */
    public static function validateRequest(string $method = null): bool {
        $method = $method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        
        // Apenas validar para métodos que podem modificar estado
        if (!in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            return true;
        }
        
        $token = $_POST[self::TOKEN_KEY] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return self::validateToken($token);
    }
    
    /**
     * Gera campo hidden para formulários
     * 
     * @return string
     */
    public static function getHiddenField(): string {
        $token = self::getToken() ?? self::generateToken();
        return sprintf('<input type="hidden" name="%s" value="%s">', 
                      self::TOKEN_KEY, 
                      htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Gera meta tag para AJAX requests
     * 
     * @return string
     */
    public static function getMetaTag(): string {
        $token = self::getToken() ?? self::generateToken();
        return sprintf('<meta name="csrf-token" content="%s">', 
                      htmlspecialchars($token, ENT_QUOTES, 'UTF-8'));
    }
    
    /**
     * Remove token da sessão
     * 
     * @return void
     */
    public static function clearToken(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        unset($_SESSION[self::TOKEN_KEY]);
        unset($_SESSION[self::TOKEN_EXPIRY_KEY]);
    }
    
    /**
     * Middleware para validação automática de CSRF
     * 
     * @throws Exception
     * @return void
     */
    public static function protect(): void {
        if (!self::validateRequest()) {
            error_log('CSRF Protection: Token inválido ou ausente. IP: ' . 
                     ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . 
                     ', User-Agent: ' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'));
            
            throw new Exception('Token CSRF inválido', 403);
        }
    }
    
    /**
     * Regenera token após operações sensíveis
     * 
     * @return string
     */
    public static function regenerateToken(): string {
        self::clearToken();
        return self::generateToken();
    }
    
    /**
     * Verifica se o referer é válido (proteção adicional)
     * 
     * @return bool
     */
    public static function validateReferer(): bool {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        if (empty($referer) || empty($host)) {
            return false;
        }
        
        $refererHost = parse_url($referer, PHP_URL_HOST);
        return $refererHost === $host;
    }
    
    /**
     * Proteção completa com validação de referer
     * 
     * @param bool $checkReferer
     * @throws Exception
     * @return void
     */
    public static function fullProtection(bool $checkReferer = true): void {
        // Validar CSRF token
        if (!self::validateRequest()) {
            throw new Exception('Token CSRF inválido', 403);
        }
        
        // Validar referer se solicitado
        if ($checkReferer && !self::validateReferer()) {
            error_log('CSRF Protection: Referer inválido. IP: ' . 
                     ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            throw new Exception('Referer inválido', 403);
        }
    }
}
