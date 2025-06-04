<?php
/**
 * Gerenciamento de Sessão e Integração com Sistema Principal
 * 
 * Este arquivo gerencia a integração com o sistema de login existente,
 * verificando automaticamente se o usuário está logado e obtendo
 * sua unidade associada.
 */

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica se o usuário está logado no sistema principal
 * 
 * @return bool True se logado, false caso contrário
 */
function isUserLoggedIn(): bool {
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

/**
 * Obtém o ID do usuário logado
 * 
 * @return int|null ID do usuário ou null se não logado
 */
function getLoggedUserId(): ?int {
    return isUserLoggedIn() ? (int)$_SESSION['id'] : null;
}

/**
 * Obtém a unidade do usuário logado a partir do banco de dados
 * 
 * @return int|null ID da unidade ou null se não encontrada
 */
function getUserUnidade(): ?int {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        // Incluir conexão com banco
        require_once __DIR__ . '/database.php';
        
        $userId = getLoggedUserId();
        
        // Consulta segura para obter unidade do usuário
        $sql = "SELECT unidade_id FROM usuario WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar consulta de unidade: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)$row['unidade_id'];
        }
        
        $stmt->close();
        return null;
        
    } catch (Exception $e) {
        error_log("Erro ao buscar unidade do usuário: " . $e->getMessage());
        return null;
    }
}

/**
 * Obtém informações completas do usuário logado
 * 
 * @return array|null Array com dados do usuário ou null se não logado
 */
function getUserInfo(): ?array {
    if (!isUserLoggedIn()) {
        return null;
    }
    
    try {
        require_once __DIR__ . '/database.php';
        
        $userId = getLoggedUserId();
        
        $sql = "SELECT id, nome, login, unidade_id FROM usuario WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Erro ao preparar consulta de usuário: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            $stmt->close();
            return $userData;
        }
        
        $stmt->close();
        return null;
        
    } catch (Exception $e) {
        error_log("Erro ao buscar informações do usuário: " . $e->getMessage());
        return null;
    }
}

/**
 * Redireciona para login se usuário não estiver logado
 * 
 * @param string $loginUrl URL da página de login
 */
function requireLogin(string $loginUrl = '/logon.php'): void {
    if (!isUserLoggedIn()) {
        header("Location: $loginUrl");
        exit();
    }
}
