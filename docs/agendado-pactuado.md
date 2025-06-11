# 📅 Agendamento vs Pactuação - Sistema RTP

## 📋 Visão Geral

Este documento explica as diferenças entre valores **Agendados** e **Pactuados** no sistema RTP, suas aplicações e como são utilizados nos cálculos de produtividade.

## 🔍 Conceitos Fundamentais

### 📅 **Agendado**

#### **Definição**
Refere-se ao total de consultas, procedimentos ou atendimentos que foram **agendados** pelos pacientes em um determinado período.

#### **Características**
- **Dinâmico**: Varia diariamente conforme demanda
- **Operacional**: Reflete a capacidade de agendamento utilizada
- **Temporal**: Soma acumulativa dos dados diários
- **Variável**: Pode exceder ou ficar abaixo do pactuado

#### **Cálculo no Sistema**
```php
// Soma dos agendamentos diários
$total_agendado = 0;
foreach ($dados_graficos[$indiceGrafico]['dadosDiarios'] as $dia) {
    $total_agendado += (int)($dia['agendado'] ?? 0);
}
```

#### **Exemplo Prático**
```
Dia 01: 15 consultas agendadas
Dia 02: 12 consultas agendadas  
Dia 03: 18 consultas agendadas
...
Dia 30: 16 consultas agendadas

Total Mensal Agendado: 450 consultas
```

### 📋 **Pactuado**

#### **Definição**
Valor **fixo mensal** acordado entre a gestão hospitalar e a unidade de saúde, representando a capacidade de atendimento contratada.

#### **Características**
- **Estático**: Valor fixo por mês
- **Contratual**: Baseado em acordos de gestão
- **Planejado**: Define capacidade teórica
- **Meta**: Objetivo operacional a ser atingido

#### **Origem dos Dados**
```php
// Valor fixo da tabela meta (não da soma diária)
$total_pactuado = (int)$servico['pactuado']; // Valor fixo da meta mensal
```

#### **Exemplo Prático**
```
Cardiologia - Janeiro 2025
Pactuado: 400 consultas/mês
(Valor fixo independente dos agendamentos diários)
```

## 📊 Comparação Prática

### 📈 **Cenários Comuns**

#### **Cenário 1: Demanda Normal**
- **Pactuado**: 400 consultas/mês
- **Agendado**: 380 consultas/mês
- **Utilização**: 95% (demanda abaixo da capacidade)
- **Interpretação**: Capacidade ociosa de 5%

#### **Cenário 2: Alta Demanda**
- **Pactuado**: 400 consultas/mês
- **Agendado**: 450 consultas/mês  
- **Utilização**: 112.5% (demanda acima da capacidade)
- **Interpretação**: Necessidade de expansão ou otimização

#### **Cenário 3: Baixa Demanda**
- **Pactuado**: 400 consultas/mês
- **Agendado**: 280 consultas/mês
- **Utilização**: 70% (alta ociosidade)
- **Interpretação**: Possível redimensionamento necessário

## 🧮 Cálculos e Indicadores

### 📊 **Taxa de Utilização**
```php
function calcularUtilizacao($agendado, $pactuado) {
    if ($pactuado == 0) return 0;
    return ($agendado / $pactuado) * 100;
}

// Exemplo de uso
$utilizacao = calcularUtilizacao(380, 400); // 95%
```

### 📈 **Análise de Capacidade**
```php
function analisarCapacidade($utilizacao) {
    if ($utilizacao > 110) return 'Sobrecarga - Necessita expansão';
    if ($utilizacao > 90)  return 'Utilização ótima';
    if ($utilizacao > 70)  return 'Utilização normal';
    return 'Subutilização - Revisar dimensionamento';
}
```

### 🎯 **Eficiência vs Utilização**
```php
// Comparação completa
function analisarPerformance($realizado, $agendado, $pactuado) {
    $utilizacao = ($agendado / $pactuado) * 100;      // Demanda vs Capacidade
    $eficiencia = ($realizado / $agendado) * 100;     // Execução vs Planejado
    $produtividade = ($realizado / $pactuado) * 100;  // Resultado vs Meta
    
    return [
        'utilizacao' => $utilizacao,
        'eficiencia' => $eficiencia, 
        'produtividade' => $produtividade
    ];
}
```

## 🎨 Visualização no Dashboard

### 📊 **Exibição dos Dados**
```php
<!-- Seção de resumo -->
<div class="summary-details">
    <div class="summary-item">Pactuado | Agendado</div>
    <div class="summary-values">
        <span class="executed"><?php echo formatarNumero($total_pactuado); ?></span> | 
        <span class="target"><?php echo formatarNumero($total_agendado); ?></span>
    </div>
</div>
```

### 🎯 **Cores por Performance**
```css
.summary-values .executed {
    color: #0d6efd; /* Azul - Pactuado */
    font-weight: bold;
}

.summary-values .target {
    color: #6c757d; /* Cinza - Agendado */
    font-weight: normal;
}
```

## 📈 Análise Temporal

### 📅 **Variação Mensal do Agendado**
```php
function calcularVariacaoAgendado($dadosMensais) {
    $variacao = [];
    $meses = array_keys($dadosMensais);
    
    for ($i = 1; $i < count($meses); $i++) {
        $atual = $dadosMensais[$meses[$i]]['agendado'];
        $anterior = $dadosMensais[$meses[$i-1]]['agendado'];
        
        $variacao[$meses[$i]] = (($atual - $anterior) / $anterior) * 100;
    }
    
    return $variacao;
}
```

### 📊 **Tendências Sazonais**
- **Janeiro-Março**: Baixa (pós-férias)
- **Abril-Junho**: Normal
- **Julho**: Baixa (férias escolares)
- **Agosto-Novembro**: Alta
- **Dezembro**: Baixa (férias)

## ⚠️ Alertas e Monitoramento

### 🚨 **Critérios de Alerta**

#### **Sobrecarga (🔴)**
```php
if ($utilizacao > 120) {
    $alerta = [
        'tipo' => 'critico',
        'mensagem' => 'Sobrecarga crítica: ' . round($utilizacao) . '%',
        'acao' => 'Expandir capacidade ou otimizar fluxo'
    ];
}
```

#### **Subutilização (🟡)**
```php
if ($utilizacao < 60) {
    $alerta = [
        'tipo' => 'atencao',
        'mensagem' => 'Subutilização: ' . round($utilizacao) . '%',
        'acao' => 'Revisar dimensionamento ou estratégia'
    ];
}
```

### 📧 **Notificações Automáticas**
```php
function verificarAlertas($servico) {
    $alertas = [];
    $utilizacao = calcularUtilizacao($servico['agendado'], $servico['pactuado']);
    
    if ($utilizacao > 120) {
        $alertas[] = criarAlerta('sobrecarga', $servico, $utilizacao);
    } elseif ($utilizacao < 60) {
        $alertas[] = criarAlerta('subutilizacao', $servico, $utilizacao);
    }
    
    return $alertas;
}
```

## 📊 Relatórios Gerenciais

### 📈 **Dashboard Executivo**
- **Utilização Média**: Média ponderada por serviço
- **Tendência Trimestral**: Análise de 3 meses
- **Comparativo Anual**: Mesmo período ano anterior
- **Ranking de Utilização**: Serviços mais/menos utilizados

### 📋 **Relatório Operacional**
- **Agendamentos por Dia**: Distribuição temporal
- **Picos de Demanda**: Identificação de padrões
- **Capacidade Ociosa**: Oportunidades de otimização
- **Eficiência de Agendamento**: Taxa de comparecimento

## 🛠️ Implementação Técnica

### 📊 **Estrutura de Dados**
```php
$dadosServico = [
    'natureza' => 'Cardiologia',
    'pactuado' => 400,           // Valor fixo mensal
    'total_agendado' => 380,     // Soma dos agendamentos diários
    'total_executados' => 360,   // Procedimentos realizados
    'dadosDiarios' => [
        ['data' => '2025-01-01', 'agendado' => 15, 'executado' => 14],
        ['data' => '2025-01-02', 'agendado' => 12, 'executado' => 11],
        // ...
    ]
];
```

### ⚡ **Otimização de Performance**
```php
// Cache para cálculos repetitivos
class CalculadoraCache {
    private $cache = [];
    
    public function calcularUtilizacao($agendado, $pactuado) {
        $key = "util_{$agendado}_{$pactuado}";
        
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = ($agendado / $pactuado) * 100;
        }
        
        return $this->cache[$key];
    }
}
```

## 📝 Boas Práticas

### ✅ **Para Gestores**
1. Monitorar utilizações extremas (>120% ou <60%)
2. Analisar tendências sazonais
3. Comparar com benchmarks históricos
4. Ajustar pactuação conforme demanda real

### ✅ **Para Analistas**
1. Validar consistência agendado vs executado
2. Identificar padrões temporais
3. Calcular médias móveis para tendências
4. Documentar anomalias e causas

### ✅ **Para Desenvolvedores**
1. Separar cálculos de agendado/pactuado
2. Implementar cache para performance
3. Validar dados de entrada
4. Documentar fórmulas utilizadas

---

*Última atualização: Junho 2025*
