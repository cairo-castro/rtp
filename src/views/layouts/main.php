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
          rel="stylesheet" crossorigin="anonymous">    <!-- CSS Local com verificação de existência -->
    <?php 
    $cssPath = APP_ROOT . '/assets/css/relatorio.css';
    $cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();
    $baseUrl = rtrim(BASE_URL, '/');
    ?>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/assets/css/relatorio.css?v=<?php echo $cssVersion; ?>">
</head>
<body>
    <!-- Main Content -->
    <main class="main-content">
        <?php echo $content; ?>
    </main>
    
    <!-- ApexCharts - Biblioteca moderna para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js" 
            crossorigin="anonymous"></script>    <!-- Scripts locais com verificação de existência -->
    <?php 
    $jsFile = 'relatorio.js';
    $jsPath = APP_ROOT . '/assets/js/' . $jsFile;
    $jsVersion = file_exists($jsPath) ? filemtime($jsPath) : time();
    $baseUrl = rtrim(BASE_URL, '/');
    ?>
    <script src="<?php echo $baseUrl; ?>/assets/js/<?php echo $jsFile; ?>?v=<?php echo $jsVersion; ?>"></script>
</body>
</html>
