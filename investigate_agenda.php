<?php
/**
 * Test script para investigar os dados da tabela agenda
 */

// Incluir configurações e classes necessárias
require_once __DIR__ . '/src/config/database.php';

echo "<h1>🔍 Investigação da Tabela Agenda</h1>";
echo "<p>Teste realizado em: " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = getDatabaseConnection();
    
    echo "<h2>📊 Estrutura da Tabela Agenda</h2>";
    $stmt = $pdo->query("DESCRIBE agenda");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>{$coluna['Field']}</td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h2>📈 Total de Registros na Agenda</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agenda");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "📊 Total de registros na agenda: " . number_format($total['total']) . "<br>";
    
    if ($total['total'] > 0) {
        echo "<h2>🏥 Unidades com Dados na Agenda</h2>";
        $stmt = $pdo->query("
            SELECT 
                u.id as unidade_id,
                u.nome as unidade_nome,
                COUNT(a.id) as total_registros
            FROM agenda a
            INNER JOIN unidade u ON a.unidade_id = u.id
            GROUP BY u.id, u.nome
            ORDER BY total_registros DESC
            LIMIT 10
        ");
        $unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Unidade ID</th><th>Nome da Unidade</th><th>Registros na Agenda</th></tr>";
        foreach ($unidades as $unidade) {
            echo "<tr>";
            echo "<td>{$unidade['unidade_id']}</td>";
            echo "<td>{$unidade['unidade_nome']}</td>";
            echo "<td>" . number_format($unidade['total_registros']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar especificamente a unidade 25
        echo "<h2>🎯 Dados da Unidade 25 (Centro de Referência)</h2>";
        $stmt = $pdo->prepare("
            SELECT 
                a.servico_id,
                s.natureza,
                a.dia_semana,
                a.consulta_por_dia,
                COUNT(*) as registros
            FROM agenda a
            INNER JOIN servico s ON a.servico_id = s.id
            WHERE a.unidade_id = 25
            GROUP BY a.servico_id, s.natureza, a.dia_semana, a.consulta_por_dia
            ORDER BY a.servico_id, a.dia_semana
            LIMIT 20
        ");
        $stmt->execute();
        $dados_unidade25 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($dados_unidade25)) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Serviço ID</th><th>Natureza</th><th>Dia da Semana</th><th>Consultas/Dia</th><th>Registros</th></tr>";
            foreach ($dados_unidade25 as $dado) {
                echo "<tr>";
                echo "<td>{$dado['servico_id']}</td>";
                echo "<td>" . substr($dado['natureza'], 0, 50) . "...</td>";
                echo "<td>{$dado['dia_semana']}</td>";
                echo "<td>{$dado['consulta_por_dia']}</td>";
                echo "<td>{$dado['registros']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "❌ <span style='color: red'>Nenhum dado encontrado para a unidade 25</span><br>";
        }
        
        // Verificar dados gerais de uma unidade que tem dados
        if (!empty($unidades)) {
            $primeira_unidade = $unidades[0];
            echo "<h2>🔍 Exemplo de Dados da Unidade {$primeira_unidade['unidade_id']}</h2>";
            
            $stmt = $pdo->prepare("
                SELECT 
                    a.servico_id,
                    s.natureza,
                    a.dia_semana,
                    a.consulta_por_dia
                FROM agenda a
                INNER JOIN servico s ON a.servico_id = s.id
                WHERE a.unidade_id = ?
                ORDER BY a.servico_id, a.dia_semana
                LIMIT 10
            ");
            $stmt->execute([$primeira_unidade['unidade_id']]);
            $dados_exemplo = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>Serviço ID</th><th>Natureza</th><th>Dia da Semana</th><th>Consultas/Dia</th></tr>";
            foreach ($dados_exemplo as $dado) {
                echo "<tr>";
                echo "<td>{$dado['servico_id']}</td>";
                echo "<td>" . substr($dado['natureza'], 0, 40) . "...</td>";
                echo "<td>{$dado['dia_semana']}</td>";
                echo "<td>{$dado['consulta_por_dia']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "❌ <span style='color: red'>A tabela agenda está vazia!</span><br>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6;'>";
    echo "<strong>❌ ERRO:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><p><strong>🏁 Investigação concluída em: " . date('d/m/Y H:i:s') . "</strong></p>";
?>
