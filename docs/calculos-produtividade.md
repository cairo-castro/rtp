# 📊 Cálculos de Produtividade - Sistema RTP

## 📋 Visão Geral

Este documento detalha todas as fórmulas, algoritmos e métodos de cálculo utilizados no sistema RTP para determinar indicadores de produtividade hospitalar.

## 🧮 Fórmulas Fundamentais

### 🎯 **Produtividade Principal (Meta PDT)**
```php
function calcularPorcentagemProdutividade($executados, $meta_pdt) {
    if ($meta_pdt == 0) {
        return 0;
    }
    return ($executados / $meta_pdt) * 100;
}
```

**Fórmula Matemática:**
```
Produtividade (%) = (Realizado ÷ Meta PDT) × 100
```

**Exemplo:**
- Realizado: 150 procedimentos
- Meta PDT: 180 procedimentos
- Produtividade: (150 ÷ 180) × 100 = **83.33%**

### 📈 **Produtividade vs Pactuado**
```php
function calcularProdutividadePactuado($executados, $pactuado) {
    if ($pactuado == 0) {
        return 0;
    }
    return ($executados / $pactuado) * 100;
}
```

**Fórmula Matemática:**
```
Produtividade Pactuado (%) = (Realizado ÷ Pactuado) × 100
```

### 📅 **Taxa de Utilização**
```php
function calcularTaxaUtilizacao($agendado, $pactuado) {
    if ($pactuado == 0) {
        return 0;
    }
    return ($agendado / $pactuado) * 100;
}
```

**Fórmula Matemática:**
```
Utilização (%) = (Agendado ÷ Pactuado) × 100
```

### ⚡ **Eficiência Operacional**
```php
function calcularEficiencia($executados, $agendado) {
    if ($agendado == 0) {
        return 0;
    }
    return ($executados / $agendado) * 100;
}
```

**Fórmula Matemática:**
```
Eficiência (%) = (Realizado ÷ Agendado) × 100
```

## 📊 Cálculos Compostos

### 🎯 **Produtividade Ponderada por Grupo**
```php
function calcularProdutividadeGrupo($servicos) {
    $total_executados = 0;
    $total_meta_pdt = 0;
    $total_peso = 0;
    
    foreach ($servicos as $servico) {
        $peso = $servico['meta_pdt']; // Peso baseado na meta
        $total_executados += $servico['total_executados'] * $peso;
        $total_meta_pdt += $servico['meta_pdt'] * $peso;
        $total_peso += $peso;
    }
    
    if ($total_peso == 0) return 0;
    
    return ($total_executados / $total_meta_pdt) * 100;
}
```

### 📈 **Média Móvel de Produtividade**
```php
function calcularMediaMovel($dados_mensais, $periodo = 3) {
    $media_movel = [];
    $valores = array_values($dados_mensais);
    
    for ($i = $periodo - 1; $i < count($valores); $i++) {
        $soma = 0;
        for ($j = 0; $j < $periodo; $j++) {
            $soma += $valores[$i - $j];
        }
        $media_movel[] = $soma / $periodo;
    }
    
    return $media_movel;
}
```

### 📊 **Tendência Linear**
```php
function calcularTendencia($dados_temporais) {
    $n = count($dados_temporais);
    $x = range(1, $n);
    $y = array_values($dados_temporais);
    
    $soma_x = array_sum($x);
    $soma_y = array_sum($y);
    $soma_xy = 0;
    $soma_x2 = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $soma_xy += $x[$i] * $y[$i];
        $soma_x2 += $x[$i] * $x[$i];
    }
    
    // Coeficiente angular (tendência)
    $tendencia = ($n * $soma_xy - $soma_x * $soma_y) / ($n * $soma_x2 - $soma_x * $soma_x);
    
    return $tendencia;
}
```

## 🎨 Sistema de Classificação

### 🚦 **Classificação por Performance**
```php
function classificarProdutividade($produtividade) {
    $classificacao = [
        'status' => '',
        'cor' => '',
        'hexadecimal' => '',
        'descricao' => ''
    ];
    
    if ($produtividade >= 100) {
        $classificacao = [
            'status' => 'Excelente',
            'cor' => 'Verde',
            'hexadecimal' => '#28a745',
            'descricao' => 'Meta atingida ou superada'
        ];
    } elseif ($produtividade >= 80) {
        $classificacao = [
            'status' => 'Bom',
            'cor' => 'Amarelo',
            'hexadecimal' => '#ffc107',
            'descricao' => 'Próximo da meta'
        ];
    } elseif ($produtividade >= 60) {
        $classificacao = [
            'status' => 'Atenção',
            'cor' => 'Laranja',
            'hexadecimal' => '#fd7e14',
            'descricao' => 'Requer atenção'
        ];
    } else {
        $classificacao = [
            'status' => 'Crítico',
            'cor' => 'Vermelho',
            'hexadecimal' => '#dc3545',
            'descricao' => 'Situação crítica'
        ];
    }
    
    return $classificacao;
}
```

### 📊 **Score de Performance Geral**
```php
function calcularScoreGeral($metricas) {
    $pesos = [
        'produtividade' => 0.5,  // 50% do peso
        'eficiencia' => 0.3,     // 30% do peso
        'utilizacao' => 0.2      // 20% do peso
    ];
    
    $score = 0;
    $score += min($metricas['produtividade'], 100) * $pesos['produtividade'];
    $score += min($metricas['eficiencia'], 100) * $pesos['eficiencia'];
    $score += min($metricas['utilizacao'], 100) * $pesos['utilizacao'];
    
    return round($score, 2);
}
```

## 📈 Análises Avançadas

### 📊 **Desvio Padrão de Produtividade**
```php
function calcularDesvioPadrao($dados) {
    $n = count($dados);
    $media = array_sum($dados) / $n;
    
    $soma_quadrados = 0;
    foreach ($dados as $valor) {
        $soma_quadrados += pow($valor - $media, 2);
    }
    
    $variancia = $soma_quadrados / $n;
    return sqrt($variancia);
}
```

### 📈 **Coeficiente de Variação**
```php
function calcularCoeficienteVariacao($dados) {
    $media = array_sum($dados) / count($dados);
    $desvio_padrao = calcularDesvioPadrao($dados);
    
    if ($media == 0) return 0;
    
    return ($desvio_padrao / $media) * 100;
}
```

### 🎯 **Índice de Sazonalidade**
```php
function calcularIndiceSazonalidade($dados_mensais) {
    $media_anual = array_sum($dados_mensais) / 12;
    $indices = [];
    
    foreach ($dados_mensais as $mes => $valor) {
        $indices[$mes] = ($valor / $media_anual) * 100;
    }
    
    return $indices;
}
```

## 🔄 Cálculos Dinâmicos

### ⚡ **Recalculo Automático**
```php
class CalculadoraProdutividade {
    private $cache = [];
    
    public function calcular($servico_id, $periodo, $force_refresh = false) {
        $cache_key = "prod_{$servico_id}_{$periodo}";
        
        if (!$force_refresh && isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        $dados = $this->obterDadosServico($servico_id, $periodo);
        $resultado = $this->executarCalculos($dados);
        
        $this->cache[$cache_key] = $resultado;
        return $resultado;
    }
    
    private function executarCalculos($dados) {
        return [
            'produtividade' => calcularPorcentagemProdutividade(
                $dados['executados'], 
                $dados['meta_pdt']
            ),
            'eficiencia' => calcularEficiencia(
                $dados['executados'], 
                $dados['agendado']
            ),
            'utilizacao' => calcularTaxaUtilizacao(
                $dados['agendado'], 
                $dados['pactuado']
            ),
            'classificacao' => classificarProdutividade(
                $dados['produtividade']
            )
        ];
    }
}
```

### 📊 **Agregação por Período**
```php
function agregarPorPeriodo($dados_diarios, $tipo_periodo = 'mensal') {
    $agregado = [];
    
    foreach ($dados_diarios as $registro) {
        $chave_periodo = '';
        
        switch ($tipo_periodo) {
            case 'mensal':
                $chave_periodo = date('Y-m', strtotime($registro['data']));
                break;
            case 'semanal':
                $chave_periodo = date('Y-W', strtotime($registro['data']));
                break;
            case 'trimestral':
                $mes = date('n', strtotime($registro['data']));
                $trimestre = ceil($mes / 3);
                $chave_periodo = date('Y', strtotime($registro['data'])) . '-T' . $trimestre;
                break;
        }
        
        if (!isset($agregado[$chave_periodo])) {
            $agregado[$chave_periodo] = [
                'executados' => 0,
                'agendados' => 0,
                'dias_uteis' => 0
            ];
        }
        
        $agregado[$chave_periodo]['executados'] += $registro['executados'];
        $agregado[$chave_periodo]['agendados'] += $registro['agendados'];
        $agregado[$chave_periodo]['dias_uteis']++;
    }
    
    return $agregado;
}
```

## 🎯 Validações e Tratamentos

### ✅ **Validação de Dados**
```php
function validarDadosCalculo($dados) {
    $erros = [];
    
    // Verificar valores negativos
    if ($dados['executados'] < 0) {
        $erros[] = 'Executados não pode ser negativo';
    }
    
    if ($dados['agendados'] < 0) {
        $erros[] = 'Agendados não pode ser negativo';
    }
    
    // Verificar consistência lógica
    if ($dados['executados'] > $dados['agendados'] * 1.2) {
        $erros[] = 'Executados muito superior aos agendados (>120%)';
    }
    
    // Verificar metas
    if ($dados['meta_pdt'] <= 0) {
        $erros[] = 'Meta PDT deve ser maior que zero';
    }
    
    return $erros;
}
```

### 🛡️ **Tratamento de Exceções**
```php
function calcularComSeguranca($executados, $meta) {
    try {
        if (!is_numeric($executados) || !is_numeric($meta)) {
            throw new InvalidArgumentException('Valores devem ser numéricos');
        }
        
        if ($meta <= 0) {
            throw new DivisionByZeroError('Meta não pode ser zero ou negativa');
        }
        
        return calcularPorcentagemProdutividade($executados, $meta);
        
    } catch (Exception $e) {
        error_log("Erro no cálculo de produtividade: " . $e->getMessage());
        return 0;
    }
}
```

## 📊 Relatórios de Cálculo

### 📈 **Auditoria de Cálculos**
```php
function gerarAuditoriaCalculo($servico_id, $periodo) {
    return [
        'servico_id' => $servico_id,
        'periodo' => $periodo,
        'timestamp' => date('Y-m-d H:i:s'),
        'dados_origem' => [
            'executados' => 0,
            'meta_pdt' => 0,
            'pactuado' => 0,
            'agendados' => 0
        ],
        'calculos_realizados' => [
            'produtividade' => 0,
            'eficiencia' => 0,
            'utilizacao' => 0
        ],
        'formulas_utilizadas' => [
            'produtividade' => '(Executados / Meta PDT) * 100',
            'eficiencia' => '(Executados / Agendados) * 100',
            'utilizacao' => '(Agendados / Pactuado) * 100'
        ],
        'validacoes' => [],
        'observacoes' => []
    ];
}
```

### 📋 **Log de Performance**
```php
function logPerformanceCalculo($inicio, $fim, $operacao) {
    $tempo_execucao = ($fim - $inicio) * 1000; // em millisegundos
    
    $log = [
        'operacao' => $operacao,
        'tempo_ms' => round($tempo_execucao, 2),
        'timestamp' => date('Y-m-d H:i:s'),
        'memoria_utilizada' => memory_get_usage(true)
    ];
    
    if ($tempo_execucao > 100) { // Alerta para operações > 100ms
        $log['alerta'] = 'Operação lenta detectada';
    }
    
    file_put_contents('logs/performance_calculos.log', 
                     json_encode($log) . "\n", 
                     FILE_APPEND);
}
```

## 🚀 Otimizações

### ⚡ **Cache Inteligente**
```php
class CacheCalculos {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('127.0.0.1', 6379);
    }
    
    public function obter($chave) {
        $valor = $this->redis->get($chave);
        return $valor ? json_decode($valor, true) : null;
    }
    
    public function armazenar($chave, $valor, $ttl = 3600) {
        $this->redis->setex($chave, $ttl, json_encode($valor));
    }
    
    public function invalidar($pattern) {
        $keys = $this->redis->keys($pattern);
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
}
```

### 📊 **Processamento em Lote**
```php
function processarCalculosLote($servicos, $periodo) {
    $resultados = [];
    $inicio = microtime(true);
    
    foreach ($servicos as $servico) {
        $dados = obterDadosServico($servico['id'], $periodo);
        $resultados[$servico['id']] = executarCalculos($dados);
    }
    
    $fim = microtime(true);
    logPerformanceCalculo($inicio, $fim, 'processamento_lote');
    
    return $resultados;
}
```

---

*Última atualização: Junho 2025*
