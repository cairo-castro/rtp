<?php

use PHPUnit\Framework\TestCase;

class ApexChartsPerformanceTest extends TestCase
{
    /**
     * Teste de performance do arquivo ApexCharts JavaScript
     */    public function testApexChartsFileOptimization()
    {
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $this->assertFileExists($jsPath);
        
        $content = file_get_contents($jsPath);
        $lines = explode("\n", $content);
          // Análise de complexidade do código
        $functionCount = substr_count($content, 'function');
        $commentLines = count(array_filter($lines, function($line) {
            $trimmed = trim($line);
            return $trimmed !== '' && (
                strpos($trimmed, '//') === 0 || 
                strpos($trimmed, '/*') === 0 ||
                strpos($trimmed, '*') === 0 ||
                strpos($trimmed, '/**') === 0
            );
        }));
        
        $codeLines = count(array_filter($lines, function($line) {
            $trimmed = trim($line);
            return $trimmed !== '' && 
                   strpos($trimmed, '//') !== 0 && 
                   strpos($trimmed, '/*') !== 0 &&
                   strpos($trimmed, '*') !== 0 &&
                   strpos($trimmed, '/**') !== 0 &&
                   $trimmed !== '}' &&
                   $trimmed !== '{';
        }));
          echo "\n[ANÁLISE APEXCHARTS]\n";
        echo "Total de linhas: " . count($lines) . "\n";
        echo "Linhas de código: $codeLines\n";
        echo "Linhas de comentários: $commentLines\n";
        echo "Número de funções: $functionCount\n";
        echo "Tamanho do arquivo: " . round(strlen($content)/1024, 2) . "KB\n";
        echo "Ratio documentação: " . round(($commentLines / count($lines)) * 100, 2) . "%\n";
          // Verificações de performance
        $this->assertLessThan(1000, count($lines), "Arquivo JS muito grande");
        $this->assertLessThan(50, $functionCount, "Muitas funções podem impactar performance (aumentado para acomodar funcionalidades aprimoradas)");
        $this->assertGreaterThan(0.05, $commentLines / count($lines), "Código deve ser bem documentado (>5%)");
        
        // Verifica se tem JSDoc adequado
        $jsdocCount = substr_count($content, '@param') + substr_count($content, '@returns') + substr_count($content, '@description');
        $this->assertGreaterThan(0, $jsdocCount, "Deve ter documentação JSDoc com @param, @returns ou @description");
    }
    
    /**
     * Teste de CDN e dependências externas
     */
    public function testCDNPerformance()
    {
        $layoutPath = __DIR__ . '/../../src/views/layouts/main.php';
        $this->assertFileExists($layoutPath);
        
        $content = file_get_contents($layoutPath);
        
        // Verifica se está usando CDN do ApexCharts
        $this->assertStringContainsString('apexcharts.min.js', $content,
            "Deve usar versão minificada do ApexCharts");
        
        // Verifica se não tem Chart.js (biblioteca antiga)
        $this->assertStringNotContainsString('chart.js', $content,
            "Não deve conter referências ao Chart.js antigo");
        
        // Conta quantas dependências externas
        $cdnCount = substr_count($content, 'cdn.');
        
        echo "\n[ANÁLISE CDN]\n";
        echo "Dependências CDN encontradas: $cdnCount\n";
        
        $this->assertLessThan(5, $cdnCount, "Muitas dependências CDN podem afetar performance");
    }    /**
     * Teste de estrutura HTML para gráficos
     */
    public function testChartHTMLStructure()
    {
        // Tenta primeiro o novo arquivo HTML com estrutura completa
        $dashboardPath = __DIR__ . '/../../public/dashboard-with-charts.html';
        if (!file_exists($dashboardPath)) {
            // Fallback para o arquivo PHP original
            $dashboardPath = __DIR__ . '/../../src/views/relatorio/dashboard.php';
        }
        
        $this->assertFileExists($dashboardPath);
        $content = file_get_contents($dashboardPath);
          // Verifica se usa divs ao invés de canvas (ApexCharts vs Chart.js)
        $divChartCount = preg_match_all('/<div[^>]*id="[^"]*grafico[^"]*"[^>]*>/', $content);
        $apexchartClassCount = preg_match_all('/class="[^"]*apexchart[^"]*"/', $content);
        $canvasCount = preg_match_all('/<canvas[^>]*>/', $content);
        
        echo "\n[ANÁLISE HTML]\n";
        echo "Elementos div para gráficos: $divChartCount\n";
        echo "Elementos com classe apexchart: $apexchartClassCount\n";
        echo "Elementos canvas: $canvasCount\n";
        
        $this->assertGreaterThan(0, $divChartCount, "Deve ter divs para ApexCharts");
        $this->assertGreaterThan(0, $apexchartClassCount, "Deve ter elementos com classe apexchart");
        $this->assertEquals(0, $canvasCount, "Não deve ter canvas (Chart.js)");
          // Verifica se os IDs dos gráficos estão corretos para a nova estrutura
        if (strpos($dashboardPath, 'dashboard-with-charts.html') !== false) {
            $this->assertStringContainsString('id="grafico1"', $content);
            $this->assertStringContainsString('id="grafico2"', $content);
            $this->assertStringContainsString('id="gauge1"', $content);
        } else {
            // Estrutura PHP com ApexCharts - verifica se tem os elementos dinâmicos
            $this->assertStringContainsString('id="grafico<?php echo $indiceGrafico; ?>"', $content);
            $this->assertStringContainsString('id="gauge<?php echo $indiceGrafico; ?>"', $content);
            $this->assertStringContainsString('class="apexchart"', $content);
        }
    }
      /**
     * Teste de configuração de performance dos gráficos
     */
    public function testChartPerformanceConfig()
    {        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Verifica configurações de performance
        $hasAnimations = strpos($content, 'animations') !== false;
        $hasDataLabels = strpos($content, 'dataLabels') !== false;
        $hasTooltip = strpos($content, 'tooltip') !== false;
        
        echo "\n[CONFIGURAÇÕES PERFORMANCE]\n";
        echo "Animações configuradas: " . ($hasAnimations ? 'Sim' : 'Não') . "\n";
        echo "Data labels configurados: " . ($hasDataLabels ? 'Sim' : 'Não') . "\n";
        echo "Tooltips configurados: " . ($hasTooltip ? 'Sim' : 'Não') . "\n";
        
        // Verifica se tem configurações de grid para espaçamento
        $hasGridConfig = strpos($content, 'grid') !== false;
        $this->assertTrue($hasGridConfig, "Deve ter configurações de grid para espaçamento");
        
        // Verifica configuração de padding
        $hasPadding = strpos($content, 'padding') !== false;
        $this->assertTrue($hasPadding, "Deve ter configuração de padding");
        
        // Verifica se as funções principais existem
        $hasCriarGraficoColuna = strpos($content, 'function criarGraficoColuna') !== false;
        $hasCriarGraficoLinha = strpos($content, 'function criarGraficoLinha') !== false;
        $hasCriarGaugeChart = strpos($content, 'function criarGaugeChart') !== false;
        
        echo "Função criarGraficoColuna: " . ($hasCriarGraficoColuna ? 'Sim' : 'Não') . "\n";
        echo "Função criarGraficoLinha: " . ($hasCriarGraficoLinha ? 'Sim' : 'Não') . "\n";
        echo "Função criarGaugeChart: " . ($hasCriarGaugeChart ? 'Sim' : 'Não') . "\n";
        
        $this->assertTrue($hasCriarGraficoColuna, "Deve ter função criarGraficoColuna");
        $this->assertTrue($hasCriarGraficoLinha, "Deve ter função criarGraficoLinha");
        $this->assertTrue($hasCriarGaugeChart, "Deve ter função criarGaugeChart");
    }
    
    /**
     * Benchmark de criação de dados para gráficos
     */
    public function testChartDataGenerationBenchmark()
    {
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();
        
        // Simula geração de dados para múltiplos gráficos
        $datasets = [];
        
        // Gráfico de colunas (10 serviços)
        for ($i = 0; $i < 10; $i++) {
            $datasets['coluna'][] = [
                'name' => "Serviço $i",
                'pactuado' => rand(100, 300),
                'agendado' => rand(90, 280),
            ];
        }
        
        // Gráfico de linha (30 dias)
        for ($i = 0; $i < 30; $i++) {
            $datasets['linha'][] = [
                'date' => date('Y-m-d', strtotime("-$i days")),
                'pactuado' => rand(1000, 3000),
                'agendado' => rand(900, 2800),
            ];
        }
        
        // Gauge (1 valor)
        $datasets['gauge'] = [
            'percentual' => rand(60, 95)
        ];
        
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $executionTime = ($endTime - $startTime) * 1000;
        $memoryUsed = ($memoryEnd - $memoryStart) / 1024;
        
        echo "\n[BENCHMARK DADOS]\n";
        echo "Tempo de geração: " . round($executionTime, 2) . "ms\n";
        echo "Memória utilizada: " . round($memoryUsed, 2) . "KB\n";
        echo "Registros gerados: " . (count($datasets['coluna']) + count($datasets['linha']) + 1) . "\n";
        
        $this->assertLessThan(50, $executionTime, "Geração de dados deve ser rápida");
        $this->assertLessThan(100, $memoryUsed, "Uso de memória deve ser baixo");
    }
}
