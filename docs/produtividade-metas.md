# 🎯 Produtividade e Metas - Sistema RTP

## 📋 Visão Geral

Este documento detalha os conceitos de produtividade, metas e indicadores utilizados no sistema RTP para monitoramento da performance hospitalar.

## 🏥 Conceitos Fundamentais

### 🎯 **Meta PDT (Plano Diretor Territorializado)**
- **Definição**: Meta mensal estabelecida pelo Plano Diretor Territorializado
- **Características**: 
  - Valor fixo por serviço/mês
  - Baseada em necessidades territoriais
  - Considera população e demanda regional
  - Prioridade máxima para cálculos de produtividade

### 📋 **Pactuado**
- **Definição**: Valor acordado entre gestão e unidade
- **Características**:
  - Valor fixo mensal
  - Pode diferir da Meta PDT
  - Baseado em capacidade operacional
  - Usado para cálculos secundários

### 📅 **Agendado**
- **Definição**: Total de consultas/procedimentos agendados
- **Características**:
  - Soma diária acumulada
  - Varia conforme demanda
  - Reflete planejamento operacional
  - Indicador de capacidade utilizada

### ✅ **Realizado (Executado)**
- **Definição**: Total de procedimentos efetivamente executados
- **Características**:
  - Resultado real da produção
  - Base para todos os cálculos de performance
  - Comparado com metas para análise

## 📊 Indicadores de Performance

### 🎯 **Produtividade Principal**
```
Produtividade = (Realizado / Meta PDT) × 100
```
- **Meta**: ≥ 100%
- **Cores**:
  - 🟢 Verde: ≥ 100%
  - 🟡 Amarelo: 80-99%
  - 🔴 Vermelho: < 80%

### 📈 **Produtividade vs Pactuado**
```
Produtividade Pactuado = (Realizado / Pactuado) × 100
```
- **Uso**: Análise complementar
- **Aplicação**: Avaliação de acordos operacionais

### 📅 **Taxa de Utilização**
```
Utilização = (Agendado / Pactuado) × 100
```
- **Interpretação**:
  - > 100%: Demanda superior à capacidade
  - 100%: Capacidade totalmente utilizada
  - < 100%: Capacidade ociosa

### ⚡ **Eficiência Operacional**
```
Eficiência = (Realizado / Agendado) × 100
```
- **Interpretação**:
  - 100%: Todos os agendamentos foram executados
  - < 100%: Existem faltas ou cancelamentos
  - > 100%: Atendimentos extras (encaixes)

## 🎨 Sistema de Cores por Performance

### 🚦 **Semáforo de Produtividade**

| Performance | Cor | Hex Code | Descrição |
|-------------|-----|----------|-----------|
| ≥ 100% | 🟢 Verde | `#28a745` | Meta atingida |
| 80-99% | 🟡 Amarelo | `#ffc107` | Próximo da meta |
| 60-79% | 🟠 Laranja | `#fd7e14` | Atenção necessária |
| < 60% | 🔴 Vermelho | `#dc3545` | Crítico |

### 🎯 **Aplicação no Dashboard**

```php
function obterCorProdutividade($produtividade) {
    if ($produtividade >= 100) return '#28a745'; // Verde
    if ($produtividade >= 80)  return '#ffc107'; // Amarelo
    if ($produtividade >= 60)  return '#fd7e14'; // Laranja
    return '#dc3545'; // Vermelho
}
```

## 📈 Análise Comparativa

### 🎯 **Produtividade vs Produtividade Máxima**

#### **Cenário 1: Performance Normal**
- Realizado: 150
- Meta PDT: 180
- Pactuado: 200
- **Resultado**: 83.3% (Amarelo - Próximo da meta)

#### **Cenário 2: Sobre-performance**
- Realizado: 220
- Meta PDT: 180
- Pactuado: 200
- **Resultado**: 122.2% (Verde - Acima da meta)

#### **Cenário 3: Sub-performance**
- Realizado: 100
- Meta PDT: 180
- Pactuado: 200
- **Resultado**: 55.6% (Vermelho - Crítico)

## 🔍 Análise de Tendências

### 📊 **Indicadores Mensais**

```php
// Cálculo de tendência mensal
function calcularTendencia($dadosMensais) {
    $meses = array_keys($dadosMensais);
    $produtividades = array_values($dadosMensais);
    
    $tendencia = 0;
    for ($i = 1; $i < count($produtividades); $i++) {
        $tendencia += $produtividades[$i] - $produtividades[$i-1];
    }
    
    return $tendencia / (count($produtividades) - 1);
}
```

### 📈 **Classificação de Tendências**
- **📈 Crescente**: Tendência > +5% ao mês
- **📊 Estável**: Tendência entre -5% e +5%
- **📉 Decrescente**: Tendência < -5% ao mês

## ⚠️ Alertas e Notificações

### 🚨 **Critérios de Alerta**

1. **Crítico** (🔴): Produtividade < 60%
2. **Atenção** (🟡): Produtividade 60-79%
3. **Observação** (🔵): Tendência decrescente por 3 meses

### 📧 **Sistema de Notificações**

```php
function verificarAlertas($servico) {
    $alertas = [];
    
    if ($servico['produtividade'] < 60) {
        $alertas[] = [
            'tipo' => 'critico',
            'mensagem' => 'Produtividade crítica: ' . $servico['produtividade'] . '%'
        ];
    }
    
    if ($servico['tendencia'] < -5) {
        $alertas[] = [
            'tipo' => 'atencao',
            'mensagem' => 'Tendência decrescente detectada'
        ];
    }
    
    return $alertas;
}
```

## 📋 Boas Práticas

### ✅ **Para Gestores**
1. Monitorar diariamente serviços em vermelho
2. Analisar tendências mensais
3. Comparar com histórico do mesmo período ano anterior
4. Investigar causas de sub-performance

### ✅ **Para Analistas**
1. Validar dados de entrada
2. Verificar consistência entre agendado/realizado
3. Analisar sazonalidades
4. Documentar anomalias

### ✅ **Para Desenvolvedores**
1. Separar lógica de cálculo da apresentação
2. Validar dados antes dos cálculos
3. Implementar logs para auditoria
4. Otimizar consultas de dados históricos

---

*Última atualização: Junho 2025*
