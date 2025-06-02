<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../../src/models/RelatorioModel.php';
// use Src\Models\RelatorioModel; // Removido para evitar erro de namespace

use RelatorioModel;

/**
 * Teste de integração com banco de dados
 * Valida consultas diretas com parâmetros específicos
 * 
 * Parâmetros de teste: mes=3, ano=2025, unidade=11
 */
class DatabaseIntegrationTest extends TestCase
{
    private $pdo;
    private $relatorioModel;
    
    // Parâmetros específicos do teste
    private const TEST_MES = 3;
    private const TEST_ANO = 2025;
    private const TEST_UNIDADE = 11;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Incluir dependências necessárias
        require_once __DIR__ . '/../../src/config/database.php';
        require_once __DIR__ . '/../../src/models/RelatorioModel.php';
        
        // Configurar conexão direta
        $this->pdo = getDatabaseConnection();
        $this->relatorioModel = new RelatorioModel();
        
        // Validar conexão
        $this->assertTrue($this->isConnectionValid(), 'Conexão com banco de dados deve estar ativa');
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->relatorioModel = null;
        parent::tearDown();
    }
    
    /**
     * Valida se a conexão com banco está funcionando
     */
    private function isConnectionValid(): bool
    {
        try {
            $stmt = $this->pdo->query('SELECT 1');
            return $stmt !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Teste principal: Leitura de dados do banco com parâmetros específicos
     * Parâmetros: mes=3, ano=2025, unidade=11
     */
    public function testLeituraDadosBancoParametrosEspecificos()
    {
        $startTime = microtime(true);
        
        // Executar consulta com parâmetros do teste
        $dados = $this->relatorioModel->obterRelatorioMensalPorGrupos(
            self::TEST_UNIDADE,
            self::TEST_MES,
            self::TEST_ANO
        );
        
        $endTime = microtime(true);
        $tempoExecucao = ($endTime - $startTime) * 1000; // em milissegundos
        
        // Validações básicas
        $this->assertIsArray($dados, 'Resultado deve ser um array');
        
        // Validar estrutura dos dados se existirem registros
        if (!empty($dados)) {
            $this->validarEstruturaDados($dados);
        }
        
        // Validar performance da consulta (deve ser < 1000ms)
        $this->assertLessThan(1000, $tempoExecucao, 
            "Consulta deve executar em menos de 1000ms. Tempo atual: {$tempoExecucao}ms");
        
        // Log dos resultados para análise
        echo "\n=== RESULTADO DO TESTE DE INTEGRAÇÃO ===\n";
        echo "Parâmetros: mes=" . self::TEST_MES . ", ano=" . self::TEST_ANO . ", unidade=" . self::TEST_UNIDADE . "\n";
        echo "Registros encontrados: " . count($dados) . "\n";
        echo "Tempo de execução: " . number_format($tempoExecucao, 2) . "ms\n";
        
        if (!empty($dados)) {
            echo "Primeira estrutura encontrada:\n";
            print_r($dados[0]);
        }
        
        return $dados;
    }
    
    /**
     * Valida a estrutura dos dados retornados
     */
    private function validarEstruturaDados(array $dados): void
    {
        $primeiroItem = $dados[0];
        
        // Validar estrutura do grupo
        $this->assertArrayHasKey('grupo_id', $primeiroItem, 'Deve conter grupo_id');
        $this->assertArrayHasKey('grupo_nome', $primeiroItem, 'Deve conter grupo_nome');
        $this->assertArrayHasKey('grupo_cor', $primeiroItem, 'Deve conter grupo_cor');
        $this->assertArrayHasKey('servicos', $primeiroItem, 'Deve conter array de serviços');
        
        // Validar estrutura dos serviços se existirem
        if (!empty($primeiroItem['servicos'])) {
            $primeiroServico = $primeiroItem['servicos'][0];
            
            $camposObrigatorios = [
                'servico_id', 'unidade_id', 'unidade_nome', 'natureza',
                'meta', 'total_agendados', 'executados', 'total_executados'
            ];
            
            foreach ($camposObrigatorios as $campo) {
                $this->assertArrayHasKey($campo, $primeiroServico, 
                    "Serviço deve conter campo: {$campo}");
            }
        }
    }
    
    /**
     * Teste específico: Validar se unidade existe no banco
     */
    public function testUnidadeExiste()
    {
        $nomeUnidade = $this->relatorioModel->obterNomeUnidade(self::TEST_UNIDADE);
        
        $this->assertNotEquals('Unidade não encontrada', $nomeUnidade, 
            'Unidade ' . self::TEST_UNIDADE . ' deve existir no banco');
        $this->assertNotEquals('Erro ao carregar unidade', $nomeUnidade, 
            'Não deve haver erro ao carregar unidade');
        
        echo "\nUnidade encontrada: {$nomeUnidade} (ID: " . self::TEST_UNIDADE . ")\n";
    }
    
    /**
     * Teste de performance: Múltiplas consultas consecutivas
     */
    public function testPerformanceMultiplasConsultas()
    {
        $tempos = [];
        $numeroTestes = 3;
        
        for ($i = 0; $i < $numeroTestes; $i++) {
            $startTime = microtime(true);
            
            $dados = $this->relatorioModel->obterRelatorioMensalPorGrupos(
                self::TEST_UNIDADE,
                self::TEST_MES,
                self::TEST_ANO
            );
            
            $endTime = microtime(true);
            $tempos[] = ($endTime - $startTime) * 1000;
        }
        
        $tempoMedio = array_sum($tempos) / count($tempos);
        $tempoMaximo = max($tempos);
        $tempoMinimo = min($tempos);
        
        // Validar que performance é consistente
        $this->assertLessThan(1500, $tempoMaximo, 
            "Tempo máximo deve ser < 1500ms. Atual: {$tempoMaximo}ms");
        $this->assertLessThan(1000, $tempoMedio, 
            "Tempo médio deve ser < 1000ms. Atual: {$tempoMedio}ms");
        
        echo "\n=== ANÁLISE DE PERFORMANCE ===\n";
        echo "Consultas executadas: {$numeroTestes}\n";
        echo "Tempo médio: " . number_format($tempoMedio, 2) . "ms\n";
        echo "Tempo mínimo: " . number_format($tempoMinimo, 2) . "ms\n";
        echo "Tempo máximo: " . number_format($tempoMaximo, 2) . "ms\n";
    }
    
    /**
     * Teste de validação: Consulta direta SQL com parâmetros
     */
    public function testConsultaDiretaSQL()
    {
        $query = "
            SELECT 
                COUNT(*) as total_servicos,
                COUNT(DISTINCT s.grupo_id) as total_grupos,
                COUNT(DISTINCT r.dia) as dias_com_dados
            FROM servico s
            LEFT JOIN rtpdiario r ON 
                r.unidade_id = s.unidade_id AND 
                r.servico_id = s.id AND
                r.ano = ? AND
                r.mes = ?
            WHERE s.unidade_id = ?
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, self::TEST_ANO, \PDO::PARAM_INT);
        $stmt->bindValue(2, self::TEST_MES, \PDO::PARAM_INT);
        $stmt->bindValue(3, self::TEST_UNIDADE, \PDO::PARAM_INT);
        
        $startTime = microtime(true);
        $stmt->execute();
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
        $endTime = microtime(true);
        
        $tempoExecucao = ($endTime - $startTime) * 1000;
        
        // Validações
        $this->assertIsArray($resultado, 'Resultado deve ser um array');
        $this->assertArrayHasKey('total_servicos', $resultado);
        $this->assertArrayHasKey('total_grupos', $resultado);
        $this->assertArrayHasKey('dias_com_dados', $resultado);
        
        // Performance da consulta direta deve ser muito rápida
        $this->assertLessThan(500, $tempoExecucao, 
            "Consulta direta deve ser < 500ms. Atual: {$tempoExecucao}ms");
        
        echo "\n=== ESTATÍSTICAS DA CONSULTA DIRETA ===\n";
        echo "Total de serviços: " . $resultado['total_servicos'] . "\n";
        echo "Total de grupos: " . $resultado['total_grupos'] . "\n";
        echo "Dias com dados: " . $resultado['dias_com_dados'] . "\n";
        echo "Tempo de execução: " . number_format($tempoExecucao, 2) . "ms\n";
    }
    
    /**
     * Teste de conectividade: Validar todas as tabelas necessárias
     */
    public function testValidarTabelasNecessarias()
    {
        $tabelas = ['servico', 'unidade', 'servico_grupo', 'rtpdiario', 'pdt', 'meta'];
        
        foreach ($tabelas as $tabela) {
            $query = "SELECT COUNT(*) as total FROM {$tabela} LIMIT 1";
            
            try {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute();
                $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                $this->assertIsArray($resultado, "Tabela {$tabela} deve ser acessível");
                $this->assertArrayHasKey('total', $resultado, "Tabela {$tabela} deve retornar contagem");
                
            } catch (\Exception $e) {
                $this->fail("Erro ao acessar tabela {$tabela}: " . $e->getMessage());
            }
        }
        
        echo "\nTodas as tabelas necessárias estão acessíveis ✓\n";
    }
    
    /**
     * Teste de dados: Verificar se existem dados para o período específico
     */
    public function testDadosExistemParaPeriodo()
    {
        $query = "
            SELECT 
                COUNT(*) as registros_rtpdiario,
                MIN(dia) as primeiro_dia,
                MAX(dia) as ultimo_dia,
                SUM(executados + executados_por_encaixe) as total_executados
            FROM rtpdiario 
            WHERE unidade_id = ? AND mes = ? AND ano = ?
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(1, self::TEST_UNIDADE, \PDO::PARAM_INT);
        $stmt->bindValue(2, self::TEST_MES, \PDO::PARAM_INT);
        $stmt->bindValue(3, self::TEST_ANO, \PDO::PARAM_INT);
        $stmt->execute();
        
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        echo "\n=== DADOS DO PERÍODO ===\n";
        echo "Período: " . self::TEST_MES . "/" . self::TEST_ANO . " - Unidade: " . self::TEST_UNIDADE . "\n";
        echo "Registros encontrados: " . $resultado['registros_rtpdiario'] . "\n";
        
        if ($resultado['registros_rtpdiario'] > 0) {
            echo "Primeiro dia: " . $resultado['primeiro_dia'] . "\n";
            echo "Último dia: " . $resultado['ultimo_dia'] . "\n";
            echo "Total executados: " . $resultado['total_executados'] . "\n";
        } else {
            echo "Nenhum dado encontrado para este período.\n";
        }
        
        // Se não há dados, pelo menos a consulta deve funcionar
        $this->assertIsArray($resultado, 'Consulta deve retornar resultado válido');
        $this->assertArrayHasKey('registros_rtpdiario', $resultado);
    }
}
