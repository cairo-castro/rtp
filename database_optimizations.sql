-- =====================================================
-- RTP Hospital Dashboard - Database Optimizations
-- Performance improvements for dashboard queries
-- Author: Performance Optimization
-- Date: 2025-05-28
-- =====================================================

-- Add indexes for improved query performance on rtpdiario table
-- These indexes will speed up the main dashboard queries

-- 1. Composite index for main query pattern (unidade_id, servico_id, ano, mes)
CREATE INDEX IF NOT EXISTS idx_rtpdiario_unidade_servico_ano_mes 
ON rtpdiario (unidade_id, servico_id, ano, mes);

-- 2. Index for filtering by year and month
CREATE INDEX IF NOT EXISTS idx_rtpdiario_ano_mes 
ON rtpdiario (ano, mes);

-- 3. Index for service-based queries
CREATE INDEX IF NOT EXISTS idx_rtpdiario_servico_ano_mes 
ON rtpdiario (servico_id, ano, mes);

-- 4. Index for unit-based queries
CREATE INDEX IF NOT EXISTS idx_rtpdiario_unidade_ano_mes 
ON rtpdiario (unidade_id, ano, mes);

-- 5. Index for day-based sorting within month
CREATE INDEX IF NOT EXISTS idx_rtpdiario_dia 
ON rtpdiario (dia);

-- Indexes for servico table (if needed)
CREATE INDEX IF NOT EXISTS idx_servico_unidade 
ON servico (unidade_id);

CREATE INDEX IF NOT EXISTS idx_servico_grupo 
ON servico (grupo_id);

-- Indexes for pdt table (if needed)
CREATE INDEX IF NOT EXISTS idx_pdt_servico_unidade 
ON pdt (servico_id, unidade_id);

CREATE INDEX IF NOT EXISTS idx_pdt_dates 
ON pdt (data_inicio, data_fim);

-- Analysis queries to check index usage
-- Run these after creating indexes to verify performance improvements:

-- EXPLAIN SELECT s.id AS servico_id, s.natureza, u.nome AS unidade_nome
-- FROM servico s 
-- INNER JOIN unidade u ON s.unidade_id = u.id
-- LEFT JOIN rtpdiario r ON r.unidade_id = s.unidade_id AND r.servico_id = s.id AND r.ano = 2025 AND r.mes = 5
-- WHERE s.unidade_id = 1;

-- Check index usage:
-- SHOW INDEX FROM rtpdiario;
-- SHOW INDEX FROM servico;
-- SHOW INDEX FROM pdt;

-- =====================================================
-- Performance monitoring queries
-- =====================================================

-- Check table sizes
-- SELECT 
--     table_name AS 'Table',
--     ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
-- FROM information_schema.tables 
-- WHERE table_schema = DATABASE()
-- ORDER BY (data_length + index_length) DESC;

-- Check slow queries (if slow query log is enabled)
-- SELECT * FROM mysql.slow_log ORDER BY start_time DESC LIMIT 10;
