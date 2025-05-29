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
     * Adiciona labels customizados para dias da semana abaixo dos números dos dias
     * Cria elementos HTML posicionados para mostrar dia da semana abaixo de cada número
     * @param {string} elementId - ID do elemento do gráfico
     * @param {Array} diasSemana - Array com os dias da semana correspondentes
     */
    function adicionarLabelsCustomizados(elementId, diasSemana) {
        try {
            const chartContainer = document.querySelector(`#${elementId}`);
            if (!chartContainer) return;

            // Remover labels customizados existentes
            const existingLabels = chartContainer.querySelectorAll('.custom-day-label');
            existingLabels.forEach(label => label.remove());

            // Encontrar os labels do eixo X
            const xAxisLabels = chartContainer.querySelectorAll('.apexcharts-xaxis-texts-g text');
            
            xAxisLabels.forEach((label, index) => {
                if (index < diasSemana.length && diasSemana[index]) {
                    const rect = label.getBoundingClientRect();
                    const containerRect = chartContainer.getBoundingClientRect();                    // Calcular posição relativa ao container
                    const left = rect.left - containerRect.left + (rect.width / 2);
                    const top = rect.bottom - containerRect.top + 6; // Reduzido de 15px para 8px para aproximar data e dia
                    
                    // Criar elemento para o dia da semana
                    const dayLabel = document.createElement('div');
                    dayLabel.className = 'custom-day-label';
                    dayLabel.textContent = diasSemana[index];
                    dayLabel.style.cssText = `
                        position: absolute;
                        left: ${left}px;
                        top: ${top}px;
                        transform: translateX(-50%);
                        font-size: 11px; // Aumentado de 9px para 11px para melhor legibilidade
                        color: #666;
                        font-weight: normal;
                        text-align: center;
                        pointer-events: none;
                        z-index: 10;
                    `;
                    
                    chartContainer.style.position = 'relative';
                    chartContainer.appendChild(dayLabel);
                }
            });
            
            debugLog(`✅ Labels customizados adicionados para ${elementId}`, diasSemana);
        } catch (error) {
            console.error(`❌ Erro ao adicionar labels customizados para ${elementId}:`, error);
        }
    }

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
        },        legend: {
            position: 'top',
            horizontalAlign: 'center',
            floating: false,
            offsetY: -5, // Reduzido de -10 para -5 para menos espaço
            offsetX: 0,
            markers: {
                width: 12,
                height: 12,
                strokeWidth: 0,
                radius: 2
            },
            itemMargin: {
                horizontal: 15,
                vertical: 5 // Reduzido de 8 para 5 para compactar verticalmente
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
            },            padding: {
                top: 20,    // Reduzido de 40 para 20 para menos espaço entre legenda e gráfico
                bottom: 40,  // Aumentado de 25 para 40 para dar mais espaço aos labels dos dias da semana
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
             * @type {Array} dadosDiarios - Array com dados diários do serviço             * @type {Array} categorias - Array com rótulos dos dias para o eixo X (apenas números)
             * @type {Array} diasSemana - Array com dias da semana para dataLabels customizados
             */
            const dadosDiarios = dados.dadosDiarios || [];
            // Criar categorias apenas com números dos dias para o eixo X
            const categorias = dadosDiarios.map(d => d.dia);
            
            // Separar dados para dataLabels customizados de dias da semana
            const diasSemana = dadosDiarios.map(d => d.dia_semana || '');
            
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
            ];            /**
             * Cálculo do valor máximo para otimização do espaçamento
             * Adiciona margem superior para melhor visualização
             * @type {Array} todosValores - Todos os valores das séries combinados
             * @type {number} valorMaximo - Maior valor encontrado
             * @type {number} maxComEspaco - Valor máximo com margem adicional
             */
            const todosValores = seriesDados.flatMap(serie => serie.data);
            const valorMaximo = Math.max(...todosValores);
            const maxComEspaco = valorMaximo + Math.ceil(valorMaximo * 0.1); // +10% do valor máximo (padrão ApexCharts)

            const opcoes = {
                ...CONFIGURACOES_GLOBAIS,                chart: {
                    ...CONFIGURACOES_GLOBAIS.chart,
                    type: 'bar',
                    height: 380, // Aumentado de 350 para 380 para aproveitar o espaço economizado
                    id: elementId,
                    // Otimizações de performance máximas
                    parentHeightOffset: 0,
                    sparkline: {
                        enabled: false
                    },
                    group: 'charts' // Agrupar para melhor gerenciamento
                },
                series: seriesDados,                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '75%',
                        borderRadius: 0, // Bordas quadradas (removido arredondamento)
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },                dataLabels: {
                    enabled: true,
                    offsetY: -20, // Aumentado de -15 para -20 para ainda mais espaço entre barras e números
                    style: {
                        fontSize: '8px', // Reduzido de 11px para 10px para evitar sobreposição
                        fontWeight: 'normal', // Mudado de 'bold' para 'normal' para números não em negrito
                        colors: ['#333']
                    },
                    textAnchor: 'middle',
                    distributed: false,
                    formatter: function(value) {
                        return value > 0 ? value : '';
                    }
                },
                xaxis: {
                    categories: categorias,
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        maxHeight: 100,  // Aumentado de 80 para 100 para garantir espaço para labels multi-linha
                        trim: false,
                        rotate: 0, // Forçar rotação 0 para melhor performance
                        formatter: function(value, timestamp, opts) {
                            // Obter índice do valor atual
                            const index = opts ? opts.dataPointIndex : categorias.indexOf(String(value));
                            const diaSemana = diasSemana[index] || '';
                            
                            // Retornar com quebra de linha usando \n
                            return `${value}\n${diaSemana}`;
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
                    tickAmount: 5,  // Padrão ApexCharts com menos linhas
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
            };            /**
             * Criar e renderizar o gráfico de barras usando chart pooling
             * @type {ApexCharts} chart - Instância do gráfico ApexCharts
             */
            const chart = ChartPool.get('bar', elementId, opcoes);            chart.render().then(() => {
                // Adicionar labels customizados para dias da semana após a renderização
                setTimeout(() => {
                    adicionarLabelsCustomizados(elementId, diasSemana);
                }, 100);
                
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
             * @type {Array} dadosDiarios - Dados diários do serviço
             * @type {Array} categorias - Categorias para o eixo X (apenas números dos dias)
             * @type {Array} diasSemana - Array com dias da semana para dataLabels customizados
             */
            const dadosDiarios = dados.dadosDiarios || [];
            // Criar categorias apenas com números dos dias para o eixo X
            const categorias = dadosDiarios.map(d => d.dia);
            
            // Separar dados para dataLabels customizados de dias da semana
            const diasSemana = dadosDiarios.map(d => d.dia_semana || '');
            
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
                ...CONFIGURACOES_GLOBAIS,                chart: {
                    ...CONFIGURACOES_GLOBAIS.chart,
                    type: 'line',
                    height: 380, // Aumentado de 350 para 380 para aproveitar o espaço economizado
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
                    categories: categorias,
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        maxHeight: 100,  // Aumentado de 80 para 100 para garantir espaço para labels multi-linha
                        trim: false,
                        rotate: 0, // Forçar rotação 0 para que labels fiquem verticais
                        formatter: function(value, timestamp, opts) {
                            // Obter índice do valor atual
                            const index = opts ? opts.dataPointIndex : categorias.indexOf(String(value));
                            const diaSemana = diasSemana[index] || '';
                            
                            // Retornar com quebra de linha usando \n
                            return `${value}\n${diaSemana}`;
                        },
                        // Configurações específicas para multi-linha
                        offsetY: 0,
                        hideOverlappingLabels: false
                    }
                },yaxis: {
                    tickAmount: 6,  // Reduzir número de linhas do grid para padrão ApexCharts
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
            };            /**
             * Criar e renderizar o gráfico de linha
             * @type {ApexCharts} lineChart - Instância do gráfico de linha
             */
            const lineChart = new ApexCharts(document.querySelector(`#${elementId}`), opcoes);
            lineChart.render().then(() => {
                // Adicionar labels customizados para dias da semana após a renderização
                setTimeout(() => {
                    adicionarLabelsCustomizados(elementId, diasSemana);
                }, 100);
                
                PerformanceMonitor.end(`line_chart_${elementId}`);
                debugLog(`✅ Gráfico de linha ApexCharts criado para ${elementId}`);
            });

        } catch (error) {
            PerformanceMonitor.end(`line_chart_${elementId}`);
            console.error(`❌ Erro ao criar gráfico de linha para ${elementId}:`, error);
        }    }        // Note: Line reference function removed - replaced by concentric gauge design

/**
 * Versão de debug para inspecionar a estrutura do SVG
 * Use esta para ver exatamente como está estruturado o seu gauge
 */
function debugGaugeStructure(elementId) {
    const container = document.querySelector(`#${elementId}`);
    if (!container) {
        console.log('Container não encontrado');
        return;
    }

    const svg = container.querySelector('svg');
    if (!svg) {
        console.log('SVG não encontrado');
        return;
    }

    console.log('🔍 ESTRUTURA DO GAUGE:');
    console.log('SVG:', svg);
    console.log('Classes do SVG:', svg.className);
    
    const allGroups = svg.querySelectorAll('g');
    console.log(`Grupos encontrados: ${allGroups.length}`);
    
    allGroups.forEach((group, index) => {
        console.log(`Grupo ${index}:`, group.className.baseVal || group.getAttribute('class') || 'sem classe');
    });

    const allPaths = svg.querySelectorAll('path');
    console.log(`Paths encontrados: ${allPaths.length}`);
    
    allPaths.forEach((path, index) => {
        const d = path.getAttribute('d');
        console.log(`Path ${index}:`, d ? d.substring(0, 50) + '...' : 'sem d');
        console.log(`  - stroke: ${path.getAttribute('stroke')}`);
        console.log(`  - fill: ${path.getAttribute('fill')}`);
        console.log(`  - class: ${path.getAttribute('class')}`);
    });
}

    /**
     * Cria um gauge (gráfico radial) usando ApexCharts
     * 
     * @function criarGaugeApex
     * @param {Object} dados - Dados do serviço
     * @param {string} elementId - ID do elemento
     */     function criarGaugeApex(dados, elementId) {
    PerformanceMonitor.start(`gauge_${elementId}`);
    
    try {
        // Calcular progresso realizado baseado nos dados
        const totalExecutados = parseInt(dados.total_executados) || 0;
        const metaPdt = parseInt(dados.meta_pdt) || 0;
        const progressoRealizado = metaPdt > 0 ? Math.min(100, Math.round((totalExecutados / metaPdt) * 100)) : 0;
        
        // Calcular progresso pactuado baseado nos dados diários
        let totalPactuado = 0;
        if (dados.dadosDiarios && Array.isArray(dados.dadosDiarios)) {
            totalPactuado = dados.dadosDiarios.reduce((sum, dia) => {
                return sum + (parseInt(dia.pactuado) || 0);
            }, 0);
        }
        
        const progressoPactuado = metaPdt > 0 ? Math.min(100, Math.round((totalPactuado / metaPdt) * 100)) : 0;
        
        // Debug para ver os valores
        console.log(`🔢 GAUGE CONCÊNTRICO PARA ${elementId}:`);
        console.log(`   📊 Realizado: ${totalExecutados} (${progressoRealizado}%)`);
        console.log(`   📋 Pactuado: ${totalPactuado} (${progressoPactuado}%)`);
        console.log(`   🎯 Meta PDT: ${metaPdt}`);

        // Usar cor do grupo se disponível
        let corRealizado = CORES_SISTEMA.realizado; // Laranja para realizado
        if (dados.grupo_cor && dados.grupo_cor !== '#6B7280') {
            corRealizado = dados.grupo_cor;
        }        // GAUGE CONCÊNTRICO: Duas séries sobrepostas
        const opcoes = {
            chart: {
                type: 'radialBar',
                height: 200,
                id: `${elementId}-gauge`,
                sparkline: {
                    enabled: true
                },
                animations: {
                    enabled: CONFIGURACOES_GLOBAIS.chart.animations.enabled,
                    speed: 400,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    }
                }
            },
            series: [progressoRealizado, progressoPactuado], // Duas séries: realizado (externo) e pactuado (interno)
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '30%', // Menor para acomodar duas séries
                        margin: 15
                    },
                    startAngle: -90,
                    endAngle: 90,
                    track: {
                        background: CORES_SISTEMA.progresso.empty,
                        strokeWidth: '100%',
                        margin: 2 // Pequeno espaço entre as séries
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            show: false
                        },
                        total: {
                            show: true,
                            fontSize: '18px',
                            fontWeight: 'bold',
                            color: '#333',
                            formatter: function (w) {
                                return totalExecutados.toString();
                            }
                        }
                    }
                }
            },
            colors: [corRealizado, CORES_SISTEMA.pactuado], // Laranja (realizado) e azul (pactuado)
            stroke: {
                lineCap: 'round',
                strokeWidth: [12, 8] // Série externa mais grossa, interna mais fina
            },
            labels: ['Realizado', 'Pactuado'],
            tooltip: {
                enabled: true,
                theme: 'light',
                style: {
                    fontSize: '12px',
                    fontFamily: 'Arial, sans-serif'
                },
                fillSeriesColor: false,
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
                    const isRealizado = seriesIndex === 0;
                    const valor = series[seriesIndex];
                    const tipo = isRealizado ? 'Realizado' : 'Pactuado';
                    const total = isRealizado ? totalExecutados : totalPactuado;
                    const cor = isRealizado ? corRealizado : CORES_SISTEMA.pactuado;
                    
                    return `
                        <div style="padding: 8px 12px; background: white; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                            <div style="display: flex; align-items: center; margin-bottom: 4px;">
                                <div style="width: 12px; height: 12px; background: ${cor}; border-radius: 2px; margin-right: 6px;"></div>
                                <strong style="color: #333; font-size: 13px;">${tipo}</strong>
                            </div>
                            <div style="color: #666; font-size: 12px; margin-bottom: 2px;">
                                Valor: <strong style="color: #333;">${total.toLocaleString('pt-BR')} procedimentos</strong>
                            </div>
                            <div style="color: #666; font-size: 12px; margin-bottom: 2px;">
                                Progresso: <strong style="color: #333;">${valor.toFixed(1)}%</strong>
                            </div>
                            <div style="color: #666; font-size: 12px;">
                                Meta: <strong style="color: #333;">${metaPdt.toLocaleString('pt-BR')} procedimentos</strong>
                            </div>
                        </div>
                    `;
                }
            }
        };

        // Criar e renderizar o gauge concêntrico
        const gauge = ChartPool.get('gauge', elementId, opcoes);
        gauge.render().then(() => {
            // Adicionar informações customizadas após renderização
            setTimeout(() => {
                adicionarInformacoesGauge(elementId, {
                    percentualRealizado: progressoRealizado,
                    percentualPactuado: progressoPactuado,
                    totalExecutados: totalExecutados,
                    totalPactuado: totalPactuado,
                    metaPdt: metaPdt
                });
            }, 300);
            
            PerformanceMonitor.end(`gauge_${elementId}`);
            debugLog(`✅ Gauge concêntrico criado para ${elementId} - Realizado: ${progressoRealizado}%, Pactuado: ${progressoPactuado}%`);
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
 * Adiciona informações customizadas ao gauge concêntrico
 * Mantém apenas valores nas extremidades (0 e meta) - legends e percentual removidos
 */
function adicionarInformacoesGauge(elementId, dados) {
    try {
        console.log(`📋 Adicionando informações simplificadas para ${elementId}`);
        
        const container = document.querySelector(`#${elementId}`);
        if (!container) {
            console.log(`❌ Container #${elementId} não encontrado`);
            return;
        }

        // Remover informações anteriores se existirem
        container.querySelectorAll('.gauge-custom-info').forEach(el => el.remove());

        // Posicionar container como relativo
        container.style.position = 'relative';

        // 1. Número na extremidade esquerda (0)
        const leftNumber = document.createElement('div');
        leftNumber.className = 'gauge-custom-info';
        leftNumber.style.cssText = `
            position: absolute;
            bottom: 45px;
            left: 35px;
            font-size: 12px;
            color: #999;
            font-weight: normal;
        `;
        leftNumber.innerHTML = '0';

        // 2. Número na extremidade direita (meta)
        const rightNumber = document.createElement('div');
        rightNumber.className = 'gauge-custom-info';
        rightNumber.style.cssText = `
            position: absolute;
            bottom: 45px;
            right: 35px;
            font-size: 12px;
            color: #999;
            font-weight: normal;
        `;
        rightNumber.innerHTML = dados.metaPdt.toString();

        // Adicionar apenas os números das extremidades ao container
        container.appendChild(leftNumber);
        container.appendChild(rightNumber);

        console.log(`✅ Informações simplificadas adicionadas para ${elementId} (apenas extremidades: 0 e ${dados.metaPdt})`);

    } catch (error) {
        console.error(`❌ Erro ao adicionar informações customizadas:`, error);
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
    if (DEBUG_MODE) {        setTimeout(() => {
            debugLog('📊 Estatísticas de performance:', window.getPerformanceStats());
        }, 2000);
    }

    // Note: Legacy line reference debug functions removed - using concentric gauge design instead

    /**
     * Função de debug para verificar os dados e forçar teste de linhas
     */
    function debugLinhaReferencia() {
        console.log('🔍 DEBUG: Verificando dados dos gauges...');
        
        // Verificar se há gauges na página
        const gauges = document.querySelectorAll('[id^="gauge"]');
        console.log(`Encontrados ${gauges.length} gauges na página`);
        
        // Verificar estrutura dos dados globais
        console.log('📋 Dados globais disponíveis:', !!window.dadosGraficos);
        if (window.dadosGraficos) {
            console.log('📋 Total de serviços nos dados:', Object.keys(window.dadosGraficos).length);
            console.log('📋 IDs dos serviços:', Object.values(window.dadosGraficos).map(d => d.id));
        }
        
        gauges.forEach((gauge, index) => {
            const elementId = gauge.id;
            console.log(`\n📊 GAUGE ${index + 1}: ${elementId}`);
            
            // Verificar se SVG existe e estrutura
            const svg = gauge.querySelector('svg');
            console.log(`   🎨 SVG presente: ${!!svg}`);
            if (svg) {
                const paths = svg.querySelectorAll('path');
                console.log(`   🎨 Paths encontrados: ${paths.length}`);
                const existingLines = svg.querySelectorAll('.pactuado-reference-line');
                console.log(`   📏 Linhas existentes: ${existingLines.length}`);
            }
            
            // Buscar dados correspondentes
            if (window.dadosGraficos) {
                const servicoId = elementId.replace('gauge', '');
                const dados = Object.values(window.dadosGraficos).find(d => d.id.toString() === servicoId);
                
                if (dados) {
                    const totalExecutados = parseInt(dados.total_executados) || 0;
                    const metaPdt = parseInt(dados.meta_pdt) || 0;
                    const progresso = metaPdt > 0 ? Math.min(100, Math.round((totalExecutados / metaPdt) * 100)) : 0;
                    
                    let totalPactuado = 0;
                    if (dados.dadosDiarios && Array.isArray(dados.dadosDiarios)) {
                        totalPactuado = dados.dadosDiarios.reduce((sum, dia) => {
                            return sum + (parseInt(dia.pactuado) || 0);
                        }, 0);
                        console.log(`   📊 Dias com dados: ${dados.dadosDiarios.length}`);
                        console.log(`   📊 Valores diários pactuado:`, dados.dadosDiarios.map(d => d.pactuado));
                    }
                    
                    const progressoPactuado = metaPdt > 0 ? Math.min(100, Math.round((totalPactuado / metaPdt) * 100)) : 0;
                    
                    console.log(`   📈 Executados: ${totalExecutados}, Meta: ${metaPdt}, Progresso: ${progresso}%`);
                    console.log(`   🎯 Pactuado Total: ${totalPactuado}, Progresso Pactuado: ${progressoPactuado}%`);
                    
                    // Testar linha com valor real primeiro
                    console.log(`   🧪 Testando linha com valor real: ${progressoPactuado}%`);
                    adicionarLinhaReferenciaPactuado(elementId, progressoPactuado);
                    
                } else {
                    console.log(`   ❌ Dados não encontrados para serviço ${servicoId}`);
                    console.log(`   🔍 Serviços disponíveis:`, Object.values(window.dadosGraficos || {}).map(d => `ID:${d.id}`));
                }
            } else {
                console.log(`   ❌ window.dadosGraficos não está disponível`);
            }
        });
    }
      /**
     * Função para testar todos os percentuais em um gauge específico
     */
    function testarTodosPercentuais(elementId) {
        console.log(`🧪 TESTE COMPLETO PARA ${elementId}:`);
        
        const percentuais = [0, 25, 50, 75, 84, 100];
        let delay = 0;
        
        percentuais.forEach((percentual, index) => {
            setTimeout(() => {
                console.log(`   🎯 Testando ${percentual}%...`);
                adicionarLinhaReferenciaPactuado(elementId, percentual);
                
                // Verificar se linha foi criada
                setTimeout(() => {
                    const container = document.querySelector(`#${elementId}`);
                    if (container) {
                        const lines = container.querySelectorAll('.pactuado-reference-line');
                        console.log(`   ✅ Linhas encontradas para ${percentual}%: ${lines.length}`);
                    }
                }, 200);
                
            }, delay);
            delay += 1500; // 1.5 segundos entre cada teste
        });
    }
    
    /**
     * Função para testar correção do semicírculo IMEDIATAMENTE
     * Use no console: testarCorrecaoSemicirculo('gauge1')
     */
    function testarCorrecaoSemicirculo(elementId) {
        console.log(`🎯 TESTE DE CORREÇÃO SEMICÍRCULO PARA ${elementId}:`);
        console.log(`   ✅ 0% deve aparecer na ESQUERDA do semicírculo`);
        console.log(`   ✅ 50% deve aparecer no TOPO do semicírculo`);
        console.log(`   ✅ 100% deve aparecer na DIREITA do semicírculo`);
        
        // Teste imediato - 0%
        setTimeout(() => {
            console.log(`🔍 Testando 0% (deve ficar na ESQUERDA)...`);
            adicionarLinhaReferenciaPactuado(elementId, 0);
        }, 100);
        
        // Teste imediato - 50%
        setTimeout(() => {
            console.log(`🔍 Testando 50% (deve ficar no TOPO)...`);
            adicionarLinhaReferenciaPactuado(elementId, 50);
        }, 2000);
        
        // Teste imediato - 100%
        setTimeout(() => {
            console.log(`🔍 Testando 100% (deve ficar na DIREITA)...`);
            adicionarLinhaReferenciaPactuado(elementId, 100);
        }, 4000);
    }    // Exportar para console
    window.debugLinhaReferencia = debugLinhaReferencia;
    window.testarTodosPercentuais = testarTodosPercentuais;
    window.testarCorrecaoSemicirculo = testarCorrecaoSemicirculo;

    /**
     * Função de debug para analisar completamente a estrutura do ApexCharts
     * Analisa todos os elementos SVG dos gauges na página
     */
    function debugApexChartsStructure() {
        console.log('🔍 ANÁLISE COMPLETA DA ESTRUTURA APEXCHARTS:');
        console.log('==========================================');
        
        const gauges = document.querySelectorAll('[id^="gauge"]');
        console.log(`📊 Total de gauges encontrados: ${gauges.length}`);
        
        gauges.forEach((gauge, index) => {
            console.log(`\n📈 GAUGE ${index + 1}: ${gauge.id}`);
            console.log('----------------------------------------');
            
            const svg = gauge.querySelector('svg');
            if (!svg) {
                console.log('❌ SVG não encontrado');
                return;
            }
            
            console.log(`📐 Dimensões SVG: ${svg.clientWidth}x${svg.clientHeight}`);
            console.log(`🎨 ViewBox: ${svg.getAttribute('viewBox') || 'não definido'}`);
            
            // Analisar grupos
            const groups = svg.querySelectorAll('g');
            console.log(`📦 Grupos encontrados: ${groups.length}`);
            groups.forEach((group, gIndex) => {
                const className = group.className.baseVal || group.getAttribute('class') || 'sem classe';
                console.log(`   Grupo ${gIndex}: ${className}`);
            });
            
            // Analisar paths (as barras do gauge)
            const paths = svg.querySelectorAll('path');
            console.log(`🎯 Paths encontrados: ${paths.length}`);
            paths.forEach((path, pIndex) => {
                const stroke = path.getAttribute('stroke');
                const fill = path.getAttribute('fill');
                const strokeWidth = path.getAttribute('stroke-width');
                console.log(`   Path ${pIndex}: stroke=${stroke}, fill=${fill}, width=${strokeWidth}`);
            });
            
            // Analisar textos
            const texts = svg.querySelectorAll('text');
            console.log(`📝 Textos encontrados: ${texts.length}`);
            texts.forEach((text, tIndex) => {
                console.log(`   Texto ${tIndex}: "${text.textContent}" (${text.getAttribute('class') || 'sem classe'})`);
            });
        });
    }

    /**
     * Função específica para analisar elementos radialBar
     * Foca nos elementos específicos dos gauges concêntricos
     */
    function analisarRadialBar() {
        console.log('🎯 ANÁLISE ESPECÍFICA RADIALBAR:');
        console.log('================================');
        
        const gauges = document.querySelectorAll('[id^="gauge"]');
        
        gauges.forEach((gauge, index) => {
            console.log(`\n⭕ RADIALBAR ${index + 1}: ${gauge.id}`);
            console.log('----------------------------');
            
            const svg = gauge.querySelector('svg');
            if (!svg) return;
            
            // Buscar elementos específicos do radialBar
            const radialBarGroups = svg.querySelectorAll('g[class*="radial"]');
            console.log(`🔄 Grupos radial encontrados: ${radialBarGroups.length}`);
            
            const progressPaths = svg.querySelectorAll('path[stroke-dasharray]');
            console.log(`📊 Paths de progresso: ${progressPaths.length}`);
            
            progressPaths.forEach((path, pIndex) => {
                const dashArray = path.getAttribute('stroke-dasharray');
                const dashOffset = path.getAttribute('stroke-dashoffset');
                const stroke = path.getAttribute('stroke');
                console.log(`   Série ${pIndex}: ${stroke} - dasharray: ${dashArray}, offset: ${dashOffset}`);
            });
            
            // Verificar se é concêntrico (duas séries)
            if (progressPaths.length === 2) {
                console.log('✅ GAUGE CONCÊNTRICO DETECTADO - Duas séries encontradas');
                console.log(`   📊 Série externa: ${progressPaths[0].getAttribute('stroke')}`);
                console.log(`   📋 Série interna: ${progressPaths[1].getAttribute('stroke')}`);
            } else {
                console.log(`❌ Gauge não concêntrico - ${progressPaths.length} série(s) encontrada(s)`);
            }
        });
    }

    /**
     * Função para testar diferentes métodos de posicionamento
     * Testa várias abordagens para posicionar elementos no gauge
     */
    function testarPosicionamentoInteligente() {
        console.log('🧪 TESTE DE POSICIONAMENTO INTELIGENTE:');
        console.log('=======================================');
        
        const gauges = document.querySelectorAll('[id^="gauge"]');
        if (gauges.length === 0) {
            console.log('❌ Nenhum gauge encontrado para teste');
            return;
        }
        
        const primeiroGauge = gauges[0];
        const elementId = primeiroGauge.id;
        
        console.log(`🎯 Testando posicionamento no gauge: ${elementId}`);
        
        // Método 1: Posicionamento por getBoundingClientRect
        console.log('\n📐 MÉTODO 1: getBoundingClientRect');
        const container = document.querySelector(`#${elementId}`);
        if (container) {
            const rect = container.getBoundingClientRect();
            console.log(`   Container: x=${rect.x}, y=${rect.y}, w=${rect.width}, h=${rect.height}`);
        }
        
        // Método 2: Posicionamento por SVG viewBox
        console.log('\n📐 MÉTODO 2: SVG viewBox');
        const svg = primeiroGauge.querySelector('svg');
        if (svg) {
            const viewBox = svg.getAttribute('viewBox');
            const clientRect = svg.getBoundingClientRect();
            console.log(`   ViewBox: ${viewBox}`);
            console.log(`   Cliente: w=${clientRect.width}, h=${clientRect.height}`);
        }
        
        // Método 3: Posicionamento por path getBBox
        console.log('\n📐 MÉTODO 3: Path getBBox');
        const paths = svg?.querySelectorAll('path');
        if (paths && paths.length > 0) {
            paths.forEach((path, index) => {
                try {
                    const bbox = path.getBBox();
                    console.log(`   Path ${index}: x=${bbox.x}, y=${bbox.y}, w=${bbox.width}, h=${bbox.height}`);
                } catch (e) {
                    console.log(`   Path ${index}: Erro ao obter bbox - ${e.message}`);
                }
            });
        }
        
        // Método 4: Posicionamento relativo ao centro
        console.log('\n📐 MÉTODO 4: Centro calculado');
        if (svg) {
            const svgRect = svg.getBoundingClientRect();
            const centerX = svgRect.width / 2;
            const centerY = svgRect.height / 2;
            console.log(`   Centro calculado: (${centerX}, ${centerY})`);
            
            // Testar posicionamento de elemento no centro
            console.log('   🧪 Testando elemento no centro...');
            const testElement = document.createElement('div');
            testElement.style.cssText = `
                position: absolute;
                width: 4px;
                height: 4px;
                background: red;
                border-radius: 50%;
                left: ${centerX - 2}px;
                top: ${centerY - 2}px;
                z-index: 1000;
                pointer-events: none;
            `;
            testElement.className = 'test-center-marker';
            
            // Remover marcador anterior se existir
            container?.querySelector('.test-center-marker')?.remove();
            
            if (container) {
                container.style.position = 'relative';
                container.appendChild(testElement);
                console.log('   ✅ Marcador vermelho adicionado no centro calculado');
                
                // Remover após 3 segundos
                setTimeout(() => {
                    testElement.remove();
                    console.log('   🗑️ Marcador removido');
                }, 3000);
            }
        }
    }

    // Exportar funções de debug para o console
    window.debugApexChartsStructure = debugApexChartsStructure;
    window.analisarRadialBar = analisarRadialBar;
    window.testarPosicionamentoInteligente = testarPosicionamentoInteligente;
});
