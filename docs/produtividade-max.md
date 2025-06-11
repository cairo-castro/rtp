# 📈 Produtividade vs Produtividade Máxima - Sistema RTP

## 📋 Visão Geral

Este documento explica a diferença entre **Produtividade Real** e **Produtividade Máxima**, como são calculadas e aplicadas no sistema RTP para análise de performance hospitalar.

## 🎯 Conceitos Fundamentais

### 📊 **Produtividade Real**
A produtividade real representa a performance atual do serviço baseada na **Meta PDT** (Plano Diretor Territorializado).

```php
$produtividade_real = ($total_executados / $meta_pdt) * 100;
```

**Características:**
- **Base**: Meta PDT oficial
- **Referência**: Necessidade territorial
- **Uso**: Indicador principal de performance
- **Range**: 0% a ∞% (pode ultrapassar 100%)

### 🚀 **Produtividade Máxima**
A produtividade máxima representa o **potencial teórico** do serviço baseado na capacidade total **Pactuada**.

```php
$produtividade_maxima = ($total_executados / $total_pactuado) * 100;
```

**Características:**
- **Base**: Capacidade pactuada
- **Referência**: Potencial operacional
- **Uso**: Análise de capacidade instalada
- **Range**: 0% a ∞% (pode ultrapassar 100%)

## 📊 Análise Comparativa

### 🔍 **Cenários de Análise**

#### **Cenário 1: Performance Equilibrada**
```
Realizado: 180 procedimentos
Meta PDT: 180 procedimentos  
Pactuado: 200 procedimentos

Produtividade Real: (180 ÷ 180) × 100 = 100% ✅
Produtividade Máxima: (180 ÷ 200) × 100 = 90%

Interpretação: Meta atingida, mas ainda há capacidade ociosa
```

#### **Cenário 2: Sobre-performance com Limitação**
```
Realizado: 210 procedimentos
Meta PDT: 180 procedimentos
Pactuado: 200 procedimentos

Produtividade Real: (210 ÷ 180) × 100 = 116.67% 🚀
Produtividade Máxima: (210 ÷ 200) × 100 = 105%

Interpretação: Superou meta e capacidade pactuada (trabalho extra)
```

#### **Cenário 3: Sub-performance com Capacidade**
```
Realizado: 120 procedimentos
Meta PDT: 180 procedimentos
Pactuado: 200 procedimentos

Produtividade Real: (120 ÷ 180) × 100 = 66.67% ⚠️
Produtividade Máxima: (120 ÷ 200) × 100 = 60%

Interpretação: Abaixo da meta com alta capacidade ociosa
```

#### **Cenário 4: Meta Superior à Capacidade**
```
Realizado: 190 procedimentos
Meta PDT: 220 procedimentos
Pactuado: 200 procedimentos

Produtividade Real: (190 ÷ 220) × 100 = 86.36% ⚠️
Produtividade Máxima: (190 ÷ 200) × 100 = 95%

Interpretação: Meta territorial incompatível com capacidade instalada
```

## 🧮 Cálculos e Métricas

### 📈 **Índice de Utilização de Capacidade**
```php
function calcularIndiceUtilizacaoCapacidade($meta_pdt, $pactuado) {
    if ($pactuado == 0) return 0;
    return ($meta_pdt / $pactuado) * 100;
}

// Interpretação:
// > 100%: Meta superior à capacidade (pressão do sistema)
// = 100%: Meta alinhada à capacidade (ideal)
// < 100%: Capacidade superior à meta (folga operacional)
```

### 🎯 **Gap de Performance**
```php
function calcularGapPerformance($produtividade_real, $produtividade_maxima) {
    return [
        'gap_absoluto' => $produtividade_maxima - $produtividade_real,
        'gap_percentual' => (($produtividade_maxima - $produtividade_real) / $produtividade_maxima) * 100,
        'interpretacao' => $produtividade_real > $produtividade_maxima ? 
                          'Superou capacidade instalada' : 
                          'Dentro da capacidade instalada'
    ];
}
```

### 📊 **Eficiência de Capacidade**
```php
function calcularEficienciaCapacidade($realizado, $agendado, $pactuado) {
    return [
        'utilizacao_agenda' => ($agendado / $pactuado) * 100,      // Demanda vs Capacidade
        'eficiencia_execucao' => ($realizado / $agendado) * 100,   // Execução vs Planejado
        'produtividade_maxima' => ($realizado / $pactuado) * 100,  // Resultado vs Capacidade
        'capacidade_ociosa' => max(0, $pactuado - $realizado),     // Capacidade não utilizada
        'sobrecarga' => max(0, $realizado - $pactuado)             // Trabalho além da capacidade
    ];
}
```

## 📈 Visualização Comparativa

### 🎨 **Gauge Concêntrico Duplo**
```javascript
// Configuração do ApexCharts para gauge duplo
const opcoes = {
    chart: { type: 'radialBar' },
    series: [produtividade_real, produtividade_maxima],
    colors: [cor_grupo, '#0d6efd'],
    labels: ['Real (PDT)', 'Máxima (Pactuado)'],
    plotOptions: {
        radialBar: {
            dataLabels: {
                name: { fontSize: '12px' },
                value: { fontSize: '16px', formatter: val => val + '%' }
            },
            track: {
                background: '#e0e0e0',
                strokeWidth: '10px'
            }
        }
    }
};
```

### 📊 **Dashboard Comparativo**
```php
<!-- Exibição lado a lado -->
<div class="performance-comparison">
    <div class="metric-card">
        <h4>Produtividade Real</h4>
        <div class="metric-value" style="color: <?php echo $cor_grupo; ?>;">
            <?php echo number_format($produtividade_real, 1); ?>%
        </div>
        <div class="metric-base">Base: Meta PDT</div>
    </div>
    
    <div class="metric-card">
        <h4>Produtividade Máxima</h4>
        <div class="metric-value" style="color: #0d6efd;">
            <?php echo number_format($produtividade_maxima, 1); ?>%
        </div>
        <div class="metric-base">Base: Pactuado</div>
    </div>
    
    <div class="gap-indicator">
        <div class="gap-value">
            Gap: <?php echo number_format(abs($produtividade_maxima - $produtividade_real), 1); ?>%
        </div>
    </div>
</div>
```

## 📊 Análise de Cenários

### 🎯 **Matriz de Performance**

| Prod. Real | Prod. Máxima | Situação | Ação Recomendada |
|------------|--------------|----------|-------------------|
| ≥100% | ≥100% | 🟢 Excelente | Manter padrão |
| ≥100% | <100% | 🟡 Meta atingida, capacidade subutilizada | Otimizar recursos |
| <100% | ≥100% | 🟠 Meta não atingida, sobrecarga | Revisar processos |
| <100% | <100% | 🔴 Performance baixa | Intervenção necessária |

### 📈 **Classificação por Quadrantes**
```php
function classificarQuadrante($prod_real, $prod_maxima) {
    if ($prod_real >= 100 && $prod_maxima >= 100) {
        return [
            'quadrante' => 'Q1 - Excelente',
            'cor' => '#28a745',
            'status' => 'Meta atingida e capacidade bem utilizada'
        ];
    } elseif ($prod_real >= 100 && $prod_maxima < 100) {
        return [
            'quadrante' => 'Q2 - Meta Atingida',
            'cor' => '#ffc107',
            'status' => 'Meta atingida mas capacidade subutilizada'
        ];
    } elseif ($prod_real < 100 && $prod_maxima >= 100) {
        return [
            'quadrante' => 'Q3 - Sobrecarga',
            'cor' => '#fd7e14',
            'status' => 'Meta não atingida com sobrecarga operacional'
        ];
    } else {
        return [
            'quadrante' => 'Q4 - Crítico',
            'cor' => '#dc3545',
            'status' => 'Performance baixa em ambos indicadores'
        ];
    }
}
```

## 🚀 Otimização de Performance

### 📊 **Análise de Capacidade Ótima**
```php
function analisarCapacidadeOtima($historico_dados) {
    $analise = [];
    
    foreach ($historico_dados as $periodo => $dados) {
        $demanda_real = $dados['total_executados'];
        $demanda_agendada = $dados['total_agendado'];
        $capacidade_atual = $dados['pactuado'];
        
        // Calcular capacidade ótima baseada na demanda histórica
        $capacidade_otima = max($demanda_real, $dados['meta_pdt']) * 1.1; // 10% de folga
        
        $analise[$periodo] = [
            'capacidade_atual' => $capacidade_atual,
            'capacidade_otima' => $capacidade_otima,
            'ajuste_necessario' => $capacidade_otima - $capacidade_atual,
            'percentual_ajuste' => (($capacidade_otima - $capacidade_atual) / $capacidade_atual) * 100
        ];
    }
    
    return $analise;
}
```

### 🎯 **Recomendações Inteligentes**
```php
function gerarRecomendacoes($prod_real, $prod_maxima, $dados_servico) {
    $recomendacoes = [];
    
    if ($prod_real < 80 && $prod_maxima < 80) {
        $recomendacoes[] = [
            'tipo' => 'critico',
            'acao' => 'Revisar processos operacionais',
            'prazo' => 'Imediato',
            'responsavel' => 'Gestão operacional'
        ];
    }
    
    if ($prod_real >= 100 && $prod_maxima < 90) {
        $recomendacoes[] = [
            'tipo' => 'otimizacao',
            'acao' => 'Redistribuir capacidade ociosa',
            'prazo' => '30 dias',
            'responsavel' => 'Planejamento'
        ];
    }
    
    if ($prod_maxima > 110) {
        $recomendacoes[] = [
            'tipo' => 'expansao',
            'acao' => 'Avaliar aumento de capacidade',
            'prazo' => '60 dias',
            'responsavel' => 'Gestão estratégica'
        ];
    }
    
    return $recomendacoes;
}
```

## 📊 Relatórios Especializados

### 📈 **Relatório Comparativo Mensal**
```php
function gerarRelatorioComparativo($unidade_id, $periodo) {
    return [
        'periodo' => $periodo,
        'resumo_executivo' => [
            'total_servicos' => 0,
            'media_prod_real' => 0,
            'media_prod_maxima' => 0,
            'gap_medio' => 0,
            'servicos_equilibrados' => 0,
            'servicos_sobrecarga' => 0,
            'servicos_subutilizados' => 0
        ],
        'analise_quadrantes' => [
            'q1_excelente' => 0,
            'q2_meta_atingida' => 0,
            'q3_sobrecarga' => 0,
            'q4_critico' => 0
        ],
        'recomendacoes_estrategicas' => [],
        'oportunidades_otimizacao' => []
    ];
}
```

### 📋 **Dashboard de Otimização**
```php
function criarDashboardOtimizacao($dados_comparativos) {
    return [
        'indicadores_chave' => [
            'eficiencia_geral' => 0,
            'capacidade_ociosa_total' => 0,
            'sobrecarga_total' => 0,
            'potencial_otimizacao' => 0
        ],
        'graficos_analise' => [
            'scatter_prod_real_vs_maxima' => [],
            'histograma_gaps' => [],
            'mapa_calor_performance' => []
        ],
        'alertas_otimizacao' => [],
        'plano_acao' => []
    ];
}
```

## 🔍 Monitoramento Contínuo

### 📊 **KPIs de Acompanhamento**
```php
function calcularKPIsComparativos($dados_mensais) {
    return [
        'indice_equilibrio' => 0,           // Proximidade entre prod real e máxima
        'taxa_utilizacao_capacidade' => 0,  // % da capacidade sendo utilizada
        'variabilidade_performance' => 0,   // Estabilidade dos indicadores
        'tendencia_convergencia' => 0,      // Aproximação entre as produtividades
        'score_otimizacao' => 0             // Índice geral 0-100
    ];
}
```

### 🚨 **Alertas Inteligentes**
```php
function verificarAlertasComparativos($prod_real, $prod_maxima) {
    $alertas = [];
    
    $gap = abs($prod_real - $prod_maxima);
    
    if ($gap > 30) {
        $alertas[] = [
            'tipo' => 'desbalanceamento',
            'severidade' => 'alta',
            'mensagem' => "Grande diferença entre produtividades: {$gap}%"
        ];
    }
    
    if ($prod_real > 120 && $prod_maxima > 120) {
        $alertas[] = [
            'tipo' => 'sobrecarga_critica',
            'severidade' => 'critica',
            'mensagem' => 'Sobrecarga em ambos indicadores'
        ];
    }
    
    return $alertas;
}
```

## 📚 Boas Práticas

### ✅ **Para Gestores**
1. Monitorar ambos indicadores simultaneamente
2. Buscar equilíbrio entre meta territorial e capacidade
3. Identificar oportunidades de otimização
4. Planejar expansão baseada em dados

### ✅ **Para Analistas**
1. Analisar tendências históricas comparativas
2. Identificar padrões sazonais
3. Calcular capacidade ótima
4. Documentar recomendações

### ✅ **Para Desenvolvedores**
1. Implementar visualizações comparativas claras
2. Criar alertas para gaps significativos
3. Otimizar cálculos de múltiplas métricas
4. Documentar diferenças conceituais

---

*Última atualização: Junho 2025*
