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
    </main>      <!-- ApexCharts - Biblioteca moderna para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js" 
            crossorigin="anonymous"></script>
          <!-- Scripts locais - ApexCharts Implementation -->
    <?php 
    // Usar sempre relatorio.js (removemos a versão minificada para melhor manutenibilidade)
    $jsFile = 'relatorio.js';
    $jsPath = PUBLIC_PATH . '/assets/js/' . $jsFile;
    ?>
    <script src="/assets/js/<?php echo $jsFile; ?>?v=<?php echo file_exists($jsPath) ? filemtime($jsPath) : time(); ?>"></script>
</body>
</html>
