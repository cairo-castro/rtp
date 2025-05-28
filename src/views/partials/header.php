<!-- CABEÇALHO FIXO - Layout responsivo otimizado -->
<header class="dashboard-header fixed">
    <!-- Primeira linha do header -->
    <div class="header-main-row">
        <div class="logo-container">
            <img src="/assets/images/logo-emserh-em-png.png" alt="EMSERH" class="logo">
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
        </div>
        
        <div class="productivity-summary">
            <?php if (isset($produtividade_geral)): ?>
            <div class="productivity-value" aria-label="Produtividade atual">
                <?php echo formatarNumero($produtividade_geral, 2); ?>%
            </div>
            <div class="productivity-label">Produtividade</div>
            <?php else: ?>
            <div class="productivity-value" aria-label="Produtividade não disponível">--</div>
            <div class="productivity-label">Produtividade</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Segunda linha do header - Seleção de Unidade -->    <div class="header-second-row">
        <div class="unit-filter">
            <label for="unidade">Unidade:</label>
            <select name="unidade" id="unidade" class="unit-select" onchange="document.getElementById('mainForm').submit();" aria-label="Selecionar unidade">
                <option value="">Selecione a Unidade</option>
                <?php if (isset($unidades)): ?>
                    <?php foreach ($unidades as $u) { ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo (isset($unidade) && $unidade == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['nome']); ?>
                        </option>
                    <?php } ?>
                <?php endif; ?>
            </select>
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
});
</script>
