
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Formulário principal -->
        <form method="GET" id="mainForm">
            <!-- CABEÇALHO FIXO - usando partial -->
            <?php include __DIR__ . '/../partials/header.php'; ?>
        
        <!-- Conteúdo principal com layout original + abas informativas COLORIDAS -->
        <?php if (!empty($unidade) && !empty($relatorio_por_grupos)) { ?>
            <div class="services-container">
                <?php 
                $indiceGraficoGlobal = 0;
                foreach ($relatorio_por_grupos as $grupoIndex => $grupo) { ?>
                    <div class="group-container">
                        <!-- ABA LATERAL COLORIDA COMPLETA -->
                        <div class="group-tab" style="--grupo-cor: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>; background-color: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>;">
                            <div class="group-tab-text">
                                <?php echo htmlspecialchars($grupo['grupo_nome']); ?>
                            </div>
                        </div>

                        <!-- Header do grupo -->
                        <div class="group-header" style="border-left-color: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>">
                            <h3>
                                <span class="group-color-indicator" 
                                      style="background-color: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>"></span>
                                <?php echo htmlspecialchars($grupo['grupo_nome']); ?>
                            </h3>
                            <div class="group-description">
                                <?php echo count($grupo['servicos']); ?> serviço(s)
                            </div>
                        </div>

                        <!-- Serviços do grupo -->                        <div class="group-services" style="border-left-color: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>">                            <?php foreach ($grupo['servicos'] as $servicoIndex => $servico) {
                                $total_executados = (int)$servico['total_executados'];
                                $meta_pdt = (int)$servico['meta_pdt'];
                                
                                // Calcular totais de pactuado e agendado dos dados diários
                                $total_pactuado = 0;
                                $total_agendado = 0;
                                
                                // Garantir que dados_graficos existe
                                if (!isset($dados_graficos)) {
                                    $dados_graficos = [];
                                }
                                
                                // Verificar se existe dados diários para este serviço
                                if (isset($dados_graficos[$indiceGraficoGlobal]['dadosDiarios'])) {
                                    foreach ($dados_graficos[$indiceGraficoGlobal]['dadosDiarios'] as $dia) {
                                        $total_pactuado += (int)($dia['pactuado'] ?? 0);
                                        $total_agendado += (int)($dia['agendado'] ?? 0);
                                    }
                                }
                                
                                // Calcular progresso baseado na relação total_executados/meta_pdt (realizado vs meta)
                                $progresso = calcularPorcentagemProdutividade($total_executados, $meta_pdt);
                                
                                // Usar a cor do grupo para o serviço
                                $service_color = $grupo['grupo_cor'];
                                
                                // Criar um índice único global
                                $indiceGrafico = $indiceGraficoGlobal++;
                            ?>
                                <div class="service-section">
                                    <div class="service-header" style="background-color: <?php echo $service_color; ?>;">
                                        <h3><?php echo htmlspecialchars($servico['natureza']); ?></h3>
                                    </div>
                                    
                                    <div class="service-body">                                        <div class="chart-container">
                                            <div id="grafico<?php echo $indiceGrafico; ?>"></div>
                                        </div>
                                          <div class="gauge-summary">             
                                            <!-- Legenda interativa do gauge -->
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
                                            
                                            <div class="gauge-container">
                                                <div id="gauge<?php echo $indiceGrafico; ?>"></div><div class="gauge-info">
                                                    <div class="gauge-value"><?php echo formatarNumero($total_executados); ?></div>
                                                    <div class="gauge-percent"><?php echo formatarNumero($progresso, 2); ?>%</div>
                                                </div>
                                            </div><div class="summary-details">
                                                <div class="summary-item">Pactuado | Agendado</div>
                                                <div class="summary-values">
                                                    <span class="executed"><?php echo formatarNumero($total_pactuado); ?></span> | 
                                                    <span class="target"><?php echo formatarNumero($total_agendado); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } elseif (!empty($unidade) && empty($relatorio_por_grupos)) { ?>
            <div class="alert alert-warning">
                <h4>Nenhum serviço encontrado</h4>
                <p>Não foram encontrados serviços para esta unidade no período selecionado.</p>
            </div>
        <?php } else { ?>
            <div class="welcome-message">
                <h3>Bem-vindo ao Sistema RTP</h3>
                <p>Selecione uma unidade para visualizar o relatório de produtividade.</p>
            </div>        <?php } ?>
    </form>
    </div>
</div>

<!-- JavaScript para aplicar cores das abas automaticamente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔥 JAVASCRIPT CARREGADO - DASHBOARD ATUALIZADO!');
    
    // Aplicar cores de fundo nas abas
    document.querySelectorAll('.group-tab').forEach(tab => {
        const cor = tab.style.getPropertyValue('--grupo-cor');
        if (cor) {
            tab.style.backgroundColor = cor;
            console.log('Aba colorida aplicada:', cor);
            
            // Efeito hover
            tab.addEventListener('mouseenter', function() {
                this.style.opacity = '0.8';
            });
              tab.addEventListener('mouseleave', function() {
                this.style.opacity = '1';
            });
        }
    });
    
    // Interatividade da legenda do gauge
    initGaugeLegendInteractivity();
});

// Função para inicializar a interatividade da legenda
function initGaugeLegendInteractivity() {
    document.querySelectorAll('.legend-item').forEach(item => {
        const type = item.getAttribute('data-type');
        
        // Efeito de clique com feedback visual
        item.addEventListener('click', function() {
            // Adicionar efeito de "pulse"
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
        
        item.addEventListener('mouseleave', function() {
            const value = this.querySelector('.legend-value');
            if (value) {
                value.style.transform = 'scale(1)';
                value.style.fontWeight = 'bold';
            }
        });
    });
}

// Função para mostrar tooltip informativo
function showLegendTooltip(element, type) {
    // Remover tooltip existente
    const existingTooltip = document.querySelector('.legend-tooltip');
    if (existingTooltip) {
        existingTooltip.remove();
    }
    
    // Criar novo tooltip
    const tooltip = document.createElement('div');
    tooltip.className = 'legend-tooltip';
    
    let tooltipText = '';
    if (type === 'realizado') {
        tooltipText = 'Total de procedimentos executados no período';
    } else if (type === 'meta') {
        tooltipText = 'Meta de produtividade definida (PDT)';
    }
    
    tooltip.textContent = tooltipText;
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
    
    // Remover tooltip após 2 segundos    setTimeout(() => {
        if (tooltip.parentNode) {
            tooltip.remove();
        }
    }, 2000);
}
</script>

<?php if (!empty($unidade) && !empty($relatorio_por_grupos)) { ?>
<script>
// Dados para os gráficos (layout original mantido)
window.dadosGraficos = <?php echo json_encode($dados_graficos ?? []); ?>;
console.log('📊 Dados dos gráficos carregados:', window.dadosGraficos);
console.log('📈 Quantidade de grupos:', <?php echo count($relatorio_por_grupos); ?>);
</script>
<?php } else { ?>
<script>
console.log('ℹ️ Aguardando seleção de unidade...');
</script>
<?php } ?>