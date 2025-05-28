<?php

/**
 * Funções utilitárias OTIMIZADAS para o sistema de relatórios
 * Removidas funções desnecessárias para hospitais que funcionam 7 dias por semana
 */

/**
 * Retorna um array com os nomes dos meses em português
 */
function obterMesesNomes() {
    return [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
    ];
}

/**
 * Determina a cor para o serviço de forma consistente
 */
function determinarCorServico($natureza) {
    $cores = [
        '#008000',  // Verde
        '#a02222',  // Vermelho
        '#1e88e5',  // Azul
        '#9c27b0',  // Roxo
        '#ff9800',  // Laranja
        '#00acc1',  // Azul turquesa
        '#5e35b1',  // Roxo escuro
        '#546e7a',  // Azul acinzentado
    ];
    
    $hash = crc32($natureza);
    $index = abs($hash % count($cores));
    
    return $cores[$index];
}

/**
 * Formata número com separadores brasileiros
 */
function formatarNumero($numero, $decimais = 0) {
    return number_format($numero, $decimais, ',', '.');
}

/**
 * Calcula porcentagem de produtividade
 */
function calcularPorcentagemProdutividade($realizado, $meta) {
    if ($meta <= 0) return 0;
    return min(100, round(($realizado / $meta) * 100, 2));
}
