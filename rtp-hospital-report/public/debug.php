<?php
// Simple debug script to check chart data generation
require_once '../src/config/app.php';
require_once '../src/core/ErrorHandler.php';
require_once '../src/config/database.php';
require_once '../src/helpers/relatorio_helpers.php';
require_once '../src/models/RelatorioModel.php';
require_once '../src/controllers/RelatorioController.php';

try {
    echo "<h1>RTP Hospital Debug</h1>";
    
    // Test database connection
    echo "<h2>Database Connection</h2>";
    $pdo = getDatabaseConnection();
    echo "Database connected: " . ($pdo ? "YES" : "NO") . "<br>";
    
    // Test model
    echo "<h2>Model Test</h2>";
    $model = new RelatorioModel();
    $unidades = $model->obterUnidades();
    echo "Units found: " . count($unidades) . "<br>";
    foreach ($unidades as $unidade) {
        echo "- Unit ID: {$unidade['id']}, Name: {$unidade['nome']}<br>";
    }
    
    // Test chart data generation
    echo "<h2>Chart Data Test</h2>";
    if (!empty($unidades)) {
        $unidade_id = $unidades[0]['id'];
        $mes = 5;
        $ano = 2025;
        
        echo "Testing with Unit ID: $unidade_id, Month: $mes, Year: $ano<br>";
        
        $relatorio = $model->obterRelatorioMensal($unidade_id, $mes, $ano);
        echo "Services found: " . count($relatorio) . "<br>";
        
        foreach ($relatorio as $index => $servico) {
            echo "- Service $index: {$servico['natureza']} (ID: {$servico['servico_id']})<br>";
        }
        
        // Test chart data preparation
        echo "<h3>Chart Data Generation</h3>";
        $dadosGraficos = [];
        foreach (array_slice($relatorio, 0, 3) as $index => $servico) {
            $dadosGraficos[$index] = [
                'id' => $index,
                'nome' => $servico['natureza'],
                'meta_pdt' => (int)($servico['meta_pdt'] ?? 0),
                'total_executados' => (int)($servico['total_executados'] ?? 0),
                'dadosDiarios' => [
                    ['dia' => 1, 'pactuado' => 50, 'agendado' => 45, 'realizado' => 40],
                    ['dia' => 2, 'pactuado' => 50, 'agendado' => 48, 'realizado' => 42],
                    ['dia' => 3, 'pactuado' => 50, 'agendado' => 46, 'realizado' => 38],
                ]
            ];
        }
        
        echo "Chart data prepared for " . count($dadosGraficos) . " services<br>";
        echo "<pre>" . json_encode($dadosGraficos, JSON_PRETTY_PRINT) . "</pre>";
    }
    
    echo "<h2>Chart.js Test Links</h2>";
    echo '<a href="/chart-test.html">Test Chart.js Integration</a><br>';
    echo '<a href="/?unidade=1&mes=5&ano=2025">Main Dashboard with Unit 1</a><br>';
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "<br>";
}
?>
