<?php

use PHPUnit\Framework\TestCase;

/**
 * Teste de integração validado com banco de dados real
 * Baseado nos resultados obtidos do teste direto
 * 
 * Resultados do teste direto:
 * - Unidade 11: POLICLINICA VILA LUIZAO
 * - 16 grupos encontrados, 30 serviços
 * - 23 dias com dados RTP em março/2025
 * - Performance: 345ms total (excelente)
 */
class DatabaseIntegrationValidatedTest extends TestCase
{
    private $pdo;
    private $relatorioModel;
    
    // Parâmetros específicos do teste - VALIDADOS
    private const TEST_MES = 3;
    private const TEST_ANO = 2025;
    private const TEST_UNIDADE = 11;
    private const TEST_UNIDADE_NOME = 'POLICLINICA VILA LUIZAO';
    
    protected function setUp(): void
    {
        parent::setUp();
        
        require_once __DIR__ . '/../../src/config/database.php';
        require_once __DIR__ . '/../../src/models/RelatorioModel.php';
        
        $this->pdo = getDatabaseConnection();
        $this->relatorioModel = new RelatorioModel();
        
        // Validar conectividade
        $this->assertInstanceOf(PDO::class, $this->pdo, 'Conexão PDO deve estar ativa');
    }
    
    /**
     * Teste principal: Leitura de dados com parâmetros validados
     * Parâmetros: mes=3, ano=2025, unidade=11
     */
    public function testLeituraDadosValidados()
    {
        $startTime = microtime(true);
        
        $dados = $this->relatorioModel->obterRelatorioMensalPorGrupos(
            self::TEST_UNIDADE,
            self::TEST_MES,
            self::TEST_ANO
        );
        
        $endTime = microtime(true);
        $tempoExecucao = ($endTime - $startTime) * 1000;
        
        // Validações baseadas nos resultados do teste direto
        $this->assertIsArray($dados, 'Resultado deve ser um array');
        $this->assertGreaterThan(0, count($dados), 'Deve retornar grupos de dados');
        
        // Validar performance (baseado no resultado: 113ms)
        $this->assertLessThan(500, $tempoExecucao, 
            "Consulta deve executar em menos de 500ms. Tempo atual: {$tempoExecucao}ms");
        
        // Validar estrutura dos dados
        $primeiroGrupo = $dados[0];
        $this->assertArrayHasKey('grupo_id', $primeiroGrupo);
        $this->assertArrayHasKey('grupo_nome', $primeiroGrupo);
        $this->assertArrayHasKey('grupo_cor', $primeiroGrupo);
        $this->assertArrayHasKey('servicos', $primeiroGrupo);
        
        // Validar que tem serviços
        $this->assertIsArray($primeiroGrupo['servicos']);
        $this->assertGreaterThan(0, count($primeiroGrupo['servicos']));
        
        // Validar estrutura do primeiro serviço
        $primeiroServico = $primeiroGrupo['servicos'][0];
        $camposObrigatorios = [
            'servico_id', 'unidade_id', 'unidade_nome', 'natureza',
            'meta', 'total_agendados', 'executados', 'total_executados'
        ];
        
        foreach ($camposObrigatorios as $campo) {
            $this->assertArrayHasKey($campo, $primeiroServico, "Campo {$campo} deve existir");
        }
        
        // Validar valores específicos
        $this->assertEquals(self::TEST_UNIDADE, $primeiroServico['unidade_id']);
        $this->assertEquals(self::TEST_UNIDADE_NOME, $primeiroServico['unidade_nome']);
        
        echo "\n✓ Teste validado - Grupos encontrados: " . count($dados);
        echo " - Tempo: " . number_format($tempoExecucao, 2) . "ms\n";
        
        return $dados;
    }
    
    /**
     * Teste específico: Validar unidade conhecida
     */
    public function testUnidadeValidada()
    {
        $nomeUnidade = $this->relatorioModel->obterNomeUnidade(self::TEST_UNIDADE);
        
        $this->assertEquals(self::TEST_UNIDADE_NOME, $nomeUnidade, 
            'Nome da unidade deve corresponder ao esperado');
        
        echo "\n✓ Unidade validada: {$nomeUnidade} (ID: " . self::TEST_UNIDADE . ")\n";
    }
    
    /**
     * Teste de estatísticas do período validado
     */
    public function testEstatisticasPeriodoValidado()
    {
        $query = "
            SELECT 
                COUNT(DISTINCT s.id) as total_servicos,
                COUNT(DISTINCT s.grupo_id) as total_grupos,
                COUNT(DISTINCT r.dia) as dias_com_dados,
                SUM(COALESCE(r.executados, 0) + COALESCE(r.executados_por_encaixe, 0)) as total_executados
            FROM servico s
            LEFT JOIN rtpdiario r ON 
                r.unidade_id = s.unidade_id AND 
                r.servico_id = s.id AND
                r.ano = ? AND r.mes = ?
            WHERE s.unidade_id = ?
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([self::TEST_ANO, self::TEST_MES, self::TEST_UNIDADE]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Validações baseadas no teste direto
        $this->assertGreaterThan(0, $stats['total_servicos'], 'Deve ter serviços cadastrados');
        $this->assertGreaterThan(0, $stats['total_grupos'], 'Deve ter grupos diferentes');
        $this->assertGreaterThan(0, $stats['dias_com_dados'], 'Deve ter dias com dados RTP');
        
        // Validar valores aproximados (podem variar com atualizações dos dados)
        $this->assertGreaterThanOrEqual(20, $stats['total_servicos'], 'Deve ter pelo menos 20 serviços');
        $this->assertGreaterThanOrEqual(10, $stats['total_grupos'], 'Deve ter pelo menos 10 grupos');
        $this->assertGreaterThanOrEqual(15, $stats['dias_com_dados'], 'Deve ter pelo menos 15 dias com dados');
        
        echo "\n✓ Estatísticas validadas:";
        echo "\n  - Serviços: " . $stats['total_servicos'];
        echo "\n  - Grupos: " . $stats['total_grupos'];
        echo "\n  - Dias com dados: " . $stats['dias_com_dados'];
        echo "\n  - Total executados: " . $stats['total_executados'] . "\n";
    }
    
    /**
     * Teste de performance consistente
     */
    public function testPerformanceConsistente()
    {
        $tempos = [];
        $numeroTestes = 5;
        
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
        $desvio = sqrt(array_sum(array_map(function($x) use ($tempoMedio) { 
            return pow($x - $tempoMedio, 2); 
        }, $tempos)) / count($tempos));
        
        // Validações de performance baseadas no teste direto (113ms)
        $this->assertLessThan(1000, $tempoMaximo, 'Tempo máximo deve ser < 1000ms');
        $this->assertLessThan(500, $tempoMedio, 'Tempo médio deve ser < 500ms');
        $this->assertLessThan(200, $desvio, 'Desvio padrão deve ser < 200ms (consistência)');
        
        echo "\n✓ Performance consistente validada:";
        echo "\n  - Tempo médio: " . number_format($tempoMedio, 2) . "ms";
        echo "\n  - Tempo mínimo: " . number_format($tempoMinimo, 2) . "ms";
        echo "\n  - Tempo máximo: " . number_format($tempoMaximo, 2) . "ms";
        echo "\n  - Desvio padrão: " . number_format($desvio, 2) . "ms";
        echo "\n  - Execuções: {$numeroTestes}\n";
    }
    
    /**
     * Teste de integridade dos dados
     */
    public function testIntegridadeDados()
    {
        $dados = $this->relatorioModel->obterRelatorioMensalPorGrupos(
            self::TEST_UNIDADE,
            self::TEST_MES,
            self::TEST_ANO
        );
        
        $totalServicosEncontrados = 0;
        $gruposComServicos = 0;
        $servicosComMeta = 0;
        $servicosComExecucoes = 0;
        
        foreach ($dados as $grupo) {
            if (!empty($grupo['servicos'])) {
                $gruposComServicos++;
                
                foreach ($grupo['servicos'] as $servico) {
                    $totalServicosEncontrados++;
                    
                    // Validar integridade dos dados numéricos
                    $this->assertIsNumeric($servico['servico_id'], 'ID do serviço deve ser numérico');
                    $this->assertIsNumeric($servico['unidade_id'], 'ID da unidade deve ser numérico');
                    $this->assertIsNumeric($servico['meta'], 'Meta deve ser numérica');
                    $this->assertIsNumeric($servico['total_agendados'], 'Total agendados deve ser numérico');
                    $this->assertIsNumeric($servico['total_executados'], 'Total executados deve ser numérico');
                    
                    // Validar valores lógicos
                    $this->assertGreaterThanOrEqual(0, $servico['meta'], 'Meta não pode ser negativa');
                    $this->assertGreaterThanOrEqual(0, $servico['total_agendados'], 'Agendados não pode ser negativo');
                    $this->assertGreaterThanOrEqual(0, $servico['total_executados'], 'Executados não pode ser negativo');
                    
                    if ($servico['meta'] > 0) $servicosComMeta++;
                    if ($servico['total_executados'] > 0) $servicosComExecucoes++;
                }
            }
        }
        
        // Validações de integridade
        $this->assertGreaterThan(0, $totalServicosEncontrados, 'Deve ter serviços válidos');
        $this->assertGreaterThan(0, $gruposComServicos, 'Deve ter grupos com serviços');
        
        echo "\n✓ Integridade dos dados validada:";
        echo "\n  - Total de serviços: {$totalServicosEncontrados}";
        echo "\n  - Grupos com serviços: {$gruposComServicos}";
        echo "\n  - Serviços com meta: {$servicosComMeta}";
        echo "\n  - Serviços com execuções: {$servicosComExecucoes}\n";
    }
    
    /**
     * Teste de conectividade e configuração
     */
    public function testConectividadeConfiguracao()
    {
        $config = getDatabaseConfig();
        
        // Validar configurações
        $this->assertNotEmpty($config['host'], 'Host deve estar configurado');
        $this->assertNotEmpty($config['dbname'], 'Nome do banco deve estar configurado');
        $this->assertNotEmpty($config['username'], 'Usuário deve estar configurado');
        $this->assertNotEmpty($config['password'], 'Senha deve estar configurada');
        
        // Testar consulta básica
        $stmt = $this->pdo->query('SELECT VERSION() as version, NOW() as current_time');
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('current_time', $result);
        
        echo "\n✓ Configuração validada:";
        echo "\n  - Host: " . $config['host'];
        echo "\n  - Database: " . $config['dbname'];
        echo "\n  - Versão MySQL: " . $result['version'];
        echo "\n  - Hora do servidor: " . $result['current_time'] . "\n";
    }
    
    protected function tearDown(): void
    {
        $this->pdo = null;
        $this->relatorioModel = null;
        parent::tearDown();
    }
}
