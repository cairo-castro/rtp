<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Formulário principal -->
        <form method="GET" id="mainForm">
            <!-- CABEÇALHO USANDO O MESMO LAYOUT DO DASHBOARD PRINCIPAL COM KPIs EXTRAS -->
            <header class="dashboard-header fixed">
                <!-- Primeira linha do header -->
                <div class="header-wrapper">
                    <div class="header-main-row">
                        <div class="logo-container">
                            <?php $baseUrl = rtrim(BASE_URL, '/'); ?>
                            <img src="<?php echo $baseUrl; ?>/assets/images/logo-emserh-em-png.png" alt="EMSERH" class="logo">
                            <div class="header-title">
                                <h1>PAINEL GERENCIAL - PRODUTIVIDADE HOSPITALAR</h1>
                            </div>
                        </div>
                                       
                        <div class="filters">
                            <div class="filter-item">
                                <label for="mes">Mês</label>
                                <select id="mes" name="mes" class="filter-select" onchange="document.getElementById('mainForm').submit();" aria-label="Selecionar mês">
                                    <?php if (isset($meses_nomes)): ?>
                                        <?php foreach ($meses_nomes as $num => $nome) { ?>
                                            <option value="<?php echo $num; ?>" <?php echo (isset($mes) && $mes == $num) ? 'selected' : ''; ?>>
                                                <?php echo $nome; ?>
                                            </option>
                                        <?php } ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label for="ano">Ano</label>
                                <select id="ano" name="ano" class="filter-select" onchange="document.getElementById('mainForm').submit();" aria-label="Selecionar ano">
                                    <?php for ($i = 2023; $i <= 2030; $i++) { ?>
                                        <option value="<?php echo $i; ?>" <?php echo (isset($ano) && $ano == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        
                        <!-- PRODUCTIVITY SUMMARY EXPANDIDO COM 3 KPIs -->
                        <div class="productivity-summary">
                            <!-- KPI 1: Produtividade Atual (mesmo estilo do original) -->
                            <?php if (isset($produtividade_geral)): ?>
                            <div class="productivity-value" aria-label="Produtividade atual">
                                <?php echo formatarNumero($produtividade_geral, 2); ?>%
                            </div>
                            <div class="productivity-label">Produtividade</div>
                            <?php else: ?>
                            <div class="productivity-value" aria-label="Produtividade não disponível">--</div>
                            <div class="productivity-label">Produtividade</div>
                            <?php endif; ?>
                            
                            <!-- Separador visual -->
                            <div style="width: 1px; height: 40px; background: #ddd; margin: 0 15px;"></div>
                            
                            <!-- KPI 2: Produtividade Máxima -->
                            <?php if (isset($produtividade_maxima)): ?>
                            <div class="productivity-value" aria-label="Produtividade máxima" style="color: <?php echo $produtividade_maxima >= 100 ? '#27ae60' : ($produtividade_maxima >= 80 ? '#f39c12' : '#e74c3c'); ?>">
                                <?php echo formatarNumero($produtividade_maxima, 2); ?>%
                            </div>
                            <div class="productivity-label">Prod. Máxima</div>
                            <?php else: ?>
                            <div class="productivity-value" aria-label="Produtividade máxima não disponível">--</div>
                            <div class="productivity-label">Prod. Máxima</div>
                            <?php endif; ?>
                            
                            <!-- Separador visual -->
                            <div style="width: 1px; height: 40px; background: #ddd; margin: 0 15px;"></div>
                            
                            <!-- KPI 3: Prod vs Prod Max -->
                            <?php if (isset($prod_vs_prod_max)): ?>
                            <div class="productivity-value" aria-label="Produtividade versus produtividade máxima" style="color: <?php echo $prod_vs_prod_max >= 100 ? '#27ae60' : ($prod_vs_prod_max >= 80 ? '#f39c12' : '#e74c3c'); ?>">
                                <?php echo formatarNumero($prod_vs_prod_max, 2); ?>%
                            </div>
                            <div class="productivity-label">Prod vs Max</div>
                            <?php else: ?>
                            <div class="productivity-value" aria-label="Produtividade versus produtividade máxima não disponível">--</div>
                            <div class="productivity-label">Prod vs Max</div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Informações do usuário logado -->
                        <?php if (isset($user_logged_in) && $user_logged_in && isset($user_info)): ?>
                        <div class="user-info">
                            <div class="user-details">
                                <div class="user-name"><?php echo htmlspecialchars($user_info['nome'] ?? 'Usuário'); ?></div>
                                <div class="user-status">Logado</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Segunda linha do header - Seleção de Unidade e Pesquisa -->
                <div class="header-wrapper">
                    <div class="header-second-row">
                        <div class="unit-filter">
                            <label for="unidade">Unidade:</label>
                            <select name="unidade" id="unidade" class="unit-select" onchange="document.getElementById('mainForm').submit();" aria-label="Selecionar unidade">
                                <option value="">Selecione a Unidade</option>
                                <?php if (isset($unidades)): ?>
                                    <?php foreach ($unidades as $u) { ?>
                                        <option value="<?php echo $u['id']; ?>" <?php echo (isset($unidade) && $unidade == $u['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($u['nome']); ?>
                                            <?php if (isset($user_logged_in) && $user_logged_in && isset($user_info) && $user_info['unidade_id'] == $u['id']): ?>
                                                (Sua unidade)
                                            <?php endif; ?>
                                        </option>
                                    <?php } ?>
                                <?php endif; ?>
                            </select>
                            <?php if (isset($user_logged_in) && $user_logged_in && !empty($unidade)): ?>
                                <small class="auto-selected-hint">Unidade selecionada automaticamente</small>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Nova barra de pesquisa -->
                        <div class="search-filter">
                            <label for="search">Pesquisar:</label>
                            <div class="search-input-container">
                                <input type="text" id="search" placeholder="Buscar por grupo ou serviço..." class="search-input" autocomplete="off" aria-label="Pesquisar grupos ou serviços">
                                <button type="button" id="clearSearch" class="clear-search" title="Limpar pesquisa" aria-label="Limpar pesquisa">
                                    <span>×</span>
                                </button>
                            </div>
                            <div class="search-results" id="searchResults" style="display: none;"></div>
                        </div>
                        
                        <!-- Link para voltar ao dashboard principal -->
                        <div class="navigation-links">
                            <a href="/" class="nav-link">
                                <span class="nav-icon">📊</span>
                                Dashboard Principal
                            </a>
                        </div>
                    </div>
                </div>
            </header>
        
        <!-- Conteúdo principal -->
        <?php if (!empty($unidade) && !empty($relatorio_por_grupos)) { ?>
            
            <!-- Painel de KPIs Detalhados -->
            <div class="kpi-detail-panel">
                <div class="kpi-grid">
                    <div class="kpi-detail-card">
                        <div class="kpi-detail-header">
                            <h3>Produtividade Atual</h3>
                            <div class="kpi-detail-value main-kpi">
                                <?php echo formatarNumero($produtividade_geral, 2); ?>%
                            </div>
                        </div>
                        <div class="kpi-detail-description">
                            Percentual de execução atual comparado ao pactuado
                        </div>
                    </div>
                    
                    <div class="kpi-detail-card">
                        <div class="kpi-detail-header">
                            <h3>Produtividade Máxima</h3>
                            <div class="kpi-detail-value <?php echo $produtividade_maxima >= 100 ? 'excellent' : ($produtividade_maxima >= 80 ? 'good' : 'low'); ?>">
                                <?php echo formatarNumero($produtividade_maxima, 2); ?>%
                            </div>
                        </div>
                        <div class="kpi-detail-description">
                            Capacidade máxima de produtividade (Meta PDT ÷ Pactuado)
                        </div>
                        <div class="kpi-detail-formula">
                            Meta PDT ÷ Pactuado × 100
                        </div>
                    </div>
                    
                    <div class="kpi-detail-card">
                        <div class="kpi-detail-header">
                            <h3>Prod vs Prod Max</h3>
                            <div class="kpi-detail-value <?php echo $prod_vs_prod_max >= 100 ? 'excellent' : ($prod_vs_prod_max >= 80 ? 'good' : 'low'); ?>">
                                <?php echo formatarNumero($prod_vs_prod_max, 2); ?>%
                            </div>
                        </div>
                        <div class="kpi-detail-description">
                            Aproveitamento da capacidade máxima (Executados ÷ Meta PDT)
                        </div>
                        <div class="kpi-detail-formula">
                            Executados ÷ Meta PDT × 100
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Dados detalhados por serviços (layout simplificado) -->
            <div class="services-container">
                <?php foreach ($relatorio_por_grupos as $grupo) { ?>
                    <div class="group-container">
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
                            <?php foreach ($grupo['servicos'] as $servico) {
                                $total_executados = (int)$servico['total_executados'];
                                $meta_pdt = (int)$servico['meta_pdt'];
                                $total_pactuado = (int)$servico['pactuado'];
                                
                                // Calcular percentuais
                                $percentual_executado = $total_pactuado > 0 ? ($total_executados / $total_pactuado) * 100 : 0;
                                
                                // KPIs específicos para gerência por serviço
                                $servico_prod_maxima = $total_pactuado > 0 ? ($meta_pdt / $total_pactuado) * 100 : 0;
                                $servico_prod_vs_max = $meta_pdt > 0 ? ($total_executados / $meta_pdt) * 100 : 0;
                                ?>
                                
                                <div class="service-card gerencia-enhanced" data-service-name="<?php echo htmlspecialchars(strtolower($servico['servico_nome'])); ?>">
                                    <div class="service-header">
                                        <h4 class="service-name"><?php echo htmlspecialchars($servico['servico_nome']); ?></h4>
                                        
                                        <!-- KPIs Gerenciais por Serviço -->
                                        <div class="service-kpis-gerencia">
                                            <div class="service-kpi-item">
                                                <span class="service-kpi-value <?php echo $servico_prod_maxima >= 100 ? 'excellent' : ($servico_prod_maxima >= 80 ? 'good' : 'low'); ?>">
                                                    <?php echo formatarNumero($servico_prod_maxima, 1); ?>%
                                                </span>
                                                <span class="service-kpi-label">Prod. Máx</span>
                                            </div>
                                            <div class="service-kpi-item">
                                                <span class="service-kpi-value <?php echo $servico_prod_vs_max >= 100 ? 'excellent' : ($servico_prod_vs_max >= 80 ? 'good' : 'low'); ?>">
                                                    <?php echo formatarNumero($servico_prod_vs_max, 1); ?>%
                                                </span>
                                                <span class="service-kpi-label">Vs Max</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Dados principais do serviço -->
                                    <div class="service-data">
                                        <div class="data-grid">
                                            <div class="data-item executados">
                                                <span class="data-value"><?php echo formatarNumero($total_executados); ?></span>
                                                <span class="data-label">Executados</span>
                                                <span class="data-percentage <?php echo $percentual_executado >= 100 ? 'high' : ($percentual_executado >= 80 ? 'medium' : 'low'); ?>">
                                                    <?php echo formatarNumero($percentual_executado, 1); ?>%
                                                </span>
                                            </div>
                                            
                                            <div class="data-item pactuado">
                                                <span class="data-value"><?php echo formatarNumero($total_pactuado); ?></span>
                                                <span class="data-label">Pactuado</span>
                                                <span class="data-percentage">100%</span>
                                            </div>
                                            
                                            <div class="data-item meta-pdt">
                                                <span class="data-value"><?php echo formatarNumero($meta_pdt); ?></span>
                                                <span class="data-label">Meta PDT</span>
                                                <span class="data-percentage meta-indicator">
                                                    <?php echo $total_pactuado > 0 ? formatarNumero(($meta_pdt / $total_pactuado) * 100, 1) . '%' : '--'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
            
        <?php } else { ?>
            <!-- Estado vazio -->
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <h3>Painel Gerencial</h3>
                <p>Selecione uma unidade para visualizar os indicadores gerenciais detalhados.</p>
            </div>
        <?php } ?>
        </form>
    </div>
</div>

<style>
/* Estilos específicos para a página de gerência */
.gerencia-layout {
    background: #f8f9fa;
}

.productivity-summary-gerencia {
    display: flex;
    gap: 15px;
    margin-left: auto;
    margin-right: 20px;
}

.kpi-card {
    background: white;
    border-radius: 8px;
    padding: 12px 16px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 120px;
}

.kpi-value {
    font-size: 1.8em;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 4px;
}

.kpi-value.kpi-excellent { color: #27ae60; }
.kpi-value.kpi-good { color: #f39c12; }
.kpi-value.kpi-low { color: #e74c3c; }

.kpi-label {
    font-size: 0.9em;
    font-weight: 600;
    color: #7f8c8d;
    text-transform: uppercase;
}

.kpi-sublabel {
    font-size: 0.7em;
    color: #95a5a6;
    margin-top: 2px;
}

.kpi-detail-panel {
    margin: 20px 0;
    padding: 0 20px;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.kpi-detail-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
}

.kpi-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.kpi-detail-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.1em;
}

.kpi-detail-value {
    font-size: 2.2em;
    font-weight: bold;
    color: #2c3e50;
}

.kpi-detail-value.excellent { color: #27ae60; }
.kpi-detail-value.good { color: #f39c12; }
.kpi-detail-value.low { color: #e74c3c; }
.kpi-detail-value.main-kpi { color: #3498db; }

.kpi-detail-description {
    color: #7f8c8d;
    font-size: 0.9em;
    margin-bottom: 8px;
}

.kpi-detail-formula {
    font-family: monospace;
    background: #ecf0f1;
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    color: #34495e;
}

.service-card.gerencia-enhanced {
    border-left: 3px solid #3498db;
}

.service-kpis-gerencia {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}

.service-kpi-item {
    text-align: center;
}

.service-kpi-value {
    display: block;
    font-size: 1.2em;
    font-weight: bold;
    color: #2c3e50;
}

.service-kpi-value.excellent { color: #27ae60; }
.service-kpi-value.good { color: #f39c12; }
.service-kpi-value.low { color: #e74c3c; }

.service-kpi-label {
    display: block;
    font-size: 0.7em;
    color: #7f8c8d;
    text-transform: uppercase;
    margin-top: 2px;
}

.navigation-links {
    display: flex;
    align-items: center;
}

.nav-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-size: 0.9em;
    transition: background-color 0.2s;
}

.nav-link:hover {
    background: #2980b9;
    color: white;
    text-decoration: none;
}

.nav-icon {
    font-size: 1.1em;
}

@media (max-width: 768px) {
    .productivity-summary-gerencia {
        flex-direction: column;
        gap: 8px;
    }
    
    .kpi-card {
        min-width: auto;
        padding: 8px 12px;
    }
    
    .kpi-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Funcionalidade de pesquisa
document.getElementById('search').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const serviceCards = document.querySelectorAll('.service-card');
    const groupContainers = document.querySelectorAll('.group-container');
    
    if (searchTerm === '') {
        // Mostrar todos
        serviceCards.forEach(card => card.style.display = 'block');
        groupContainers.forEach(container => container.style.display = 'block');
        return;
    }
    
    // Filtrar serviços
    serviceCards.forEach(card => {
        const serviceName = card.dataset.serviceName || '';
        const isVisible = serviceName.includes(searchTerm);
        card.style.display = isVisible ? 'block' : 'none';
    });
    
    // Ocultar grupos sem serviços visíveis
    groupContainers.forEach(container => {
        const visibleServices = container.querySelectorAll('.service-card[style*="block"], .service-card:not([style*="none"])');
        container.style.display = visibleServices.length > 0 ? 'block' : 'none';
    });
});

document.getElementById('clearSearch').addEventListener('click', function() {
    document.getElementById('search').value = '';
    document.getElementById('search').dispatchEvent(new Event('input'));
});
</script>