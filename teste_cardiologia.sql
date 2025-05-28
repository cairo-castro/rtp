-- SQL PARA TESTAR DADOS DE CARDIOLOGIA
-- Parâmetros: unidade=11, mes=3, ano=2025
-- Este SQL replica exatamente o que o sistema está fazendo

-- 1. PRIMEIRO: Buscar serviços de cardiologia na unidade 11
SELECT 
    s.id AS servico_id,
    s.natureza,
    s.grupo_id,
    sg.nome AS grupo_nome,
    sg.cor AS grupo_cor
FROM servico s
LEFT JOIN servico_grupo sg ON s.grupo_id = sg.id
WHERE s.unidade_id = 11 
  AND s.natureza LIKE '%CARDIO%'
ORDER BY s.natureza;

-- 2. QUERY PRINCIPAL - EXATAMENTE COMO NO SISTEMA
-- Esta é a query real do RelatorioModel.php->obterRelatorioMensalPorGrupos()
SELECT 
    s.unidade_id,
    s.id AS servico_id,
    s.grupo_id,
    u.nome AS unidade_nome,
    s.natureza,
    sg.nome AS grupo_nome,
    sg.descricao AS grupo_descricao,
    sg.cor AS grupo_cor,
    CONCAT('01/', LPAD(3, 2, '0'), '/', 2025) AS mes_agrupado,
    COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN 1 ELSE NULL END, s.meta, 0) AS meta,
    COALESCE(p.meta, 0) AS meta_pdt,
    CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END AS meta_temporal,
    COALESCE(s.meta, 0) AS pactuado,
    COALESCE(SUM(r.agendados), 0) AS total_agendados,
    COALESCE(SUM(r.executados), 0) AS executados,
    COALESCE(SUM(r.executados_por_encaixe), 0) AS total_executados_por_encaixe,
    COALESCE(SUM(r.executados + r.executados_por_encaixe), 0) AS total_executados
FROM 
    servico s
    INNER JOIN unidade u ON s.unidade_id = u.id
    LEFT JOIN servico_grupo sg ON s.grupo_id = sg.id
    LEFT JOIN rtpdiario r ON 
        r.unidade_id = s.unidade_id AND 
        r.servico_id = s.id AND
        r.ano = 2025 AND
        r.mes = 3
    LEFT JOIN pdt p ON 
        p.servico_id = s.id AND 
        p.unidade_id = s.unidade_id AND
        MAKEDATE(2025, 1) BETWEEN 
            COALESCE(p.data_inicio, '1900-01-01') AND 
            COALESCE(p.data_fim, '2100-12-31')
    LEFT JOIN meta m ON 
        m.servico_id = s.id AND
        m.ativa = 1 AND
        MAKEDATE(2025, 1) BETWEEN 
            COALESCE(m.data_inicio, '1900-01-01') AND 
            COALESCE(m.data_fim, '2100-12-31')
WHERE 
    s.unidade_id = 11
    AND s.natureza LIKE '%CARDIO%'  -- Filtrar apenas cardiologia
GROUP BY 
    s.unidade_id, s.id, s.grupo_id, u.nome, s.natureza, 
    sg.nome, sg.descricao, sg.cor,
    COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN 1 ELSE NULL END, s.meta, 0), 
    COALESCE(p.meta, 0), 
    CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END, 
    COALESCE(s.meta, 0)
ORDER BY 
    sg.nome ASC, s.natureza ASC;

-- 3. VERIFICAR SE O SERVIÇO É EXCLUÍDO PELO FILTRO meta_pdt > 0
-- Esta é a lógica no RelatorioController.php que pode estar excluindo cardiologia
SELECT 
    s.id AS servico_id,
    s.natureza,
    COALESCE(p.meta, 0) AS meta_pdt,
    CASE 
        WHEN COALESCE(p.meta, 0) > 0 THEN 'INCLUÍDO'
        ELSE 'EXCLUÍDO (meta_pdt = 0)'
    END AS status_filtro
FROM 
    servico s
    LEFT JOIN pdt p ON 
        p.servico_id = s.id AND 
        p.unidade_id = s.unidade_id AND
        MAKEDATE(2025, 1) BETWEEN 
            COALESCE(p.data_inicio, '1900-01-01') AND 
            COALESCE(p.data_fim, '2100-12-31')
WHERE 
    s.unidade_id = 11
    AND s.natureza LIKE '%CARDIO%'
ORDER BY s.natureza;

-- 4. DADOS DIÁRIOS DE CARDIOLOGIA (para gráficos)
-- Esta é a query do obterDadosDiariosServico()
SELECT 
    r.dia,
    SUM(r.agendados) as agendado,
    SUM(r.executados + r.executados_por_encaixe) as realizado
FROM rtpdiario r
INNER JOIN servico s ON s.id = r.servico_id
WHERE r.unidade_id = 11 
  AND s.natureza LIKE '%CARDIO%'
  AND r.mes = 3 
  AND r.ano = 2025
GROUP BY r.dia
ORDER BY r.dia ASC;

-- 5. VERIFICAR DADOS DA AGENDA (valores "pactuados")
SELECT 
    a.servico_id,
    s.natureza,
    a.dia_semana,
    SUM(a.consulta_por_dia) as total_consultas_dia
FROM agenda a
INNER JOIN servico s ON s.id = a.servico_id
WHERE a.unidade_id = 11 
  AND s.natureza LIKE '%CARDIO%'
GROUP BY a.servico_id, s.natureza, a.dia_semana
ORDER BY s.natureza, a.dia_semana;

-- 6. COMPARAÇÃO GERAL - VER TODOS OS DADOS EM UMA SÓ CONSULTA
SELECT 
    'SERVICO' as tipo,
    s.id as id,
    s.natureza as nome,
    'N/A' as dia,
    'N/A' as dia_semana,
    NULL as valor,
    COALESCE(p.meta, 0) AS meta_pdt,
    CASE 
        WHEN COALESCE(p.meta, 0) > 0 THEN 'SIM'
        ELSE 'NÃO (meta_pdt=0)'
    END AS aparece_no_sistema
FROM servico s
LEFT JOIN pdt p ON 
    p.servico_id = s.id AND 
    p.unidade_id = s.unidade_id AND
    MAKEDATE(2025, 1) BETWEEN 
        COALESCE(p.data_inicio, '1900-01-01') AND 
        COALESCE(p.data_fim, '2100-12-31')
WHERE s.unidade_id = 11 AND s.natureza LIKE '%CARDIO%'

UNION ALL

SELECT 
    'RTPDIARIO' as tipo,
    r.servico_id as id,
    s.natureza as nome,
    r.dia as dia,
    'N/A' as dia_semana,
    (r.executados + r.executados_por_encaixe) as valor,
    NULL as meta_pdt,
    'DADOS EXISTEM' as aparece_no_sistema
FROM rtpdiario r
INNER JOIN servico s ON s.id = r.servico_id
WHERE r.unidade_id = 11 
  AND s.natureza LIKE '%CARDIO%'
  AND r.mes = 3 
  AND r.ano = 2025
  AND (r.executados + r.executados_por_encaixe) > 0

UNION ALL

SELECT 
    'AGENDA' as tipo,
    a.servico_id as id,
    s.natureza as nome,
    'N/A' as dia,
    a.dia_semana as dia_semana,
    a.consulta_por_dia as valor,
    NULL as meta_pdt,
    'DADOS EXISTEM' as aparece_no_sistema
FROM agenda a
INNER JOIN servico s ON s.id = a.servico_id
WHERE a.unidade_id = 11 
  AND s.natureza LIKE '%CARDIO%'

ORDER BY tipo, id, dia;
