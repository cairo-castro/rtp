
<div class="dashboard-container">
    <!-- Formulário principal -->
    <form method="GET" id="mainForm">
        <!-- CABEÇALHO COMPLETO COMO NA IMAGEM ORIGINAL -->
        <header class="dashboard-header">
            <div class="logo-container">
                <img src="/assets/images/logo-emserh-em-png.png" alt="EMSERH" class="logo">
                <div class="header-title">
                    <h1>ACOMPANHAMENTO DIÁRIO DE PRODUTIVIDADE</h1>
                </div>
            </div>
                       
            <div class="filters">
                <div class="filter-item">
                    <label for="mes">Mês</label>
                    <select id="mes" name="mes" class="filter-select" onchange="document.getElementById('mainForm').submit();">
                        <?php foreach ($meses_nomes as $num => $nome) { ?>
                            <option value="<?php echo $num; ?>" <?php echo ($mes == $num) ? 'selected' : ''; ?>>
                                <?php echo $nome; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="filter-item">
                    <label for="ano">Ano</label>
                    <select id="ano" name="ano" class="filter-select" onchange="document.getElementById('mainForm').submit();">
                        <?php for ($i = 2023; $i <= 2030; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php echo ($ano == $i) ? 'selected' : ''; ?>>
                                <?php echo $i; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            
            <div class="productivity-summary">
                <div class="productivity-value">
                    <?php echo formatarNumero($produtividade_geral, 2); ?>%
                </div>
                <div class="productivity-label">Produtividade</div>
            </div>
        </header>
        
        <!-- Seleção de unidade -->
        <div class="filter-form">
            <div class="form-group">
                <label for="unidade">Unidade:</label>
                <select name="unidade" id="unidade" class="form-control" onchange="document.getElementById('mainForm').submit();">
                    <option value="">Selecione a Unidade</option>
                    <?php foreach ($unidades as $u) { ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo ($unidade == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nome']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

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

                        <!-- Serviços do grupo -->
                        <div class="group-services" style="border-left-color: <?php echo htmlspecialchars($grupo['grupo_cor']); ?>">
                            <?php foreach ($grupo['servicos'] as $servicoIndex => $servico) { 
                                $total_executados = (int)$servico['total_executados'];
                                $meta_pdt = (int)$servico['meta'];
                                $progresso = calcularPorcentagemProdutividade($total_executados, $meta_pdt);
                                
                                // Usar a cor do grupo para o serviço
                                $service_color = $grupo['grupo_cor'];
                                
                                // Criar um índice único global
                                $indiceGrafico = $indiceGraficoGlobal++;
                            ?>
                                <div class="service-section">
                                    <div class="service-header" style="background-color: <?php echo $service_color; ?>;">
                                        <h3><?php echo htmlspecialchars($servico['natureza']); ?></h3>
                                        <div class="service-controls">
                                            <button type="button" class="btn-control" aria-label="Expandir"><i class="arrow-up"></i></button>
                                            <button type="button" class="btn-control" aria-label="Reduzir"><i class="arrow-down"></i></button>
                                            <button type="button" class="btn-control" aria-label="Anterior"><i class="arrow-left"></i></button>
                                            <button type="button" class="btn-control" aria-label="Próximo"><i class="arrow-right"></i></button>
                                            <button type="button" class="btn-control" aria-label="Restaurar"><i class="restore"></i></button>
                                            <button type="button" class="btn-control" aria-label="Mais opções"><i class="more"></i></button>
                                        </div>
                                    </div>
                                    
                                    <div class="service-body">
                                        <div class="chart-container">
                                            <div class="chart-legend">
                                                <span class="legend-item"><span class="color-box pactuado"></span> Pactuado</span>
                                                <span class="legend-item"><span class="color-box agendados"></span> Agendados</span>
                                                <span class="legend-item"><span class="color-box realizados"></span> Realizados</span>
                                            </div>
                                            <canvas id="grafico<?php echo $indiceGrafico; ?>"></canvas>
                                        </div>
                                        
                                        <div class="gauge-summary">
                                            <div class="gauge-container">
                                                <canvas id="gauge<?php echo $indiceGrafico; ?>"></canvas>
                                                <div class="gauge-info">
                                                    <div class="gauge-value"><?php echo formatarNumero($total_executados); ?></div>
                                                    <div class="gauge-percent"><?php echo formatarNumero($progresso, 2); ?>%</div>
                                                    <div class="gauge-target"><?php echo formatarNumero($meta_pdt); ?></div>
                                                </div>
                                            </div>
                                            <div class="summary-details">
                                                <div class="summary-item">Realizados | Meta PDT</div>
                                                <div class="summary-values">
                                                    <span class="executed"><?php echo formatarNumero($total_executados); ?></span> | 
                                                    <span class="target"><?php echo formatarNumero($meta_pdt); ?></span>
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
            </div>
        <?php } ?>
    </form>
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
});
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