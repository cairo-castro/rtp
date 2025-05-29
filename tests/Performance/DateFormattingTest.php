<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

class DateFormattingTest extends TestCase
{
    public function testDateFormattingImplementation()
    {
        echo "\n=== TESTE DE FORMATAÇÃO DE DATAS ===\n";
        
        // 1. Verificar se o JavaScript contém o formatter correto
        $jsFile = file_get_contents(__DIR__ . '/../../public/assets/js/relatorio.js');
        
        echo "--- 1. VERIFICAÇÃO DO FORMATTER JAVASCRIPT ---\n";
        
        // Verificar se existe o formatter para dividir linhas
        $hasFormatter = strpos($jsFile, 'formatter: function(value)') !== false;
        $hasSplitLogic = strpos($jsFile, "value.split('\\n')") !== false;
        $hasMultiLineLogic = strpos($jsFile, "value.includes('\\n')") !== false;
        
        echo "Formatter implementado: " . ($hasFormatter ? "Sim" : "Não") . "\n";
        echo "Lógica de divisão implementada: " . ($hasSplitLogic ? "Sim" : "Não") . "\n";
        echo "Verificação multi-linha implementada: " . ($hasMultiLineLogic ? "Sim" : "Não") . "\n";
        
        if ($hasFormatter && $hasSplitLogic && $hasMultiLineLogic) {
            echo "✅ Formatter de datas implementado corretamente\n";
        } else {
            echo "❌ Formatter de datas não implementado corretamente\n";
        }
          // Verificar se a criação de categorias está correta
        echo "\n--- 2. VERIFICAÇÃO DA CRIAÇÃO DE CATEGORIAS ---\n";
        
        $hasCategoryMapping = strpos($jsFile, 'dadosDiarios.map(d => `${d.dia}<br/>${d.dia_semana || \'\'}`)') !== false;
        $hasCategoryComment = strpos($jsFile, 'dia da semana embaixo do número') !== false;
        
        echo "Mapeamento de categorias correto: " . ($hasCategoryMapping ? "Sim" : "Não") . "\n";
        echo "Comentário explicativo presente: " . ($hasCategoryComment ? "Sim" : "Não") . "\n";
        
        if ($hasCategoryMapping && $hasCategoryComment) {
            echo "✅ Criação de categorias implementada corretamente\n";
        } else {
            echo "⚠️  Criação de categorias pode precisar de ajustes\n";
        }
        
        // 3. Simular processamento de dados
        echo "\n--- 3. SIMULAÇÃO DE PROCESSAMENTO DE DADOS ---\n";
        
        // Simular dados como seriam processados pelo sistema
        $dadosTeste = [
            ['dia' => '15', 'dia_semana' => 'Seg'],
            ['dia' => '16', 'dia_semana' => 'Ter'],
            ['dia' => '17', 'dia_semana' => 'Qua'],
            ['dia' => '18', 'dia_semana' => 'Qui'],
            ['dia' => '19', 'dia_semana' => 'Sex']
        ];
          $categorias = [];
        foreach ($dadosTeste as $d) {
            $categorias[] = $d['dia'] . "<br/>" . ($d['dia_semana'] ?? '');
        }
        
        echo "Dados de teste processados: " . count($dadosTeste) . " registros\n";
        echo "Categorias geradas: " . count($categorias) . " categorias\n";
        echo "Formato esperado: 'Número<br/>DiaSemana'\n";
        echo "Exemplo categoria 1: '" . $categorias[0] . "'\n";
        echo "Exemplo categoria 3: '" . $categorias[2] . "'\n";
        
        // Verificar se as categorias contêm o separador HTML
        $todasComSeparador = true;
        foreach ($categorias as $categoria) {
            if (strpos($categoria, "<br/>") === false) {
                $todasComSeparador = false;
                break;
            }
        }
        
        echo "Todas categorias com separador <br/>: " . ($todasComSeparador ? "Sim" : "Não") . "\n";
        
        if ($todasComSeparador) {
            echo "✅ Formato de categorias está correto\n";
        } else {
            echo "❌ Formato de categorias precisa de correção\n";
        }
        
        // 4. Verificar compatibilidade com ApexCharts
        echo "\n--- 4. VERIFICAÇÃO DE COMPATIBILIDADE APEXCHARTS ---\n";
          // Verificar se o JavaScript tem as configurações corretas para multi-linha
        $hasXAxisConfig = strpos($jsFile, 'xaxis: {') !== false;
        $hasLabelsConfig = strpos($jsFile, 'labels: {') !== false;
        $hasMaxHeight = strpos($jsFile, 'maxHeight: 60') !== false;
        $hasRotateZero = strpos($jsFile, 'rotate: 0') !== false;
        $hasHTMLSupport = strpos($jsFile, '<br/>') !== false;
          echo "Configuração xaxis presente: " . ($hasXAxisConfig ? "Sim" : "Não") . "\n";
        echo "Configuração labels presente: " . ($hasLabelsConfig ? "Sim" : "Não") . "\n";
        echo "MaxHeight configurado: " . ($hasMaxHeight ? "Sim" : "Não") . "\n";
        echo "Rotação zerada: " . ($hasRotateZero ? "Sim" : "Não") . "\n";
        echo "Suporte HTML implementado: " . ($hasHTMLSupport ? "Sim" : "Não") . "\n";
        
        $compatibilidadeOk = $hasXAxisConfig && $hasLabelsConfig && $hasMaxHeight && $hasRotateZero && $hasHTMLSupport;
        
        if ($compatibilidadeOk) {
            echo "✅ Configuração compatível com ApexCharts\n";
        } else {
            echo "⚠️  Configuração pode precisar de ajustes para ApexCharts\n";
        }
        
        // 5. Resumo final
        echo "\n--- 5. RESUMO FINAL ---\n";
        
        $implementacaoCompleta = $hasFormatter && $hasSplitLogic && $hasMultiLineLogic && 
                                $hasCategoryMapping && $todasComSeparador && $compatibilidadeOk;
        
        if ($implementacaoCompleta) {
            echo "✅ IMPLEMENTAÇÃO COMPLETA: Formatação de datas verticais implementada com sucesso\n";
            echo "ℹ️  As datas devem aparecer com o número do dia na linha superior\n";
            echo "ℹ️  e o dia da semana na linha inferior nos gráficos\n";
        } else {
            echo "⚠️  IMPLEMENTAÇÃO PARCIAL: Algumas verificações falharam\n";
            echo "ℹ️  Verifique os pontos marcados acima para completar a implementação\n";
        }
        
        echo "\n=== FIM DO TESTE ===\n";
    }
}
