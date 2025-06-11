# ⭕ Sistema de Gauge (Medidores Circulares) - RTP

## 📋 Visão Geral

O sistema de gauge do RTP utiliza **ApexCharts** para criar medidores circulares (radial bars) que visualizam a produtividade dos serviços hospitalares de forma intuitiva e responsiva.

## 🏗️ Estrutura do Gauge

### 📂 **Arquivos Relacionados**

- **JavaScript**: `public/assets/js/relatorio.js` - Lógica de criação dos gauges
- **Estilos**: `public/assets/css/relatorio.css` - CSS responsivo dos gauges
- **Template**: `src/views/relatorio/dashboard.php` - Estrutura HTML e integração
- **Configuração**: `public/assets/js/gauge.html` - Configurações específicas

## 🎯 Localização no Código

### 📍 **Estrutura HTML (dashboard.php)**

O gauge é montado no HTML nas **linhas 88-93**:

```php
<div class="gauge-container">
    <div id="gauge<?php echo $indiceGrafico; ?>"></div>
    <div class="gauge-info">
        <div class="gauge-value" style="color: <?php echo htmlspecialchars($service_color); ?>;">
            <?php echo formatarNumero($total_executados); ?>
        </div>
        <div class="gauge-percent"><?php echo formatarNumero($progresso, 2); ?>%</div>
    </div>
</div>
```

### 🏷️ **Legenda Interativa (linhas 74-85)**

```php
<div class="gauge-legend">
    <div class="legend-item" data-type="realizado">
        <span class="legend-color realizado-color"></span>
        <span class="legend-text">Realizado</span>
        <span class="legend-value"><?php echo formatarNumero($total_executados); ?></span>
    </div>
    <div class="legend-separator">|</div>
    <div class="legend-item" data-type="meta">
        <span class="legend-color meta-color"></span>
        <span class="legend-text">Meta PDT</span>
        <span class="legend-value"><?php echo formatarNumero($meta_pdt); ?></span>
    </div>
</div>
```

## 🔧 Funcionamento Técnico

### 📊 **Dados de Entrada**

```php
// Dados calculados para cada gauge (dashboard.php)
$total_executados = (int)$servico['total_executados'];  // Procedimentos realizados
$meta_pdt = (int)$servico['meta_pdt'];                 // Meta PDT (prioritária)
$total_pactuado = (int)$servico['pactuado'];           // Meta mensal pactuada

// Calcular progresso baseado na relação total_executados/meta_pdt
$progresso = calcularPorcentagemProdutividade($total_executados, $meta_pdt);

// Usar a cor do grupo para o serviço
$service_color = $grupo['grupo_cor'];
```

### ⚙️ **Configuração ApexCharts (relatorio.js)**

```javascript
function criarGaugeApex(dados, elementId) {
    const opcoes = {
        chart: {
            type: 'radialBar',
            height: 200,
            sparkline: { enabled: true }
        },
        series: [dados.progresso],
        colors: [dados.cor_grupo],
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                track: {
                    background: '#e0e0e0',
                    strokeWidth: '10px',
                    margin: 5
                },
                dataLabels: {
                    name: { show: false },
                    value: {
                        offsetY: -2,
                        fontSize: '22px',
                        color: dados.cor_grupo,
                        formatter: function(val) {
                            return val + '%';
                        }
                    }
                }
            }
        },
        grid: { padding: { top: -10 } },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                shadeIntensity: 0.4,
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 1,
                stops: [0, 50, 53, 91]
            }
        },
        labels: ['Produtividade']
    };
    
    const gauge = new ApexCharts(document.getElementById(elementId), opcoes);
    gauge.render();
}
```

## 🎨 Sistema de Cores

### 🌈 **Cores Dinâmicas por Grupo**

As cores são aplicadas dinamicamente baseadas no grupo do serviço:

```php
<!-- Definição da cor do grupo -->
<div class="group-tab" style="--grupo-cor: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>;">

<!-- Aplicação no gauge -->
<div class="gauge-value" style="color: <?php echo htmlspecialchars($service_color); ?>;">
    <?php echo formatarNumero($total_executados); ?>
</div>
```

### 🎯 **Paleta de Cores Padrão**

```css
:root {
    --clinica-medica: #fd7e14;      /* Laranja */
    --cirurgicas: #0d6efd;          /* Azul */
    --diagnostico: #28a745;         /* Verde */
    --urgencia: #dc3545;            /* Vermelho */
    --pediatria: #6f42c1;           /* Roxo */
    --gineco: #e91e63;              /* Rosa */
    --reabilitacao: #17a2b8;        /* Ciano */
}
```

### 🚦 **Cores por Performance**

```javascript
function obterCorPorPerformance(produtividade) {
    if (produtividade >= 100) return '#28a745'; // Verde - Meta atingida
    if (produtividade >= 80)  return '#ffc107'; // Amarelo - Próximo da meta
    if (produtividade >= 60)  return '#fd7e14'; // Laranja - Atenção
    return '#dc3545'; // Vermelho - Crítico
}
```

## 🔄 Interatividade da Legenda

### 🖱️ **Eventos de Mouse (dashboard.php, linhas 147-175)**

```javascript
function initGaugeLegendInteractivity() {
    document.querySelectorAll('.legend-item').forEach(item => {
        const type = item.getAttribute('data-type');
        
        // Efeito de clique com feedback visual
        item.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-1px)';
            }, 150);
            
            // Mostrar tooltip informativo
            showLegendTooltip(this, type);
        });
        
        // Efeito de hover melhorado
        item.addEventListener('mouseenter', function() {
            const value = this.querySelector('.legend-value');
            if (value) {
                value.style.transform = 'scale(1.1)';
                value.style.fontWeight = 'bold';
            }
        });
    });
}
```

### 💡 **Sistema de Tooltips (linhas 178-214)**

```javascript
function showLegendTooltip(element, type) {
    // Remover tooltip existente
    const existingTooltip = document.querySelector('.legend-tooltip');
    if (existingTooltip) {
        existingTooltip.remove();
    }
    
    // Criar novo tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'legend-tooltip';
    
    // Definir texto baseado no tipo
    let tooltipText = '';
    if (type === 'realizado') {
        tooltipText = 'Total de procedimentos executados no período';
    } else if (type === 'meta') {
        tooltipText = 'Meta de produtividade definida pelo PDT';
    }
    
    // Aplicar estilos CSS inline
    tooltip.style.cssText = `
        position: absolute;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        z-index: 1000;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    `;
    
    document.body.appendChild(tooltip);
    
    // Posicionar tooltip
    const rect = element.getBoundingClientRect();
    tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 8) + 'px';
    
    // Remover tooltip após 2 segundos
    setTimeout(() => {
        if (tooltip.parentNode) {
            tooltip.remove();
        }
    }, 2000);
}
```

## 📱 Responsividade

### 📐 **Breakpoints e Adaptações**

```css
/* Desktop - Tamanho padrão */
.gauge-container {
    width: 200px;
    height: 200px;
    margin: 0 auto;
}

.gauge-info {
    text-align: center;
    margin-top: 10px;
}

.gauge-value {
    font-size: 1.5rem;
    font-weight: bold;
}

.gauge-percent {
    font-size: 1rem;
    color: #6c757d;
}

/* Tablet - Reduzido */
@media (max-width: 768px) {
    .gauge-container {
        width: 170px;
        height: 170px;
    }
    
    .gauge-value {
        font-size: 1.3rem;
    }
    
    .gauge-legend {
        flex-direction: column;
        gap: 8px;
    }
}

/* Mobile - Otimizado */
@media (max-width: 480px) {
    .gauge-container {
        width: 150px;
        height: 150px;
    }
    
    .gauge-value {
        font-size: 1.1rem;
    }
    
    .gauge-percent {
        font-size: 0.9rem;
    }
    
    .gauge-legend {
        font-size: 0.8rem;
    }
}
```

## 🛠️ Customização dos Tooltips

### ✏️ **Como Modificar as Mensagens**

Para personalizar os textos dos tooltips, edite a função `showLegendTooltip` no dashboard.php:

```javascript
let tooltipText = '';
if (type === 'realizado') {
    tooltipText = 'Procedimentos efetivamente realizados neste período'; // ← Modifique aqui
} else if (type === 'meta') {
    tooltipText = 'Meta mensal estabelecida no PDT'; // ← Modifique aqui
}
```

### ➕ **Adicionar Novos Tipos de Tooltip**

1. **Adicione atributos data-type no HTML:**
```php
<div class="summary-values">
    <span class="executed" data-type="pactuado"><?php echo formatarNumero($total_pactuado); ?></span> | 
    <span class="target" data-type="agendado"><?php echo formatarNumero($total_agendado); ?></span>
</div>
```

2. **Estenda a função showLegendTooltip:**
```javascript
} else if (type === 'pactuado') {
    tooltipText = 'Valor pactuado mensalmente para este serviço';
} else if (type === 'agendado') {
    tooltipText = 'Total de consultas/procedimentos agendados';
}
```

## ⚡ Otimizações de Performance

### 🚀 **Lazy Loading**

```javascript
// Criar gauges apenas quando visíveis
const observerGauge = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const elementId = entry.target.id;
            if (!window.gaugesCreated[elementId]) {
                criarGaugeApex(dadosGraficos[elementId], elementId);
                window.gaugesCreated[elementId] = true;
            }
        }
    });
});

document.querySelectorAll('[id^="gauge"]').forEach(gauge => {
    observerGauge.observe(gauge);
});
```

### 📊 **Cache de Instâncias**

```javascript
const GaugeCache = {
    instancias: new Map(),
    
    obter(elementId) {
        return this.instancias.get(elementId);
    },
    
    armazenar(elementId, instancia) {
        this.instancias.set(elementId, instancia);
    },
    
    limpar() {
        this.instancias.forEach(gauge => gauge.destroy());
        this.instancias.clear();
    }
};
```

## 🎯 Tipos de Gauge

### 📊 **Gauge Simples (Produtividade PDT)**

```javascript
const opcaoGaugeSimples = {
    chart: { type: 'radialBar', height: 200 },
    series: [produtividade_pdt],
    colors: [cor_grupo],
    plotOptions: {
        radialBar: {
            hollow: { size: '70%' },
            dataLabels: {
                value: {
                    fontSize: '22px',
                    formatter: val => val + '%'
                }
            }
        }
    }
};
```

### ⭕ **Gauge Duplo (PDT + Pactuado)**

```javascript
const opcaoGaugeDuplo = {
    chart: { type: 'radialBar', height: 250 },
    series: [produtividade_pdt, produtividade_pactuado],
    colors: [cor_grupo, '#0d6efd'],
    labels: ['Meta PDT', 'Pactuado'],
    plotOptions: {
        radialBar: {
            dataLabels: {
                name: { fontSize: '14px' },
                value: { fontSize: '18px' },
                total: {
                    show: true,
                    label: 'Média',
                    formatter: function(w) {
                        const avg = w.globals.seriesTotals.reduce((a, b) => a + b, 0) / w.globals.series.length;
                        return avg.toFixed(1) + '%';
                    }
                }
            }
        }
    }
};
```

## 🔍 Debugging e Troubleshooting

### 🐛 **Problemas Comuns**

#### **Gauge não aparece**
```javascript
// Verificar se ApexCharts está carregado
if (typeof ApexCharts === 'undefined') {
    console.error('ApexCharts não carregado');
}

// Verificar se elemento existe
const elemento = document.getElementById(elementId);
if (!elemento) {
    console.error(`Elemento ${elementId} não encontrado`);
}
```

#### **Cores não aplicadas**
```javascript
// Verificar se cor está válida
function validarCor(cor) {
    const s = new Option().style;
    s.color = cor;
    return s.color !== '';
}
```

#### **Performance lenta**
```javascript
// Monitorar tempo de criação
console.time(`Gauge ${elementId}`);
criarGaugeApex(dados, elementId);
console.timeEnd(`Gauge ${elementId}`);
```

## 📊 Métricas e Monitoramento

### 📈 **Tracking de Uso**

```javascript
const GaugeMetrics = {
    totalCriados: 0,
    tempoMedioCriacao: 0,
    erros: [],
    
    registrarCriacao(tempo) {
        this.totalCriados++;
        this.tempoMedioCriacao = (this.tempoMedioCriacao + tempo) / 2;
    },
    
    registrarErro(erro, elementId) {
        this.erros.push({
            erro: erro.message,
            elemento: elementId,
            timestamp: new Date().toISOString()
        });
    }
};
```

## 📋 Checklist de Implementação

### ✅ **Antes de Implementar**
- [ ] ApexCharts carregado
- [ ] Dados validados
- [ ] Elemento DOM existe
- [ ] Cores definidas

### ✅ **Durante Implementação**
- [ ] Configurações apropriadas
- [ ] Responsividade testada
- [ ] Interatividade funcionando
- [ ] Performance verificada

### ✅ **Após Implementação**
- [ ] Testes em diferentes browsers
- [ ] Acessibilidade verificada
- [ ] Documentação atualizada
- [ ] Métricas coletadas

---

*Última atualização: Junho 2025*