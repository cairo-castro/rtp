<?php

/**
 * Funções utilitárias para o sistema de relatórios
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
 * Retorna um array com os nomes dos dias da semana
 */
function obterDiasSemana() {
    return ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
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
        '#6d4c41',  // Marrom
        '#f44336',  // Vermelho claro
        '#3949ab',  // Azul índigo
        '#00897b',  // Verde escuro
        '#d81b60',  // Rosa escuro
        '#fb8c00',  // Laranja escuro
        '#43a047',  // Verde médio
    ];
    
    $hash = crc32($natureza);
    $index = abs($hash % count($cores));
    
    return $cores[$index];
}

/**
 * Calcula o número de dias úteis em um mês
 */
function calcularDiasUteis($mes, $ano) {
    // Validar parâmetros
    if (!is_numeric($mes) || !is_numeric($ano)) {
        error_log("RTP Error: Invalid parameters for calcularDiasUteis - mes: $mes, ano: $ano");
        return 22; // Retorna valor padrão
    }
    
    $mes = (int)$mes;
    $ano = (int)$ano;
      if ($mes < 1 || $mes > 12 || $ano < 1900 || $ano > 2100) {
        error_log("RTP Error: Invalid date range for calcularDiasUteis - mes: $mes, ano: $ano");
        return 22; // Retorna valor padrão
    }
    
    $dias_no_mes = date('t', mktime(0, 0, 0, $mes, 1, $ano));
    $dias_uteis = 0;
    
    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
        if (!checkdate($mes, $dia, $ano)) {
            continue; // Pula datas inválidas
        }
        
        $timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        if ($timestamp === false) {
            continue; // Pula se mktime falhar
        }
        
        $dia_semana = date('N', $timestamp); // 1 (segunda) até 7 (domingo)
        if ($dia_semana <= 5) { // Segunda a sexta
            $dias_uteis++;
        }
    }
    
    return $dias_uteis;
}

/**
 * Retorna o nome do dia da semana em português
 */
function obterNomeDiaSemana($mes, $dia, $ano) {
    $dias_semana = obterDiasSemana();
    
    // Validar parâmetros de data
    if (!checkdate($mes, $dia, $ano)) {
        error_log("RTP Error: Invalid date - mes: $mes, dia: $dia, ano: $ano");
        return 'Dom'; // Retorna domingo por padrão
    }
    
    $timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
    if ($timestamp === false) {
        error_log("RTP Error: mktime failed for date - mes: $mes, dia: $dia, ano: $ano");
        return 'Dom'; // Retorna domingo por padrão
    }
    
    $dia_semana_num = date('w', $timestamp); // 0=Dom, 1=Seg, ..., 6=Sáb
    
    return $dias_semana[$dia_semana_num];
}

/**
 * Calcula meta diária baseada no tipo de dia
 */
function calcularMetaDiaria($meta_mensal, $dia_semana, $dias_uteis) {
    if ($dia_semana == 'Dom') {
        return 0; // Domingo geralmente tem meta zero
    } elseif ($dia_semana == 'Sab') {
        return floor(($meta_mensal / $dias_uteis) * 0.5); // Sábado tem meta reduzida
    } else {
        return ceil($meta_mensal / $dias_uteis); // Dias úteis
    }
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
