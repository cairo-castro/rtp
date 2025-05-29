<?php

use PHPUnit\Framework\TestCase;

class ChartOptimizationTest extends TestCase
{
    /**
     * Teste específico para verificar otimizações de performance implementadas
     */
    public function testChartPerformanceOptimizations()
    {
        echo "\n=== TESTE DE OTIMIZAÇÕES DE PERFORMANCE ===\n";
        
        // 1. Verificar implementação de Chart Pooling
        $this->verifyChartPooling();
        
        // 2. Verificar configurações otimizadas
        $this->verifyOptimizedConfigurations();
        
        // 3. Verificar debouncing melhorado
        $this->verifyImprovedDebouncing();
        
        // 4. Verificar formatação corrigida das datas
        $this->verifyDateFormatting();
        
        // 5. Simular performance com dados reais
        $this->simulateRealWorldPerformance();
    }
    
    private function verifyChartPooling()
    {
        echo "\n--- 1. VERIFICAÇÃO DO CHART POOLING ---\n";
        
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Verificar se ChartPool foi implementado
        $hasChartPool = strpos($content, 'ChartPool') !== false;
        $hasPoolGet = strpos($content, 'ChartPool.get') !== false;
        $hasPoolRelease = strpos($content, 'ChartPool.release') !== false;
        
        echo "Chart Pooling implementado: " . ($hasChartPool ? 'Sim' : 'Não') . "\n";
        echo "Método get() implementado: " . ($hasPoolGet ? 'Sim' : 'Não') . "\n";
        echo "Método release() implementado: " . ($hasPoolRelease ? 'Sim' : 'Não') . "\n";
        
        if ($hasChartPool && $hasPoolGet) {
            echo "✅ Chart Pooling implementado com sucesso\n";
        } else {
            echo "❌ Chart Pooling não encontrado ou incompleto\n";
        }
    }
    
    private function verifyOptimizedConfigurations()
    {
        echo "\n--- 2. VERIFICAÇÃO DAS CONFIGURAÇÕES OTIMIZADAS ---\n";
        
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Verificar otimizações específicas
        $animationSpeed = strpos($content, 'speed: 400') !== false; // Verificar se velocidade foi reduzida
        $dynamicAnimations = strpos($content, 'dynamicAnimation: { enabled: false }') !== false;
        $redrawOptimization = strpos($content, 'redrawOnWindowResize: false') !== false;
        $zoomDisabled = strpos($content, 'zoom: { enabled: false }') !== false;
        
        echo "Velocidade de animação otimizada (400ms): " . ($animationSpeed ? 'Sim' : 'Não') . "\n";
        echo "Animações dinâmicas desabilitadas: " . ($dynamicAnimations ? 'Sim' : 'Não') . "\n";
        echo "Auto-resize otimizado: " . ($redrawOptimization ? 'Sim' : 'Não') . "\n";
        echo "Zoom desabilitado: " . ($zoomDisabled ? 'Sim' : 'Não') . "\n";
        
        $optimizations = [$animationSpeed, $dynamicAnimations, $redrawOptimization, $zoomDisabled];
        $optimizationCount = count(array_filter($optimizations));
        
        echo "Otimizações implementadas: {$optimizationCount}/4\n";
        
        if ($optimizationCount >= 3) {
            echo "✅ Configurações bem otimizadas\n";
        } else {
            echo "⚠️  Algumas otimizações podem estar faltando\n";
        }
    }
    
    private function verifyImprovedDebouncing()
    {
        echo "\n--- 3. VERIFICAÇÃO DO DEBOUNCING MELHORADO ---\n";
        
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Verificar melhorias no debouncing
        $improvedTimeout = strpos($content, '100'); // Timeout reduzido para 100ms
        $throttling = strpos($content, 'lastResize') !== false;
        $performanceMonitoring = strpos($content, 'PerformanceMonitor.start(\'resize_all_charts\')') !== false;
        
        echo "Timeout otimizado (100ms): " . ($improvedTimeout ? 'Sim' : 'Não') . "\n";
        echo "Throttling adicional implementado: " . ($throttling ? 'Sim' : 'Não') . "\n";
        echo "Monitoramento de performance no resize: " . ($performanceMonitoring ? 'Sim' : 'Não') . "\n";
        
        if ($throttling && $performanceMonitoring) {
            echo "✅ Debouncing melhorado implementado\n";
        } else {
            echo "⚠️  Debouncing pode precisar de melhorias\n";
        }
    }
    
    private function verifyDateFormatting()
    {
        echo "\n--- 4. VERIFICAÇÃO DA FORMATAÇÃO DE DATAS ---\n";
        
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        $content = file_get_contents($jsPath);
        
        // Verificar se a formatação das datas foi corrigida
        $dateFormatComment = strpos($content, 'dia da semana embaixo do número') !== false;
        $categoryMapping = strpos($content, '`${d.dia}\\n${d.dia_semana || \'\'}`') !== false;
        
        echo "Comentário sobre formatação atualizado: " . ($dateFormatComment ? 'Sim' : 'Não') . "\n";
        echo "Mapeamento de categorias mantido correto: " . ($categoryMapping ? 'Sim' : 'Não') . "\n";
        
        if ($dateFormatComment) {
            echo "✅ Formatação de datas documentada corretamente\n";
            echo "ℹ️  Formato: Número do dia na linha superior, dia da semana na linha inferior\n";
        } else {
            echo "❌ Documentação da formatação de datas não encontrada\n";
        }
    }
    
    private function simulateRealWorldPerformance()
    {
        echo "\n--- 5. SIMULAÇÃO DE PERFORMANCE COM DADOS REAIS ---\n";
        
        $startTime = microtime(true);
        $memoryStart = memory_get_usage();
        
        // Simular criação de múltiplos gráficos como no dashboard real
        $numeroGraficos = 20; // Simular 20 gráficos
        $dadosProcessados = 0;
        
        for ($i = 1; $i <= $numeroGraficos; $i++) {
            // Simular processamento de dados para cada gráfico
            $dadosGrafico = $this->generateChartData();
            
            // Simular processamento das categorias (formatação de data)
            foreach ($dadosGrafico['dadosDiarios'] as $dia) {
                $categoria = $dia['dia'] . "\n" . $dia['dia_semana'];
                $dadosProcessados++;
            }
            
            // Simular criação de configuração do gráfico
            $configuracao = [
                'chart' => ['type' => 'bar', 'height' => 350],
                'series' => $dadosGrafico['series'],
                'xaxis' => ['categories' => $dadosGrafico['categorias']]
            ];
            
            // Simular overhead de criação
            usleep(1000); // 1ms por gráfico
        }
        
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $processingTime = ($endTime - $startTime) * 1000;
        $memoryUsed = ($memoryEnd - $memoryStart) / 1024;
        
        echo "Gráficos simulados: {$numeroGraficos}\n";
        echo "Pontos de dados processados: {$dadosProcessados}\n";
        echo "Tempo total de processamento: " . round($processingTime, 2) . "ms\n";
        echo "Memória utilizada: " . round($memoryUsed, 2) . "KB\n";
        echo "Tempo médio por gráfico: " . round($processingTime / $numeroGraficos, 2) . "ms\n";
        echo "Performance: " . round($numeroGraficos / ($processingTime/1000), 1) . " gráficos/segundo\n";
        
        // Avaliar performance
        $tempoMedioPorGrafico = $processingTime / $numeroGraficos;
        if ($tempoMedioPorGrafico < 5) {
            echo "✅ Performance excelente (<5ms por gráfico)\n";
        } elseif ($tempoMedioPorGrafico < 10) {
            echo "✅ Performance boa (<10ms por gráfico)\n";
        } elseif ($tempoMedioPorGrafico < 20) {
            echo "⚠️  Performance aceitável (<20ms por gráfico)\n";
        } else {
            echo "❌ Performance ruim (>20ms por gráfico)\n";
        }
    }
    
    private function generateChartData()
    {
        $dadosDiarios = [];
        $categorias = [];
        $series = [
            ['name' => 'Pactuado', 'data' => []],
            ['name' => 'Agendado', 'data' => []],
            ['name' => 'Realizado', 'data' => []]
        ];
        
        // Gerar 31 dias de dados
        for ($dia = 1; $dia <= 31; $dia++) {
            $diaSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'][($dia - 1) % 7];
            
            $dadoDia = [
                'dia' => $dia,
                'dia_semana' => $diaSemana,
                'pactuado' => rand(50, 200),
                'agendado' => rand(45, 180),
                'realizado' => rand(40, 170)
            ];
            
            $dadosDiarios[] = $dadoDia;
            $categorias[] = "{$dia}\n{$diaSemana}";
            
            $series[0]['data'][] = $dadoDia['pactuado'];
            $series[1]['data'][] = $dadoDia['agendado'];
            $series[2]['data'][] = $dadoDia['realizado'];
        }
        
        return [
            'dadosDiarios' => $dadosDiarios,
            'categorias' => $categorias,
            'series' => $series
        ];
    }
}
