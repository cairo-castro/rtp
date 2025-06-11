# 🎯 Meta PDT - Plano Diretor Territorializado

## 📋 Visão Geral

A **Meta PDT** (Plano Diretor Territorializado) é o indicador mais importante do sistema RTP, representando a meta mensal de produtividade estabelecida pelo planejamento territorial de saúde pública.

## 🏥 Conceito PDT

### 📍 **Definição**
O Plano Diretor Territorializado é um instrumento de gestão que define metas de produção de serviços de saúde baseadas em:
- **População territorial**
- **Perfil epidemiológico**
- **Necessidades de saúde**
- **Capacidade instalada**
- **Recursos disponíveis**

### 🎯 **Características da Meta PDT**
- **Oficial**: Estabelecida por órgãos competentes
- **Territorial**: Baseada na população adscrita
- **Mensal**: Definida para períodos mensais
- **Prioritária**: Referência principal para cálculos
- **Fixa**: Não varia durante o mês

## 📊 Cálculo e Aplicação

### 🧮 **Fórmula Principal**
```php
function calcularProdutividadePDT($realizado, $meta_pdt) {
    if ($meta_pdt <= 0) {
        return 0;
    }
    return ($realizado / $meta_pdt) * 100;
}
```

**Interpretação:**
- **≥ 100%**: Meta atingida ✅
- **80-99%**: Próximo da meta ⚠️
- **< 80%**: Abaixo da meta ❌

### 📈 **Exemplo Prático**
```
Serviço: Cardiologia
Meta PDT: 180 consultas/mês
Realizado: 165 consultas
Produtividade: (165 ÷ 180) × 100 = 91.67%
Status: Próximo da meta (Amarelo)
```

## 🏗️ Estrutura de Dados

### 📋 **Origem dos Dados**
```php
// A Meta PDT vem diretamente da tabela 'meta'
$meta_pdt = (int)$servico['meta_pdt']; // Valor fixo mensal

// Diferente do pactuado que pode ser negociado
$total_pactuado = (int)$servico['pactuado']; // Valor acordado
```

### 🗄️ **Estrutura de Banco**
```sql
CREATE TABLE meta (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unidade_id INT NOT NULL,
    servico_id INT NOT NULL,
    mes INT NOT NULL,
    ano INT NOT NULL,
    meta_pdt INT NOT NULL,           -- Meta oficial PDT
    pactuado INT DEFAULT NULL,       -- Meta pactuada (opcional)
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_meta (unidade_id, servico_id, mes, ano)
);
```

## 🎨 Visualização no Sistema

### 📊 **Dashboard Principal**
```php
<!-- Exibição da Meta PDT -->
<div class="legend-item" data-type="meta">
    <span class="legend-color meta-color"></span>
    <span class="legend-text">Meta PDT</span>
    <span class="legend-value"><?php echo formatarNumero($meta_pdt); ?></span>
</div>

<!-- Gauge com Meta PDT -->
<div class="gauge-container">
    <div id="gauge<?php echo $indiceGrafico; ?>"></div>
    <div class="gauge-info">
        <div class="gauge-value"><?php echo formatarNumero($total_executados); ?></div>
        <div class="gauge-percent"><?php echo formatarNumero($progresso, 2); ?>%</div>
    </div>
</div>
```

### 🎯 **Tooltips Informativos**
```javascript
if (type === 'meta') {
    tooltipText = 'Meta de produtividade definida pelo Plano Diretor Territorializado (PDT)';
}
```

## 📈 Análise Comparativa

### 🔄 **Meta PDT vs Pactuado**

| Aspecto | Meta PDT | Pactuado |
|---------|----------|----------|
| **Origem** | Planejamento territorial | Acordo de gestão |
| **Base** | Necessidade populacional | Capacidade operacional |
| **Flexibilidade** | Fixa (oficial) | Negociável |
| **Prioridade** | Principal | Secundária |
| **Periodicidade** | Anual/territorial | Pode variar |

### 📊 **Cenários de Comparação**

#### **Cenário 1: PDT > Pactuado**
```
Meta PDT: 200 procedimentos
Pactuado: 180 procedimentos
Interpretação: Demanda territorial superior à capacidade pactuada
```

#### **Cenário 2: PDT < Pactuado**
```
Meta PDT: 150 procedimentos  
Pactuado: 180 procedimentos
Interpretação: Capacidade pactuada superior à necessidade territorial
```

#### **Cenário 3: PDT = Pactuado**
```
Meta PDT: 180 procedimentos
Pactuado: 180 procedimentos
Interpretação: Alinhamento entre necessidade e capacidade
```

## 🎯 Cálculos Avançados

### 📊 **Produtividade Ponderada por PDT**
```php
function calcularProdutividadePonderadaPDT($servicos) {
    $numerador = 0;
    $denominador = 0;
    
    foreach ($servicos as $servico) {
        $peso = $servico['meta_pdt']; // PDT como peso
        $numerador += $servico['total_executados'] * $peso;
        $denominador += $servico['meta_pdt'] * $peso;
    }
    
    return $denominador > 0 ? ($numerador / $denominador) * 100 : 0;
}
```

### 📈 **Déficit/Superávit PDT**
```php
function calcularDeficitSuperavitPDT($realizado, $meta_pdt) {
    $diferenca = $realizado - $meta_pdt;
    $percentual = $meta_pdt > 0 ? ($diferenca / $meta_pdt) * 100 : 0;
    
    return [
        'valor_absoluto' => $diferenca,
        'percentual' => $percentual,
        'tipo' => $diferenca >= 0 ? 'superavit' : 'deficit',
        'status' => $diferenca >= 0 ? 'Meta atingida' : 'Meta não atingida'
    ];
}
```

### 🎯 **Ranking por Performance PDT**
```php
function criarRankingPDT($servicos) {
    $ranking = [];
    
    foreach ($servicos as $servico) {
        $produtividade = calcularProdutividadePDT(
            $servico['total_executados'], 
            $servico['meta_pdt']
        );
        
        $ranking[] = [
            'servico' => $servico['natureza'],
            'realizado' => $servico['total_executados'],
            'meta_pdt' => $servico['meta_pdt'],
            'produtividade' => $produtividade,
            'classificacao' => classificarProdutividade($produtividade)
        ];
    }
    
    // Ordenar por produtividade decrescente
    usort($ranking, function($a, $b) {
        return $b['produtividade'] <=> $a['produtividade'];
    });
    
    return $ranking;
}
```

## 📊 Relatórios Especializados

### 📈 **Relatório Mensal PDT**
```php
function gerarRelatorioMensalPDT($unidade_id, $mes, $ano) {
    return [
        'periodo' => "$mes/$ano",
        'unidade' => $unidade_id,
        'resumo_geral' => [
            'total_servicos' => 0,
            'meta_pdt_total' => 0,
            'realizado_total' => 0,
            'produtividade_geral' => 0,
            'servicos_meta_atingida' => 0,
            'percentual_sucesso' => 0
        ],
        'detalhamento_servicos' => [],
        'analise_performance' => [
            'melhores_performances' => [],
            'piores_performances' => [],
            'alertas' => []
        ],
        'tendencias' => [
            'comparativo_mes_anterior' => 0,
            'comparativo_mesmo_mes_ano_anterior' => 0,
            'projecao_mensal' => 0
        ]
    ];
}
```

### 📋 **Dashboard Gerencial PDT**
```php
function criarDashboardPDT($dados_unidade) {
    $dashboard = [
        'indicadores_principais' => [
            'produtividade_geral_pdt' => 0,
            'total_metas_atingidas' => 0,
            'total_servicos' => 0,
            'percentual_sucesso' => 0
        ],
        'graficos' => [
            'evolucao_mensal' => [],
            'comparativo_grupos' => [],
            'distribuicao_performance' => []
        ],
        'alertas_pdt' => [],
        'recomendacoes' => []
    ];
    
    // Processar dados e calcular indicadores
    return $dashboard;
}
```

## ⚠️ Monitoramento e Alertas

### 🚨 **Sistema de Alertas PDT**
```php
function verificarAlertasPDT($servico) {
    $alertas = [];
    $produtividade = calcularProdutividadePDT(
        $servico['total_executados'], 
        $servico['meta_pdt']
    );
    
    if ($produtividade < 60) {
        $alertas[] = [
            'tipo' => 'critico',
            'nivel' => 'PDT_CRITICO',
            'mensagem' => "Produtividade PDT crítica: {$produtividade}%",
            'servico' => $servico['natureza'],
            'acao_recomendada' => 'Intervenção imediata necessária'
        ];
    } elseif ($produtividade < 80) {
        $alertas[] = [
            'tipo' => 'atencao',
            'nivel' => 'PDT_ATENCAO',
            'mensagem' => "Produtividade PDT abaixo do esperado: {$produtividade}%",
            'servico' => $servico['natureza'],
            'acao_recomendada' => 'Monitorar e investigar causas'
        ];
    }
    
    return $alertas;
}
```

### 📊 **Métricas de Acompanhamento**
```php
function calcularMetricasAcompanhamentoPDT($periodo_dados) {
    return [
        'taxa_cumprimento_geral' => 0,        // % de serviços que atingiram PDT
        'media_produtividade_pdt' => 0,       // Média ponderada
        'desvio_padrao_pdt' => 0,             // Variabilidade
        'tendencia_mensal' => 0,              // Crescimento/decrescimento
        'servicos_criticos' => 0,             // Abaixo de 60%
        'servicos_atencao' => 0,              // Entre 60-80%
        'servicos_adequados' => 0,            // Acima de 80%
        'indice_qualidade_pdt' => 0           // Score geral 0-100
    ];
}
```

## 🔄 Atualização e Manutenção

### 📅 **Cronograma de Atualização**
```php
class GerenciadorMetaPDT {
    public function atualizarMetasMensais($unidade_id, $mes, $ano) {
        // 1. Validar período
        // 2. Buscar diretrizes territoriais
        // 3. Calcular metas por serviço
        // 4. Atualizar banco de dados
        // 5. Notificar gestores
    }
    
    public function validarConsistencia($unidade_id, $periodo) {
        // Verificar se todas as metas foram definidas
        // Validar valores coerentes
        // Comparar com histórico
        // Gerar relatório de inconsistências
    }
    
    public function importarMetasTerritoriais($arquivo_pdt) {
        // Processar arquivo oficial
        // Validar dados
        // Atualizar sistema
        // Gerar log de importação
    }
}
```

### 📋 **Auditoria de Metas**
```php
function auditarMetasPDT($unidade_id, $periodo) {
    return [
        'periodo_auditado' => $periodo,
        'total_servicos_verificados' => 0,
        'metas_definidas' => 0,
        'metas_pendentes' => 0,
        'inconsistencias_encontradas' => [],
        'recomendacoes' => [],
        'data_auditoria' => date('Y-m-d H:i:s')
    ];
}
```

## 📚 Boas Práticas

### ✅ **Para Gestores**
1. Revisar metas PDT mensalmente
2. Comparar com capacidade instalada
3. Analisar aderência territorial
4. Documentar ajustes necessários

### ✅ **Para Analistas**
1. Validar consistência dos dados
2. Monitorar tendências históricas
3. Comparar com benchmarks
4. Alertar sobre discrepâncias

### ✅ **Para Desenvolvedores**
1. Priorizar PDT nos cálculos
2. Implementar validações robustas
3. Manter histórico de alterações
4. Otimizar consultas de meta

---

*Última atualização: Junho 2025*
