<?php

return [
    'GET' => [
        '/' => ['RelatorioController', 'index'],
        '/relatorio' => ['RelatorioController', 'index'],
        '/api/dados-diarios' => ['RelatorioController', 'getDadosDiarios']
    ],
    'POST' => [
        '/relatorio/filtrar' => ['RelatorioController', 'filtrar']
    ]
];
