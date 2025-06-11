# 📈 Gráficos de Barras Temporais - Sistema RTP

## 📋 Visão Geral

O sistema de gráficos de barras do RTP utiliza **ApexCharts** para criar visualizações temporais que mostram a evolução diária da produtividade hospitalar, comparando valores agendados vs executados ao longo do tempo.

## 🏗️ Estrutura dos Gráficos

### 📂 **Arquivos Relacionados**

- **JavaScript**: `public/assets/js/relatorio.js` - Lógica de criação dos gráficos
- **Estilos**: `public/assets/css/relatorio.css` - CSS responsivo dos gráficos
- **Template**: `src/views/relatorio/dashboard.php` - Estrutura HTML e integração
- **Dados**: `window.dadosGraficos` - Array com dados diários processados

## 🎯 Localização no Código

### 📍 **Container HTML (dashboard.php, linha 69)**

```php
<div class="chart-container">
    <div id="grafico<?php echo $indiceGrafico; ?>"></div>
</div>
```

### 📊 **Dados JavaScript (linhas 232-236)**

```javascript
<?php if (!empty($unidade) && !empty($relatorio_por_grupos)) { ?>
<script>
// Dados para os gráficos (layout original mantido)
window.dadosGraficos = <?php echo json_encode($dados_graficos ?? []); ?>;
</script>
<?php } ?>
```

## 🔧 Funcionamento Técnico

### 📊 **Estrutura de Dados**

```php
// Preparação dos dados no Controller/Model
$dados_graficos = [
    [
        'servico_id' => 1,
        'servico_nome' => 'Cardiologia',
        'grupo_cor' => '#fd7e14',
        'dadosDiarios' => [
            [
                'data' => '2025-01-01',
                'agendado' => 15,
                'executado' => 12,
                'dia_semana' => 'Seg',
                'mes' => 'Jan'
            ],
            [
                'data' => '2025-01-02', 
                'agendado' => 18,
                'executado' => 16,
                'dia_semana' => 'Ter',
                'mes' => 'Jan'
            ],
            // ... outros dias
        ]
    ],
    // ... outros serviços
];
```

### ⚙️ **Configuração ApexCharts**

```javascript
function criarGraficoBarra(dados, elementId) {
    // Preparar dados para o gráfico
    const categorias = dados.dadosDiarios.map(dia => {
        const data = new Date(dia.data);
        return data.getDate() + '/' + (data.getMonth() + 1);
    });
    
    const seriesAgendado = dados.dadosDiarios.map(dia => parseInt(dia.agendado || 0));
    const seriesExecutado = dados.dadosDiarios.map(dia => parseInt(dia.executado || 0));
    
    const opcoes = {
        chart: {
            type: 'bar',
            height: 350,
            stacked: false,
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: false,
                    reset: true
                }
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 800
            }
        },
        series: [
            {
                name: 'Agendado',
                data: seriesAgendado,
                color: '#17a2b8'  // Ciano para agendado
            },
            {
                name: 'Executado', 
                data: seriesExecutado,
                color: dados.grupo_cor  // Cor dinâmica do grupo
            }
        ],
        xaxis: {
            categories: categorias,
            title: {
                text: 'Dias do Mês',
                style: { fontSize: '14px', fontWeight: 'bold' }
            },
            labels: {
                rotate: -45,
                style: { fontSize: '12px' }
            }
        },
        yaxis: {
            title: {
                text: 'Quantidade de Procedimentos',
                style: { fontSize: '14px', fontWeight: 'bold' }
            },
            labels: {
                formatter: function(val) {
                    return Math.floor(val);
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '65%',
                borderRadius: 4,
                dataLabels: {
                    position: 'top'
                }
            }
        },
        dataLabels: {
            enabled: false  // Desabilitado para não poluir
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            fontSize: '14px',
            markers: {
                width: 12,
                height: 12,
                radius: 2
            }
        },
        grid: {
            borderColor: '#e0e0e0',
            strokeDashArray: 3
        },
        tooltip: {
            shared: true,
            intersect: false,
            y: {
                formatter: function(val, { seriesIndex, dataPointIndex, w }) {
                    const data = dados.dadosDiarios[dataPointIndex];
                    const eficiencia = data.agendado > 0 ? 
                        ((data.executado / data.agendado) * 100).toFixed(1) + '%' : 
                        '0%';
                    
                    return val + ' procedimentos' + 
                           (seriesIndex === 1 ? ` (Eficiência: ${eficiencia})` : '');
                }
            }
        },
        responsive: [
            {
                breakpoint: 768,
                options: {
                    chart: { height: 300 },
                    legend: { position: 'bottom' },
                    xaxis: {
                        labels: { rotate: 0, style: { fontSize: '10px' } }
                    }
                }
            },
            {
                breakpoint: 480,
                options: {
                    chart: { height: 250 },
                    plotOptions: {
                        bar: { columnWidth: '80%' }
                    }
                }
            }
        ]
    };
    
    const grafico = new ApexCharts(document.getElementById(elementId), opcoes);
    grafico.render();
    
    return grafico;
}
```

## 📊 Tipos de Visualização

### 📈 **Gráfico de Barras Agrupadas**

```javascript
// Configuração para barras lado a lado
plotOptions: {
    bar: {
        horizontal: false,
        columnWidth: '65%',
        borderRadius: 4,
        dataLabels: { position: 'top' }
    }
}
```

### 📊 **Gráfico de Barras Empilhadas**

```javascript
// Configuração para barras empilhadas
chart: {
    type: 'bar',
    stacked: true  // Ativa modo empilhado
},
plotOptions: {
    bar: {
        horizontal: false,
        columnWidth: '50%'
    }
}
```

### 📉 **Gráfico de Linha Temporal**

```javascript
// Alternativa com linhas para tendências
chart: {
    type: 'line',
    height: 300
},
stroke: {
    curve: 'smooth',
    width: 3
},
markers: {
    size: 5,
    hover: { size: 7 }
}
```

## 🎨 Sistema de Cores

### 🌈 **Cores Padrão por Tipo**

```javascript
const CORES_GRAFICOS = {
    agendado: '#17a2b8',      // Ciano - Representa planejamento
    executado: 'dinâmica',    // Cor do grupo - Representa resultado
    meta: '#28a745',          // Verde - Linha de meta
    deficit: '#dc3545',       // Vermelho - Valores negativos
    superavit: '#28a745'      // Verde - Valores positivos
};
```

### 🎯 **Aplicação de Cores Dinâmicas**

```javascript
function obterCoresGrafico(dados) {
    return {
        agendado: '#17a2b8',
        executado: dados.grupo_cor,
        background: dados.grupo_cor + '20',  // 20% transparência
        border: dados.grupo_cor
    };
}
```

### 🚦 **Cores por Performance**

```javascript
function obterCorPorEficiencia(eficiencia) {
    if (eficiencia >= 90) return '#28a745';  // Verde - Excelente
    if (eficiencia >= 75) return '#ffc107';  // Amarelo - Bom  
    if (eficiencia >= 60) return '#fd7e14';  // Laranja - Regular
    return '#dc3545';  // Vermelho - Crítico
}
```

## 📱 Responsividade

### 📐 **Breakpoints Específicos**

```css
/* Desktop - Visualização completa */
.chart-container {
    width: 100%;
    height: 350px;
    margin: 20px 0;
}

/* Tablet - Altura reduzida */
@media (max-width: 768px) {
    .chart-container {
        height: 300px;
        margin: 15px 0;
    }
    
    /* Legenda embaixo */
    .apexcharts-legend {
        flex-direction: column;
        align-items: center;
    }
}

/* Mobile - Otimizado */
@media (max-width: 480px) {
    .chart-container {
        height: 250px;
        margin: 10px 0;
    }
    
    /* Barras mais largas para facilitar toque */
    .apexcharts-bar-series {
        stroke-width: 1;
    }
    
    /* Tooltip otimizado */
    .apexcharts-tooltip {
        font-size: 12px;
    }
}
```

### 📊 **Configurações Responsivas JavaScript**

```javascript
responsive: [
    {
        breakpoint: 768,
        options: {
            chart: { height: 300 },
            legend: { 
                position: 'bottom',
                fontSize: '12px'
            },
            xaxis: {
                labels: { 
                    rotate: 0, 
                    style: { fontSize: '10px' } 
                }
            },
            dataLabels: { enabled: false }
        }
    },
    {
        breakpoint: 480,
        options: {
            chart: { height: 250 },
            plotOptions: {
                bar: { columnWidth: '80%' }
            },
            toolbar: { show: false }
        }
    }
]
```

## 🔄 Interatividade

### 🖱️ **Eventos de Clique**

```javascript
chart: {
    events: {
        dataPointSelection: function(event, chartContext, config) {
            const dadoSelecionado = dados.dadosDiarios[config.dataPointIndex];
            mostrarDetalheDia(dadoSelecionado);
        },
        markerClick: function(event, chartContext, config) {
            console.log('Marcador clicado:', config);
        }
    }
}
```

### 💡 **Tooltips Avançados**

```javascript
tooltip: {
    shared: true,
    intersect: false,
    custom: function({ series, seriesIndex, dataPointIndex, w }) {
        const data = dados.dadosDiarios[dataPointIndex];
        const agendado = data.agendado;
        const executado = data.executado;
        const eficiencia = agendado > 0 ? 
            ((executado / agendado) * 100).toFixed(1) : 0;
        const dataFormatada = new Date(data.data).toLocaleDateString('pt-BR');
        
        return `
            <div class="custom-tooltip">
                <div class="tooltip-header">${dataFormatada}</div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Agendado:</span>
                    <span class="tooltip-value">${agendado}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Executado:</span>
                    <span class="tooltip-value">${executado}</span>
                </div>
                <div class="tooltip-row">
                    <span class="tooltip-label">Eficiência:</span>
                    <span class="tooltip-value">${eficiencia}%</span>
                </div>
                ${executado > agendado ? 
                    '<div class="tooltip-extra">+ Atendimentos extras</div>' : 
                    ''}
            </div>
        `;
    }
}
```

## 📈 Análises Avançadas

### 📊 **Tendências e Padrões**

```javascript
function analisarTendencias(dadosDiarios) {
    const analise = {
        tendencia_agendado: 0,
        tendencia_executado: 0,
        media_eficiencia: 0,
        dias_pico: [],
        dias_baixa: []
    };
    
    // Calcular tendência linear
    const n = dadosDiarios.length;
    let soma_x = 0, soma_y_agendado = 0, soma_y_executado = 0;
    let soma_xy_agendado = 0, soma_xy_executado = 0, soma_x2 = 0;
    
    dadosDiarios.forEach((dia, index) => {
        const x = index + 1;
        const y_agendado = parseInt(dia.agendado);
        const y_executado = parseInt(dia.executado);
        
        soma_x += x;
        soma_y_agendado += y_agendado;
        soma_y_executado += y_executado;
        soma_xy_agendado += x * y_agendado;
        soma_xy_executado += x * y_executado;
        soma_x2 += x * x;
    });
    
    // Coeficiente angular (tendência)
    analise.tendencia_agendado = (n * soma_xy_agendado - soma_x * soma_y_agendado) / 
                                (n * soma_x2 - soma_x * soma_x);
    analise.tendencia_executado = (n * soma_xy_executado - soma_x * soma_y_executado) / 
                                 (n * soma_x2 - soma_x * soma_x);
    
    // Média de eficiência
    const eficiencias = dadosDiarios.map(dia => 
        dia.agendado > 0 ? (dia.executado / dia.agendado) * 100 : 0
    );
    analise.media_eficiencia = eficiencias.reduce((a, b) => a + b, 0) / eficiencias.length;
    
    return analise;
}
```

### 🎯 **Identificação de Padrões**

```javascript
function identificarPadroes(dadosDiarios) {
    const padroes = {
        melhor_dia_semana: '',
        pior_dia_semana: '',
        sazonalidade: [],
        outliers: []
    };
    
    // Agrupar por dia da semana
    const porDiaSemana = {};
    dadosDiarios.forEach(dia => {
        const diaSemana = new Date(dia.data).getDay();
        const nomeDia = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'][diaSemana];
        
        if (!porDiaSemana[nomeDia]) {
            porDiaSemana[nomeDia] = { total_executado: 0, count: 0 };
        }
        
        porDiaSemana[nomeDia].total_executado += parseInt(dia.executado);
        porDiaSemana[nomeDia].count++;
    });
    
    // Calcular médias por dia da semana
    const medias = Object.keys(porDiaSemana).map(dia => ({
        dia,
        media: porDiaSemana[dia].total_executado / porDiaSemana[dia].count
    }));
    
    medias.sort((a, b) => b.media - a.media);
    
    padroes.melhor_dia_semana = medias[0]?.dia || '';
    padroes.pior_dia_semana = medias[medias.length - 1]?.dia || '';
    
    return padroes;
}
```

## ⚡ Otimizações de Performance

### 🚀 **Lazy Loading de Gráficos**

```javascript
const GraphicsLoader = {
    observer: null,
    
    init() {
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const chartId = entry.target.id;
                    const index = chartId.replace('grafico', '');
                    
                    if (window.dadosGraficos[index]) {
                        this.criarGrafico(window.dadosGraficos[index], chartId);
                        this.observer.unobserve(entry.target);
                    }
                }
            });
        }, { threshold: 0.1 });
        
        // Observar todos os containers de gráfico
        document.querySelectorAll('[id^="grafico"]').forEach(chart => {
            this.observer.observe(chart);
        });
    },
    
    criarGrafico(dados, elementId) {
        performance.mark('chart-start-' + elementId);
        
        const grafico = criarGraficoBarra(dados, elementId);
        
        performance.mark('chart-end-' + elementId);
        performance.measure(
            'chart-creation-' + elementId,
            'chart-start-' + elementId,
            'chart-end-' + elementId
        );
    }
};

// Inicializar após DOM ready
document.addEventListener('DOMContentLoaded', () => {
    GraphicsLoader.init();
});
```

### 📊 **Cache de Configurações**

```javascript
const ChartConfigCache = {
    configs: new Map(),
    
    obterConfig(tipo, dados) {
        const key = `${tipo}-${dados.grupo_cor}`;
        
        if (!this.configs.has(key)) {
            const config = this.gerarConfig(tipo, dados);
            this.configs.set(key, config);
        }
        
        return { ...this.configs.get(key) }; // Clone para evitar mutação
    },
    
    gerarConfig(tipo, dados) {
        // Gerar configuração baseada no tipo e dados
        return criarConfiguracao(tipo, dados);
    }
};
```

## 🔍 Debugging e Monitoramento

### 🐛 **Console de Debug**

```javascript
const ChartDebugger = {
    enabled: false,
    
    log(message, data = null) {
        if (this.enabled) {
            console.log(`[Chart Debug] ${message}`, data);
        }
    },
    
    error(message, error = null) {
        console.error(`[Chart Error] ${message}`, error);
    },
    
    performance(chartId, duration) {
        if (duration > 1000) { // Mais de 1 segundo
            console.warn(`[Chart Performance] ${chartId} demorou ${duration}ms para renderizar`);
        }
    }
};
```

### 📊 **Validação de Dados**

```javascript
function validarDadosGrafico(dados) {
    const erros = [];
    
    if (!dados.dadosDiarios || !Array.isArray(dados.dadosDiarios)) {
        erros.push('dadosDiarios deve ser um array');
    }
    
    if (dados.dadosDiarios.length === 0) {
        erros.push('dadosDiarios não pode estar vazio');
    }
    
    dados.dadosDiarios.forEach((dia, index) => {
        if (!dia.data) {
            erros.push(`dia[${index}] não possui data`);
        }
        
        if (isNaN(parseInt(dia.agendado))) {
            erros.push(`dia[${index}] agendado inválido`);
        }
        
        if (isNaN(parseInt(dia.executado))) {
            erros.push(`dia[${index}] executado inválido`);
        }
    });
    
    return {
        valido: erros.length === 0,
        erros: erros
    };
}
```

## 📋 Checklist de Implementação

### ✅ **Pré-requisitos**
- [ ] ApexCharts carregado
- [ ] Dados validados e formatados
- [ ] Container DOM existe
- [ ] Cores definidas por grupo

### ✅ **Configuração**
- [ ] Tipo de gráfico apropriado
- [ ] Responsividade configurada
- [ ] Tooltips personalizados
- [ ] Interatividade habilitada

### ✅ **Performance**
- [ ] Lazy loading implementado
- [ ] Cache de configurações ativo
- [ ] Validação de dados presente
- [ ] Monitoramento de performance

### ✅ **Teste e Deploy**
- [ ] Testado em diferentes resoluções
- [ ] Acessibilidade verificada
- [ ] Performance aceitável
- [ ] Documentação atualizada

---

*Última atualização: Junho 2025*