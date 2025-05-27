<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Acompanhamento Diário de Produtividade - RTP Hospital</title>
    
    <!-- Bootstrap CSS - removendo integrity para evitar bloqueios -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/relatorio.css?v=<?php echo filemtime(PUBLIC_PATH . '/assets/css/relatorio.css'); ?>">
      
    <!-- CSRF Token para JavaScript -->
    <?php if (isset($csrf_token)): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <?php endif; ?>
      <!-- Chart.js UMD version - compatível com browsers sem módulos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" 
            crossorigin="anonymous"></script>
    
    <!-- ChartJS Plugin Datalabels UMD version -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.umd.min.js" 
            crossorigin="anonymous"></script>
</head>
<body>
    <!-- Conteúdo da página -->
    <?php echo $content; ?>
      <!-- Scripts locais -->
    <script src="/assets/js/csrf.js?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/csrf.js'); ?>"></script>
    <script src="/assets/js/relatorio.js?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/relatorio.js'); ?>"></script>
    
    <!-- Script de segurança básica -->
    <script>
        // Prevenir console em produção
        if (location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
            console.log = console.warn = console.error = function() {};
        }
        
        // Adicionar timestamp para debug
        console.log('RTP Hospital - Carregado em:', new Date().toLocaleString('pt-BR'));
    </script>
</body>
</html>
