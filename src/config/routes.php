<?php

return [
    'GET' => [
        '/' => ['RelatorioController', 'index'],
        '/relatorio' => ['RelatorioController', 'index'],
        '/gerencia' => ['RelatorioController', 'gerencia'],
        '/api/dados-diarios' => ['RelatorioController', 'getDadosDiarios']
    ],
    'POST' => [
        '/relatorio/filtrar' => ['RelatorioController', 'filtrar']
    ]
];
