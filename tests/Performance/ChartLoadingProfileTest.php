<?php

use PHPUnit\Framework\TestCase;

class ChartLoadingProfileTest extends TestCase
{
    /**
     * Perfil detalhado do carregamento dos gráficos
     */
    public function testDetailedChartLoadingProfile()
    {
        echo "\n=== PERFIL DETALHADO DE CARREGAMENTO ===\n";
        
        // 1. Análise do HTML Dashboard
        $this->profileHTMLGeneration();
        
        // 2. Análise do JavaScript
        $this->profileJavaScriptExecution();
        
        // 3. Análise de dependências
        $this->profileDependencies();
        
        // 4. Análise de dados
        $this->profileDataProcessing();
        
        // 5. Recomendações
        $this->generateRecommendations();
    }
    
    private function profileHTMLGeneration()
    {
        echo "\n--- 1. GERAÇÃO HTML ---\n";
        
        $startTime = microtime(true);
        
        $dashboardPath = __DIR__ . '/../../src/views/relatorio/dashboard.php';
        $content = file_get_contents($dashboardPath);
        
        $htmlSize = strlen($content);
        $lineCount = substr_count($content, "\n");
        
        // Analisa elementos pesados
        $scriptTags = substr_count($content, '<script');
        $divCharts = preg_match_all('/<div[^>]*chart[^>]*>/', $content);
        $inlineStyles = substr_count($content, 'style=');
        
        $endTime = microtime(true);
        $loadTime = ($endTime - $startTime) * 1000;
        
        echo "Tamanho HTML: " . round($htmlSize/1024, 2) . "KB\n";
        echo "Linhas de código: $lineCount\n";
        echo "Tags script: $scriptTags\n";
        echo "Divs de gráfico: $divCharts\n";
        echo "Estilos inline: $inlineStyles\n";
        echo "Tempo de leitura: " . round($loadTime, 2) . "ms\n";
        
        // Verifica otimizações
        if ($inlineStyles > 10) {
            echo "⚠️  ATENÇÃO: Muitos estilos inline podem afetar performance\n";
        }
        
        if ($htmlSize > 50000) {
            echo "⚠️  ATENÇÃO: HTML muito grande (>50KB)\n";
        }
    }
    
    private function profileJavaScriptExecution()
    {        echo "\n--- 2. JAVASCRIPT APEXCHARTS ---\n";
        
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Análise de complexidade
        $functionCount = preg_match_all('/function\s+\w+/', $content);
        $loopCount = substr_count($content, 'for(') + substr_count($content, 'forEach');
        $apexCallsCount = substr_count($content, 'ApexCharts');
        $configObjectsCount = substr_count($content, 'options');
        
        // Análise de otimização
        $hasMinification = strpos($content, '//# sourceMappingURL') !== false;
        $hasComments = substr_count($content, '//') + substr_count($content, '/*');
        $hasConsoleLog = substr_count($content, 'console.log');
        
        echo "Funções JavaScript: $functionCount\n";
        echo "Loops (for/forEach): $loopCount\n";
        echo "Chamadas ApexCharts: $apexCallsCount\n";
        echo "Objetos de configuração: $configObjectsCount\n";
        echo "Comentários: $hasComments\n";
        echo "Console.log (debug): $hasConsoleLog\n";
        echo "Minificado: " . ($hasMinification ? 'Sim' : 'Não') . "\n";
        
        // Performance patterns
        $hasEventDelegation = strpos($content, 'addEventListener') !== false;
        $hasDocumentReady = strpos($content, 'DOMContentLoaded') !== false;
        
        echo "Event delegation: " . ($hasEventDelegation ? 'Sim' : 'Não') . "\n";
        echo "Document ready: " . ($hasDocumentReady ? 'Sim' : 'Não') . "\n";
        
        if ($hasConsoleLog > 0) {
            echo "⚠️  ATENÇÃO: Console.log pode afetar performance em produção\n";
        }
    }
    
    private function profileDependencies()
    {
        echo "\n--- 3. DEPENDÊNCIAS EXTERNAS ---\n";
        
        $layoutPath = __DIR__ . '/../../src/views/layouts/main.php';
        $content = file_get_contents($layoutPath);
        
        // Analisa CDNs
        preg_match_all('/src="([^"]*cdn[^"]*)"/', $content, $cdnMatches);
        preg_match_all('/href="([^"]*cdn[^"]*)"/', $content, $cssMatches);
        
        $totalCDNs = count($cdnMatches[1]) + count($cssMatches[1]);
        
        echo "CDNs JavaScript: " . count($cdnMatches[1]) . "\n";
        echo "CDNs CSS: " . count($cssMatches[1]) . "\n";
        echo "Total CDNs: $totalCDNs\n";
        
        // Lista as dependências
        foreach ($cdnMatches[1] as $cdn) {
            echo "  JS: " . basename($cdn) . "\n";
        }
        
        foreach ($cssMatches[1] as $css) {
            echo "  CSS: " . basename($css) . "\n";
        }
        
        if ($totalCDNs > 5) {
            echo "⚠️  ATENÇÃO: Muitas dependências CDN podem afetar performance\n";
        }
        
        // Verifica se ApexCharts está otimizado
        $hasApexCharts = strpos($content, 'apexcharts.min.js') !== false;
        echo "ApexCharts minificado: " . ($hasApexCharts ? 'Sim' : 'Não') . "\n";
    }
    
    private function profileDataProcessing()
    {
        echo "\n--- 4. PROCESSAMENTO DE DADOS ---\n";
        
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();
        
        // Simula processamento real do dashboard
        $services = $this->generateTestData();
        
        // Processa dados como no dashboard real
        foreach ($services as &$service) {
            $total_pactuado = 0;
            $total_agendado = 0;
            
            foreach ($service->dadosDiarios as $dado) {
                $total_pactuado += $dado->pactuado;
                $total_agendado += $dado->agendado;
            }
            
            $service->total_pactuado = $total_pactuado;
            $service->total_agendado = $total_agendado;
            $service->percentual = $total_agendado > 0 ? ($total_pactuado / $total_agendado) * 100 : 0;
        }
        
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $processingTime = ($endTime - $startTime) * 1000;
        $memoryUsed = ($memoryEnd - $memoryStart) / 1024;
        
        echo "Serviços processados: " . count($services) . "\n";
        echo "Registros por serviço: " . count($services[0]->dadosDiarios) . "\n";
        echo "Total de registros: " . (count($services) * count($services[0]->dadosDiarios)) . "\n";
        echo "Tempo de processamento: " . round($processingTime, 2) . "ms\n";
        echo "Memória utilizada: " . round($memoryUsed, 2) . "KB\n";
        echo "Performance: " . round(count($services) / ($processingTime/1000), 0) . " serviços/segundo\n";
        
        if ($processingTime > 100) {
            echo "⚠️  ATENÇÃO: Processamento lento (>100ms)\n";
        }
    }
    
    private function generateTestData()
    {
        $services = [];
        
        // 10 serviços com 30 dias de dados cada
        for ($i = 1; $i <= 10; $i++) {
            $dadosDiarios = [];
            
            for ($j = 0; $j < 30; $j++) {
                $dadosDiarios[] = (object)[
                    'data' => date('Y-m-d', strtotime("-$j days")),
                    'pactuado' => rand(50, 200),
                    'agendado' => rand(45, 180),
                    'executado' => rand(40, 170)
                ];
            }
            
            $services[] = (object)[
                'id' => $i,
                'nome' => "Serviço de Teste $i",
                'dadosDiarios' => $dadosDiarios
            ];
        }
        
        return $services;
    }
    
    private function generateRecommendations()
    {
        echo "\n--- 5. RECOMENDAÇÕES DE OTIMIZAÇÃO ---\n";
          $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $dashboardPath = __DIR__ . '/../../src/views/relatorio/dashboard.php';
        
        $jsContent = file_get_contents($jsPath);
        $htmlContent = file_get_contents($dashboardPath);
        
        echo "POSSÍVEIS MELHORIAS:\n\n";
        
        // 1. Lazy Loading
        if (strpos($jsContent, 'IntersectionObserver') === false) {
            echo "1. 🚀 IMPLEMENTAR LAZY LOADING:\n";
            echo "   - Carregar gráficos apenas quando visíveis\n";
            echo "   - Usar IntersectionObserver API\n\n";
        }
        
        // 2. Debouncing
        if (strpos($jsContent, 'debounce') === false) {
            echo "2. ⏱️  IMPLEMENTAR DEBOUNCING:\n";
            echo "   - Atrasar atualização de gráficos em resize\n";
            echo "   - Evitar múltiplas renderizações\n\n";
        }
        
        // 3. Data caching
        if (strpos($jsContent, 'localStorage') === false && strpos($jsContent, 'sessionStorage') === false) {
            echo "3. 💾 IMPLEMENTAR CACHE DE DADOS:\n";
            echo "   - Usar localStorage para dados estáticos\n";
            echo "   - Reduzir requisições ao servidor\n\n";
        }
        
        // 4. Minificação
        $hasComments = substr_count($jsContent, '//') > 10;
        if ($hasComments) {
            echo "4. 📦 MINIFICAR JAVASCRIPT:\n";
            echo "   - Remover comentários em produção\n";
            echo "   - Comprimir código JavaScript\n\n";
        }
        
        // 5. Chart pooling
        $chartCount = substr_count($htmlContent, 'id="chart');
        if ($chartCount > 3) {
            echo "5. 🔄 IMPLEMENTAR CHART POOLING:\n";
            echo "   - Reutilizar instâncias de gráficos\n";
            echo "   - Reduzir criação/destruição de objetos\n\n";
        }
        
        // 6. Virtualização
        echo "6. 🎯 CONSIDERAR VIRTUALIZAÇÃO:\n";
        echo "   - Para grandes volumes de dados\n";
        echo "   - Renderizar apenas dados visíveis\n\n";
        
        echo "7. 📊 CONFIGURAÇÕES ESPECÍFICAS:\n";
        echo "   - Desabilitar animações em dispositivos lentos\n";
        echo "   - Ajustar qualidade de rendering\n";
        echo "   - Configurar update rate apropriado\n\n";
        
        echo "PRÓXIMOS PASSOS:\n";
        echo "1. Implementar uma das otimizações acima\n";
        echo "2. Executar testes de performance novamente\n";
        echo "3. Medir impacto das mudanças\n";
        echo "4. Repetir processo até performance satisfatória\n";
    }
}
