<div class="dashboard-container">    <!-- Formulário principal - Encapsulando todo o conteúdo relevante -->    <form method="GET" id="mainForm">
        <!-- Cabeçalho -->
        <header class="dashboard-header">
            <div class="logo-container">
                <img src="/assets/images/logo-emserh-em-png.png" alt="EMSERH" class="logo">
                <div class="header-title">
                    <h1>ACOMPANHAMENTO DIÁRIO DE PRODUTIVIDADE</h1>
                </div>
            </div>
            <div class="header-info">
                <div class="update-info">
                    <span>Última atualização:</span>
                    <span class="update-time"><?php echo $data_atual; ?></span>
                </div>
                <h2 class="unit-name"><?php echo !empty($unidade_nome) ? htmlspecialchars($unidade_nome) : 'Selecione uma unidade'; ?></h2>
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

        <!-- Conteúdo principal -->
        <?php if (!empty($unidade) && !empty($relatorio_mensal)) { ?>
            <div class="services-container">
                <?php foreach ($relatorio_mensal as $index => $servico) { 
                    $total_executados = (int)$servico['total_executados'];
                    $meta_pdt = (int)$servico['meta'];
                    $progresso = calcularPorcentagemProdutividade($total_executados, $meta_pdt);
                    
                    // Determinar a cor da barra lateral com base no tipo de serviço
                    $service_color = determinarCorServico($servico['natureza']);
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
                                <canvas id="grafico<?php echo $index; ?>"></canvas>
                            </div>
                            
                            <div class="gauge-summary">
                                <div class="gauge-container">
                                    <canvas id="gauge<?php echo $index; ?>"></canvas>
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
                      <!-- Dados individuais removidos para evitar conflito -->
                <?php } ?>
            </div>
        <?php } elseif (!empty($unidade) && empty($relatorio_mensal)) { ?>
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

<?php if (!empty($unidade) && !empty($relatorio_mensal)) { ?>
<script>
// Dados para os gráficos
window.dadosGraficos = <?php echo json_encode($dados_graficos ?? []); ?>;
console.log('Dados dos gráficos:', window.dadosGraficos);
console.log('Quantidade de serviços:', <?php echo count($relatorio_mensal); ?>);
console.log('Quantidade de gráficos:', <?php echo count($dados_graficos ?? []); ?>);
</script>
<?php } else { ?>
<script>
console.log('Sem dados - Unidade:', '<?php echo $unidade; ?>', 'Relatório mensal:', <?php echo count($relatorio_mensal); ?>);
</script>
<?php } ?>
