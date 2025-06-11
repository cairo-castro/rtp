# 🔧 UI vs Lógica de Negócio - Sistema RTP

## 📋 Visão Geral

Este documento estabelece as diretrizes para **separação clara** entre Interface do Usuário (UI) e Lógica de Negócio no sistema RTP, garantindo manutenibilidade, testabilidade e escalabilidade.

## 🏗️ Arquitetura de Separação

### 🎨 **Camada de Apresentação (UI)**
**Responsabilidades**: 
- Exibição de dados
- Interação com usuário
- Formatação visual
- Responsividade

**Localização**: 
- `src/views/`
- `public/assets/css/`
- `public/assets/js/` (apenas interação)

### 🧮 **Camada de Lógica de Negócio**
**Responsabilidades**:
- Cálculos de produtividade
- Validações de regras
- Processamento de dados
- Aplicação de políticas

**Localização**:
- `src/controllers/`
- `src/models/`
- `src/helpers/`

## 📊 Exemplo Prático: Dashboard de Produtividade

### ❌ **ANTI-PADRÃO: Lógica na View**
```php
<!-- dashboard.php - EVITAR ISSO -->
<div class="gauge-value">
    <?php 
    // ❌ Cálculo direto na view
    $produtividade = ($total_executados / $meta_pdt) * 100;
    
    // ❌ Lógica de cor na view
    if ($produtividade >= 100) {
        $cor = '#28a745';
    } elseif ($produtividade >= 80) {
        $cor = '#ffc107';
    } else {
        $cor = '#dc3545';
    }
    
    echo number_format($produtividade, 2) . '%';
    ?>
</div>
```

### ✅ **PADRÃO CORRETO: Separação Clara**

#### 🧮 **Lógica de Negócio (Helper)**
```php
// src/helpers/relatorio_helpers.php
function calcularPorcentagemProdutividade($executados, $meta_pdt) {
    if ($meta_pdt <= 0) {
        return 0;
    }
    return ($executados / $meta_pdt) * 100;
}

function obterCorProdutividade($produtividade) {
    if ($produtividade >= 100) return '#28a745';
    if ($produtividade >= 80)  return '#ffc107';
    if ($produtividade >= 60)  return '#fd7e14';
    return '#dc3545';
}

function classificarProdutividade($produtividade) {
    return [
        'valor' => $produtividade,
        'cor' => obterCorProdutividade($produtividade),
        'status' => $produtividade >= 100 ? 'Meta atingida' : 'Abaixo da meta',
        'classe_css' => $produtividade >= 100 ? 'success' : 'warning'
    ];
}
```

#### 🎮 **Controller (Orquestração)**
```php
// src/controllers/RelatorioController.php
class RelatorioController extends Controller {
    
    public function dashboard() {
        $model = new RelatorioModel();
        $dados_brutos = $model->obterDadosRelatorio();
        
        // Processar dados aplicando lógica de negócio
        $dados_processados = $this->processarDadosParaDashboard($dados_brutos);
        
        // Passar dados limpos para a view
        $this->view('relatorio/dashboard', $dados_processados);
    }
    
    private function processarDadosParaDashboard($dados_brutos) {
        $dados_processados = [];
        
        foreach ($dados_brutos as $servico) {
            // Aplicar regras de negócio
            $produtividade = calcularPorcentagemProdutividade(
                $servico['total_executados'], 
                $servico['meta_pdt']
            );
            
            $classificacao = classificarProdutividade($produtividade);
            
            $dados_processados[] = [
                'natureza' => $servico['natureza'],
                'total_executados' => $servico['total_executados'],
                'meta_pdt' => $servico['meta_pdt'],
                'produtividade' => $classificacao,
                'dados_graficos' => $this->prepararDadosGrafico($servico)
            ];
        }
        
        return $dados_processados;
    }
}
```

#### 🎨 **View (Apenas Apresentação)**
```php
<!-- src/views/relatorio/dashboard.php -->
<div class="gauge-value" style="color: <?php echo $servico['produtividade']['cor']; ?>;">
    <?php echo formatarNumero($servico['produtividade']['valor'], 2); ?>%
</div>

<div class="status-badge <?php echo $servico['produtividade']['classe_css']; ?>">
    <?php echo $servico['produtividade']['status']; ?>
</div>
```

## 🔄 Padrões de Separação

### 📊 **Dados para Visualização**

#### ✅ **Preparação no Controller**
```php
// RelatorioController.php
private function prepararDadosGauge($servico) {
    return [
        'id_elemento' => 'gauge' . $servico['id'],
        'series' => [
            calcularPorcentagemProdutividade($servico['executados'], $servico['meta_pdt']),
            calcularPorcentagemProdutividade($servico['executados'], $servico['pactuado'])
        ],
        'cores' => [
            $servico['grupo_cor'],
            '#0d6efd'
        ],
        'labels' => ['Realizado vs Meta', 'Realizado vs Pactuado'],
        'configuracoes' => obterConfiguracaoGauge()
    ];
}

function obterConfiguracaoGauge() {
    return [
        'height' => 200,
        'type' => 'radialBar',
        'responsive' => true
    ];
}
```

#### 🎨 **Consumo na View**
```php
<!-- dashboard.php -->
<div id="<?php echo $dados_gauge['id_elemento']; ?>"></div>

<script>
// Dados preparados pelo controller
const dadosGauge = <?php echo json_encode($dados_gauge); ?>;

// Apenas criação da visualização
const gauge = new ApexCharts(
    document.getElementById(dadosGauge.id_elemento), 
    {
        series: dadosGauge.series,
        colors: dadosGauge.cores,
        labels: dadosGauge.labels,
        ...dadosGauge.configuracoes
    }
);
gauge.render();
</script>
```

### 🔍 **Validações e Regras**

#### ✅ **Lógica no Helper/Model**
```php
// relatorio_helpers.php
function validarDadosServico($dados) {
    $erros = [];
    
    if ($dados['meta_pdt'] <= 0) {
        $erros[] = 'Meta PDT deve ser maior que zero';
    }
    
    if ($dados['total_executados'] < 0) {
        $erros[] = 'Executados não pode ser negativo';
    }
    
    if ($dados['total_executados'] > $dados['total_agendado'] * 1.5) {
        $erros[] = 'Executados muito superior aos agendados';
    }
    
    return [
        'valido' => empty($erros),
        'erros' => $erros
    ];
}

function aplicarRegrasProdutividade($dados) {
    $validacao = validarDadosServico($dados);
    
    if (!$validacao['valido']) {
        return [
            'sucesso' => false,
            'erros' => $validacao['erros']
        ];
    }
    
    return [
        'sucesso' => true,
        'produtividade' => calcularPorcentagemProdutividade(
            $dados['total_executados'], 
            $dados['meta_pdt']
        )
    ];
}
```

#### 🎨 **Exibição na View**
```php
<!-- dashboard.php -->
<?php if (!$dados['valido']): ?>
    <div class="alert alert-danger">
        <?php foreach ($dados['erros'] as $erro): ?>
            <p><?php echo htmlspecialchars($erro); ?></p>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <!-- Exibir dados normalmente -->
<?php endif; ?>
```

## 🧪 Testabilidade

### ✅ **Lógica Testável (Separada)**
```php
// tests/Unit/RelatorioHelpersTest.php
class RelatorioHelpersTest extends PHPUnit\Framework\TestCase {
    
    public function testCalcularPorcentagemProdutividade() {
        // Teste da lógica pura
        $resultado = calcularPorcentagemProdutividade(150, 180);
        $this->assertEquals(83.33, $resultado, '', 0.01);
    }
    
    public function testObterCorProdutividade() {
        $this->assertEquals('#28a745', obterCorProdutividade(100));
        $this->assertEquals('#ffc107', obterCorProdutividade(85));
        $this->assertEquals('#dc3545', obterCorProdutividade(50));
    }
    
    public function testValidarDadosServico() {
        $dados_invalidos = ['meta_pdt' => 0, 'total_executados' => -5];
        $resultado = validarDadosServico($dados_invalidos);
        
        $this->assertFalse($resultado['valido']);
        $this->assertNotEmpty($resultado['erros']);
    }
}
```

### ❌ **UI Não Testável (Misturada)**
```php
// Impossível testar isoladamente
<div><?php echo ($executados / $meta) * 100; ?>%</div>
```

## 📱 Responsividade e Adaptação

### ✅ **Dados Adaptáveis**
```php
// Controller prepara dados para diferentes dispositivos
private function prepararDadosResponsivos($dados, $dispositivo) {
    $config_base = [
        'dados' => $dados,
        'mostrar_detalhes' => true,
        'tamanho_gauge' => 200
    ];
    
    switch ($dispositivo) {
        case 'mobile':
            $config_base['mostrar_detalhes'] = false;
            $config_base['tamanho_gauge'] = 150;
            break;
            
        case 'tablet':
            $config_base['tamanho_gauge'] = 170;
            break;
    }
    
    return $config_base;
}
```

### 🎨 **CSS Responsivo Puro**
```css
/* relatorio.css - Apenas estilos */
.gauge-container {
    width: var(--gauge-size, 200px);
    height: var(--gauge-size, 200px);
}

@media (max-width: 768px) {
    .gauge-container {
        --gauge-size: 150px;
    }
    
    .gauge-details {
        display: none;
    }
}
```

## 🔄 Fluxo de Dados

### 📊 **Fluxo Unidirecional**
```
1. Model → Dados brutos do banco
2. Controller → Aplicar lógica de negócio
3. Helper → Cálculos e validações
4. Controller → Preparar dados para view
5. View → Apenas exibir dados processados
6. Assets → Estilização e interação
```

### 🚫 **Evitar Fluxo Reverso**
```
❌ View modificando dados
❌ JavaScript alterando lógica de negócio
❌ CSS contendo regras de negócio
❌ Model formatando apresentação
```

## 📋 Checklist de Qualidade

### ✅ **Lógica de Negócio**
- [ ] Cálculos em helpers separados
- [ ] Validações centralizadas
- [ ] Regras documentadas
- [ ] Testabilidade garantida
- [ ] Sem dependência de UI

### ✅ **Interface do Usuário**
- [ ] Views sem cálculos
- [ ] CSS semântico
- [ ] JavaScript apenas para interação
- [ ] Responsividade pura
- [ ] Acessibilidade considerada

### ✅ **Controller**
- [ ] Orquestra sem implementar
- [ ] Delega para helpers
- [ ] Prepara dados para view
- [ ] Trata erros adequadamente
- [ ] Mantém-se enxuto

## 🚀 Benefícios da Separação

### 🧪 **Testabilidade**
- Lógica isolada e testável
- Mocks facilitados
- Coverage mais preciso
- Debugging simplificado

### 🔄 **Manutenibilidade**
- Mudanças localizadas
- Responsabilidades claras
- Código reutilizável
- Documentação natural

### 📈 **Escalabilidade**
- APIs futuras facilitadas
- Múltiplas interfaces possíveis
- Lógica compartilhável
- Performance otimizável

### 👥 **Trabalho em Equipe**
- Frontend/Backend separados
- Especialização possível
- Conflitos reduzidos
- Produtividade aumentada

---

*Última atualização: Junho 2025*