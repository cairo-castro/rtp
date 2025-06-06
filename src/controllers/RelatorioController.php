<?php

// Incluir gerenciamento de sessão
require_once __DIR__ . '/../config/session.php';

/**
 * Controller responsável pelos relatórios de produtividade
 * 
 * @author Equipe EMSERH
 * @version 1.0.0
 */
class RelatorioController extends Controller {
    private $model;
      // Constantes para validação
    private const ANO_MINIMO = 2020;
    private const ANO_MAXIMO = 2030;
    private const MES_MINIMO = 1;
    private const MES_MAXIMO = 12;

    public function __construct() {
        $this->model = new RelatorioModel();
    }
    
    /**
     * Exibe o dashboard principal com os relatórios de produtividade organizados por grupos
     */
    public function index() {
        try {
            // Validar e sanitizar dados de entrada
            $inputData = $this->validateAndSanitizeInput();            $data = [
                'unidades' => $this->model->obterUnidades(),
                'meses_nomes' => $this->model->obterMesesNomes(),
                'unidade' => $inputData['unidade'],
                'ano' => $inputData['ano'],
                'mes' => $inputData['mes'],
                'data_atual' => $this->formatDateTime(),
                'relatorio_mensal' => [],
                'relatorio_por_grupos' => [],
                'unidade_nome' => '',
                'produtividade_geral' => 0,
                'dados_graficos' => [],
                'user_logged_in' => $inputData['user_logged_in'],
                'user_info' => $inputData['user_info']
            ];
            
            // Processar dados se unidade foi selecionada
            if (!empty($inputData['unidade'])) {
                $this->processarDadosUnidade($data, $inputData);
            }
              $this->render('relatorio/dashboard', $data);
              } catch (Exception $e) {
            error_log('Erro ao carregar dashboard: ' . $e->getMessage());
            // Renderizar com dados vazios em caso de erro
            $this->render('relatorio/dashboard', [
                'unidades' => [],
                'meses_nomes' => [],
                'unidade' => '',
                'ano' => date('Y'),
                'mes' => date('m'),
                'data_atual' => $this->formatDateTime(),
                'relatorio_mensal' => [],
                'relatorio_por_grupos' => [],
                'unidade_nome' => 'Erro ao carregar',
                'produtividade_geral' => 0,
                'dados_graficos' => []
            ]);
        }
    }    /**
     * Valida e obtém dados de entrada
     * Automaticamente usa a unidade do usuário logado se disponível
     * 
     * @return array
     */
    private function validateAndSanitizeInput(): array {
        $unidade = $_GET['unidade'] ?? '';
        $ano = $_GET['ano'] ?? date('Y');
        $mes = $_GET['mes'] ?? date('m');
        
        // Se não foi especificada unidade via GET e o usuário está logado,
        // usar automaticamente a unidade do usuário
        if (empty($unidade) && isUserLoggedIn()) {
            $userUnidade = getUserUnidade();
            if ($userUnidade) {
                $unidade = (string)$userUnidade;
            }
        }
        
        return [
            'unidade' => $unidade,
            'ano' => (int)$ano,
            'mes' => (int)$mes,
            'user_logged_in' => isUserLoggedIn(),
            'user_info' => getUserInfo()
        ];
    }
      
    /**
     * Formata data e hora atual
     * 
     * @return string
     */
    private function formatDateTime(): string {
        return date('d/m/Y H:i:s');
    }
      /**
     * Processa dados específicos de uma unidade com organização por grupos
     * 
     * @param array &$data
     * @param array $inputData
     * @return void
     */
    private function processarDadosUnidade(array &$data, array $inputData): void {
        try {
            // Obter dados organizados por grupos
            $data['relatorio_por_grupos'] = $this->model->obterRelatorioMensalPorGrupos(
                $inputData['unidade'], 
                $inputData['mes'], 
                $inputData['ano']
            );

            // Manter compatibilidade com o método original (lista simples)
            $data['relatorio_mensal'] = $this->model->obterRelatorioMensal(
                $inputData['unidade'], 
                $inputData['mes'], 
                $inputData['ano']
            );
            
            $data['unidade_nome'] = $this->model->obterNomeUnidade($inputData['unidade']);            $data['produtividade_geral'] = $this->calcularProdutividade($data['relatorio_mensal']);
              // Preparar dados para gráficos de forma segura (baseado nos grupos)
            $data['dados_graficos'] = $this->prepararDadosGraficosPorGrupos(
                $data['relatorio_por_grupos'], 
                $inputData
            );
            
        } catch (Exception $e) {
            // Log do erro e continue com dados vazios
            error_log("Erro ao processar dados da unidade {$inputData['unidade']}: " . $e->getMessage());
            $data['unidade_nome'] = 'Erro ao carregar unidade';
        }
    }
      /**
     * OTIMIZADO: Prepara dados para gráficos organizados por grupos - SEM N+1 QUERIES
     * 
     * @param array $relatorio_por_grupos
     * @param array $inputData
     * @return array
     */    private function prepararDadosGraficosPorGrupos(array $relatorio_por_grupos, array $inputData): array {
        $dadosGraficos = [];
        $indiceGlobal = 0;
        
        // OTIMIZAÇÃO CRÍTICA: Coletar todos os IDs dos serviços primeiro
        $todos_servicos_ids = [];
        foreach ($relatorio_por_grupos as $grupo) {
            foreach ($grupo['servicos'] as $servico) {
                if (isset($servico['servico_id']) && is_numeric($servico['servico_id'])) {
                    $todos_servicos_ids[] = (int)$servico['servico_id'];
                }
            }
        }
        
        // OTIMIZAÇÃO: Buscar TODOS os dados diários de uma vez só
        $todos_dados_diarios = $this->obterTodosDadosDiariosLote(
            $inputData['unidade'], 
            $todos_servicos_ids, 
            $inputData['mes'], 
            $inputData['ano']
        );
        
        foreach ($relatorio_por_grupos as $grupo) {
            foreach ($grupo['servicos'] as $servico) {
                  try {
                    // Validar dados do serviço
                    if (!isset($servico['servico_id']) || !is_numeric($servico['servico_id'])) {
                        continue;
                    }
                    
                    $servico_id = (int)$servico['servico_id'];
                      // OTIMIZAÇÃO: Usar dados já carregados em lote
                    $dadosDiarios = $todos_dados_diarios[$servico_id] ?? [];
                      $dadosGraficos[$indiceGlobal] = [
                        'id' => $indiceGlobal,                        'grupo_id' => (int)$grupo['grupo_id'],
                        'grupo_nome' => $grupo['grupo_nome'],
                        'grupo_cor' => $grupo['grupo_cor'],
                        'unidade_id' => (int)$inputData['unidade'],
                        'servico_id' => $servico_id,
                        'mes' => (int)$inputData['mes'],
                        'ano' => (int)$inputData['ano'],
                        'nome' => $servico['natureza'] ?? 'Serviço',
                        'meta_pdt' => (int)($servico['meta_pdt'] ?? 0),
                        'total_executados' => (int)($servico['total_executados'] ?? 0),
                        'total_pactuado' => (int)($servico['pactuado'] ?? 0),
                        'dadosDiarios' => $dadosDiarios
                    ];
                    
                    $indiceGlobal++;
                    
                } catch (Exception $e) {
                    error_log("Erro ao preparar dados do gráfico para serviço {$servico['servico_id']}: " . $e->getMessage());
                    continue;
                }
            }
        }
        
        return $dadosGraficos;
    }
      /**
     * Prepara dados para gráficos de forma segura (método original mantido para compatibilidade)
     * 
     * @param array $relatorioMensal
     * @param array $inputData
     * @return array
     */
    private function prepararDadosGraficos(array $relatorioMensal, array $inputData): array {
        $dadosGraficos = [];
        
        // OTIMIZAÇÃO: Remover limite - processar todos os gráficos disponíveis
        
        foreach ($relatorioMensal as $index => $servico) {
            try {
                // Validar dados do serviço
                if (!isset($servico['servico_id']) || !is_numeric($servico['servico_id'])) {
                    continue;
                }
                
                // OTIMIZAÇÃO: Usar apenas dados reais do banco
                $dadosDiarios = $this->model->obterDadosDiariosServico(
                    $inputData['unidade'], 
                    $servico['servico_id'], 
                    $inputData['mes'], 
                    $inputData['ano']
                );
                  $dadosGraficos[$index] = [
                    'id' => $index,
                    'unidade_id' => (int)$inputData['unidade'],
                    'servico_id' => (int)$servico['servico_id'],
                    'mes' => (int)$inputData['mes'],
                    'ano' => (int)$inputData['ano'],
                    'nome' => $servico['natureza'] ?? 'Serviço',
                    'meta_pdt' => (int)($servico['meta_pdt'] ?? 0),
                    'total_executados' => (int)($servico['total_executados'] ?? 0),
                    'total_pactuado' => (int)($servico['pactuado'] ?? 0),
                    'dadosDiarios' => $dadosDiarios
                ];
                
            } catch (Exception $e) {
                error_log("Erro ao preparar dados do gráfico para serviço {$servico['servico_id']}: " . $e->getMessage());
                continue;
            }
        }
        
        return $dadosGraficos;
    }    /**
     * Endpoint API para obter dados diários
     * 
     * @return void
     */
    public function getDadosDiarios(): void {
        $unidade = $_GET['unidade'] ?? '';
        $servico_id = $_GET['servico_id'] ?? '';
        $mes = $_GET['mes'] ?? date('m');
        $ano = $_GET['ano'] ?? date('Y');
        
        // Validar parâmetros básicos
        if (empty($unidade) || empty($servico_id) || !is_numeric($unidade) || !is_numeric($servico_id)) {
            $this->json(['error' => 'Parâmetros obrigatórios inválidos']);
            return;
        }
        
        try {
            $dados = $this->model->obterDadosDiarios($unidade, $servico_id, $mes, $ano);
            $this->json(['success' => true, 'dados' => $dados]);
        } catch (Exception $e) {
            $this->json(['error' => 'Erro ao obter dados: ' . $e->getMessage()]);
        }
    }
      /**
     * Calcula a produtividade geral do relatório
     * Considera apenas serviços com meta_pdt > 0 (meta válida da tabela PDT para o período)
     */
    private function calcularProdutividade($relatorio_mensal) {
        if (empty($relatorio_mensal)) return 0;
        
        $soma_produtividade = 0;
        $total_servicos = 0;
        
        foreach ($relatorio_mensal as $servico) {
            $meta_pdt = (int)$servico['meta_pdt'];
            $realizado = (int)$servico['total_executados'];
            
            // Só considera serviços que têm meta PDT válida para o período
            if ($meta_pdt > 0) {
                $soma_produtividade += min(100, ($realizado / $meta_pdt) * 100);
                $total_servicos++;
            }
        }        return $total_servicos > 0 ? $soma_produtividade / $total_servicos : 0;
    }
      /**
     * OTIMIZAÇÃO: Obtém dados diários de múltiplos serviços em uma única consulta
     * Evita o problema N+1 queries
     * 
     * @param int $unidade_id
     * @param array $servicos_ids
     * @param int $mes
     * @param int $ano
     * @return array
     */
    private function obterTodosDadosDiariosLote($unidade_id, $servicos_ids, $mes, $ano) {
        if (empty($servicos_ids)) return [];
        
        try {
            // Usar método otimizado do model
            return $this->model->obterDadosDiariosMultiplosServicos($unidade_id, $servicos_ids, $mes, $ano);
              } catch (Exception $e) {
            error_log("Erro ao obter dados diários em lote: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Exibe o painel gerencial com KPIs adicionais
     */
    public function gerencia() {
        try {
            // Validar e sanitizar dados de entrada
            $inputData = $this->validateAndSanitizeInput();

            $data = [
                'unidades' => $this->model->obterUnidades(),
                'meses_nomes' => $this->model->obterMesesNomes(),
                'unidade' => $inputData['unidade'],
                'ano' => $inputData['ano'],
                'mes' => $inputData['mes'],
                'data_atual' => $this->formatDateTime(),
                'relatorio_mensal' => [],
                'relatorio_por_grupos' => [],
                'unidade_nome' => '',
                'produtividade_geral' => 0,
                'produtividade_maxima' => 0,
                'prod_vs_prod_max' => 0,
                'dados_graficos' => [],
                'user_logged_in' => $inputData['user_logged_in'],
                'user_info' => $inputData['user_info']
            ];

            // Processar dados se unidade foi selecionada
            if (!empty($inputData['unidade'])) {
                $this->processarDadosUnidadeGerencia($data, $inputData);
            }

            $this->render('relatorio/gerencia', $data);

        } catch (Exception $e) {
            error_log('Erro ao carregar dashboard de gerência: ' . $e->getMessage());
            $this->render('error', ['message' => 'Erro interno do servidor. Tente novamente mais tarde.']);
        }
    }

    /**
     * Processa dados específicos para a página de gerência
     */
    private function processarDadosUnidadeGerencia(&$data, $inputData) {
        // Processar dados básicos (mesma lógica do dashboard principal)
        $this->processarDadosUnidade($data, $inputData);

        // Calcular estatísticas adicionais para gerência
        if (!empty($data['relatorio_por_grupos'])) {
            $this->calcularEstatisticasGerencia($data);
        }
    }

    /**
     * Calcula as estatísticas específicas para gerência
     */
    private function calcularEstatisticasGerencia(&$data) {
        $total_meta_pdt = 0;
        $total_pactuado = 0;
        $total_executados = 0;

        foreach ($data['relatorio_por_grupos'] as $grupo) {
            foreach ($grupo['servicos'] as $servico) {
                $meta_pdt = (float)($servico['meta_pdt'] ?? 0);
                $pactuado = (float)($servico['pactuado'] ?? 0);
                $executados = (float)($servico['total_executados'] ?? 0);

                $total_meta_pdt += $meta_pdt;
                $total_pactuado += $pactuado;
                $total_executados += $executados;
            }
        }

        // Produtividade Máxima = (Meta PDT / Pactuado) * 100
        if ($total_pactuado > 0) {
            $data['produtividade_maxima'] = ($total_meta_pdt / $total_pactuado) * 100;
        } else {
            $data['produtividade_maxima'] = 0;
        }

        // Prod vs Prod Max = (Executados / Meta PDT) * 100
        if ($total_meta_pdt > 0) {
            $data['prod_vs_prod_max'] = ($total_executados / $total_meta_pdt) * 100;
        } else {
            $data['prod_vs_prod_max'] = 0;
        }
    }
}