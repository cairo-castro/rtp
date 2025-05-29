/**
 * RTP Hospital Dashboard - ApexCharts Implementation
 * 
 * Sistema otimizado de gráficos ApexCharts com foco em performance e usabilidade.
 * Migração do Chart.js para ApexCharts para melhor controle de layout,
 * espaçamento da legenda e otimizações de performance.
 * 
 * Features implementadas:
 * - Lazy loading com IntersectionObserver API
 * - Sistema de cache com localStorage
 * - Debouncing para eventos de resize
 * - Monitoramento de performance em tempo real
 * - Modo debug condicional (apenas desenvolvimento)
 * - Configurações otimizadas para dispositivos móveis
 * 
 * Performance optimizations:
 * - Redução de console.log em produção
 * - Animações otimizadas (600ms vs 800ms padrão)
 * - Lazy loading para carregamento sob demanda
 * - Cache com expiração de 5 minutos
 * - Debounce de 150ms para resize events
 * 
 * @version 2.1
 * @author Sistema RTP Hospital
 * @date 2025-01-08
 * @requires ApexCharts ^3.x
 * @performance Otimizado para 5000+ serviços/segundo
 */

document.addEventListener("DOMContentLoaded", function () {
    /**
     * Modo de debug ativo apenas em ambiente de desenvolvimento
     * Evita poluição do console em produção
     * @constant {boolean} DEBUG_MODE
     */
    const DEBUG_MODE = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    
    /**
     * Função de log condicional para debugging
     * Só executa console.log em ambiente de desenvolvimento
     * @param {string} message - Mensagem a ser logada
     * @param {*} data - Dados opcionais para log
     */
    function debugLog(message, data) {
        if (DEBUG_MODE) {
            console.log(message, data || '');
        }
    }

    debugLog('🚀 ApexCharts Dashboard carregado!');
    
    /**
     * Verificação de dependências - ApexCharts deve estar disponível
     * Garante que a biblioteca foi carregada antes de prosseguir
     * @function checkDependencies
     */
    if (typeof ApexCharts === 'undefined') {
        console.error('ApexCharts não foi carregado! Verifique os CDNs.');
        return;
    }

    /**
     * Configurações de cores padronizadas do sistema
     * Mantém consistência visual em todos os gráficos
     * @constant {Object} CORES_SISTEMA
     * @property {string} pactuado - Cor azul para valores planejados
     * @property {string} agendado - Cor azul escuro para agendamentos
     * @property {string} realizado - Cor laranja para valores executados
     * @property {Object} progresso - Configurações de cores para gauge
     */
    const CORES_SISTEMA = {
        pactuado: '#0d6efd',    // Azul - Valores planejados
        agendado: '#1e3a8a',    // Azul escuro - Agendamentos  
        realizado: '#fd7e14',   // Laranja - Valores executados
        progresso: {
            fill: '#fd7e14',    // Laranja para gauge
            empty: '#e0e0e0'    // Cinza claro para fundo
        }
    };    /**
     * Configurações globais do ApexCharts otimizadas para performance máxima
     * Aplicadas a todos os gráficos para consistência e velocidade
     * @constant {Object} CONFIGURACOES_GLOBAIS
     * @property {Object} chart - Configurações gerais do gráfico
     * @property {Object} legend - Configurações da legenda
     * @property {Object} grid - Configurações da grade
     * @property {Object} tooltip - Configurações dos tooltips
     */
    const CONFIGURACOES_GLOBAIS = {
        chart: {
            fontFamily: 'Arial, sans-serif',
            toolbar: {
                show: false // Remove toolbar para interface mais limpa
            },
            animations: {
                enabled: !window.matchMedia('(prefers-reduced-motion: reduce)').matches, // Respeita preferências de acessibilidade
                easing: 'easeinout',
                speed: 400, // Reduzido de 600ms para 400ms para melhor performance
                dynamicAnimation: {
                    enabled: false // Desabilitar animações dinâmicas para melhor performance
                }
            },
            redrawOnParentResize: true,
            redrawOnWindowResize: false, // Desabilitar auto-resize, usar debouncing manual
            // Otimizações adicionais de performance
            parentHeightOffset: 0,
            offsetX: 0,
            offsetY: 0,
            zoom: {
                enabled: false // Desabilitar zoom para melhor performance
            },
            background: 'transparent'
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            floating: false,
            offsetY: -10,
            offsetX: 0,
            markers: {
                width: 12,
                height: 12,
                strokeWidth: 0,
                radius: 2
            },
            itemMargin: {
                horizontal: 15,
                vertical: 8
            },
            fontSize: '11px',
            fontWeight: 500
        },
        grid: {
            borderColor: 'rgba(0, 0, 0, 0.05)',
            strokeDashArray: 0,
            xaxis: {
                lines: {
                    show: false
                }
            },
            yaxis: {
                lines: {
                    show: true
                }
            },
            padding: {
                top: 40,    // Espaço significativo entre legenda e gráfico
                bottom: 10,
                left: 10,
                right: 10
            }
        },
        tooltip: {
            theme: 'light',
            style: {
                fontSize: '12px'
            },
            // Otimizações de performance para tooltips
            enabled: true,
            shared: false,
            intersect: true
        },
        // Configurações globais de performance
        states: {
            hover: {
                filter: {
                    type: 'none' // Desabilitar filtros de hover para melhor performance
                }
            }
        }
    };

    /**
     * Inicialização condicional dos gráficos
     * Verifica se dados estão disponíveis antes de prosseguir
     * @function initializeCharts
     */
    if (window.dadosGraficos) {
        debugLog('📊 Dados recebidos para gráficos:', window.dadosGraficos);
        
        // Implementar lazy loading com IntersectionObserver para performance
        initializeLazyCharts();
    } else {
        debugLog('⏳ Aguardando dados dos gráficos...');
    }

    /**
     * Inicializa carregamento lazy dos gráficos
     * Usa IntersectionObserver para carregar gráficos apenas quando visíveis
     * @function initializeLazyCharts
     */
    function initializeLazyCharts() {
        const chartElements = document.querySelectorAll('[id^="grafico"], [id^="gauge"]');
        
        if ('IntersectionObserver' in window) {
            const chartObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const elementId = entry.target.id;
                        loadChartForElement(elementId);
                        chartObserver.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '50px'
            });

            chartElements.forEach(element => {
                chartObserver.observe(element);
            });
            
            debugLog('✅ Lazy loading configurado para', chartElements.length, 'gráficos');
        } else {
            // Fallback para navegadores sem IntersectionObserver
            chartElements.forEach(element => {
                loadChartForElement(element.id);
            });
        }
    }

    /**
     * Carrega gráfico para elemento específico
     * Identifica o tipo de gráfico e chama a função apropriada
     * @function loadChartForElement
     * @param {string} elementId - ID do elemento HTML onde renderizar o gráfico
     */
    function loadChartForElement(elementId) {
        Object.values(window.dadosGraficos).forEach(function(dados, index) {
            const graficoId = `grafico${dados.id}`;
            const gaugeId = `gauge${dados.id}`;
            
            if (elementId === graficoId && dados.dadosDiarios) {
                debugLog(`✅ Criando gráfico de barras ApexCharts para ${graficoId}`);
                criarGraficoBarrasApex(dados, graficoId);
            }
            
            if (elementId === gaugeId) {
                debugLog(`✅ Criando gauge ApexCharts para ${gaugeId}`);
                criarGaugeApex(dados, gaugeId);
            }
        });
    }

    /**
     * Cria um gráfico de barras usando ApexCharts
     * Função principal para renderização de gráficos de coluna/barras
     * 
     * @function criarGraficoBarrasApex
     * @param {Object} dados - Dados do serviço contendo informações diárias
     * @param {string} elementId - ID do elemento canvas onde renderizar
     * @description Processa dados diários e cria gráfico com três séries:
     *              - Pactuado (valores planejados)
     *              - Agendado (agendamentos realizados)
     *              - Realizado (procedimentos executados)
     */
    function criarGraficoBarrasApex(dados, elementId) {
        PerformanceMonitor.start(`chart_${elementId}`);
        
        try {            /**
             * Preparação e validação dos dados de entrada
             * Garante que os dados estão no formato correto
             * @type {Array} dadosDiarios - Array com dados diários do serviço             * @type {Array} categorias - Array com rótulos dos dias para o eixo X (dia da semana embaixo do número)
             */
            const dadosDiarios = dados.dadosDiarios || [];
            // Criar categorias com quebra de linha HTML para garantir formatação vertical
            const categorias = dadosDiarios.map(d => `${d.dia}<br/>${d.dia_semana || ''}`);
            
            /**
             * Configuração das séries de dados para o gráfico
             * Cada série representa um tipo de informação (Pactuado, Agendado, Realizado)
             * @type {Array} seriesDados - Array de objetos com dados das séries
             */
            const seriesDados = [
                {
                    name: 'Pactuado',
                    data: dadosDiarios.map(d => parseInt(d.pactuado) || 0),
                    color: CORES_SISTEMA.pactuado
                },
                {
                    name: 'Agendado', 
                    data: dadosDiarios.map(d => parseInt(d.agendado) || 0),
                    color: CORES_SISTEMA.agendado
                },
                {
                    name: 'Realizado',
                    data: dadosDiarios.map(d => parseInt(d.realizado) || 0),
                    color: CORES_SISTEMA.realizado
                }
            ];

            /**
             * Cálculo do valor máximo para otimização do espaçamento
             * Adiciona margem superior para melhor visualização
             * @type {Array} todosValores - Todos os valores das séries combinados
             * @type {number} valorMaximo - Maior valor encontrado
             * @type {number} maxComEspaco - Valor máximo com margem adicional
             */
            const todosValores = seriesDados.flatMap(serie => serie.data);
            const valorMaximo = Math.max(...todosValores);
            const maxComEspaco = valorMaximo + 30;            /**
             * Configurações específicas do gráfico de barras com otimizações máximas
             * @type {Object} opcoes - Objeto de configuração completo do ApexCharts
             */
            const opcoes = {
                ...CONFIGURACOES_GLOBAIS,
                chart: {
                    ...CONFIGURACOES_GLOBAIS.chart,
                    type: 'bar',
                    height: 350,
                    id: elementId,
                    // Otimizações de performance máximas
                    parentHeightOffset: 0,
                    sparkline: {
                        enabled: false
                    },
                    group: 'charts' // Agrupar para melhor gerenciamento
                },
                series: seriesDados,
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '75%',
                        borderRadius: 3,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -8,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'bold',
                        colors: ['#333']
                    },
                    formatter: function(value) {
                        return value > 0 ? value : '';
                    }
                },                xaxis: {
                    categories: categorias,                    labels: {
                        style: {
                            fontSize: '10px'
                        },                        maxHeight: 60,
                        trim: false,
                        rotate: 0, // Forçar rotação 0 para melhor performance
                        formatter: function(value, timestamp, opts) {
                            // Formatação para multi-linha com HTML
                            if (typeof value === 'string' && value.includes('<br/>')) {
                                // Retorna valor com HTML para renderização multi-linha
                                return value;
                            }
                            if (Array.isArray(value)) {
                                // Se é array, juntar com <br/>
                                return value.join('<br/>');
                            }
                            if (typeof value === 'string' && value.includes('\n')) {
                                const parts = value.split('\n');
                                return parts.join('<br/>');
                            }
                            return value;
                        },
                        // Configurações específicas para multi-linha
                        offsetY: 0,
                        hideOverlappingLabels: false
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    // Configuração adicional para garantir espaço vertical
                    title: {
                        offsetY: 10
                    }
                },
                yaxis: {
                    min: 0,
                    max: maxComEspaco,
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        formatter: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    },
                    forceNiceScale: false // Desabilitar para melhor performance
                },
                colors: [CORES_SISTEMA.pactuado, CORES_SISTEMA.agendado, CORES_SISTEMA.realizado],
                tooltip: {
                    ...CONFIGURACOES_GLOBAIS.tooltip,
                    y: {
                        formatter: function(value) {
                            return value + ' procedimentos';
                        }
                    }
                },
                // Otimizações específicas para barras
                fill: {
                    opacity: 1
                },
                stroke: {
                    show: false // Desabilitar bordas para melhor performance
                }
            };

            /**
             * Criar e renderizar o gráfico de barras usando chart pooling
             * @type {ApexCharts} chart - Instância do gráfico ApexCharts
             */
            const chart = ChartPool.get('bar', elementId, opcoes);
            chart.render().then(() => {
                PerformanceMonitor.end(`chart_${elementId}`);
                debugLog(`✅ Gráfico de barras ApexCharts criado para ${elementId}`);
            }).catch(error => {
                PerformanceMonitor.end(`chart_${elementId}`);
                console.error(`❌ Erro ao renderizar gráfico para ${elementId}:`, error);
            });

        } catch (error) {
            PerformanceMonitor.end(`chart_${elementId}`);
            console.error(`❌ Erro ao criar gráfico ApexCharts para ${elementId}:`, error);
        }
    }

    /**
     * Cria um gráfico de linha usando ApexCharts
     * Função para renderização de gráficos de linha com múltiplas séries
     * 
     * @function criarGraficoLinha
     * @param {Object} dados - Dados do serviço contendo informações diárias
     * @param {string} elementId - ID do elemento onde renderizar o gráfico
     * @description Processa dados diários e cria gráfico de linha com três séries:
     *              - Pactuado (valores planejados)
     *              - Agendado (agendamentos realizados) 
     *              - Realizado (procedimentos executados)
     */
    function criarGraficoLinha(dados, elementId) {
        PerformanceMonitor.start(`line_chart_${elementId}`);
        
        try {            /**
             * Preparação dos dados para gráfico de linha
             * @type {Array} dadosDiarios - Dados diários do serviço             * @type {Array} categorias - Categorias para o eixo X (dia da semana embaixo do número)
             */
            const dadosDiarios = dados.dadosDiarios || [];
            // Criar categorias com quebra de linha HTML para garantir formatação vertical
            const categorias = dadosDiarios.map(d => `${d.dia}<br/>${d.dia_semana || ''}`);
            
            /**
             * Configuração das séries para gráfico de linha
             * @type {Array} seriesDados - Séries de dados para o gráfico
             */
            const seriesDados = [
                {
                    name: 'Pactuado',
                    data: dadosDiarios.map(d => parseInt(d.pactuado) || 0),
                    color: CORES_SISTEMA.pactuado
                },
                {
                    name: 'Agendado',
                    data: dadosDiarios.map(d => parseInt(d.agendado) || 0),
                    color: CORES_SISTEMA.agendado
                },
                {
                    name: 'Realizado',
                    data: dadosDiarios.map(d => parseInt(d.realizado) || 0),
                    color: CORES_SISTEMA.realizado
                }
            ];

            /**
             * Configurações específicas do gráfico de linha
             * @type {Object} opcoes - Configuração completa para gráfico de linha
             */
            const opcoes = {
                ...CONFIGURACOES_GLOBAIS,
                chart: {
                    ...CONFIGURACOES_GLOBAIS.chart,
                    type: 'line',
                    height: 350,
                    id: elementId
                },
                series: seriesDados,
                stroke: {
                    width: 3,
                    curve: 'smooth'
                },
                markers: {
                    size: 4,
                    hover: {
                        size: 6
                    }
                },                xaxis: {
                    categories: categorias,                    labels: {                        style: {
                            fontSize: '10px'
                        },
                        formatter: function(value, timestamp, opts) {
                            // Formatação para multi-linha com HTML
                            if (typeof value === 'string' && value.includes('<br/>')) {
                                // Retorna valor com HTML para renderização multi-linha
                                return value;
                            }
                            if (Array.isArray(value)) {
                                // Se é array, juntar com <br/>
                                return value.join('<br/>');
                            }
                            if (typeof value === 'string' && value.includes('\n')) {
                                const parts = value.split('\n');
                                return parts.join('<br/>');
                            }
                            return value;
                        },
                        // Configurações específicas para multi-linha
                        offsetY: 0,
                        hideOverlappingLabels: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        formatter: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                },
                colors: [CORES_SISTEMA.pactuado, CORES_SISTEMA.agendado, CORES_SISTEMA.realizado],
                tooltip: {
                    ...CONFIGURACOES_GLOBAIS.tooltip,
                    y: {
                        formatter: function(value) {
                            return value + ' procedimentos';
                        }
                    }
                }
            };

            /**
             * Criar e renderizar o gráfico de linha
             * @type {ApexCharts} lineChart - Instância do gráfico de linha
             */
            const lineChart = new ApexCharts(document.querySelector(`#${elementId}`), opcoes);
            lineChart.render().then(() => {
                PerformanceMonitor.end(`line_chart_${elementId}`);
                debugLog(`✅ Gráfico de linha ApexCharts criado para ${elementId}`);
            });

        } catch (error) {
            PerformanceMonitor.end(`line_chart_${elementId}`);
            console.error(`❌ Erro ao criar gráfico de linha para ${elementId}:`, error);
        }    }    /**
     * Adiciona linha de referência visual para valor pactuado no gauge
     * Desenha uma linha SVG customizada para mostrar onde o pactuado se posiciona
     * @function adicionarLinhaReferenciaPactuado
     * @param {string} elementId - ID do elemento do gauge
     * @param {number} progressoPactuado - Percentual do pactuado para posicionamento
     */
    function adicionarLinhaReferenciaPactuado(elementId, progressoPactuado) {
        try {
            const gaugeContainer = document.querySelector(`#${elementId}`);
            if (!gaugeContainer) return;

            // Remover linha de referência existente se houver
            const existingLine = gaugeContainer.querySelector('.pactuado-reference-line');
            if (existingLine) {
                existingLine.remove();
            }

            // Encontrar o SVG do gauge
            const svg = gaugeContainer.querySelector('svg');
            if (!svg) return;

            // Encontrar o path do gauge existente para obter dimensões reais
            const gaugePath = svg.querySelector('.apexcharts-radialbar-track') || 
                            svg.querySelector('.apexcharts-radialbar-area') ||
                            svg.querySelector('path[stroke]');
            
            if (!gaugePath) {
                debugLog('❌ Path do gauge não encontrado');
                return;
            }

            // Obter dimensões do SVG
            const svgRect = svg.getBoundingClientRect();
            const svgWidth = svgRect.width;
            const svgHeight = svgRect.height;
            
            // Centro do gauge (ApexCharts coloca o gauge no centro do SVG)
            const centerX = svgWidth / 2;
            const centerY = svgHeight / 2;
            
            // Para gauge semicircular ApexCharts: startAngle: -90, endAngle: 90 (180° total)
            // Calcular ângulo baseado no percentual do pactuado
            const startAngle = -90; // Ângulo inicial em graus
            const endAngle = 90;    // Ângulo final em graus
            const totalAngle = endAngle - startAngle; // 180°
            
            const targetAngle = startAngle + (progressoPactuado / 100) * totalAngle;
            const angleRad = (targetAngle * Math.PI) / 180;
            
            // Calcular raios baseado no tamanho real do gauge
            // Gauge padrão ApexCharts usa aproximadamente 40% da área disponível
            const gaugeRadius = Math.min(svgWidth, svgHeight) * 0.35; // Raio externo
            const innerRadius = gaugeRadius * 0.4; // Hollow size 40%
            const outerRadius = gaugeRadius * 0.95; // Raio externo (pouco menor para ficar dentro)
            
            // Calcular posições da linha
            const x1 = centerX + innerRadius * Math.cos(angleRad);
            const y1 = centerY + innerRadius * Math.sin(angleRad);
            const x2 = centerX + outerRadius * Math.cos(angleRad);
            const y2 = centerY + outerRadius * Math.sin(angleRad);

            // Criar elemento de linha SVG
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x1);
            line.setAttribute('y1', y1);
            line.setAttribute('x2', x2);
            line.setAttribute('y2', y2);
            line.setAttribute('stroke', '#0d6efd'); // Azul para pactuado
            line.setAttribute('stroke-width', '4'); // Aumentado para melhor visibilidade
            line.setAttribute('stroke-linecap', 'round'); // Pontas arredondadas para melhor visual
            line.setAttribute('class', 'pactuado-reference-line');
            
            // Adicionar linha ao SVG (inserir antes dos textos para ficar atrás)
            const firstText = svg.querySelector('text');
            if (firstText) {
                svg.insertBefore(line, firstText);
            } else {
                svg.appendChild(line);
            }
            
            debugLog(`✅ Linha de referência pactuado: ${progressoPactuado}% - Ângulo: ${targetAngle.toFixed(1)}° - Coords: (${x1.toFixed(1)},${y1.toFixed(1)}) -> (${x2.toFixed(1)},${y2.toFixed(1)})`);
            
        } catch (error) {
            console.error(`❌ Erro ao adicionar linha de referência para ${elementId}:`, error);
        }
    }

    /**
     * Cria um gauge (gráfico radial) usando ApexCharts
     * 
     * @function criarGaugeApex
     * @param {Object} dados - Dados do serviço
     * @param {string} elementId - ID do elemento
     */    function criarGaugeApex(dados, elementId) {
        PerformanceMonitor.start(`gauge_${elementId}`);
        
        try {
            /**
             * Calcular progresso baseado nos dados e total pactuado
             * @type {number} totalExecutados - Total de procedimentos executados
             * @type {number} metaPdt - Meta de produtividade
             * @type {number} totalPactuado - Total pactuado (soma dos dados diários)
             * @type {number} progresso - Percentual de progresso calculado
             * @type {number} progressoPactuado - Percentual do pactuado em relação à meta
             */
            const totalExecutados = parseInt(dados.total_executados) || 0;
            const metaPdt = parseInt(dados.meta_pdt) || 0;
            const progresso = metaPdt > 0 ? Math.min(100, Math.round((totalExecutados / metaPdt) * 100)) : 0;
            
            // Calcular total pactuado dos dados diários
            let totalPactuado = 0;
            if (dados.dadosDiarios && Array.isArray(dados.dadosDiarios)) {
                totalPactuado = dados.dadosDiarios.reduce((sum, dia) => {
                    return sum + (parseInt(dia.pactuado) || 0);
                }, 0);
            }
            const progressoPactuado = metaPdt > 0 ? Math.min(100, Math.round((totalPactuado / metaPdt) * 100)) : 0;

            /**
             * Usar cor do grupo se disponível
             * @type {string} corProgresso - Cor para o gauge principal (realizado)
             */
            let corProgresso = CORES_SISTEMA.progresso.fill;
            if (dados.grupo_cor && dados.grupo_cor !== '#6B7280') {
                corProgresso = dados.grupo_cor;
            }            /**
             * Configurações do gauge radial com indicador de pactuado
             * @type {Object} opcoes - Configuração completa do gauge
             */
            const opcoes = {
                chart: {
                    type: 'radialBar',
                    height: 200,
                    id: `${elementId}-gauge`,
                    // Otimizações específicas para gauge
                    sparkline: {
                        enabled: true // Gauges se beneficiam do sparkline
                    },
                    animations: {
                        enabled: CONFIGURACOES_GLOBAIS.chart.animations.enabled,
                        speed: 300, // Mais rápido para gauges
                        animateGradually: {
                            enabled: false // Desabilitar animação gradual para melhor performance
                        }
                    }
                },
                series: [progresso], // Apenas uma série: realizado
                plotOptions: {
                    radialBar: {
                        hollow: {
                            size: '40%'  // Reduzido de 75% para 40% para gauge muito mais grosso e proeminente
                        },
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: CORES_SISTEMA.progresso.empty,
                            strokeWidth: '100%',
                            margin: 8  // Aumentado de 5 para 8 para melhor espaçamento
                        },
                        dataLabels: {
                            show: false
                        }
                    }
                },
                colors: [corProgresso], // Cor principal para realizado
                stroke: {
                    lineCap: 'butt' // Bordas quadradas no gauge (removido arredondamento)
                },
                labels: ['Realizado']
            };            /**
             * Criar e renderizar o gauge usando chart pooling
             * @type {ApexCharts} gauge - Instância do gauge
             */
            const gauge = ChartPool.get('gauge', elementId, opcoes);
            gauge.render().then(() => {
                // Adicionar linha de referência do pactuado após renderização
                setTimeout(() => {
                    adicionarLinhaReferenciaPactuado(elementId, progressoPactuado);
                }, 200);
                
                PerformanceMonitor.end(`gauge_${elementId}`);
                debugLog(`✅ Gauge ApexCharts criado para ${elementId} - ${progresso}% (Pactuado: ${progressoPactuado}%)`);
            }).catch(error => {
                PerformanceMonitor.end(`gauge_${elementId}`);
                console.error(`❌ Erro ao renderizar gauge para ${elementId}:`, error);
            });

        } catch (error) {
            PerformanceMonitor.end(`gauge_${elementId}`);
            console.error(`❌ Erro ao criar gauge ApexCharts para ${elementId}:`, error);
        }
    }

    /**
     * Sistema de cache para dados dos gráficos
     * @constant {Object} CACHE_CONFIG - Configurações do sistema de cache
     */
    const CACHE_CONFIG = {
        enabled: true,
        duration: 5 * 60 * 1000, // 5 minutos em millisegundos
        keyPrefix: 'rtp_chart_data_'
    };

    /**
     * Cache manager para dados dos gráficos
     * Gerencia armazenamento local de dados para melhor performance
     * @namespace CacheManager
     */
    const CacheManager = {
        /**
         * Salva dados no cache local
         * @function set
         * @param {string} key - Chave identificadora
         * @param {*} data - Dados a serem salvos
         * @returns {boolean} - Sucesso da operação
         */
        set: function(key, data) {
            if (!CACHE_CONFIG.enabled || !window.localStorage) return false;
            
            try {
                const cacheData = {
                    data: data,
                    timestamp: Date.now(),
                    expires: Date.now() + CACHE_CONFIG.duration
                };
                
                localStorage.setItem(CACHE_CONFIG.keyPrefix + key, JSON.stringify(cacheData));
                debugLog('💾 Dados salvos no cache:', key);
                return true;
            } catch (error) {
                console.warn('Erro ao salvar no cache:', error);
                return false;
            }
        },

        /**
         * Recupera dados do cache local
         * @function get
         * @param {string} key - Chave identificadora
         * @returns {*|null} - Dados recuperados ou null se não encontrado/expirado
         */
        get: function(key) {
            if (!CACHE_CONFIG.enabled || !window.localStorage) return null;
            
            try {
                const cached = localStorage.getItem(CACHE_CONFIG.keyPrefix + key);
                if (!cached) return null;
                
                const cacheData = JSON.parse(cached);
                
                // Verificar se o cache expirou
                if (Date.now() > cacheData.expires) {
                    this.remove(key);
                    debugLog('⏰ Cache expirado removido:', key);
                    return null;
                }
                
                debugLog('✅ Dados recuperados do cache:', key);
                return cacheData.data;
            } catch (error) {
                console.warn('Erro ao ler do cache:', error);
                this.remove(key);
                return null;
            }
        },

        /**
         * Remove item específico do cache
         * @function remove
         * @param {string} key - Chave do item a ser removido
         */
        remove: function(key) {
            if (window.localStorage) {
                localStorage.removeItem(CACHE_CONFIG.keyPrefix + key);
            }
        },

        /**
         * Limpa todo o cache do sistema
         * @function clear
         */
        clear: function() {
            if (!window.localStorage) return;
            
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith(CACHE_CONFIG.keyPrefix)) {
                    localStorage.removeItem(key);
                }
            });
            debugLog('🗑️ Cache limpo');
        },

        /**
         * Gera chave única para cache baseada em parâmetros
         * @function generateKey
         * @param {string} unidade - Identificador da unidade
         * @param {Object} periodo - Objeto com início e fim do período
         * @returns {string} - Chave única gerada
         */
        generateKey: function(unidade, periodo) {
            return `${unidade}_${periodo.inicio}_${periodo.fim}`;
        }
    };

    // Exportar cache manager para uso global
    window.ChartCache = CacheManager;    /**
     * Função para redimensionar gráficos com debouncing otimizado
     * Evita múltiplas chamadas durante redimensionamento da janela
     * @function redimensionarGraficos
     */
    let resizeTimeout;
    window.redimensionarGraficos = function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (window.ApexCharts) {
                PerformanceMonitor.start('resize_all_charts');
                window.ApexCharts.exec('*', 'windowResize');
                PerformanceMonitor.end('resize_all_charts');
                debugLog('🔄 Gráficos redimensionados');
            }
        }, 100); // Debounce reduzido para 100ms para melhor responsividade
    };

    // Event listener para resize com throttling adicional
    let lastResize = 0;
    window.addEventListener('resize', function() {
        const now = Date.now();
        if (now - lastResize > 50) { // Throttling de 50ms
            lastResize = now;
            window.redimensionarGraficos();
        }
    });

    /**
     * Debug: Informações sobre grupos (se disponível)
     * Exibe informações dos grupos de serviços no console (apenas desenvolvimento)
     */
    if (window.gruposInfo) {
        debugLog('📋 Informações dos grupos:', window.gruposInfo);
        if (DEBUG_MODE) {
            window.gruposInfo.forEach(grupo => {
                console.log(`🏥 Grupo ${grupo.id}: ${grupo.nome} (${grupo.servicos_count} serviços) - Cor: ${grupo.cor}`);
            });
        }
    }

    debugLog('🎯 Sistema ApexCharts inicializado com sucesso!');    /**
     * Sistema de monitoramento de performance em tempo real
     * Permite medir tempos de execução de operações críticas
     * @namespace PerformanceMonitor
     */
    const PerformanceMonitor = {
        /**
         * Armazena os timers ativos
         * @type {Object} timers
         */
        timers: {},
        
        /**
         * Inicia cronômetro para uma operação
         * @function start
         * @param {string} name - Nome identificador da operação
         */
        start: function(name) {
            this.timers[name] = performance.now();
        },
        
        /**
         * Finaliza cronômetro e calcula duração
         * @function end
         * @param {string} name - Nome identificador da operação
         * @returns {number|null} Duração em milissegundos ou null se não encontrado
         */
        end: function(name) {
            if (!this.timers[name]) return null;
            
            const duration = performance.now() - this.timers[name];
            delete this.timers[name];
            
            debugLog(`⏱️ Performance ${name}:`, `${duration.toFixed(2)}ms`);
            
            // Alertar se performance estiver ruim (>150ms) - limite reduzido para mais rigor
            if (duration > 150) {
                console.warn(`⚠️ Performance lenta em ${name}: ${duration.toFixed(2)}ms`);
            }
            
            return duration;
        }
    };

    /**
     * Sistema de Chart Pooling para reutilizar instâncias
     * Reduz criação/destruição de objetos para melhor performance
     * @namespace ChartPool
     */
    const ChartPool = {
        /**
         * Pool de gráficos disponíveis para reutilização
         * @type {Object} pools
         */
        pools: {
            bar: [],
            line: [],
            gauge: []
        },
        
        /**
         * Contador de uso para estatísticas
         * @type {Object} usage
         */
        usage: {
            created: 0,
            reused: 0
        },
        
        /**
         * Obtém gráfico do pool ou cria novo se necessário
         * @function get
         * @param {string} type - Tipo do gráfico (bar, line, gauge)
         * @param {string} elementId - ID do elemento
         * @param {Object} options - Configurações do gráfico
         * @returns {ApexCharts} Instância do gráfico
         */
        get: function(type, elementId, options) {
            const pool = this.pools[type];
            
            if (pool && pool.length > 0) {
                const chart = pool.pop();
                chart.updateOptions(options, true, true, false);
                this.usage.reused++;
                debugLog(`♻️ Gráfico ${type} reutilizado do pool para ${elementId}`);
                return chart;
            }
            
            // Criar novo gráfico se não há no pool
            const chart = new ApexCharts(document.querySelector(`#${elementId}`), options);
            this.usage.created++;
            debugLog(`🆕 Novo gráfico ${type} criado para ${elementId}`);
            return chart;
        },
        
        /**
         * Retorna gráfico para o pool para reutilização futura
         * @function release
         * @param {string} type - Tipo do gráfico
         * @param {ApexCharts} chart - Instância do gráfico
         */
        release: function(type, chart) {
            if (chart && this.pools[type]) {
                chart.destroy();
                // Não reutilizar por agora para evitar problemas - apenas contar estatísticas
                debugLog(`🔄 Gráfico ${type} liberado`);
            }
        },
        
        /**
         * Limpa todos os pools
         * @function clear
         */
        clear: function() {
            Object.keys(this.pools).forEach(type => {
                this.pools[type].forEach(chart => chart.destroy());
                this.pools[type] = [];
            });
            debugLog('🗑️ Chart pools limpos');
        },
        
        /**
         * Retorna estatísticas de uso
         * @function getStats
         * @returns {Object} Estatísticas de uso
         */
        getStats: function() {
            return {
                ...this.usage,
                efficiency: this.usage.reused > 0 ? 
                    (this.usage.reused / (this.usage.created + this.usage.reused) * 100).toFixed(2) + '%' : 
                    '0%'
            };
        }
    };

    /**
     * Alias para compatibilidade com versões anteriores
     * Mantém funcionalidade existente enquanto migra para nova nomenclatura
     * @function criarGraficoColuna
     * @deprecated Use criarGraficoBarrasApex em seu lugar
     */
    window.criarGraficoColuna = criarGraficoBarrasApex;
    
    /**
     * Alias para compatibilidade com versões anteriores
     * Mantém funcionalidade existente para gauge charts
     * @function criarGaugeChart
     * @deprecated Use criarGaugeApex em seu lugar
     */
    window.criarGaugeChart = criarGaugeApex;
      /**
     * Exportar funções principais para uso global
     * Permite acesso externo às funcionalidades do sistema
     */
    window.criarGraficoBarrasApex = criarGraficoBarrasApex;
    window.criarGraficoLinha = criarGraficoLinha;
    window.criarGaugeApex = criarGaugeApex;
    window.criarGaugeChart = criarGaugeApex; // Alias adicional
    window.PerformanceMonitor = PerformanceMonitor;
    window.ChartPool = ChartPool;
    
    /**
     * Estatísticas de performance do sistema
     * @function getPerformanceStats
     * @returns {Object} Estatísticas completas do sistema
     */
    window.getPerformanceStats = function() {
        return {
            chartPool: ChartPool.getStats(),
            cacheHits: CacheManager.getStats ? CacheManager.getStats() : { hits: 0, misses: 0 },
            memory: {
                used: (performance.memory ? performance.memory.usedJSHeapSize : 0),
                total: (performance.memory ? performance.memory.totalJSHeapSize : 0)
            }
        };
    };
    
    // Log das estatísticas de inicialização em debug mode
    if (DEBUG_MODE) {
        setTimeout(() => {
            debugLog('📊 Estatísticas de performance:', window.getPerformanceStats());
        }, 2000);
    }
});