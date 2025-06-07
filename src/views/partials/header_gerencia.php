<!-- CABEÇALHO FIXO - Layout responsivo otimizado -->
<header class="dashboard-header fixed">
    <!-- Primeira linha do header -->
    <div class="header-wrapper">
        <div class="header-main-row">            <div class="logo-container">
                <?php $baseUrl = rtrim(BASE_URL, '/'); ?>
                <img src="<?php echo $baseUrl; ?>/assets/images/logo-emserh-em-png.png" alt="EMSERH" class="logo">
                <div class="header-title">
                    <h1>ACOMPANHAMENTO DIÁRIO DE PRODUTIVIDADE</h1>
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
        </div>        <div class="productivity-summary">
            <?php if (isset($produtividade_geral)): ?>
            <div class="productivity-value" aria-label="Produtividade atual">
                <?php echo formatarNumero($produtividade_geral, 2); ?>%
            </div>            <div class="productivity-label">Produtividade</div>
            <?php else: ?>
            <div class="productivity-value" aria-label="Produtividade não disponível">--</div>
            <div class="productivity-label">Produtividade</div>
            <?php endif; ?>
        </div>
          <!-- Produtividade Máxima (Soma Metas / Soma PDT) -->
        <div class="productivity-summary">
            <?php if (isset($produtividade_maxima)): ?>
            <div class="productivity-value" aria-label="Produtividade máxima possível">
                <?php echo formatarNumero($produtividade_maxima, 2); ?>%
            </div>
            <div class="productivity-label">Prod Máx</div>
            <?php else: ?>
            <div class="productivity-value" aria-label="Produtividade máxima não disponível">--</div>
            <div class="productivity-label">Prod Máx</div>
            <?php endif; ?>
        </div>
        
        <!-- Produtividade vs Produtividade Máxima (Realizado x Pactuado) -->
        <div class="productivity-summary">
            <?php if (isset($prod_vs_prod_max)): ?>
            <div class="productivity-value" aria-label="Relação produtividade atual vs máxima">
                <?php echo formatarNumero($prod_vs_prod_max, 2); ?>%
            </div>
            <div class="productivity-label">Prod vs Máx</div>
            <?php else: ?>
            <div class="productivity-value" aria-label="Relação não disponível">--</div>
            <div class="productivity-label">Prod vs Máx</div>
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
        <div class="header-second-row">            <div class="unit-filter">
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
            
            <!-- Link para página de gerência -->
            <div class="navigation-links">
                <a href="/gerencia<?php echo !empty($unidade) ? '?unidade=' . $unidade . '&mes=' . ($mes ?? date('n')) . '&ano=' . ($ano ?? date('Y')) : ''; ?>" class="nav-link">
                    <span class="nav-icon">👔</span>
                    Painel Gerencial
                </a>
            </div>
        </div>
    </div>
</header>

<!-- Meta viewport para responsividade (se não estiver já definido) -->
<script>
// Garantir que o viewport está configurado para responsividade
if (!document.querySelector('meta[name="viewport"]')) {
    const viewport = document.createElement('meta');
    viewport.name = 'viewport';
    viewport.content = 'width=device-width, initial-scale=1.0, user-scalable=yes';
    document.head.appendChild(viewport);
}

// Melhorar acessibilidade com atalhos de teclado
document.addEventListener('keydown', function(e) {
    // Alt + M para focar no filtro de mês
    if (e.altKey && e.key === 'm') {
        e.preventDefault();
        document.getElementById('mes')?.focus();
    }
    // Alt + A para focar no filtro de ano
    if (e.altKey && e.key === 'a') {
        e.preventDefault();
        document.getElementById('ano')?.focus();
    }
    // Alt + U para focar na seleção de unidade
    if (e.altKey && e.key === 'u') {
        e.preventDefault();
        document.getElementById('unidade')?.focus();
    }
    // Alt + S para focar na pesquisa
    if (e.altKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('search')?.focus();
    }
});
</script>
