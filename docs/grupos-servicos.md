# 🏷️ Grupos e Serviços - Sistema RTP

## 📋 Visão Geral

Este documento detalha a organização hierárquica dos serviços hospitalares no sistema RTP, explicando como grupos e serviços são estruturados, categorizados e utilizados nos relatórios de produtividade.

## 🏗️ Estrutura Hierárquica

### 🏥 **Hospital/Unidade**
```
Hospital Regional de Exemplo
├── 🩺 Grupo: Clínica Médica
│   ├── Serviço: Cardiologia
│   ├── Serviço: Endocrinologia  
│   └── Serviço: Gastroenterologia
├── 🏥 Grupo: Especialidades Cirúrgicas
│   ├── Serviço: Cirurgia Geral
│   ├── Serviço: Ortopedia
│   └── Serviço: Neurocirurgia
└── 🔬 Grupo: Diagnóstico
    ├── Serviço: Radiologia
    ├── Serviço: Laboratorio
    └── Serviço: Ultrassonografia
```

## 🏷️ Sistema de Grupos

### 🎨 **Cores por Grupo**

Cada grupo possui uma cor única para identificação visual:

```php
$grupos_cores = [
    'Clínica Médica' => '#fd7e14',           // Laranja
    'Especialidades Cirúrgicas' => '#0d6efd', // Azul
    'Diagnóstico' => '#28a745',              // Verde
    'Urgência/Emergência' => '#dc3545',      // Vermelho
    'Pediatria' => '#6f42c1',                // Roxo
    'Ginecologia/Obstetrícia' => '#e91e63',  // Rosa
    'Reabilitação' => '#17a2b8',             // Ciano
];
```

### 📊 **Estrutura de Dados**
```php
$relatorio_por_grupos = [
    [
        'grupo_nome' => 'Clínica Médica',
        'grupo_cor' => '#fd7e14',
        'servicos' => [
            [
                'natureza' => 'Cardiologia',
                'total_executados' => 150,
                'meta_pdt' => 180,
                'pactuado' => 200,
                // ... outros dados
            ],
            // ... outros serviços
        ]
    ],
    // ... outros grupos
];
```

## 🩺 Tipos de Serviços Hospitalares

### 🏥 **Clínica Médica**
- **Cardiologia**: Doenças cardiovasculares
- **Endocrinologia**: Distúrbios hormonais
- **Gastroenterologia**: Sistema digestivo
- **Pneumologia**: Sistema respiratório
- **Neurologia**: Sistema nervoso
- **Nefrologia**: Sistema renal
- **Reumatologia**: Doenças articulares

### 🔪 **Especialidades Cirúrgicas**
- **Cirurgia Geral**: Procedimentos cirúrgicos gerais
- **Ortopedia**: Sistema musculoesquelético
- **Neurocirurgia**: Cirurgias neurológicas
- **Cirurgia Vascular**: Sistema circulatório
- **Otorrinolaringologia**: Ouvido, nariz e garganta
- **Oftalmologia**: Sistema visual
- **Urologia**: Sistema urogenital

### 🔬 **Diagnóstico e Apoio**
- **Radiologia**: Exames de imagem
- **Laboratório**: Análises clínicas
- **Ultrassonografia**: Exames ultrassonográficos
- **Tomografia**: Exames tomográficos
- **Ressonância**: Exames de ressonância magnética
- **Endoscopia**: Exames endoscópicos

### 🚨 **Urgência e Emergência**
- **Pronto Socorro**: Atendimentos de urgência
- **UTI**: Unidade de Terapia Intensiva
- **Emergência Pediátrica**: Urgências infantis
- **Trauma**: Atendimento traumatológico

### 👶 **Pediatria**
- **Pediatria Geral**: Atendimento infantil geral
- **Neonatologia**: Recém-nascidos
- **Pediatria Cirúrgica**: Cirurgias infantis
- **Adolescentes**: Medicina do adolescente

### 👩 **Ginecologia e Obstetrícia**
- **Ginecologia**: Saúde feminina
- **Obstetrícia**: Acompanhamento gestacional
- **Planejamento Familiar**: Orientação reprodutiva
- **Climatério**: Menopausa e pós-menopausa

## 📊 Métricas por Grupo

### 📈 **Cálculos de Grupo**
```php
function calcularMetricasGrupo($grupo) {
    $total_executados = 0;
    $total_meta_pdt = 0;
    $total_pactuado = 0;
    $total_agendado = 0;
    
    foreach ($grupo['servicos'] as $servico) {
        $total_executados += $servico['total_executados'];
        $total_meta_pdt += $servico['meta_pdt'];
        $total_pactuado += $servico['pactuado'];
        $total_agendado += $servico['total_agendado'];
    }
    
    return [
        'produtividade_pdt' => ($total_executados / $total_meta_pdt) * 100,
        'produtividade_pactuado' => ($total_executados / $total_pactuado) * 100,
        'utilizacao' => ($total_agendado / $total_pactuado) * 100,
        'total_servicos' => count($grupo['servicos'])
    ];
}
```

### 🏆 **Ranking de Performance**
```php
function criarRankingGrupos($relatorio_por_grupos) {
    $ranking = [];
    
    foreach ($relatorio_por_grupos as $grupo) {
        $metricas = calcularMetricasGrupo($grupo);
        $ranking[] = [
            'nome' => $grupo['grupo_nome'],
            'cor' => $grupo['grupo_cor'],
            'produtividade' => $metricas['produtividade_pdt'],
            'servicos' => $metricas['total_servicos']
        ];
    }
    
    // Ordenar por produtividade decrescente
    usort($ranking, function($a, $b) {
        return $b['produtividade'] <=> $a['produtividade'];
    });
    
    return $ranking;
}
```

## 🎨 Identidade Visual

### 🌈 **Paleta de Cores Padrão**
```css
:root {
    --clinica-medica: #fd7e14;          /* Laranja */
    --cirurgicas: #0d6efd;              /* Azul */
    --diagnostico: #28a745;             /* Verde */
    --urgencia: #dc3545;                /* Vermelho */
    --pediatria: #6f42c1;               /* Roxo */
    --gineco: #e91e63;                  /* Rosa */
    --reabilitacao: #17a2b8;            /* Ciano */
    --admin: #6c757d;                   /* Cinza */
}
```

### 🎯 **Aplicação no Dashboard**
```php
<!-- Aba lateral colorida -->
<div class="group-tab" style="background-color: <?php echo $grupo['grupo_cor']; ?>;">
    <div class="group-tab-text">
        <?php echo $grupo['grupo_nome']; ?>
    </div>
</div>

<!-- Header do grupo -->
<div class="group-header" style="border-left-color: <?php echo $grupo['grupo_cor']; ?>">
    <h3>
        <span class="group-color-indicator" 
              style="background-color: <?php echo $grupo['grupo_cor']; ?>"></span>
        <?php echo $grupo['grupo_nome']; ?>
    </h3>
</div>
```

## 🔍 Filtros e Navegação

### 🎯 **Filtro por Grupo**
```php
function filtrarPorGrupo($relatorio_por_grupos, $grupo_filtro) {
    if (empty($grupo_filtro)) {
        return $relatorio_por_grupos;
    }
    
    return array_filter($relatorio_por_grupos, function($grupo) use ($grupo_filtro) {
        return $grupo['grupo_nome'] === $grupo_filtro;
    });
}
```

### 🔍 **Busca por Serviço**
```php
function buscarServico($relatorio_por_grupos, $termo_busca) {
    $resultados = [];
    
    foreach ($relatorio_por_grupos as $grupo) {
        $servicos_encontrados = array_filter($grupo['servicos'], function($servico) use ($termo_busca) {
            return stripos($servico['natureza'], $termo_busca) !== false;
        });
        
        if (!empty($servicos_encontrados)) {
            $grupo_resultado = $grupo;
            $grupo_resultado['servicos'] = $servicos_encontrados;
            $resultados[] = $grupo_resultado;
        }
    }
    
    return $resultados;
}
```

## 📊 Relatórios Especializados

### 📈 **Relatório por Grupo**
```php
function gerarRelatorioGrupo($grupo_nome, $periodo) {
    return [
        'grupo' => $grupo_nome,
        'periodo' => $periodo,
        'resumo' => [
            'total_servicos' => 0,
            'produtividade_media' => 0,
            'meta_total' => 0,
            'realizado_total' => 0
        ],
        'servicos_detalhados' => [],
        'graficos' => [
            'produtividade_mensal' => [],
            'comparativo_servicos' => [],
            'tendencia_grupo' => []
        ]
    ];
}
```

### 📋 **Dashboard Gerencial**
```php
function criarDashboardGerencial($relatorio_por_grupos) {
    $dashboard = [
        'resumo_geral' => [
            'total_grupos' => count($relatorio_por_grupos),
            'total_servicos' => 0,
            'produtividade_geral' => 0
        ],
        'grupos_performance' => [],
        'alertas' => [],
        'tendencias' => []
    ];
    
    foreach ($relatorio_por_grupos as $grupo) {
        $metricas = calcularMetricasGrupo($grupo);
        $dashboard['grupos_performance'][] = [
            'nome' => $grupo['grupo_nome'],
            'cor' => $grupo['grupo_cor'],
            'metricas' => $metricas
        ];
        
        $dashboard['resumo_geral']['total_servicos'] += count($grupo['servicos']);
    }
    
    return $dashboard;
}
```

## ⚙️ Configuração de Grupos

### 📝 **Arquivo de Configuração**
```php
// config/grupos.php
return [
    'grupos_padrao' => [
        [
            'nome' => 'Clínica Médica',
            'cor' => '#fd7e14',
            'icone' => 'fas fa-stethoscope',
            'ordem' => 1,
            'servicos_padrao' => [
                'Cardiologia', 'Endocrinologia', 'Gastroenterologia'
            ]
        ],
        // ... outros grupos
    ],
    
    'cores_disponiveis' => [
        '#fd7e14', '#0d6efd', '#28a745', '#dc3545',
        '#6f42c1', '#e91e63', '#17a2b8', '#ffc107'
    ]
];
```

### 🔧 **Gerenciamento Dinâmico**
```php
class GerenciadorGrupos {
    public function adicionarGrupo($nome, $cor, $servicos = []) {
        // Validar se cor não está em uso
        // Criar novo grupo
        // Atualizar configuração
    }
    
    public function editarGrupo($id, $dados) {
        // Validar dados
        // Atualizar grupo
        // Manter histórico
    }
    
    public function removerGrupo($id) {
        // Verificar dependências
        // Reatribuir serviços
        // Remover grupo
    }
}
```

## 🎯 Melhores Práticas

### ✅ **Organização**
1. Máximo de 8 grupos para melhor visualização
2. Cores contrastantes para acessibilidade
3. Nomes descritivos e padronizados
4. Hierarquia lógica por especialidade

### ✅ **Performance**
1. Cache de cálculos por grupo
2. Lazy loading para grupos grandes
3. Paginação em listas extensas
4. Índices de banco otimizados

### ✅ **Usabilidade**
1. Filtros rápidos por grupo
2. Busca inteligente por serviço
3. Cores consistentes em todo sistema
4. Tooltips informativos

---

*Última atualização: Junho 2025*
