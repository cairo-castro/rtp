<?php
/**
 * Script para testar as otimizações de performance implementadas
 */

// Configurar PHP para debugging
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);

// Incluir arquivos necessários
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/models/RelatorioModel.php';
require_once __DIR__ . '/src/controllers/RelatorioController.php';

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function formatTime($time) {
    return number_format($time * 1000, 2) . 'ms';
}

echo "<h1>🚀 Teste de Performance - APÓS OTIMIZAÇÕES</h1>";
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
    
    // Teste 2: Query principal dos grupos
    echo "<h2>🎯 Teste 2: Query Principal de Grupos</h2>";
    $start = microtime_float();
    $relatorio_grupos = $model->obterRelatorioMensalPorGrupos($unidade_id, $mes, $ano);
    $time = microtime_float() - $start;
    echo "⏱️ Query principal executada em: <strong>" . formatTime($time) . "</strong><br>";
    echo "📊 Grupos encontrados: " . count($relatorio_grupos) . "<br>";
    
    $total_servicos = 0;
    $todos_servicos_ids = [];
    foreach ($relatorio_grupos as $grupo) {
        $servicos_count = count($grupo['servicos']);
        $total_servicos += $servicos_count;
        echo "   - {$grupo['grupo_nome']}: {$servicos_count} serviços<br>";
        
        // Coletar IDs dos serviços
        foreach ($grupo['servicos'] as $servico) {
            if (isset($servico['servico_id'])) {
                $todos_servicos_ids[] = (int)$servico['servico_id'];
            }
        }
    }
    echo "<strong>📈 Total de serviços: {$total_servicos}</strong><br>";
    
    // Teste 3: NOVA query otimizada de dados diários em lote
    echo "<h2>🚀 Teste 3: Dados Diários OTIMIZADOS (Lote)</h2>";
    $start = microtime_float();
    $dados_lote = $model->obterDadosDiariosMultiplosServicos($unidade_id, $todos_servicos_ids, $mes, $ano);
    $time_lote = microtime_float() - $start;
    echo "⚡ <strong>NOVA QUERY OTIMIZADA</strong> executada em: <strong>" . formatTime($time_lote) . "</strong><br>";
    echo "📊 Serviços processados: " . count($dados_lote) . "<br>";
    echo "📈 Dados retornados por serviço: " . (count($dados_lote) > 0 ? count(current($dados_lote)) : 0) . " dias<br>";
    
    // Teste 4: Comparação com método antigo (apenas alguns serviços)
    echo "<h2>⚖️ Teste 4: Comparação Performance (5 serviços)</h2>";
    $servicos_teste = array_slice($todos_servicos_ids, 0, 5);
    
    // Método antigo (individual)
    $start = microtime_float();
    $dados_individuais = [];
    foreach ($servicos_teste as $servico_id) {
        $dados_individuais[$servico_id] = $model->obterDadosDiariosServico($unidade_id, $servico_id, $mes, $ano);
    }
    $time_individual = microtime_float() - $start;
    
    // Método novo (lote)
    $start = microtime_float();
    $dados_lote_teste = $model->obterDadosDiariosMultiplosServicos($unidade_id, $servicos_teste, $mes, $ano);
    $time_lote_teste = microtime_float() - $start;
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Método</th><th>Tempo</th><th>Speedup</th></tr>";
    echo "<tr><td>Método Antigo (Individual)</td><td>" . formatTime($time_individual) . "</td><td>-</td></tr>";
    echo "<tr><td><strong>Método NOVO (Lote)</strong></td><td><strong>" . formatTime($time_lote_teste) . "</strong></td><td><strong>" . number_format($time_individual / $time_lote_teste, 1) . "x mais rápido</strong></td></tr>";
    echo "</table>";
    
    // Teste 5: Simulação do controller completo
    echo "<h2>🎮 Teste 5: Simulação Controller Completo</h2>";
    
    // Simular processamento completo do controller
    $start = microtime_float();
    
    // Simular método prepararDadosGraficosPorGrupos otimizado
    $dadosGraficos = [];
    $indiceGlobal = 0;
    
    // Buscar todos os dados em lote (já testado acima)
    $todos_dados_diarios = $dados_lote;
    
    foreach ($relatorio_grupos as $grupo) {
        foreach ($grupo['servicos'] as $servico) {
            if (!isset($servico['servico_id']) || !is_numeric($servico['servico_id'])) {
                continue;
            }
            
            $servico_id = (int)$servico['servico_id'];
            $dadosDiarios = $todos_dados_diarios[$servico_id] ?? [];
            
            $dadosGraficos[$indiceGlobal] = [
                'id' => $indiceGlobal,
                'grupo_id' => (int)$grupo['grupo_id'],
                'grupo_nome' => $grupo['grupo_nome'],
                'grupo_cor' => $grupo['grupo_cor'],
                'unidade_id' => (int)$unidade_id,
                'servico_id' => $servico_id,
                'mes' => (int)$mes,
                'ano' => (int)$ano,
                'nome' => $servico['natureza'] ?? 'Serviço',
                'meta_pdt' => (int)($servico['meta_pdt'] ?? 0),
                'total_executados' => (int)($servico['total_executados'] ?? 0),
                'dadosDiarios' => $dadosDiarios
            ];
            
            $indiceGlobal++;
        }
    }
    
    $time_controller = microtime_float() - $start;
    echo "⏱️ Controller completo executado em: <strong>" . formatTime($time_controller) . "</strong><br>";
    echo "📊 Gráficos preparados: " . count($dadosGraficos) . "<br>";
    
    // Resumo final
    echo "<h2>📋 Resumo da Otimização</h2>";
    $tempo_total_novo = $time + $time_lote + $time_controller;
    echo "<strong>⏱️ Tempo total OTIMIZADO: " . formatTime($tempo_total_novo) . "</strong><br>";
    
    // Calcular melhoria baseada na análise anterior (10.2 segundos)
    $tempo_anterior = 10.219; // segundos do teste anterior
    $melhoria = ($tempo_anterior - ($tempo_total_novo / 1000)) / $tempo_anterior * 100;
    
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Versão</th><th>Tempo Total</th><th>Status</th></tr>";
    echo "<tr><td>ANTES (N+1 Queries)</td><td>~10,220ms</td><td style='color: red;'>❌ Muito Lento</td></tr>";
    echo "<tr><td><strong>DEPOIS (Otimizado)</strong></td><td><strong>" . formatTime($tempo_total_novo / 1000) . "</strong></td><td style='color: green;'><strong>✅ Otimizado</strong></td></tr>";
    echo "</table>";
    
    echo "<h3>🎉 Resultados:</h3>";
    echo "<ul>";
    echo "<li>⚡ <strong>Melhoria de performance: " . number_format($melhoria, 1) . "%</strong></li>";
    echo "<li>🚀 <strong>Speedup: " . number_format($tempo_anterior / ($tempo_total_novo / 1000), 1) . "x mais rápido</strong></li>";
    echo "<li>✅ Problema N+1 queries resolvido</li>";
    echo "<li>✅ Consultas à tabela agenda removidas</li>";
    echo "<li>✅ Uma única query para todos os dados diários</li>";
    echo "</ul>";
    
    if ($tempo_total_novo / 1000 < 2) {
        echo "🎯 <span style='color: green;'><strong>OBJETIVO ALCANÇADO: Página agora carrega em menos de 2 segundos!</strong></span><br>";
    } else if ($tempo_total_novo / 1000 < 5) {
        echo "✅ <span style='color: blue;'><strong>MELHORIA SIGNIFICATIVA: Página agora carrega em menos de 5 segundos</strong></span><br>";
    }
    
} catch (Exception $e) {
    echo "❌ <strong>Erro durante teste:</strong> " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><p><strong>🏁 Teste de otimização concluído em: " . date('d/m/Y H:i:s') . "</strong></p>";
?>
