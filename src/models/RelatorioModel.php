<?php

/**
 * Model responsável pelos dados de relatórios de produtividade
 * 
 * @author Equipe EMSERH
 * @version 2.0.0 - Otimizado para performance
 */
class RelatorioModel {
    private $pdo;
    
    // Constantes otimizadas
    private const MAX_RESULTS = 1000;
    private const DAYS_WEEK = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
    
    public function __construct() {
        $this->pdo = getDatabaseConnection();
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }    /**
     * Obtém todas as unidades
     */
    public function obterUnidades(): array {
        try {
            $stmt = $this->pdo->prepare("SELECT id, nome FROM unidade ORDER BY nome LIMIT ?");
            $stmt->bindValue(1, self::MAX_RESULTS, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter unidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém grupos de serviços ativos
     */
    public function obterGruposServicos(): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, nome, descricao, cor 
                FROM servico_grupo 
                WHERE ativo = 1 
                ORDER BY nome 
                LIMIT ?
            ");
            $stmt->bindValue(1, self::MAX_RESULTS, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao obter grupos de serviços: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém nome de uma unidade
     */
    public function obterNomeUnidade($unidade_id): string {
        try {
            $stmt = $this->pdo->prepare("SELECT nome FROM unidade WHERE id = ?");
            $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['nome'] : 'Unidade não encontrada';
        } catch (PDOException $e) {
            error_log("Erro ao obter nome da unidade: " . $e->getMessage());
            return 'Erro ao carregar unidade';
        }
    }

    /**
     * Obtém nomes dos meses
     */
    public function obterMesesNomes(): array {
        return obterMesesNomes();
    }    /**
     * Obtém relatório mensal completo com meta PDT e temporal
     */
    public function obterRelatorioMensalPorGrupos($unidade_id, $mes, $ano): array {
        try {
            // Query otimizada com meta PDT e temporal
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.unidade_id,
                    s.id AS servico_id,
                    s.grupo_id,
                    u.nome AS unidade_nome,
                    s.natureza,
                    sg.nome AS grupo_nome,
                    sg.descricao AS grupo_descricao,
                    sg.cor AS grupo_cor,
                    COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN CAST(m.descricao AS UNSIGNED) ELSE s.meta END, 0) AS pactuado,
                    COALESCE(p.meta, 0) AS meta_pdt,
                    CASE WHEN m.id IS NOT NULL THEN CAST(m.descricao AS UNSIGNED) ELSE 0 END AS meta_temporal,
                    COALESCE(SUM(r.agendados), 0) AS total_agendados,
                    COALESCE(SUM(r.executados), 0) AS executados,
                    COALESCE(SUM(r.executados_por_encaixe), 0) AS total_executados_por_encaixe,
                    COALESCE(SUM(r.executados + r.executados_por_encaixe), 0) AS total_executados
                FROM servico s
                INNER JOIN unidade u ON s.unidade_id = u.id
                LEFT JOIN servico_grupo sg ON s.grupo_id = sg.id
                LEFT JOIN rtpdiario r ON r.unidade_id = s.unidade_id AND r.servico_id = s.id AND r.ano = ? AND r.mes = ?
                LEFT JOIN pdt p ON p.servico_id = s.id AND p.unidade_id = s.unidade_id 
                    AND (CONCAT(?, '-', LPAD(?, 2, '0'), '-01') BETWEEN COALESCE(p.data_inicio, '1900-01-01') AND COALESCE(p.data_fim, '2100-12-31')
                         OR (p.data_inicio IS NULL AND p.data_fim IS NULL))
                LEFT JOIN meta m ON m.servico_id = s.id AND m.ativa = 1
                    AND (CONCAT(?, '-', LPAD(?, 2, '0'), '-01') BETWEEN COALESCE(m.data_inicio, '1900-01-01') AND COALESCE(m.data_fim, '2100-12-31')
                         OR (m.data_inicio IS NULL AND m.data_fim IS NULL))
                WHERE s.unidade_id = ?
                GROUP BY s.unidade_id, s.id, s.grupo_id, u.nome, s.natureza, sg.nome, sg.cor
                ORDER BY sg.nome ASC, s.natureza ASC
                LIMIT ?
            ");
            
            $stmt->execute([
                (int)$ano, (int)$mes, (int)$ano, (int)$mes, 
                (int)$ano, (int)$mes, (int)$unidade_id, self::MAX_RESULTS
            ]);
            
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar por grupos
            $grupos = [];
            foreach ($resultado as $servico) {
                $grupo_id = $servico['grupo_id'] ?? 0;
                $grupo_nome = $servico['grupo_nome'] ?? 'Sem Grupo';
                
                if (!isset($grupos[$grupo_id])) {
                    $grupos[$grupo_id] = [
                        'grupo_id' => $grupo_id,
                        'grupo_nome' => $grupo_nome,
                        'grupo_cor' => $servico['grupo_cor'] ?? '#6B7280',
                        'servicos' => []
                    ];
                }
                
                $grupos[$grupo_id]['servicos'][] = $servico;
            }
            
            return array_values($grupos);
            
        } catch (PDOException $e) {
            error_log("Erro ao obter relatório mensal por grupos: " . $e->getMessage());
            return [];
        }
    }    /**
     * Obtém relatório mensal (compatibilidade)
     */
    public function obterRelatorioMensal($unidade_id, $mes, $ano): array {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    s.unidade_id,
                    s.id AS servico_id,
                    s.grupo_id,
                    u.nome AS unidade_nome,
                    s.natureza,
                    sg.nome AS grupo_nome,
                    sg.cor AS grupo_cor,
                    CONCAT('01/', LPAD(?, 2, '0'), '/', ?) AS mes_agrupado,
                    COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN CAST(m.descricao AS UNSIGNED) ELSE s.meta END, 0) AS meta,
                    COALESCE(p.meta, 0) AS meta_pdt,
                    CASE WHEN m.id IS NOT NULL THEN CAST(m.descricao AS UNSIGNED) ELSE 0 END AS meta_temporal,
                    COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN CAST(m.descricao AS UNSIGNED) ELSE s.meta END, 0) AS pactuado,
                    COALESCE(SUM(r.agendados), 0) AS total_agendados,
                    COALESCE(SUM(r.executados), 0) AS executados,
                    COALESCE(SUM(r.executados_por_encaixe), 0) AS total_executados_por_encaixe,
                    COALESCE(SUM(r.executados + r.executados_por_encaixe), 0) AS total_executados
                FROM servico s
                INNER JOIN unidade u ON s.unidade_id = u.id
                LEFT JOIN servico_grupo sg ON s.grupo_id = sg.id
                LEFT JOIN rtpdiario r ON r.unidade_id = s.unidade_id AND r.servico_id = s.id AND r.ano = ? AND r.mes = ?
                LEFT JOIN pdt p ON p.servico_id = s.id AND p.unidade_id = s.unidade_id 
                    AND (CONCAT(?, '-', LPAD(?, 2, '0'), '-01') BETWEEN COALESCE(p.data_inicio, '1900-01-01') AND COALESCE(p.data_fim, '2100-12-31')
                         OR (p.data_inicio IS NULL AND p.data_fim IS NULL))
                LEFT JOIN meta m ON m.servico_id = s.id AND m.ativa = 1
                    AND (CONCAT(?, '-', LPAD(?, 2, '0'), '-01') BETWEEN COALESCE(m.data_inicio, '1900-01-01') AND COALESCE(m.data_fim, '2100-12-31')
                         OR (m.data_inicio IS NULL AND m.data_fim IS NULL))
                WHERE s.unidade_id = ?
                GROUP BY s.unidade_id, s.id, s.grupo_id, u.nome, s.natureza, sg.nome, sg.cor
                ORDER BY s.natureza
                LIMIT ?
            ");
            
            $stmt->execute([
                (int)$mes, (int)$ano, (int)$ano, (int)$mes, 
                (int)$ano, (int)$mes, (int)$ano, (int)$mes, 
                (int)$unidade_id, self::MAX_RESULTS
            ]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao obter relatório mensal: " . $e->getMessage());
            return [];
        }
    }    /**
     * Obtém dados diários para um serviço
     */
    public function obterDadosDiarios($unidade_id, $servico_id, $mes, $ano) {
        return $this->obterDadosDiariosServico($unidade_id, $servico_id, $mes, $ano);
    }

    /**
     * Obtém dados diários completos para um serviço (31 dias)
     */
    public function obterDadosDiariosServico($unidade_id, $servico_id, $mes, $ano) {
        try {
            // Buscar dados reais do mês
            $stmt = $this->pdo->prepare("
                SELECT dia, SUM(agendados) as agendado, SUM(executados + executados_por_encaixe) as realizado
                FROM rtpdiario
                WHERE unidade_id = ? AND servico_id = ? AND mes = ? AND ano = ?
                GROUP BY dia
                ORDER BY dia ASC
            ");
            $stmt->execute([(int)$unidade_id, (int)$servico_id, (int)$mes, (int)$ano]);
            $dados_reais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converter para array indexado por dia
            $dados_por_dia = [];
            foreach ($dados_reais as $dado) {
                $dados_por_dia[(int)$dado['dia']] = $dado;
            }
            
            // Obter dados pactuados da agenda
            $dados_pactuados = $this->obterDadosPactuadosAgenda($unidade_id, $servico_id, $mes, $ano);
            
            // Criar array completo para 31 dias
            $dados_combinados = [];
            for ($dia = 1; $dia <= 31; $dia++) {
                $dado_dia = $dados_por_dia[$dia] ?? null;
                
                $dados_combinados[] = [
                    'dia' => $dia,
                    'dia_semana' => $this->calcularDiaSemana($dia, $mes, $ano),
                    'agendado' => $dado_dia ? (int)$dado_dia['agendado'] : 0,
                    'realizado' => $dado_dia ? (int)$dado_dia['realizado'] : 0,
                    'pactuado' => $dados_pactuados[$dia] ?? 0
                ];
            }
            
            return $dados_combinados;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter dados diários do serviço: " . $e->getMessage());
            return [];
        }
    }    /**
     * Calcula dia da semana de forma otimizada
     */
    private function calcularDiaSemana($dia, $mes, $ano) {
        static $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        $timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        return $dias_semana[date('w', $timestamp)];
    }

    /**
     * Obtém dados pactuados da agenda com soma manhã + tarde
     */
    private function obterDadosPactuadosAgenda($unidade_id, $servico_id, $mes, $ano) {
        try {
            // Buscar e agrupar dados da agenda (soma manhã + tarde)
            $stmt = $this->pdo->prepare("
                SELECT 
                    CASE 
                        WHEN dia_semana LIKE '%manhã%' OR dia_semana LIKE '%manha%' THEN 
                            REPLACE(REPLACE(REPLACE(REPLACE(dia_semana, '-manhã', ''), '-manha', ''), ' manhã', ''), ' manha', '')
                        WHEN dia_semana LIKE '%tarde%' THEN 
                            REPLACE(REPLACE(dia_semana, '-tarde', ''), ' tarde', '')
                        ELSE dia_semana
                    END as dia_base,
                    SUM(consulta_por_dia) as total_consultas_dia
                FROM agenda
                WHERE unidade_id = ? AND servico_id = ?
                GROUP BY dia_base
            ");
            $stmt->execute([(int)$unidade_id, (int)$servico_id]);
            $agenda_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Inicializar array para 31 dias
            $dados_pactuados = array_fill(1, 31, 0);
            
            // Mapear agenda para dias do calendário
            $agenda_por_dia_semana = [];
            foreach ($agenda_dados as $registro) {
                $dia_semana_normalizado = $this->normalizarDiaSemanaAgenda($registro['dia_base']);
                $agenda_por_dia_semana[$dia_semana_normalizado] = (int)$registro['total_consultas_dia'];
            }
            
            // Mapear para todos os dias do mês
            $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            for ($dia = 1; $dia <= min(31, $dias_no_mes); $dia++) {
                $data = mktime(0, 0, 0, $mes, $dia, $ano);
                $dia_semana_numero = date('w', $data);
                $dia_semana_nome = self::DAYS_WEEK[$dia_semana_numero];
                
                if (isset($agenda_por_dia_semana[$dia_semana_nome])) {
                    $dados_pactuados[$dia] = $agenda_por_dia_semana[$dia_semana_nome];
                }
            }
            
            return $dados_pactuados;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter dados pactuados da agenda: " . $e->getMessage());
            return array_fill(1, 31, 0);
        }
    }    /**
     * Normaliza nome do dia da semana removendo acentos
     */
    private function normalizarDiaSemanaAgenda($dia_agenda) {
        $dia = strtolower(trim($dia_agenda));
        
        // Remover acentos
        $acentos = ['ç' => 'c', 'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
                   'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o',
                   'õ' => 'o', 'ú' => 'u', 'ü' => 'u'];
        $dia = str_replace(array_keys($acentos), array_values($acentos), $dia);
        
        // Remover sufixos
        $sufixos = ['-manha', '-tarde', '-manhã', ' manha', ' tarde', ' manhã'];
        $dia = str_replace($sufixos, '', $dia);
        
        // Mapeamento padrão
        $mapeamento = [
            'domingo' => 'domingo', 'segunda' => 'segunda', 'segunda-feira' => 'segunda',
            'terca' => 'terca', 'terça' => 'terca', 'terca-feira' => 'terca', 'terça-feira' => 'terca',
            'quarta' => 'quarta', 'quarta-feira' => 'quarta',
            'quinta' => 'quinta', 'quinta-feira' => 'quinta',
            'sexta' => 'sexta', 'sexta-feira' => 'sexta',
            'sabado' => 'sabado', 'sábado' => 'sabado'
        ];
        
        return $mapeamento[$dia] ?? $dia;
    }    /**
     * Obtém dados diários para múltiplos serviços - OTIMIZAÇÃO CRÍTICA
     * Evita problema N+1 queries
     */
    public function obterDadosDiariosMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano) {
        if (empty($servicos_ids)) return [];
        
        try {
            $placeholders = str_repeat('?,', count($servicos_ids) - 1) . '?';
            
            // Query única para todos os serviços
            $stmt = $this->pdo->prepare("
                SELECT servico_id, dia, SUM(agendados) as agendado, SUM(executados + executados_por_encaixe) as realizado
                FROM rtpdiario
                WHERE unidade_id = ? AND servico_id IN ({$placeholders}) AND mes = ? AND ano = ?
                GROUP BY servico_id, dia
                ORDER BY servico_id, dia ASC
            ");
            
            $params = array_merge([(int)$unidade_id], array_map('intval', $servicos_ids), [(int)$mes, (int)$ano]);
            $stmt->execute($params);
            $todos_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Buscar dados da agenda para todos os serviços
            $dados_agenda_todos = $this->obterDadosAgendaMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano);
            
            // Inicializar dados para todos os serviços
            $dados_por_servico = [];
            foreach ($servicos_ids as $servico_id) {
                $dados_por_servico[$servico_id] = [];
                
                // Criar 31 dias vazios
                for ($dia = 1; $dia <= 31; $dia++) {
                    $dados_por_servico[$servico_id][] = [
                        'dia' => $dia,
                        'dia_semana' => $this->calcularDiaSemana($dia, $mes, $ano),
                        'agendado' => 0,
                        'realizado' => 0,
                        'pactuado' => $dados_agenda_todos[$servico_id][$dia] ?? 0
                    ];
                }
            }
            
            // Preencher dados reais
            foreach ($todos_dados as $dado) {
                $servico_id = (int)$dado['servico_id'];
                $dia = (int)$dado['dia'];
                
                if (isset($dados_por_servico[$servico_id]) && $dia >= 1 && $dia <= 31) {
                    $dados_por_servico[$servico_id][$dia - 1]['agendado'] = (int)$dado['agendado'];
                    $dados_por_servico[$servico_id][$dia - 1]['realizado'] = (int)$dado['realizado'];
                }
            }
            
            return $dados_por_servico;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados diários em lote: " . $e->getMessage());
            return [];
        }
    }    /**
     * Obtém dados da agenda para múltiplos serviços - OTIMIZAÇÃO
     */
    private function obterDadosAgendaMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano) {
        if (empty($servicos_ids)) return [];
        
        try {
            $placeholders = str_repeat('?,', count($servicos_ids) - 1) . '?';
            
            // Query única para todos os serviços da agenda
            $stmt = $this->pdo->prepare("
                SELECT 
                    servico_id,
                    CASE 
                        WHEN dia_semana LIKE '%manhã%' OR dia_semana LIKE '%manha%' THEN 
                            REPLACE(REPLACE(REPLACE(REPLACE(dia_semana, '-manhã', ''), '-manha', ''), ' manhã', ''), ' manha', '')
                        WHEN dia_semana LIKE '%tarde%' THEN 
                            REPLACE(REPLACE(dia_semana, '-tarde', ''), ' tarde', '')
                        ELSE dia_semana
                    END as dia_base,
                    SUM(consulta_por_dia) as total_consultas_dia
                FROM agenda
                WHERE unidade_id = ? AND servico_id IN ({$placeholders})
                GROUP BY servico_id, dia_base
            ");
            
            $params = array_merge([(int)$unidade_id], array_map('intval', $servicos_ids));
            $stmt->execute($params);
            $agenda_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Inicializar arrays para todos os serviços
            $agenda_por_servico = [];
            foreach ($servicos_ids as $servico_id) {
                $agenda_por_servico[$servico_id] = array_fill(1, 31, 0);
            }
            
            // Processar dados da agenda
            $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
            foreach ($agenda_dados as $registro) {
                $servico_id = (int)$registro['servico_id'];
                $dia_semana_normalizado = $this->normalizarDiaSemanaAgenda($registro['dia_base']);
                $total_consultas = (int)$registro['total_consultas_dia'];
                
                // Mapear para dias do calendário
                for ($dia = 1; $dia <= min(31, $dias_no_mes); $dia++) {
                    $data = mktime(0, 0, 0, $mes, $dia, $ano);
                    $dia_semana_numero = date('w', $data);
                    $dia_semana_calendario = self::DAYS_WEEK[$dia_semana_numero];
                    
                    if ($dia_semana_normalizado === $dia_semana_calendario) {
                        $agenda_por_servico[$servico_id][$dia] = $total_consultas;
                    }
                }
            }
            
            return $agenda_por_servico;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter dados da agenda em lote: " . $e->getMessage());
            
            // Retornar arrays vazios para todos os serviços
            $resultado_vazio = [];
            foreach ($servicos_ids as $servico_id) {
                $resultado_vazio[$servico_id] = array_fill(1, 31, 0);
            }
            return $resultado_vazio;
        }
    }
}