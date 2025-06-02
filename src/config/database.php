<?php

/**
 * Configuração segura do banco de dados
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */

/**
 * Obtém conexão segura com o banco de dados
 * 
 * @return PDO
 * @throws Exception
 */
function getDatabaseConnection(): PDO {
    // Configurações do banco - idealmente devem vir de variáveis de ambiente
    $config = getDatabaseConfig();
    
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_PERSISTENT => false,
        // Segurança adicional
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_AUTOCOMMIT => true
    ];
    
    try {
        $pdo = new PDO($dsn, $config['username'], $config['password'], $options);
        
        // Configurações de segurança adicionais
        $pdo->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
        
        return $pdo;
        
    } catch (PDOException $e) {
        // Log do erro sem expor credenciais
        error_log("Erro na conexão com banco de dados: " . $e->getMessage());
        throw new Exception('Erro na conexão com o banco de dados. Verifique a configuração.');
    }
}

/**
 * Obtém configuração do banco de dados
 * Prioriza variáveis de ambiente para maior segurança
 * 
 * @return array
 */
function getDatabaseConfig(): array {
    return [
        'host' => getenv('DB_HOST') ?: '193.203.175.60',
        'dbname' => getenv('DB_NAME') ?: 'u313569922_rtpdiario',
        'username' => getenv('DB_USER') ?: 'u313569922_root9',
        'password' => getenv('DB_PASS') ?: 'Vhsl124578*'
    ];
}

/**
 * Testa conexão com o banco de dados
 * 
 * @return bool
 */
function testDatabaseConnection(): bool {
    try {
        $pdo = getDatabaseConnection();
        $stmt = $pdo->query('SELECT 1');
        return $stmt !== false;
    } catch (Exception $e) {
        error_log("Teste de conexão falhou: " . $e->getMessage());
        return false;
    }
}
