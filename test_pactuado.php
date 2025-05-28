<?php
/**
 * Test script para verificar se dados pactuados estão sendo retornados corretamente
 */

// Incluir configurações e classes necessárias
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/models/RelatorioModel.php';
require_once __DIR__ . '/src/helpers/relatorio_helpers.php';

echo "<h1>🧪 Teste de Dados Pactuados</h1>";
echo "<p>Teste realizado em: " . date('d/m/Y H:i:s') . "</p>";

try {
    $model = new RelatorioModel();
    
    // Usar parâmetros de teste
    $unidade_id = 25; // CENTRO DE REFERÊNCIA DA PESSOA IDOSA
    $mes = 5;
    $ano = 2025;
    
    echo "<h2>📋 Teste 1: Relatório por Grupos</h2>";
    $inicio = microtime(true);
    $relatorio_grupos = $model->obterRelatorioMensalPorGrupos($unidade_id, $mes, $ano);
    $tempo_grupos = (microtime(true) - $inicio) * 1000;
    
    echo "⏱️ Tempo para obter grupos: " . number_format($tempo_grupos, 2) . "ms<br>";
    echo "📊 Grupos encontrados: " . count($relatorio_grupos) . "<br>";
    
    if (!empty($relatorio_grupos)) {
        // Pegar os primeiros 3 serviços para testar
        $servicos_teste = [];
        $contador = 0;
        
        foreach ($relatorio_grupos as $grupo) {
            foreach ($grupo['servicos'] as $servico) {
                if ($contador < 3 && isset($servico['servico_id'])) {
                    $servicos_teste[] = (int)$servico['servico_id'];
                    echo "   📌 Serviço selecionado: {$servico['natureza']} (ID: {$servico['servico_id']})<br>";
                    $contador++;
                }
            }
            if ($contador >= 3) break;
        }
        
        if (!empty($servicos_teste)) {
            echo "<h2>📅 Teste 2: Dados Diários com Pactuado (Método Otimizado)</h2>";
            
            $inicio = microtime(true);
            $dados_otimizados = $model->obterDadosDiariosMultiplosServicos($unidade_id, $servicos_teste, $mes, $ano);
            $tempo_otimizado = (microtime(true) - $inicio) * 1000;
            
            echo "⏱️ Tempo para obter dados diários otimizados: " . number_format($tempo_otimizado, 2) . "ms<br>";
            echo "📊 Serviços processados: " . count($dados_otimizados) . "<br>";
            
            // Verificar dados pactuados
            $pactuado_encontrado = false;
            $total_pactuado = 0;
            
            foreach ($dados_otimizados as $servico_id => $dados_diarios) {
                echo "<h3>🔍 Serviço ID: {$servico_id}</h3>";
                echo "📅 Dias retornados: " . count($dados_diarios) . "<br>";
                
                // Verificar primeiros 7 dias
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>Dia</th><th>Dia Semana</th><th>Pactuado</th><th>Agendado</th><th>Realizado</th></tr>";
                
                for ($i = 0; $i < min(7, count($dados_diarios)); $i++) {
                    $dia_data = $dados_diarios[$i];
                    $pactuado = $dia_data['pactuado'];
                    $total_pactuado += $pactuado;
                    
                    if ($pactuado > 0) {
                        $pactuado_encontrado = true;
                    }
                    
                    echo "<tr>";
                    echo "<td>{$dia_data['dia']}</td>";
                    echo "<td>{$dia_data['dia_semana']}</td>";
                    echo "<td style='background-color: " . ($pactuado > 0 ? '#90EE90' : '#FFB6C1') . "'>{$pactuado}</td>";
                    echo "<td>{$dia_data['agendado']}</td>";
                    echo "<td>{$dia_data['realizado']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                break; // Mostrar apenas o primeiro serviço para simplicidade
            }
            
            echo "<h2>📊 Resultado do Teste</h2>";
            if ($pactuado_encontrado) {
                echo "✅ <span style='color: green'><strong>SUCESSO: Dados pactuados foram encontrados!</strong></span><br>";
                echo "📈 Total de pactuados encontrados: {$total_pactuado}<br>";
            } else {
                echo "❌ <span style='color: red'><strong>PROBLEMA: Nenhum dado pactuado foi encontrado!</strong></span><br>";
            }
            
            // Teste individual para comparar
            echo "<h2>🔍 Teste 3: Comparação com Método Individual</h2>";
            if (!empty($servicos_teste)) {
                $primeiro_servico = $servicos_teste[0];
                
                $inicio = microtime(true);
                $dados_individuais = $model->obterDadosDiariosServico($unidade_id, $primeiro_servico, $mes, $ano);
                $tempo_individual = (microtime(true) - $inicio) * 1000;
                
                echo "⏱️ Tempo para obter dados individuais: " . number_format($tempo_individual, 2) . "ms<br>";
                echo "📅 Dias retornados: " . count($dados_individuais) . "<br>";
                
                // Verificar primeiros 3 dias
                $pactuado_individual = 0;
                for ($i = 0; $i < min(3, count($dados_individuais)); $i++) {
                    $dia_data = $dados_individuais[$i];
                    $pactuado_individual += $dia_data['pactuado'];
                    echo "   📅 Dia {$dia_data['dia']}: Pactuado={$dia_data['pactuado']}, Agendado={$dia_data['agendado']}, Realizado={$dia_data['realizado']}<br>";
                }
                
                echo "📊 Total pactuados nos primeiros 3 dias (individual): {$pactuado_individual}<br>";
            }
            
        } else {
            echo "❌ Nenhum serviço válido encontrado para teste<br>";
        }
    } else {
        echo "❌ Nenhum grupo encontrado<br>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; background-color: #ffe6e6;'>";
    echo "<strong>❌ ERRO:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><p><strong>🏁 Teste concluído em: " . date('d/m/Y H:i:s') . "</strong></p>";
?>
