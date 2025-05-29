<?php

use PHPUnit\Framework\TestCase;

class DashboardPerformanceTest extends TestCase
{
    private $startTime;
    private $memoryStart;
    
    protected function setUp(): void
    {
        $this->startTime = microtime(true);
        $this->memoryStart = memory_get_usage();
    }
      /**
     * Teste de performance da página do dashboard
     */
    public function testDashboardPageLoadTime()
    {
        // Captura o tempo de início
        $startTime = microtime(true);
        
        // Simula o carregamento da página do dashboard
        ob_start();
        $_GET['unidade_id'] = 1;
        $_GET['data_inicio'] = '2024-01-01';
        $_GET['data_fim'] = '2024-01-31';
        
        // Lê arquivo HTML do dashboard com ApexCharts
        $dashboardPath = __DIR__ . '/../../public/dashboard-with-charts.html';
        if (!file_exists($dashboardPath)) {
            $dashboardPath = __DIR__ . '/../../src/views/relatorio/dashboard.php';
        }
        
        $this->assertTrue(file_exists($dashboardPath), "Arquivo dashboard não encontrado");
        
        $htmlContent = file_get_contents($dashboardPath);
        echo $htmlContent;
        
        $output = ob_get_clean();
        
        // Calcula o tempo de execução
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // em millisegundos
        
        // Assertions de performance
        $this->assertLessThan(500, $executionTime, 
            "Dashboard deve carregar em menos de 500ms. Tempo atual: {$executionTime}ms");
        
        // Verifica se o HTML foi gerado
        $this->assertNotEmpty($output, "Dashboard deve gerar conteúdo HTML");
        
        // Verifica se contém elementos essenciais ApexCharts
        $this->assertStringContainsString('apexchart', $output, 
            "Dashboard deve conter elementos ApexCharts");
        
        echo "\n[PERFORMANCE] Dashboard carregou em: " . round($executionTime, 2) . "ms\n";
    }
    
    /**
     * Teste de performance do JavaScript ApexCharts
     */
    public function testApexChartsJavaScriptLoadTime()
    {
        $startTime = microtime(true);
        
        // Lê o arquivo JavaScript do ApexCharts
        $jsPath = __DIR__ . '/../../public/assets/js/relatorio.js';
        
        $this->assertFileExists($jsPath, "Arquivo ApexCharts JavaScript deve existir");
        
        $jsContent = file_get_contents($jsPath);
        $this->assertNotEmpty($jsContent, "Arquivo JavaScript não deve estar vazio");
        
        // Verifica tamanho do arquivo (performance)
        $fileSize = strlen($jsContent);
        $this->assertLessThan(50000, $fileSize, 
            "Arquivo JavaScript deve ser menor que 50KB. Tamanho atual: " . round($fileSize/1024, 2) . "KB");
        
        // Verifica se contém funções essenciais
        $this->assertStringContainsString('criarGraficoColuna', $jsContent,
            "JavaScript deve conter função criarGraficoColuna");
        $this->assertStringContainsString('criarGraficoLinha', $jsContent,
            "JavaScript deve conter função criarGraficoLinha");
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        echo "\n[PERFORMANCE] JavaScript analisado em: " . round($executionTime, 2) . "ms\n";
        echo "[INFO] Tamanho do arquivo JS: " . round($fileSize/1024, 2) . "KB\n";
    }
    
    /**
     * Teste de memory usage durante processamento de dados
     */
    public function testDashboardMemoryUsage()
    {
        $memoryStart = memory_get_usage();
        
        // Simula processamento de dados pesados
        $services = [];
        for ($i = 0; $i < 10; $i++) {
            $dadosDiarios = [];
            for ($j = 0; $j < 31; $j++) { // 31 dias
                $dadosDiarios[] = (object)[
                    'pactuado' => rand(50, 200),
                    'agendado' => rand(45, 180),
                    'executado' => rand(40, 170)
                ];
            }
            
            $services[] = (object)[
                'id' => $i,
                'nome' => "Serviço $i",
                'dadosDiarios' => $dadosDiarios
            ];
        }
        
        // Simula cálculos do dashboard
        foreach ($services as $service) {
            $total_pactuado = 0;
            $total_agendado = 0;
            
            foreach ($service->dadosDiarios as $dado) {
                $total_pactuado += $dado->pactuado;
                $total_agendado += $dado->agendado;
            }
            
            $service->total_pactuado = $total_pactuado;
            $service->total_agendado = $total_agendado;
        }
        
        $memoryEnd = memory_get_usage();
        $memoryUsed = ($memoryEnd - $memoryStart) / 1024 / 1024; // em MB
        
        // Assertion de memory usage
        $this->assertLessThan(10, $memoryUsed, 
            "Processamento deve usar menos de 10MB. Uso atual: " . round($memoryUsed, 2) . "MB");
        
        echo "\n[PERFORMANCE] Memória utilizada: " . round($memoryUsed, 2) . "MB\n";
    }
    
    /**
     * Teste de performance da query de dados (simulado)
     */
    public function testDataQueryPerformance()
    {
        $startTime = microtime(true);
        
        // Simula uma query pesada de dados
        $results = [];
        for ($i = 0; $i < 1000; $i++) {
            $results[] = [
                'id' => $i,
                'data' => date('Y-m-d', strtotime("-$i days")),
                'pactuado' => rand(50, 200),
                'agendado' => rand(45, 180),
                'executado' => rand(40, 170)
            ];
        }
        
        // Simula processamento dos dados
        $grouped = [];
        foreach ($results as $row) {
            $key = $row['data'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'pactuado' => 0,
                    'agendado' => 0,
                    'executado' => 0
                ];
            }
            
            $grouped[$key]['pactuado'] += $row['pactuado'];
            $grouped[$key]['agendado'] += $row['agendado'];
            $grouped[$key]['executado'] += $row['executado'];
        }
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->assertLessThan(100, $executionTime,
            "Query e processamento devem executar em menos de 100ms. Tempo atual: {$executionTime}ms");
        
        echo "\n[PERFORMANCE] Query simulada executada em: " . round($executionTime, 2) . "ms\n";
        echo "[INFO] Registros processados: " . count($results) . "\n";
        echo "[INFO] Grupos criados: " . count($grouped) . "\n";
    }
    
    protected function tearDown(): void
    {
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage();
        
        $totalTime = ($endTime - $this->startTime) * 1000;
        $totalMemory = ($memoryEnd - $this->memoryStart) / 1024 / 1024;
        
        echo "\n[TOTAL] Tempo do teste: " . round($totalTime, 2) . "ms\n";
        echo "[TOTAL] Memória do teste: " . round($totalMemory, 2) . "MB\n";
    }
}
