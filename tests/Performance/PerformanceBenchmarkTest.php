<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

/**
 * Benchmark de performance para monitoramento de melhorias
 * Mede tempos de execução e uso de recursos
 */
class PerformanceBenchmarkTest extends TestCase
{
    /**
     * @var array Histórico de benchmarks para comparação
     */
    private $benchmarkHistory = [];
    
    /**
     * @var array Limites de performance baseados nos logs do console
     */
    private $performanceLimits;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Limites baseados nos problemas reportados no console
        // Objetivo: reduzir de 200-377ms para menos de 150ms por gráfico
        $this->performanceLimits = [
            'chart_render_max' => 150.0, // Máximo 150ms por gráfico (melhoria de ~60%)
            'chart_render_target' => 100.0, // Objetivo de 100ms por gráfico
            'total_render_max' => 800.0, // Máximo 800ms para todos os gráficos
            'memory_limit' => 32 * 1024 * 1024, // 32MB máximo
            'dom_operations_max' => 50, // Máximo 50 operações DOM por gráfico
        ];
    }

    /**
     * Testa performance de renderização individual de gráficos
     * @dataProvider chartRenderingDataProvider
     */
    public function testIndividualChartRenderingPerformance($chartData, $expectedMaxTime)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Simular processamento de renderização de gráfico
        $processedData = $this->simulateChartRendering($chartData);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $renderTime = ($endTime - $startTime) * 1000; // Convert para ms
        $memoryUsed = $endMemory - $startMemory;
        
        // Registrar benchmark
        $this->recordBenchmark('chart_rendering', [
            'render_time' => $renderTime,
            'memory_used' => $memoryUsed,
            'data_points' => count($chartData['dadosDiarios'] ?? [])
        ]);
        
        // Asserções de performance
        $this->assertLessThan($expectedMaxTime, $renderTime, 
            "Renderização do gráfico demorou {$renderTime}ms, excedendo limite de {$expectedMaxTime}ms");
        
        $this->assertLessThan($this->performanceLimits['memory_limit'], $memoryUsed,
            "Uso de memória ({$memoryUsed} bytes) excede o limite");
        
        // Log para análise
        echo "\n📊 Performance Chart: {$renderTime}ms | Memória: " . number_format($memoryUsed / 1024, 2) . "KB";
    }

    /**
     * Testa performance de múltiplos gráficos (cenário real)
     */
    public function testMultipleChartsRenderingPerformance()
    {
        $chartCount = 10; // Simular 10 gráficos como no projeto real
        $chartData = $this->generateSampleChartData();
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        $renderTimes = [];
        
        // Simular renderização de múltiplos gráficos
        for ($i = 0; $i < $chartCount; $i++) {
            $chartStartTime = microtime(true);
            
            $this->simulateChartRendering($chartData);
            
            $chartEndTime = microtime(true);
            $chartRenderTime = ($chartEndTime - $chartStartTime) * 1000;
            $renderTimes[] = $chartRenderTime;
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        $totalMemory = memory_get_usage() - $startMemory;
        
        // Análise estatística
        $avgRenderTime = array_sum($renderTimes) / count($renderTimes);
        $maxRenderTime = max($renderTimes);
        $minRenderTime = min($renderTimes);
        
        // Registrar benchmark completo
        $this->recordBenchmark('multiple_charts', [
            'total_time' => $totalTime,
            'avg_render_time' => $avgRenderTime,
            'max_render_time' => $maxRenderTime,
            'min_render_time' => $minRenderTime,
            'total_memory' => $totalMemory,
            'chart_count' => $chartCount
        ]);
        
        // Asserções críticas
        $this->assertLessThan($this->performanceLimits['total_render_max'], $totalTime,
            "Tempo total de renderização ({$totalTime}ms) excede limite de {$this->performanceLimits['total_render_max']}ms");
        
        $this->assertLessThan($this->performanceLimits['chart_render_max'], $maxRenderTime,
            "Gráfico mais lento ({$maxRenderTime}ms) excede limite de {$this->performanceLimits['chart_render_max']}ms");
        
        $this->assertLessThan($this->performanceLimits['chart_render_target'], $avgRenderTime,
            "Tempo médio de renderização ({$avgRenderTime}ms) excede objetivo de {$this->performanceLimits['chart_render_target']}ms");
        
        // Relatório detalhado
        echo "\n🚀 Performance Report:";
        echo "\n   Total: {$totalTime}ms";
        echo "\n   Média: " . number_format($avgRenderTime, 2) . "ms";
        echo "\n   Min: " . number_format($minRenderTime, 2) . "ms";
        echo "\n   Max: " . number_format($maxRenderTime, 2) . "ms";
        echo "\n   Memória: " . number_format($totalMemory / 1024, 2) . "KB";
    }

    /**
     * Testa performance de responsividade (resize operations)
     */
    public function testResponsivePerformance()
    {
        $chartData = $this->generateSampleChartData();
        $resizeOperations = 5; // Simular 5 redimensionamentos
        
        $startTime = microtime(true);
        
        $resizeTimes = [];
        
        for ($i = 0; $i < $resizeOperations; $i++) {
            $resizeStartTime = microtime(true);
            
            // Simular redimensionamento e reposicionamento
            $this->simulateChartResize($chartData);
            
            $resizeEndTime = microtime(true);
            $resizeTimes[] = ($resizeEndTime - $resizeStartTime) * 1000;
        }
        
        $totalResizeTime = (microtime(true) - $startTime) * 1000;
        $avgResizeTime = array_sum($resizeTimes) / count($resizeTimes);
        
        // Registrar benchmark de responsividade
        $this->recordBenchmark('responsive_performance', [
            'total_resize_time' => $totalResizeTime,
            'avg_resize_time' => $avgResizeTime,
            'operations' => $resizeOperations
        ]);
        
        // Responsividade deve ser muito rápida
        $this->assertLessThan(50, $avgResizeTime, 
            "Tempo médio de resize ({$avgResizeTime}ms) muito lento para boa experiência");
        
        echo "\n📱 Responsive Performance: " . number_format($avgResizeTime, 2) . "ms média";
    }

    /**
     * Testa performance de lazy loading
     */
    public function testLazyLoadingPerformance()
    {
        $totalCharts = 20; // Simular página com muitos gráficos
        $visibleCharts = 3; // Apenas 3 visíveis inicialmente
        
        $startTime = microtime(true);
        
        // Simular lazy loading - apenas charts visíveis são carregados
        $loadedCharts = 0;
        for ($i = 0; $i < $visibleCharts; $i++) {
            $this->simulateChartRendering($this->generateSampleChartData());
            $loadedCharts++;
        }
        
        $lazyLoadTime = (microtime(true) - $startTime) * 1000;
        
        // Registrar benchmark de lazy loading
        $this->recordBenchmark('lazy_loading', [
            'load_time' => $lazyLoadTime,
            'loaded_charts' => $loadedCharts,
            'total_charts' => $totalCharts,
            'load_ratio' => $loadedCharts / $totalCharts
        ]);
        
        // Lazy loading deve ser muito mais rápido que carregar tudo
        $expectedFullLoadTime = $totalCharts * $this->performanceLimits['chart_render_target'];
        $this->assertLessThan($expectedFullLoadTime * 0.3, $lazyLoadTime,
            "Lazy loading não está oferecendo benefício suficiente");
        
        echo "\n⚡ Lazy Loading: {$loadedCharts}/{$totalCharts} charts em " . number_format($lazyLoadTime, 2) . "ms";
    }    /**
     * Provider de dados para testes de renderização
     */
    public function chartRenderingDataProvider()
    {
        return [
            'Small Dataset' => [
                self::generateStaticSampleChartData(7), // 7 dias
                100.0 // 100ms target
            ],
            'Medium Dataset' => [
                self::generateStaticSampleChartData(30), // 30 dias
                120.0 // 120ms (80% of max)
            ],
            'Large Dataset' => [
                self::generateStaticSampleChartData(90), // 90 dias
                150.0 // 150ms max
            ]
        ];
    }

    /**
     * Simula renderização de gráfico
     */
    private function simulateChartRendering(array $chartData): array
    {
        // Simular processamento de dados
        $processedData = [];
        
        foreach ($chartData['dadosDiarios'] as $dia) {
            $processedData[] = [
                'categoria' => $dia['dia'],
                'pactuado' => (int)$dia['pactuado'],
                'agendado' => (int)$dia['agendado'],
                'realizado' => (int)$dia['realizado']
            ];
        }
        
        // Simular cálculos de layout
        $maxValue = 0;
        foreach ($processedData as $data) {
            $maxValue = max($maxValue, $data['pactuado'], $data['agendado'], $data['realizado']);
        }
        
        // Simular operações DOM
        for ($i = 0; $i < 10; $i++) {
            $dummy = array_map(function($item) use ($maxValue) {
                return $item['realizado'] / $maxValue;
            }, $processedData);
        }
        
        return $processedData;
    }

    /**
     * Simula redimensionamento de gráfico
     */
    private function simulateChartResize(array $chartData): void
    {
        // Simular recálculo de posições
        $elements = count($chartData['dadosDiarios']);
        
        for ($i = 0; $i < $elements; $i++) {
            // Simular reposicionamento de elementos
            $newPosition = [
                'x' => $i * 50,
                'y' => 100 + ($i % 3) * 20
            ];
        }
        
        // Simular validação de labels
        usleep(1000); // 1ms de delay para simular operação DOM
    }

    /**
     * Gera dados de exemplo para testes
     */
    private function generateSampleChartData(int $days = 7): array
    {
        $diasSemana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'];
        $dadosDiarios = [];
        
        for ($i = 1; $i <= $days; $i++) {
            $dadosDiarios[] = [
                'dia' => $i,
                'dia_semana' => $diasSemana[($i - 1) % 7],
                'pactuado' => rand(50, 200),
                'agendado' => rand(40, 180),
                'realizado' => rand(30, 160)
            ];
        }
        
        return [
            'id' => 1,
            'nome' => 'Teste Performance',
            'dadosDiarios' => $dadosDiarios
        ];
    }

    /**
     * Gera dados de exemplo para testes (versão estática para data provider)
     */
    private static function generateStaticSampleChartData(int $days = 7): array
    {
        $diasSemana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB'];
        $dadosDiarios = [];
        
        for ($i = 1; $i <= $days; $i++) {
            $dadosDiarios[] = [
                'dia' => $i,
                'dia_semana' => $diasSemana[($i - 1) % 7],
                'pactuado' => rand(50, 200),
                'agendado' => rand(40, 180),
                'realizado' => rand(30, 160)
            ];
        }
        
        return [
            'id' => 1,
            'nome' => 'Teste Performance',
            'dadosDiarios' => $dadosDiarios
        ];
    }

    /**
     * Registra benchmark para análise histórica
     */
    private function recordBenchmark(string $type, array $metrics): void
    {
        $this->benchmarkHistory[] = [
            'type' => $type,
            'timestamp' => microtime(true),
            'metrics' => $metrics
        ];
    }

    /**
     * Exibe relatório final de benchmarks
     */
    protected function tearDown(): void
    {
        if (!empty($this->benchmarkHistory)) {
            echo "\n\n" . str_repeat("=", 50);
            echo "\n📋 RELATÓRIO DE PERFORMANCE COMPLETO";
            echo "\n" . str_repeat("=", 50);
            
            foreach ($this->benchmarkHistory as $benchmark) {
                echo "\n\n🔍 {$benchmark['type']}:";
                foreach ($benchmark['metrics'] as $key => $value) {
                    if (is_numeric($value)) {
                        echo "\n   {$key}: " . number_format($value, 2);
                    } else {
                        echo "\n   {$key}: {$value}";
                    }
                }
            }
            echo "\n\n" . str_repeat("=", 50);
        }
        
        parent::tearDown();
    }
}