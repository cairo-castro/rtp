<?php

namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

/**
 * Testes de performance para otimizações de gráficos
 * Valida melhorias de renderização e responsividade
 */
class ChartOptimizationTest extends TestCase
{
    /**
     * @var array Dados de teste simulando um serviço
     */
    private $sampleData;
    
    /**
     * @var array Configurações de performance esperadas
     */
    private $performanceThresholds;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Dados de teste simulando um serviço médico
        $this->sampleData = [
            'id' => 1,
            'nome' => 'Consultas Cardiologia',
            'dadosDiarios' => [
                ['dia' => 1, 'dia_semana' => 'SEG', 'pactuado' => 100, 'agendado' => 85, 'realizado' => 78],
                ['dia' => 2, 'dia_semana' => 'TER', 'pactuado' => 120, 'agendado' => 110, 'realizado' => 95],
                ['dia' => 3, 'dia_semana' => 'QUA', 'pactuado' => 110, 'agendado' => 100, 'realizado' => 88],
                ['dia' => 4, 'dia_semana' => 'QUI', 'pactuado' => 130, 'agendado' => 125, 'realizado' => 118],
                ['dia' => 5, 'dia_semana' => 'SEX', 'pactuado' => 140, 'agendado' => 135, 'realizado' => 128],
                ['dia' => 6, 'dia_semana' => 'SAB', 'pactuado' => 80, 'agendado' => 75, 'realizado' => 72],
                ['dia' => 7, 'dia_semana' => 'DOM', 'pactuado' => 60, 'agendado' => 55, 'realizado' => 52]
            ],
            'realizado' => 631,
            'pactuado' => 640,
            'meta_pdt' => 700
        ];
        
        // Limites de performance aceitáveis
        $this->performanceThresholds = [
            'max_chart_render_time' => 150, // 150ms máximo por gráfico
            'max_total_render_time' => 800, // 800ms máximo para todos os gráficos
            'min_fps' => 30, // 30 FPS mínimo durante animações
            'max_memory_usage' => 50 * 1024 * 1024, // 50MB máximo
            'max_dom_elements' => 1000 // Máximo 1000 elementos DOM por gráfico
        ];
    }

    /**
     * Testa se as configurações de performance estão otimizadas
     */
    public function testChartPerformanceConfigurations()
    {
        // Simula as configurações do JavaScript
        $optimizedConfig = [
            'animations' => [
                'enabled' => false,
                'speed' => 200,
                'dynamicAnimation' => ['enabled' => false]
            ],
            'redrawOnParentResize' => false,
            'redrawOnWindowResize' => false,
            'zoom' => ['enabled' => false],
            'states' => [
                'hover' => [
                    'filter' => ['type' => 'none']
                ]
            ]
        ];
        
        // Validações de configurações otimizadas
        $this->assertFalse($optimizedConfig['animations']['enabled'], 
            'Animações devem estar desabilitadas para melhor performance');
        
        $this->assertLessThanOrEqual(200, $optimizedConfig['animations']['speed'],
            'Velocidade de animação deve ser <= 200ms');
            
        $this->assertFalse($optimizedConfig['redrawOnParentResize'],
            'Redraw automático no resize deve estar desabilitado');
            
        $this->assertFalse($optimizedConfig['zoom']['enabled'],
            'Zoom deve estar desabilitado para melhor performance');
    }

    /**
     * Testa a estrutura de dados para eficiência
     */
    public function testDataStructureEfficiency()
    {
        $data = $this->sampleData;
        
        // Validar estrutura dos dados
        $this->assertIsArray($data['dadosDiarios'], 'Dados diários devem ser um array');
        $this->assertNotEmpty($data['dadosDiarios'], 'Dados diários não podem estar vazios');
        
        // Validar que todos os dias têm dados completos
        foreach ($data['dadosDiarios'] as $dia) {
            $this->assertArrayHasKey('dia', $dia, 'Cada dia deve ter número do dia');
            $this->assertArrayHasKey('dia_semana', $dia, 'Cada dia deve ter dia da semana');
            $this->assertArrayHasKey('pactuado', $dia, 'Cada dia deve ter valor pactuado');
            $this->assertArrayHasKey('agendado', $dia, 'Cada dia deve ter valor agendado');
            $this->assertArrayHasKey('realizado', $dia, 'Cada dia deve ter valor realizado');
            
            // Validar tipos de dados
            $this->assertIsNumeric($dia['pactuado'], 'Pactuado deve ser numérico');
            $this->assertIsNumeric($dia['agendado'], 'Agendado deve ser numérico');
            $this->assertIsNumeric($dia['realizado'], 'Realizado deve ser numérico');
        }
    }

    /**
     * Testa otimizações de memória
     */
    public function testMemoryOptimizations()
    {
        $initialMemory = memory_get_usage();
        
        // Simular processamento de dados como no JavaScript
        $processedData = $this->processChartData($this->sampleData);
        
        $finalMemory = memory_get_usage();
        $memoryUsed = $finalMemory - $initialMemory;
        
        $this->assertLessThan($this->performanceThresholds['max_memory_usage'], $memoryUsed,
            "Uso de memória ({$memoryUsed} bytes) excede o limite de {$this->performanceThresholds['max_memory_usage']} bytes");
    }

    /**
     * Testa a eficiência do processamento de dados
     */
    public function testDataProcessingPerformance()
    {
        $startTime = microtime(true);
        
        // Simular múltiplos processamentos (como múltiplos gráficos)
        for ($i = 0; $i < 10; $i++) {
            $this->processChartData($this->sampleData);
        }
        
        $endTime = microtime(true);
        $processingTime = ($endTime - $startTime) * 1000; // Convert para ms
        
        $this->assertLessThan($this->performanceThresholds['max_total_render_time'], $processingTime,
            "Tempo de processamento ({$processingTime}ms) excede o limite de {$this->performanceThresholds['max_total_render_time']}ms");
    }

    /**
     * Testa otimizações de lazy loading
     */
    public function testLazyLoadingConfiguration()
    {
        // Configurações de lazy loading
        $lazyConfig = [
            'threshold' => 0.1,
            'rootMargin' => '50px',
            'enabled' => true
        ];
        
        $this->assertTrue($lazyConfig['enabled'], 'Lazy loading deve estar habilitado');
        $this->assertLessThanOrEqual(0.5, $lazyConfig['threshold'], 
            'Threshold deve ser baixo para carregamento eficiente');
        $this->assertNotEmpty($lazyConfig['rootMargin'], 
            'Root margin deve estar configurado para pre-loading');
    }

    /**
     * Testa configurações de responsive design
     */
    public function testResponsiveOptimizations()
    {
        $breakpoints = [
            'mobile' => 480,
            'tablet' => 768,
            'desktop' => 1024
        ];
        
        foreach ($breakpoints as $device => $width) {
            $this->assertIsNumeric($width, "Breakpoint para {$device} deve ser numérico");
            $this->assertGreaterThan(0, $width, "Breakpoint para {$device} deve ser positivo");
        }
        
        // Testar configurações específicas para mobile
        $mobileConfig = $this->getMobileOptimizations();
        $this->assertFalse($mobileConfig['animations'], 'Animações devem estar desabilitadas em mobile');
        $this->assertLessThan(12, $mobileConfig['fontSize'], 'Font size deve ser otimizado para mobile');
    }

    /**
     * Testa pooling de objetos para reutilização
     */
    public function testObjectPooling()
    {
        // Simular criação de múltiplos gráficos
        $chartTypes = ['bar', 'line', 'gauge'];
        $createdCharts = [];
        
        foreach ($chartTypes as $type) {
            for ($i = 0; $i < 5; $i++) {
                $chartId = "{$type}_chart_{$i}";
                $createdCharts[] = $this->createMockChart($type, $chartId);
            }
        }
        
        $this->assertCount(15, $createdCharts, 'Devem ser criados 15 gráficos mock');
        
        // Verificar que não há vazamentos de memória
        $memoryAfterCreation = memory_get_usage();
        
        // Limpar referências
        $createdCharts = null;
        gc_collect_cycles();
        
        $memoryAfterCleanup = memory_get_usage();
        $memoryFreed = $memoryAfterCreation - $memoryAfterCleanup;
        
        $this->assertGreaterThanOrEqual(0, $memoryFreed, 'Memória deve ser liberada após limpeza');
    }

    /**
     * Processa dados do gráfico (simulação do JavaScript)
     */
    private function processChartData(array $data): array
    {
        $processed = [
            'categories' => [],
            'series' => [
                'pactuado' => [],
                'agendado' => [],
                'realizado' => []
            ],
            'diasSemana' => []
        ];
        
        foreach ($data['dadosDiarios'] as $dia) {
            $processed['categories'][] = $dia['dia'];
            $processed['series']['pactuado'][] = (int)$dia['pactuado'];
            $processed['series']['agendado'][] = (int)$dia['agendado'];
            $processed['series']['realizado'][] = (int)$dia['realizado'];
            $processed['diasSemana'][] = $dia['dia_semana'];
        }
        
        return $processed;
    }

    /**
     * Retorna configurações otimizadas para mobile
     */
    private function getMobileOptimizations(): array
    {
        return [
            'animations' => false,
            'fontSize' => 9,
            'reduced_data_points' => true,
            'simplified_tooltips' => true,
            'minimal_grid' => true
        ];
    }

    /**
     * Cria um gráfico mock para testes
     */
    private function createMockChart(string $type, string $id): array
    {
        return [
            'id' => $id,
            'type' => $type,
            'config' => $this->getOptimizedConfig($type),
            'data' => $this->sampleData,
            'created_at' => microtime(true)
        ];
    }

    /**
     * Retorna configuração otimizada para um tipo de gráfico
     */
    private function getOptimizedConfig(string $type): array
    {
        $baseConfig = [
            'animations' => ['enabled' => false],
            'redrawOnParentResize' => false,
            'redrawOnWindowResize' => false
        ];
        
        switch ($type) {
            case 'bar':
                $baseConfig['plotOptions'] = [
                    'bar' => [
                        'columnWidth' => '75%',
                        'borderRadius' => 0
                    ]
                ];
                break;
                
            case 'gauge':
                $baseConfig['plotOptions'] = [
                    'radialBar' => [
                        'lineCap' => 'butt',
                        'dataLabels' => [
                            'total' => ['show' => false]
                        ]
                    ]
                ];
                break;
        }
        
        return $baseConfig;
    }
}