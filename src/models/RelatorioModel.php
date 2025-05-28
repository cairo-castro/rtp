<?php

/**
 * Model responsável pelos dados de relatórios de produtividade
 * 
 * @author Equipe EMSERH
 * @version 1.1.0
 */
class RelatorioModel {
    private $pdo;
    
    // Constantes para validação
    private const MAX_RESULTADOS = 1000;
    private const CACHE_TTL = 300; // 5 minutos
    
    public function __construct() {
        $this->pdo = getDatabaseConnection();
        
        // Configurar prepared statements
        $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    }
      /**
     * Obtém a lista de todas as unidades
     * 
     * @return array
     */
    public function obterUnidades(): array {
        try {
            $query = "SELECT id, nome FROM unidade ORDER BY nome LIMIT 1000";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Erro ao obter unidades: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtém todos os grupos de serviços ativos
     * 
     * @return array
     * @throws Exception
     */
    public function obterGruposServicos(): array {
        try {
            $query = "
                SELECT 
                    id, 
                    nome, 
                    descricao, 
                    cor 
                FROM servico_grupo 
                WHERE ativo = 1 
                ORDER BY nome 
                LIMIT ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, self::MAX_RESULTADOS, PDO::PARAM_INT);
            $stmt->execute();
              $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $grupos;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter grupos de serviços: " . $e->getMessage());
            return [];
        }
    }
      /**
     * Obtém o nome de uma unidade pelo ID
     * 
     * @param int $unidade_id
     * @return string
     */
    public function obterNomeUnidade($unidade_id): string {
        try {
            $query = "SELECT nome FROM unidade WHERE id = ?";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return 'Unidade não encontrada';
            }
            
            return $result['nome'];
            
        } catch (PDOException $e) {
            error_log("Erro ao obter nome da unidade {$unidade_id}: " . $e->getMessage());
            return 'Erro ao carregar unidade';
        }
    }
      /**
     * Obtém nomes dos meses usando helper de forma segura
     * 
     * @return array
     */
    public function obterMesesNomes(): array {
        return obterMesesNomes();
    }
      /**
     * Obtém o relatório mensal organizado por grupos
     * 
     * @param int $unidade_id
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function obterRelatorioMensalPorGrupos($unidade_id, $mes, $ano): array {
        try {
            // Query otimizada incluindo dados do grupo e tabela meta com validação temporal
            // Prioridade das metas: 1°PDT, 2°Meta temporal (tabela meta), 3°Meta fixa (tabela servico)
            $query = "
            SELECT 
                s.unidade_id,
                s.id AS servico_id,
                s.grupo_id,
                u.nome AS unidade_nome,
                s.natureza,
                sg.nome AS grupo_nome,
                sg.descricao AS grupo_descricao,
                sg.cor AS grupo_cor,
                CONCAT('01/', LPAD(?, 2, '0'), '/', ?) AS mes_agrupado,
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
                    r.ano = ? AND
                    r.mes = ?
                LEFT JOIN pdt p ON 
                    p.servico_id = s.id AND 
                    p.unidade_id = s.unidade_id AND
                    MAKEDATE(?, 1) BETWEEN 
                        COALESCE(p.data_inicio, '1900-01-01') AND 
                        COALESCE(p.data_fim, '2100-12-31')
                LEFT JOIN meta m ON 
                    m.servico_id = s.id AND
                    m.ativa = 1 AND
                    MAKEDATE(?, 1) BETWEEN 
                        COALESCE(m.data_inicio, '1900-01-01') AND 
                        COALESCE(m.data_fim, '2100-12-31')
            WHERE 
                s.unidade_id = ?            GROUP BY 
                s.unidade_id, s.id, s.grupo_id, u.nome, s.natureza, 
                sg.nome, sg.descricao, sg.cor,
                COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN 1 ELSE NULL END, s.meta, 0), 
                COALESCE(p.meta, 0), 
                CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END, 
                COALESCE(s.meta, 0)
            ORDER BY 
                sg.nome ASC, s.natureza ASC
            LIMIT ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(4, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(5, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(6, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(7, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->bindValue(8, self::MAX_RESULTADOS, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar dados por grupo
            $relatorio_agrupado = [];
            foreach ($resultado as $servico) {
                $grupo_id = $servico['grupo_id'] ?? 0;
                $grupo_nome = $servico['grupo_nome'] ?? 'Sem Grupo';
                $grupo_cor = $servico['grupo_cor'] ?? '#6B7280';
                
                if (!isset($relatorio_agrupado[$grupo_id])) {
                    $relatorio_agrupado[$grupo_id] = [
                        'grupo_id' => $grupo_id,
                        'grupo_nome' => $grupo_nome,
                        'grupo_cor' => $grupo_cor,
                        'servicos' => []
                    ];
                }
                  $relatorio_agrupado[$grupo_id]['servicos'][] = $servico;
            }
            
            return array_values($relatorio_agrupado);
            
        } catch (PDOException $e) {
            error_log("Erro ao obter relatório mensal por grupos: " . $e->getMessage());
            return [];
        }
    }
      /**
     * Obtém o relatório mensal (método original mantido para compatibilidade)
     * 
     * @param int $unidade_id
     * @param int $mes
     * @param int $ano
     * @return array
     */
    public function obterRelatorioMensal($unidade_id, $mes, $ano): array {
        try {
            // Query otimizada incluindo tabela meta com validação temporal
            // Prioridade das metas: 1°PDT, 2°Meta temporal (tabela meta), 3°Meta fixa (tabela servico)
            $query = "
            SELECT 
                s.unidade_id,
                s.id AS servico_id,
                s.grupo_id,
                u.nome AS unidade_nome,
                s.natureza,
                sg.nome AS grupo_nome,
                sg.cor AS grupo_cor,
                CONCAT('01/', LPAD(?, 2, '0'), '/', ?) AS mes_agrupado,
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
                    r.ano = ? AND
                    r.mes = ?
                LEFT JOIN pdt p ON 
                    p.servico_id = s.id AND 
                    p.unidade_id = s.unidade_id AND
                    MAKEDATE(?, 1) BETWEEN 
                        COALESCE(p.data_inicio, '1900-01-01') AND 
                        COALESCE(p.data_fim, '2100-12-31')
                LEFT JOIN meta m ON 
                    m.servico_id = s.id AND
                    m.ativa = 1 AND
                    MAKEDATE(?, 1) BETWEEN 
                        COALESCE(m.data_inicio, '1900-01-01') AND 
                        COALESCE(m.data_fim, '2100-12-31')            WHERE 
                s.unidade_id = ?
            GROUP BY 
                s.unidade_id, s.id, s.grupo_id, u.nome, s.natureza, 
                sg.nome, sg.cor,
                COALESCE(p.meta, CASE WHEN m.id IS NOT NULL THEN 1 ELSE NULL END, s.meta, 0), 
                COALESCE(p.meta, 0), 
                CASE WHEN m.id IS NOT NULL THEN 1 ELSE 0 END, 
                COALESCE(s.meta, 0)
            ORDER BY 
                s.natureza
            LIMIT ?
            ";
              $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(4, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(5, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(6, (int)$ano, PDO::PARAM_INT);
            $stmt->bindValue(7, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->bindValue(8, self::MAX_RESULTADOS, PDO::PARAM_INT);
            $stmt->execute();            
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $resultado;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter relatório mensal: " . $e->getMessage());
            return [];
        }
    }    /**
     * Obtém dados diários para um serviço
     */
    public function obterDadosDiarios($unidade_id, $servico_id, $mes, $ano) {
        return $this->buscarDadosReais($unidade_id, $servico_id, $mes, $ano);
    }/**
     * Calcula o dia da semana de forma performática usando timestamp
     * Mais rápido que usar DAYNAME no MySQL
     */
    private function calcularDiaSemana($dia, $mes, $ano) {
        // Array pré-definido para performance
        static $dias_semana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        
        // Usar mktime é mais rápido que criar DateTime
        $timestamp = mktime(0, 0, 0, $mes, $dia, $ano);
        $dia_numero = (int)date('w', $timestamp); // 0=domingo, 1=segunda, etc
        
        return $dias_semana[$dia_numero];
    }

    /**
     * Formatar nome do dia da semana de forma simples (mantido para compatibilidade)
     */
    private function formatarDiaSemana($diaSemana) {
        $dias = [
            'Monday' => 'Seg',
            'Tuesday' => 'Ter', 
            'Wednesday' => 'Qua',
            'Thursday' => 'Qui',
            'Friday' => 'Sex',
            'Saturday' => 'Sab',
            'Sunday' => 'Dom'
        ];
        
        return $dias[$diaSemana] ?? 'N/A';
    }/**
     * Busca dados reais do banco - OTIMIZADO e AGRUPADO por dia
     * Inclui dados pactuados da tabela agenda
     */
    private function buscarDadosReais($unidade_id, $servico_id, $mes, $ano) {
        try {            // Query otimizada SEM DAYNAME (que é lenta) - apenas dados essenciais
            $query = "
            SELECT 
                dia,
                SUM(agendados) as agendados,
                SUM(executados) as executados,
                SUM(executados_por_encaixe) as executados_por_encaixe
            FROM 
                rtpdiario
            WHERE 
                unidade_id = ? AND 
                servico_id = ? AND 
                mes = ? AND 
                ano = ?
            GROUP BY 
                dia
            ORDER BY 
                dia ASC
            LIMIT 31
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$servico_id, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(4, (int)$ano, PDO::PARAM_INT);
            $stmt->execute();
            
            $dados_reais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obter dados pactuados da agenda
            $dados_pactuados = $this->obterDadosPactuadosAgenda($unidade_id, $servico_id, $mes, $ano);
            
            // Processar dados agrupados por dia incluindo dados pactuados com cálculo performático
            $dados_formatados = [];
            foreach ($dados_reais as $dado) {
                $dia = (int)$dado['dia'];
                
                // Dados já agrupados por dia com soma dos valores + dados pactuados da agenda
                $dados_formatados[] = [
                    'dia' => $dia,
                    'dia_semana' => $this->calcularDiaSemana($dia, $mes, $ano), // Calcular no PHP (mais rápido)
                    'pactuado' => $dados_pactuados[$dia] ?? 0, // Dados da agenda
                    'agendado' => (int)$dado['agendados'],
                    'realizado' => (int)$dado['executados'] + (int)$dado['executados_por_encaixe']
                ];
            }
            
            return $dados_formatados;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados reais: " . $e->getMessage());
            return []; // Retornar array vazio em caso de erro
        }
    }    /**
     * Obtém dados diários de um serviço específico - SEMPRE 31 DIAS
     */
    public function obterDadosDiariosServico($unidade_id, $servico_id, $mes, $ano) {
        try {
            // Query otimizada para buscar todos os dados do mês
            $query_real = "
            SELECT 
                dia,
                SUM(agendados) as agendado,
                SUM(executados + executados_por_encaixe) as realizado
            FROM rtpdiario
            WHERE unidade_id = ? AND servico_id = ? AND mes = ? AND ano = ?
            GROUP BY dia
            ORDER BY dia ASC
            ";
            
            $stmt = $this->pdo->prepare($query_real);
            $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$servico_id, PDO::PARAM_INT);
            $stmt->bindValue(3, (int)$mes, PDO::PARAM_INT);
            $stmt->bindValue(4, (int)$ano, PDO::PARAM_INT);
            $stmt->execute();
            
            $dados_reais = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Converter para array indexado por dia para fácil acesso
            $dados_por_dia = [];
            foreach ($dados_reais as $dado) {
                $dados_por_dia[(int)$dado['dia']] = $dado;
            }
            
            // Obter dados pactuados da tabela agenda (com soma manhã + tarde)
            $dados_pactuados = $this->obterDadosPactuadosAgenda($unidade_id, $servico_id, $mes, $ano);
            
            // SEMPRE retornar 31 dias - criar array completo
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
     * Obtém dados pactuados da tabela agenda mapeando dia_semana para dias do calendário
     * SEMPRE retorna dados para 31 dias, mapeando corretamente dias da semana
     * 
     * @param int $unidade_id
     * @param int $servico_id  
     * @param int $mes
     * @param int $ano
     * @return array Array indexado por dia do mês (1-31) com valores pactuados
     */
    private function obterDadosPactuadosAgenda($unidade_id, $servico_id, $mes, $ano) {
        try {
            // Buscar registros da agenda para o serviço
            $query_agenda = "
            SELECT 
                dia_semana,
                SUM(consulta_por_dia) as total_consultas_dia
            FROM agenda
            WHERE unidade_id = ? AND servico_id = ?
            GROUP BY dia_semana
            ";
            
            $stmt = $this->pdo->prepare($query_agenda);
            $stmt->bindValue(1, (int)$unidade_id, PDO::PARAM_INT);
            $stmt->bindValue(2, (int)$servico_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $agenda_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Inicializar array para 31 dias (sempre retornar 31 dias)
            $dados_pactuados = [];
            for ($i = 1; $i <= 31; $i++) {
                $dados_pactuados[$i] = 0;
            }
            
            if (empty($agenda_dados)) {
                return $dados_pactuados; // Retornar array com zeros se não há dados na agenda
            }
            
            // Mapear registros da agenda por dia da semana (já somados)
            $agenda_por_dia_semana = [];
            foreach ($agenda_dados as $registro) {
                $dia_semana_normalizado = $this->normalizarDiaSemanaAgenda($registro['dia_semana']);
                $agenda_por_dia_semana[$dia_semana_normalizado] = (int)$registro['total_consultas_dia'];
            }
            
            // Mapear dias da semana da agenda para TODOS os 31 dias do calendário
            for ($dia = 1; $dia <= 31; $dia++) {
                // Verificar se o dia existe no mês (para evitar datas inválidas)
                $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
                if ($dia > $dias_no_mes) {
                    // Para dias que não existem no mês, manter como 0
                    continue;
                }
                
                $data = mktime(0, 0, 0, $mes, $dia, $ano);
                $dia_semana_numero = date('w', $data); // 0=domingo, 1=segunda, etc
                $dia_semana_nome = $this->obterNomeDiaSemanaSimplificado($dia_semana_numero);
                
                // Buscar valor correspondente na agenda
                if (isset($agenda_por_dia_semana[$dia_semana_nome])) {
                    $dados_pactuados[$dia] = $agenda_por_dia_semana[$dia_semana_nome];
                }
            }
            
            return $dados_pactuados;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter dados pactuados da agenda: " . $e->getMessage());
            // Retornar array com zeros em caso de erro
            $dados_vazios = [];
            for ($i = 1; $i <= 31; $i++) {
                $dados_vazios[$i] = 0;
            }
            return $dados_vazios;
        }
    }

    /**
     * Converte número do dia da semana para nome
     * 
     * @param int $numero 0=domingo, 1=segunda, etc
     * @return string
     */
    private function obterNomeDiaSemana($numero) {
        $dias = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terça',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sábado'
        ];
        
        return $dias[$numero] ?? '';
    }

    /**
     * Verifica se um dia_semana da agenda corresponde ao dia do calendário
     * 
     * @param string $dia_agenda Ex: "terça-manhã", "terça-tarde"
     * @param string $dia_calendario Ex: "terça"
     * @return bool
     */
    private function diaCorresponde($dia_agenda, $dia_calendario) {
        // Remover acentos e normalizar
        $dia_agenda = $this->normalizarDiaSemana($dia_agenda);
        $dia_calendario = $this->normalizarDiaSemana($dia_calendario);
        
        // Verificar se o dia da agenda contém o dia do calendário
        return strpos($dia_agenda, $dia_calendario) !== false;
    }

    /**
     * Normaliza o nome do dia da semana removendo acentos e convertendo para minúsculas
     * 
     * @param string $dia
     * @return string
     */
    private function normalizarDiaSemana($dia) {
        $dia = strtolower(trim($dia));
        
        // Mapeamento de acentos
        $acentos = [
            'ç' => 'c', 'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ú' => 'u', 'ü' => 'u'
        ];
        
        return str_replace(array_keys($acentos), array_values($acentos), $dia);
    }

    /**
     * Normaliza especificamente os dias da semana da agenda removendo sufixos (manhã/tarde)
     * e mapeando para formato padrão
     * 
     * @param string $dia_agenda Ex: "segunda-manhã", "terça-tarde", "quarta"
     * @return string Ex: "segunda", "terca", "quarta"
     */
    private function normalizarDiaSemanaAgenda($dia_agenda) {
        $dia = strtolower(trim($dia_agenda));
        
        // Remover acentos
        $acentos = [
            'ç' => 'c', 'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e', 'í' => 'i', 'ó' => 'o', 'ô' => 'o',
            'õ' => 'o', 'ú' => 'u', 'ü' => 'u'
        ];
        $dia = str_replace(array_keys($acentos), array_values($acentos), $dia);
        
        // Remover sufixos (manhã, tarde, manha, etc.)
        $sufixos = ['-manha', '-tarde', '-manhã', ' manha', ' tarde', ' manhã'];
        $dia = str_replace($sufixos, '', $dia);
        
        // Mapeamento para padrão simplificado
        $mapeamento = [
            'domingo' => 'domingo',
            'segunda' => 'segunda',
            'segunda-feira' => 'segunda',
            'terca' => 'terca',
            'terça' => 'terca',
            'terca-feira' => 'terca',
            'terça-feira' => 'terca',
            'quarta' => 'quarta',
            'quarta-feira' => 'quarta',
            'quinta' => 'quinta',
            'quinta-feira' => 'quinta',
            'sexta' => 'sexta',
            'sexta-feira' => 'sexta',
            'sabado' => 'sabado',
            'sábado' => 'sabado'
        ];
        
        return $mapeamento[$dia] ?? $dia;
    }    /**
     * Obtém nome simplificado do dia da semana para mapeamento com agenda
     * 
     * @param int $numero 0=domingo, 1=segunda, etc
     * @return string
     */
    private function obterNomeDiaSemanaSimplificado($numero) {
        $dias = [
            0 => 'domingo',
            1 => 'segunda',
            2 => 'terca',
            3 => 'quarta',
            4 => 'quinta',
            5 => 'sexta',
            6 => 'sabado'
        ];
        
        return $dias[$numero] ?? '';
    }    /**
     * OTIMIZAÇÃO CRÍTICA: Obtém dados diários de múltiplos serviços em uma única query
     * Resolve o problema N+1 queries que estava causando lentidão
     * INCLUI DADOS PACTUADOS DA AGENDA de forma otimizada
     * 
     * @param int $unidade_id
     * @param array $servicos_ids
     * @param int $mes
     * @param int $ano
     * @return array Array associativo organizado por servico_id
     */
    public function obterDadosDiariosMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano) {
        if (empty($servicos_ids)) return [];
        
        try {
            // Criar placeholders para prepared statement
            $placeholders = str_repeat('?,', count($servicos_ids) - 1) . '?';
            
            // Query otimizada - buscar todos os dados de uma vez
            $query = "
            SELECT 
                servico_id,
                dia,
                SUM(agendados) as agendado,
                SUM(executados + executados_por_encaixe) as realizado
            FROM rtpdiario
            WHERE unidade_id = ? AND servico_id IN ({$placeholders}) AND mes = ? AND ano = ?
            GROUP BY servico_id, dia
            ORDER BY servico_id, dia ASC
            ";
            
            $stmt = $this->pdo->prepare($query);
            
            // Parâmetros: unidade_id + todos os servicos_ids + mes + ano
            $params = array_merge(
                [(int)$unidade_id], 
                array_map('intval', $servicos_ids), 
                [(int)$mes, (int)$ano]
            );
            
            $stmt->execute($params);
            $todos_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // OTIMIZAÇÃO: Buscar dados da agenda para todos os serviços de uma vez
            $dados_agenda_todos = $this->obterDadosAgendaMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano);
            
            // Organizar dados por serviço
            $dados_por_servico = [];
            
            // Inicializar arrays vazios para todos os serviços
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
                    // pactuado já foi preenchido na inicialização
                }
            }
            
            error_log("OTIMIZAÇÃO: Dados diários carregados para " . count($servicos_ids) . " serviços em uma única query (COM agenda otimizada)");
            
            return $dados_por_servico;
            
        } catch (PDOException $e) {
            error_log("Erro ao buscar dados diários em lote: " . $e->getMessage());
            return [];
        }
    }
    /**
     * OTIMIZAÇÃO: Obtém dados pactuados da agenda para múltiplos serviços em uma única query
     * Evita N+1 queries na tabela agenda
     * 
     * @param int $unidade_id
     * @param array $servicos_ids
     * @param int $mes
     * @param int $ano
     * @return array Array[servico_id][dia] com valores pactuados
     */
    private function obterDadosAgendaMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano) {
        if (empty($servicos_ids)) return [];
        
        try {
            // Criar placeholders para prepared statement
            $placeholders = str_repeat('?,', count($servicos_ids) - 1) . '?';
            
            // Query otimizada - buscar dados da agenda para todos os serviços
            $query_agenda = "
            SELECT 
                servico_id,
                dia_semana,
                SUM(consulta_por_dia) as total_consultas_dia
            FROM agenda
            WHERE unidade_id = ? AND servico_id IN ({$placeholders})
            GROUP BY servico_id, dia_semana
            ";
            
            $stmt = $this->pdo->prepare($query_agenda);
            
            // Parâmetros: unidade_id + todos os servicos_ids
            $params = array_merge(
                [(int)$unidade_id], 
                array_map('intval', $servicos_ids)
            );
            
            $stmt->execute($params);
            $agenda_dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Organizar dados de agenda por serviço e mapear para dias do calendário
            $agenda_por_servico = [];
            
            // Inicializar array para todos os serviços
            foreach ($servicos_ids as $servico_id) {
                $agenda_por_servico[$servico_id] = [];
                
                // Criar 31 dias vazios
                for ($dia = 1; $dia <= 31; $dia++) {
                    $agenda_por_servico[$servico_id][$dia] = 0;
                }
            }
            
            // Processar dados da agenda
            foreach ($agenda_dados as $registro) {
                $servico_id = (int)$registro['servico_id'];
                $dia_semana_agenda = $registro['dia_semana'];
                $total_consultas = (int)$registro['total_consultas_dia'];
                
                // Normalizar dia da semana da agenda
                $dia_semana_normalizado = $this->normalizarDiaSemanaAgenda($dia_semana_agenda);
                
                // Mapear para todos os dias do mês que correspondem a este dia da semana
                for ($dia = 1; $dia <= 31; $dia++) {
                    // Verificar se o dia existe no mês
                    $dias_no_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
                    if ($dia > $dias_no_mes) {
                        continue;
                    }
                    
                    $data = mktime(0, 0, 0, $mes, $dia, $ano);
                    $dia_semana_numero = date('w', $data); // 0=domingo, 1=segunda, etc
                    $dia_semana_calendario = $this->obterNomeDiaSemanaSimplificado($dia_semana_numero);
                    
                    // Se corresponde ao dia da semana da agenda, atribuir valor
                    if ($dia_semana_normalizado === $dia_semana_calendario) {
                        $agenda_por_servico[$servico_id][$dia] = $total_consultas;
                    }
                }
            }
            
            error_log("OTIMIZAÇÃO: Dados da agenda carregados para " . count($servicos_ids) . " serviços em uma única query");
            
            return $agenda_por_servico;
            
        } catch (PDOException $e) {
            error_log("Erro ao obter dados da agenda em lote: " . $e->getMessage());
            
            // Retornar arrays vazios para todos os serviços em caso de erro
            $resultado_vazio = [];
            foreach ($servicos_ids as $servico_id) {
                $resultado_vazio[$servico_id] = [];
                for ($dia = 1; $dia <= 31; $dia++) {
                    $resultado_vazio[$servico_id][$dia] = 0;
                }
            }
            return $resultado_vazio;
        }
    }
}