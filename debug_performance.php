<?php
/**
 * Script de debug para identificar gargalos de performance
 */

// Configurar PHP para debugging
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);

// Incluir arquivos necessários
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/models/RelatorioModel.php';

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function formatTime($time) {
    return number_format($time * 1000, 2) . 'ms';
}

echo "<h1>🔍 Análise de Performance - Sistema RTP</h1>";
echo "<p>Teste realizado em: " . date('d/m/Y H:i:s') . "</p>";

try {
    $model = new RelatorioModel();
    
    // Teste 1: Listar unidades
    echo "<h2>📍 Teste 1: Carregamento de Unidades</h2>";
    $start = microtime_float();
    $unidades = $model->obterUnidades();
    $time = microtime_float() - $start;
    echo "✅ Unidades carregadas: " . count($unidades) . " em " . formatTime($time) . "<br>";
    
    if (empty($unidades)) {
        echo "❌ Nenhuma unidade encontrada. Verifique a conexão com o banco.<br>";
        exit;
    }
    
    // Usar primeira unidade para testes
    $unidade_id = $unidades[0]['id'];
    $unidade_nome = $unidades[0]['nome'];
    $mes = 5;
    $ano = 2025;
    
    echo "<p><strong>Testando com:</strong> {$unidade_nome} (ID: {$unidade_id}) - {$mes}/{$ano}</p>";
    
    // Teste 2: Query principal dos grupos (GARGALO IDENTIFICADO)
    echo "<h2>🎯 Teste 2: Query Principal de Grupos (GARGALO SUSPEITO)</h2>";
    $start = microtime_float();
    $relatorio_grupos = $model->obterRelatorioMensalPorGrupos($unidade_id, $mes, $ano);
    $time = microtime_float() - $start;
    echo "⏱️ Query principal executada em: <strong>" . formatTime($time) . "</strong><br>";
    echo "📊 Grupos encontrados: " . count($relatorio_grupos) . "<br>";
    
    $total_servicos = 0;
    foreach ($relatorio_grupos as $grupo) {
        $servicos_count = count($grupo['servicos']);
        $total_servicos += $servicos_count;
        echo "   - {$grupo['grupo_nome']}: {$servicos_count} serviços<br>";
    }
    echo "<strong>📈 Total de serviços: {$total_servicos}</strong><br>";
    
    if ($time > 2) {
        echo "⚠️ <span style='color: red'>ALERTA: Query principal muito lenta (&gt; 2s)</span><br>";
    }
    
    // Teste 3: Query de dados diários por serviço (SEGUNDO GARGALO)
    echo "<h2>📅 Teste 3: Dados Diários por Serviço</h2>";
    $total_time_diarios = 0;
    $servicos_testados = 0;
    $limit_teste = min(5, $total_servicos); // Testar apenas 5 serviços
    
    foreach ($relatorio_grupos as $grupo) {
        foreach ($grupo['servicos'] as $servico) {
            if ($servicos_testados >= $limit_teste) break 2;
            
            $start = microtime_float();
            $dados_diarios = $model->obterDadosDiariosServico(
                $unidade_id, 
                $servico['servico_id'], 
                $mes, 
                $ano
            );
            $time = microtime_float() - $start;
            $total_time_diarios += $time;
            $servicos_testados++;
            
            echo "   - Serviço {$servico['servico_id']} ({$servico['natureza']}): " . formatTime($time);
            echo " | Dias retornados: " . count($dados_diarios) . "<br>";
            
            if ($time > 0.5) {
                echo "     ⚠️ <span style='color: orange'>Query lenta para este serviço</span><br>";
            }
        }
    }
    
    $media_diarios = $servicos_testados > 0 ? $total_time_diarios / $servicos_testados : 0;
    echo "<strong>⏱️ Tempo médio por serviço: " . formatTime($media_diarios) . "</strong><br>";
    echo "<strong>🔢 Projeção para {$total_servicos} serviços: " . formatTime($media_diarios * $total_servicos) . "</strong><br>";
    
    if ($media_diarios * $total_servicos > 3) {
        echo "🚨 <span style='color: red'><strong>GARGALO IDENTIFICADO: Dados diários muito lentos!</strong></span><br>";
    }
    
    // Teste 4: Análise da tabela agenda (terceiro suspeito)
    echo "<h2>📋 Teste 4: Performance da Tabela Agenda</h2>";
    $start = microtime_float();
    
    // Simular primeira query da agenda que é executada para cada serviço
    $pdo = getDatabaseConnection();
    $query_agenda = "
    SELECT 
        dia_semana,
        SUM(consulta_por_dia) as total_consultas_dia
    FROM agenda
    WHERE unidade_id = ? AND servico_id = ?
    GROUP BY dia_semana
    ";
    
    $primeiro_servico = null;
    foreach ($relatorio_grupos as $grupo) {
        if (!empty($grupo['servicos'])) {
            $primeiro_servico = $grupo['servicos'][0];
            break;
        }
    }
    
    if ($primeiro_servico) {
        $stmt = $pdo->prepare($query_agenda);
        $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$primeiro_servico['servico_id'], PDO::PARAM_INT);
        $stmt->execute();
        $resultado_agenda = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $time = microtime_float() - $start;
        
        echo "⏱️ Query agenda executada em: " . formatTime($time) . "<br>";
        echo "📊 Registros na agenda: " . count($resultado_agenda) . "<br>";
        echo "<strong>🔢 Projeção agenda para {$total_servicos} serviços: " . formatTime($time * $total_servicos) . "</strong><br>";
        
        if ($time * $total_servicos > 1) {
            echo "⚠️ <span style='color: orange'>Tabela agenda pode ser otimizada</span><br>";
        }
    }
    
    // Resumo final
    echo "<h2>📋 Resumo da Análise</h2>";
    $tempo_total_estimado = $time + ($media_diarios * $total_servicos) + ($time * $total_servicos);
    echo "<strong>⏱️ Tempo total estimado da página: " . formatTime($tempo_total_estimado) . "</strong><br>";
    
    if ($tempo_total_estimado > 5) {
        echo "🚨 <span style='color: red'><strong>PROBLEMA CONFIRMADO: Página muito lenta!</strong></span><br>";
        echo "<h3>🔧 Recomendações:</h3>";
        echo "<ul>";
        echo "<li>✅ Otimizar query principal de grupos (adicionar índices)</li>";
        echo "<li>✅ Implementar cache para dados diários</li>";
        echo "<li>✅ Otimizar consultas à tabela agenda</li>";
        echo "<li>✅ Reduzir número de queries por serviço</li>";
        echo "</ul>";
    } else {
        echo "✅ <span style='color: green'>Performance dentro do aceitável</span><br>";
    }
    
    // Teste de índices
    echo "<h2>🔍 Teste 5: Verificação de Índices</h2>";
    
    $tabelas_indice = ['servico', 'rtpdiario', 'agenda', 'pdt', 'meta'];
    foreach ($tabelas_indice as $tabela) {
        try {
            $stmt = $pdo->query("SHOW INDEX FROM {$tabela}");
            $indices = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "<strong>📊 {$tabela}:</strong> " . count($indices) . " índices<br>";
            
            foreach ($indices as $indice) {
                echo "   - {$indice['Key_name']} em {$indice['Column_name']}<br>";
            }
        } catch (Exception $e) {
            echo "❌ Erro ao verificar índices de {$tabela}: " . $e->getMessage() . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erro durante análise:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><p><strong>🏁 Análise concluída em: " . date('d/m/Y H:i:s') . "</strong></p>";
?>
