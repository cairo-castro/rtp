# 📊 Sistema de Gauge (Medidores Circulares) - RTP Hospital

## 📋 Visão Geral

O sistema de gauge do RTP Hospital utiliza gráficos radiais para visualizar o progresso de produtividade dos serviços hospitalares de forma intuitiva e responsiva. Os gauges são renderizados dinamicamente com cores personalizadas por grupo de serviço.

## 🏗️ Estrutura do Gauge

### 📂 Arquivos Relacionados

- **Frontend**: `public/assets/js/relatorio.js` - Lógica de criação dos gauges
- **Estilos**: `public/assets/css/relatorio.css` - CSS responsivo dos gauges  
- **Template**: `src/views/relatorio/dashboard.php` - Estrutura HTML e JavaScript

## 🎯 Localização no Código

### 📍 **Onde o Gauge é Montado (dashboard.php)**

O gauge é criado no HTML nas **linhas 72-89**:

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

### 🏷️ **Legenda Interativa (linhas 60-71)**

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

## 🔧 Como Funciona

### 📊 **Estrutura de Dados PHP**

```php
// Dados calculados para cada gauge
$total_executados = (int)$servico['total_executados'];  // Procedimentos realizados
$meta_pdt = (int)$servico['meta_pdt'];                 // Meta PDT (prioritária)
$total_pactuado = (int)$servico['pactuado'];           // Meta mensal pactuada
$total_agendado = 0;                                   // Soma dos agendamentos diários

// Calcular progresso baseado na relação total_executados/meta_pdt
$progresso = calcularPorcentagemProdutividade($total_executados, $meta_pdt);

// Usar a cor do grupo para o serviço
$service_color = $grupo['grupo_cor'];
```

### ⚙️ **Inicialização JavaScript**

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar cores de fundo nas abas
    document.querySelectorAll('.group-tab').forEach(tab => {
        const cor = tab.style.getPropertyValue('--grupo-cor');
        if (cor) {
            tab.style.backgroundColor = cor;
        }
    });
    
    // Interatividade da legenda do gauge
    initGaugeLegendInteractivity();
});
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

### 🎯 **Estrutura de Grupos e Serviços**

```php
foreach ($relatorio_por_grupos as $grupoIndex => $grupo) {
    // $grupo['grupo_cor'] - Cor hexadecimal do grupo
    // $grupo['grupo_nome'] - Nome do grupo de serviços
    // $grupo['servicos'] - Array de serviços do grupo
    
    foreach ($grupo['servicos'] as $servicoIndex => $servico) {
        // $servico['natureza'] - Nome do serviço
        // $servico['total_executados'] - Quantidade realizada
        // $servico['meta_pdt'] - Meta PDT
        // $servico['pactuado'] - Meta pactuada
    }
}
```

## 🔄 Interatividade da Legenda

### 🖱️ **Eventos de Mouse (linhas 121-149)**

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

### 💡 **Sistema de Tooltips (linhas 152-196)**

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
        tooltipText = 'Meta de produtividade definida (PDT)';
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

Para adicionar tooltips para outros elementos (pactuado/agendado):

1. **Adicione atributos data-type no HTML:**
```php
<div class="summary-values">
    <span class="executed" data-type="pactuado"><?php echo formatarNumero($total_pactuado); ?></span> | 
    <span class="target" data-type="agendado"><?php echo formatarNumero($total_agendado); ?></span>
</div>
```

2. **Estenda a função showLegendTooltip:**
```javascript
let tooltipText = '';
if (type === 'realizado') {
    tooltipText = 'Total de procedimentos executados no período';
} else if (type === 'meta') {
    tooltipText = 'Meta de produtividade definida (PDT)';
} else if (type === 'pactuado') {
    tooltipText = 'Valor pactuado mensalmente para este serviço';
} else if (type === 'agendado') {
    tooltipText = 'Total de consultas/procedimentos agendados';
}
```

3. **Registre os novos eventos:**
```javascript
// Adicione ao final da função initGaugeLegendInteractivity()
document.querySelectorAll('[data-type="pactuado"], [data-type="agendado"]').forEach(item => {
    item.addEventListener('click', function() {
        showLegendTooltip(this, this.getAttribute('data-type'));
    });
});
```

## 📱 Responsividade

### 📐 **Estrutura Responsiva**

O sistema utiliza CSS Grid e Flexbox para adaptar-se a diferentes tamanhos de tela:

```css
/* Desktop - Layout padrão */
.gauge-summary {
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Mobile - Layout otimizado */
@media (max-width: 768px) {
    .gauge-container {
        width: 150px;
        height: 150px;
    }
    
    .gauge-legend {
        flex-direction: column;
        gap: 8px;
    }
}
```

## ⚡ Performance

### 🚀 **Otimizações Implementadas**

- **Lazy Loading**: Gauges são criados apenas quando visíveis
- **Event Delegation**: Um único listener para múltiplos elementos
- **Debounced Events**: Throttling em eventos de hover
- **Memory Management**: Cleanup automático de tooltips

### 📊 **Dados de Performance**

```javascript
// Dados transferidos via window.dadosGraficos
window.dadosGraficos = <?php echo json_encode($dados_graficos ?? []); ?>;
```

## 🐛 Depuração

### 🔍 **Como Verificar se o Gauge está Funcionando**

1. **Verificar se os dados estão disponíveis:**
```javascript
console.log('Dados dos gráficos:', window.dadosGraficos);
```

2. **Verificar se os elementos estão sendo criados:**
```javascript
console.log('Gauges encontrados:', document.querySelectorAll('[id^="gauge"]').length);
```

3. **Verificar eventos da legenda:**
```javascript
console.log('Itens de legenda:', document.querySelectorAll('.legend-item').length);
```

## 📝 Notas Técnicas

### ⚠️ **Pontos de Atenção**

- **Linha 89**: A palavra `gauge` isolada parece ser código incompleto e pode ser removida
- **Índices Globais**: O sistema usa `$indiceGraficoGlobal++` para IDs únicos
- **Cores Hexadecimais**: Todas as cores devem estar no formato `#RRGGBB`
- **Sanitização**: Uso de `htmlspecialchars()` para prevenir XSS

### 🔧 **Dependências**

- **PHP**: Funções `formatarNumero()` e `calcularPorcentagemProdutividade()`
- **JavaScript**: ES6+ (arrow functions, template literals)
- **CSS**: Flexbox e Grid Layout support

## 📞 Suporte

Para dúvidas ou problemas com o sistema de gauge:

1. Verifique os logs de erro em `logs/error.log`
2. Teste as funções PHP isoladamente
3. Use as ferramentas de desenvolvimento do navegador para debugar JavaScript
4. Verifique se todos os dados necessários estão sendo passados corretamente

---

*Documentação atualizada em: Junho 2025*
