<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title>Acompanhamento Diário de Produtividade - RTP Hospital</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" 
          rel="stylesheet" crossorigin="anonymous">    <link rel="stylesheet" href="/assets/css/relatorio.css?v=<?php echo filemtime(PUBLIC_PATH . '/assets/css/relatorio.css'); ?>">
</head>
<body>
    <!-- Main Content -->
    <main class="main-content">
        <?php echo $content; ?>
    </main>
    
    <!-- Chart.js UMD version - compatível com browsers sem módulos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js" 
            crossorigin="anonymous"></script>
    
    <!-- ChartJS Plugin Datalabels - versão corrigida -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js" 
            crossorigin="anonymous"></script>        <!-- Scripts locais -->
    <script src="/assets/js/relatorio.js?v=<?php echo filemtime(PUBLIC_PATH . '/assets/js/relatorio.js'); ?>"></script>
</body>
</html>
