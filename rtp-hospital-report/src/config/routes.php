<?php

return [
    'GET' => [
        '/' => ['RelatorioController', 'index'],
        '/relatorio' => ['RelatorioController', 'index'],
        '/api/dados-diarios' => ['RelatorioController', 'getDadosDiarios'],
        '/csrf/refresh' => ['RelatorioController', 'refreshCsrf']
    ],
    'POST' => [
        '/relatorio/filtrar' => ['RelatorioController', 'filtrar'],
        '/api/save-settings' => ['RelatorioController', 'saveSettings']
    ]
];
