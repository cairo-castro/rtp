<?php

/**
 * Configurações gerais da aplicação
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */

// Configurações de ambiente
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', APP_ENV === 'development');
define('APP_VERSION', '1.1.0');
define('APP_NAME', 'RTP Hospital');

// Configurações de segurança
define('SESSION_TIMEOUT', 3600); // 1 hora
define('MAX_LOGIN_ATTEMPTS', 5);
define('RATE_LIMIT_REQUESTS', 100); // Requests por hora por IP

// Configurações de logs
define('LOG_LEVEL', APP_DEBUG ? 'DEBUG' : 'ERROR');
define('LOG_MAX_SIZE', 10 * 1024 * 1024); // 10MB

// Configurações de cache
define('CACHE_ENABLED', true);
define('CACHE_TTL', 300); // 5 minutos

// Configurações de banco
define('DB_POOL_SIZE', 10);
define('DB_TIMEOUT', 30);

/**
 * Configurações de segurança para headers
 */
function getSecurityHeaders(): array {
    return [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
    ];
}

/**
 * Aplica headers de segurança
 */
function applySecurityHeaders(): void {
    if (headers_sent()) {
        return;
    }
    
    foreach (getSecurityHeaders() as $header => $value) {
        header("{$header}: {$value}");
    }
}

/**
 * Configuração de timezone
 */
date_default_timezone_set('America/Sao_Paulo');

/**
 * Configuração de locale para português brasileiro
 */
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'portuguese');
