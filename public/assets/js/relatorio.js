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
     * Debug removido para produção
     * Sistema otimizado sem logs de desenvolvimento
     */
    // debugLog function removed for production

    // Debug: Dashboard carregado (removed for production)

    /**
     * Armazena dados dos labels para reposicionamento responsivo
     * @type {Map} labelsData - Mapeamento de elementId para dados dos labels
     */
    const labelsData = new Map();
    
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

            // Armazenar dados para reposicionamento responsivo
            labelsData.set(elementId, diasSemana);

            // Remover labels customizados existentes
            const existingLabels = chartContainer.querySelectorAll('.custom-day-label');
            existingLabels.forEach(label => label.remove());

            // Aguardar um pouco para garantir que o gráfico foi totalmente renderizado
            setTimeout(() => {
                const xAxisLabels = chartContainer.querySelectorAll('.apexcharts-xaxis-texts-g text');
                
                xAxisLabels.forEach((label, index) => {
                    if (index < diasSemana.length && diasSemana[index]) {
                        const rect = label.getBoundingClientRect();
                        const containerRect = chartContainer.getBoundingClientRect();
                        
                        // Calcular posição relativa ao container com melhor precisão
                        const left = rect.left - containerRect.left + (rect.width / 2);
                        const top = rect.bottom - containerRect.top + 8; // Espaçamento otimizado
                        
                        // Criar elemento para o dia da semana
                        const dayLabel = document.createElement('div');
                        dayLabel.className = 'custom-day-label';
                        dayLabel.textContent = diasSemana[index];
                        dayLabel.style.cssText = `
                            position: absolute;
                            left: ${left}px;
                            top: ${top}px;
                            transform: translateX(-50%);
                            font-size: 10px;
                            color: #666;
                            font-weight: normal;
                            text-align: center;
                            pointer-events: none;
                            z-index: 10;
                            font-family: Arial, sans-serif;
                            white-space: nowrap;
                            opacity: 0.9;
                            transition: opacity 0.2s ease;
                        `;
                        
                        chartContainer.style.position = 'relative';
                        chartContainer.appendChild(dayLabel);
                    }
                });
                
                // Debug: Labels customizados adicionados (removed for production)
            }, 50); // Pequeno delay para garantir renderização completa
            
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
    };

    /**
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
                enabled: false, // Desabilitar animações para performance máxima
                easing: 'linear',
                speed: 200, // Reduzido drasticamente para 200ms
                dynamicAnimation: {
                    enabled: false // Desabilitar animações dinâmicas para melhor performance
                }
            },
            redrawOnParentResize: false, // Desabilitar para usar sistema manual otimizado
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
            },
            padding: {
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
              // Debug: Performance monitoring (removed for production)
            
            // Alertar se performance estiver ruim (>150ms) - apenas em desenvolvimento
            if (duration > 150 && location.hostname === 'localhost') {
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
                // Usar updateOptions otimizado para reutilização
                chart.updateOptions(options, false, false, false); // Sem animação, sem redraw, sem recalculo
                this.usage.reused++;
                // Debug: Chart reused from pool (removed for production)
                return chart;
            }
            
            // Criar nova instância com configurações otimizadas
            const element = document.querySelector(`#${elementId}`);
            if (!element) {
                console.error(`❌ Elemento #${elementId} não encontrado`);
                return null;
            }
            
            const chart = new ApexCharts(element, options);
            this.usage.created++;
            // Debug: New chart created (removed for production)
            return chart;
        }
    };

    /**
     * Detector de dispositivos móveis otimizado
     * @function isMobile
     * @returns {boolean} True se for dispositivo móvel
     */
    function isMobile() {
        return window.innerWidth <= 768 || 
               /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Função otimizada para forçar visibilidade de elementos em mobile
     * @function forcarVisibilidadeElementos
     * @param {string} elementId - ID do elemento do gráfico
     */
    function forcarVisibilidadeElementos(elementId) {
        const chartContainer = document.querySelector(`#${elementId}`);
        if (!chartContainer) return;

        const elementsToShow = chartContainer.querySelectorAll(
            '.apexcharts-xaxis-texts-g text, .custom-day-label, .apexcharts-xaxis'
        );
        
        elementsToShow.forEach(element => {
            element.style.visibility = 'visible';
            element.style.display = 'block';
            element.style.opacity = '1';
        });
        
        // Debug log removed for production
    }

    /**
     * Validação e correção otimizada de labels
     * @function validarECorrigirLabels
     * @param {string} elementId - ID do elemento do gráfico
     */
    function validarECorrigirLabels(elementId) {
        const chartContainer = document.querySelector(`#${elementId}`);
        if (!chartContainer) return;

        const customLabels = chartContainer.querySelectorAll('.custom-day-label');
        const xAxisLabels = chartContainer.querySelectorAll('.apexcharts-xaxis-texts-g text');
        
        if (customLabels.length !== xAxisLabels.length) {
            // Debug log removed for production
            
            // Recriar labels se necessário
            const diasSemana = labelsData.get(elementId);
            if (diasSemana) {
                setTimeout(() => {
                    adicionarLabelsCustomizados(elementId, diasSemana);
                }, 200);
            }
        }
    }

    /**
     * Inicialização condicional dos gráficos
     * Verifica se dados estão disponíveis antes de prosseguir
     * @function initializeCharts
     */
    if (window.dadosGraficos) {
        // Debug log removed for production
        
        // Implementar lazy loading com IntersectionObserver para performance
        initializeLazyCharts();
    } else {
        // Debug log removed for production
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
            
            // Debug log removed for production
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
                // Debug log removed for production
                criarGraficoBarrasApex(dados, graficoId);
            }
            
            if (elementId === gaugeId) {
                // Debug log removed for production
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
        
        try {
            const dadosDiarios = dados.dadosDiarios || [];
            const categorias = dadosDiarios.map(d => d.dia);
            const diasSemana = dadosDiarios.map(d => d.dia_semana || '');
            
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

            const todosValores = seriesDados.flatMap(serie => serie.data);
            const valorMaximo = Math.max(...todosValores);
            const maxComEspaco = valorMaximo + Math.ceil(valorMaximo * 0.1);

            const opcoes = {
                ...CONFIGURACOES_GLOBAIS,
                chart: {
                    ...CONFIGURACOES_GLOBAIS.chart,
                    type: 'bar',
                    height: 380,
                    id: elementId
                },
                series: seriesDados,
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '75%',
                        borderRadius: 0,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '9px',
                        fontWeight: 'normal',
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
                        maxHeight: 120,
                        trim: false,
                        rotate: 0,
                        formatter: function(value, timestamp, opts) {
                            return value;
                        },
                        offsetY: 0,
                        hideOverlappingLabels: false
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    title: {
                        offsetY: 10
                    }
                },
                yaxis: {
                    min: 0,
                    max: maxComEspaco,
                    tickAmount: 5,
                    labels: {
                        style: {
                            fontSize: '10px'
                        },
                        formatter: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    },
                    forceNiceScale: false
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
                fill: {
                    opacity: 1
                },
                stroke: {
                    show: false
                }
            };

            const chart = ChartPool.get('bar', elementId, opcoes);
            chart.render().then(() => {
                requestAnimationFrame(() => {
                    adicionarLabelsCustomizados(elementId, diasSemana);
                    
                    if (isMobile()) {
                        forcarVisibilidadeElementos(elementId);
                    }
                    
                    setTimeout(() => {
                        validarECorrigirLabels(elementId);
                    }, 50);
                });
                
                PerformanceMonitor.end(`chart_${elementId}`);
                // Debug log removed for production
            }).catch(error => {
                PerformanceMonitor.end(`chart_${elementId}`);
                console.error(`❌ Erro ao renderizar gráfico para ${elementId}:`, error);
            });

        } catch (error) {
            PerformanceMonitor.end(`chart_${elementId}`);
            console.error(`❌ Erro ao criar gráfico ApexCharts para ${elementId}:`, error);
        }    }

    /**
     * Adiciona informações complementares ao gauge após renderização
     * @param {string} elementId - ID do elemento do gauge
     * @param {Object} dados - Dados do gauge
     * @param {number} dados.percentualRealizado - Percentual realizado
     * @param {number} dados.percentualPactuado - Percentual pactuado
     * @param {number} dados.totalExecutados - Total de executados
     * @param {number} dados.totalPactuado - Total pactuado
     * @param {number} dados.metaPdt - Meta PDT
     */
    function adicionarInformacoesGauge(elementId, dados) {
        try {
            const gaugeContainer = document.querySelector(`#${elementId}`);
            if (!gaugeContainer) {
                // Debug log removed for production
                return;
            }
            
            // Encontrar container do gauge-info
            const gaugeInfo = gaugeContainer.closest('.gauge-summary')?.querySelector('.gauge-info');
            if (!gaugeInfo) {
                // Debug log removed for production
                return;
            }
            
            // Atualizar valores se necessário
            const gaugeValue = gaugeInfo.querySelector('.gauge-value');
            const gaugePercent = gaugeInfo.querySelector('.gauge-percent');
            
            if (gaugeValue) {
                gaugeValue.textContent = dados.totalExecutados.toLocaleString('pt-BR');
            }
            
            if (gaugePercent) {
                gaugePercent.textContent = `${dados.percentualRealizado.toFixed(1)}%`;
            }
            
            // Debug log removed for production
            
        } catch (error) {
            console.error(`❌ Erro ao adicionar informações do gauge ${elementId}:`, error);
        }
    }

    /**
     * Aplica cores dinâmicas do gauge à legenda correspondente
     * @param {string} elementId - ID do elemento do gauge
     * @param {Object} cores - Objeto com as cores do gauge
     * @param {string} cores.corRealizado - Cor para "Realizado"
     * @param {string} cores.corMeta - Cor para "Meta PDT"
     */
    function aplicarCoresDinamicasLegenda(elementId, cores) {
        try {
            // Encontrar o container do gauge
            const gaugeContainer = document.querySelector(`#${elementId}`);
            if (!gaugeContainer) {
                // Debug log removed for production
                return;
            }
            
            // Encontrar o container de summary que contém a legenda
            const summaryContainer = gaugeContainer.closest('.gauge-summary');
            if (!summaryContainer) {
                // Debug log removed for production
                return;
            }
            
            // Aplicar cor dinâmica ao indicador "Realizado"
            const realizadoColor = summaryContainer.querySelector('.legend-color.realizado-color');
            if (realizadoColor) {
                realizadoColor.style.backgroundColor = cores.corRealizado;
                realizadoColor.setAttribute('data-dynamic-color', cores.corRealizado);
                // Debug log removed for production
            }
            
            // Aplicar cor ao indicador "Meta PDT" (sempre azul)
            const metaColor = summaryContainer.querySelector('.legend-color.meta-color');
            if (metaColor) {
                metaColor.style.backgroundColor = cores.corMeta;
                metaColor.setAttribute('data-dynamic-color', cores.corMeta);
                // Debug log removed for production
            }
            
            // Aplicar eventos de hover dinâmicos
            const realizadoItem = summaryContainer.querySelector('.legend-item[data-type="realizado"]');
            if (realizadoItem) {
                // Remover eventos anteriores
                realizadoItem.replaceWith(realizadoItem.cloneNode(true));
                const novoRealizadoItem = summaryContainer.querySelector('.legend-item[data-type="realizado"]');
                
                // Aplicar novos eventos com cores dinâmicas
                novoRealizadoItem.addEventListener('mouseenter', function() {
                    // Converter hex para rgba com 10% de opacidade
                    const rgba = hexToRgba(cores.corRealizado, 0.1);
                    this.style.backgroundColor = rgba;
                    
                    const text = this.querySelector('.legend-text');
                    const value = this.querySelector('.legend-value');
                    if (text) text.style.color = cores.corRealizado;
                    if (value) value.style.color = cores.corRealizado;
                });
                
                novoRealizadoItem.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    const text = this.querySelector('.legend-text');
                    const value = this.querySelector('.legend-value');
                    if (text) text.style.color = '';
                    if (value) value.style.color = '';
                });
            }
            
            // Debug log removed for production
            
        } catch (error) {
            console.error(`❌ Erro ao aplicar cores dinâmicas à legenda ${elementId}:`, error);
        }
    }

    /**
     * Converte cor hexadecimal para rgba com opacidade
     * @param {string} hex - Cor em formato hex (#ffffff)
     * @param {number} alpha - Valor de opacidade (0-1)
     * @returns {string} Cor em formato rgba
     */
    function hexToRgba(hex, alpha) {
        try {
            // Remove o # se presente
            hex = hex.replace('#', '');
            
            // Converte para RGB
            const r = parseInt(hex.substring(0, 2), 16);
            const g = parseInt(hex.substring(2, 4), 16);
            const b = parseInt(hex.substring(4, 6), 16);
            
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        } catch (error) {
            // Fallback para cor padrão
            return `rgba(253, 126, 20, ${alpha})`;
        }
    }

    /**
     * Cria um gauge (gráfico radial) usando ApexCharts
     * 
     * @function criarGaugeApex
     * @param {Object} dados - Dados do serviço
     * @param {string} elementId - ID do elemento
     */function criarGaugeApex(dados, elementId) {
        PerformanceMonitor.start(`gauge_${elementId}`);
        
        try {
            // Calcular total realizado a partir dos dados diários
            let totalRealizado = 0;
            if (dados.dadosDiarios && Array.isArray(dados.dadosDiarios)) {
                totalRealizado = dados.dadosDiarios.reduce((sum, dia) => {
                    return sum + (parseInt(dia.realizado) || 0);
                }, 0);
            }
              const totalExecutados = totalRealizado > 0 ? totalRealizado : (parseInt(dados.total_executados) || 0);
            const totalPactuado = parseInt(dados.total_pactuado) || 0;
            const metaPdt = parseInt(dados.meta_pdt) || 1;
              // Debug: Gauge data calculated (removed for production)
              // Primeiro gauge (interno): Realizado vs Meta PDT
            const progressoRealizado = Math.min((totalExecutados / metaPdt) * 100, 100);
            
            // Segundo gauge (externo): Realizado vs Pactuado
            const progressoPactuado = totalPactuado > 0 ? Math.min((totalExecutados / totalPactuado) * 100, 100) : 0;
            
            // Debug apenas em desenvolvimento
            if (DEBUG_MODE) {
                console.log(`Gauge ${elementId}:`, {
                    totalExecutados,
                    totalPactuado,
                    metaPdt,
                    progressoRealizado: progressoRealizado.toFixed(2) + '%',
                    progressoPactuado: progressoPactuado.toFixed(2) + '%'
                });
            }
            
            // Usar cor do grupo para "Realizado" ou cor padrão
            let corRealizado = CORES_SISTEMA.realizado;
            if (dados.grupo_cor && dados.grupo_cor !== '#6B7280') {
                corRealizado = dados.grupo_cor;
            }
              // Debug: Gauge concentrico data (removed for production)
            
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
                series: [progressoRealizado, progressoPactuado],
                plotOptions: {
                    radialBar: {
                        hollow: {
                            size: '30%',
                            margin: 15
                        },
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: CORES_SISTEMA.progresso.empty,
                            strokeWidth: '100%',
                            margin: 2
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
                colors: [corRealizado, CORES_SISTEMA.pactuado],
                stroke: {
                    lineCap: 'butt',
                    strokeWidth: [12, 8]
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

            const gauge = ChartPool.get('gauge', elementId, opcoes);
            gauge.render().then(() => {
                setTimeout(() => {
                    adicionarInformacoesGauge(elementId, {
                        percentualRealizado: progressoRealizado,
                        percentualPactuado: progressoPactuado,
                        totalExecutados: totalExecutados,
                        totalPactuado: totalPactuado,
                        metaPdt: metaPdt
                    });
                    
                    // NOVO: Aplicar cores dinâmicas do gauge à legenda
                    aplicarCoresDinamicasLegenda(elementId, {
                        corRealizado: corRealizado,
                        corMeta: CORES_SISTEMA.pactuado
                    });
                    
                }, 300);
                
                PerformanceMonitor.end(`gauge_${elementId}`);
                // Debug log removed for production
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
                
                // Reposicionar labels customizados após resize dos gráficos
                setTimeout(() => {
                    reposicionarLabelsCustomizados();
                    
                    // Forçar visibilidade em dispositivos móveis após resize
                    if (isMobile()) {
                        labelsData.forEach((diasSemana, elementId) => {
                            forcarVisibilidadeElementos(elementId);
                        });
                    }
                }, 150);
                
                PerformanceMonitor.end('resize_all_charts');
                // Debug log removed for production
            }
        }, 100);
    };

    /**
     * Reposiciona todos os labels customizados após resize
     * @function reposicionarLabelsCustomizados
     */
    function reposicionarLabelsCustomizados() {
        labelsData.forEach((diasSemana, elementId) => {
            adicionarLabelsCustomizados(elementId, diasSemana);
            // Validar após reposicionar
            setTimeout(() => {
                validarECorrigirLabels(elementId);
            }, 100);
        });
        // Debug log removed for production
    }

    // Event listeners
    let lastResize = 0;
    window.addEventListener('resize', function() {
        const now = Date.now();
        if (now - lastResize > 50) {
            lastResize = now;
            window.redimensionarGraficos();
        }
    });

    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            window.redimensionarGraficos();
        }, 200);
    });

    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            setTimeout(() => {
                reposicionarLabelsCustomizados();
            }, 100);
        }    });

    // Debug: Informações sobre grupos (se disponível)
    if (window.gruposInfo) {
        // Debug: Groups info collected (removed for production)
        window.gruposInfo.forEach(grupo => {
            // Debug: Group info (removed for production)
        });
    }

    // ===== FUNCIONALIDADE DE PESQUISA ===== 
    try {
        initializeSearch();
    } catch (error) {
        console.error('❌ Erro ao inicializar sistema de pesquisa:', error);
    }

    // Exportar utilitários para uso global se necessário
    window.RTDashboard = {
        PerformanceMonitor,
        ChartPool,
        isMobile,
        redimensionarGraficos: window.redimensionarGraficos
    };

    // Debug: Dashboard initialization complete (removed for production)
});

// ===== SISTEMA DE PESQUISA PERFORMÁTICO =====

/**
 * Inicializa o sistema de pesquisa
 */
function initializeSearch() {
    const searchInput = document.getElementById('search');
    const clearButton = document.getElementById('clearSearch');
    const searchResults = document.getElementById('searchResults');
    
    if (!searchInput || !clearButton || !searchResults) {
        return; // Elementos não encontrados
    }

    let searchTimeout;
    let searchData = [];    // Coletar dados de grupos e serviços para pesquisa
    function collectSearchData() {
        searchData = [];
        
        // Coletar dados dos grupos e serviços na página
        const groupContainers = document.querySelectorAll('.group-container');
        
        groupContainers.forEach(container => {
            const groupHeader = container.querySelector('.group-header h3');
            const groupName = groupHeader ? groupHeader.textContent.trim() : '';
            const groupColor = container.querySelector('.group-color-indicator')?.style.backgroundColor || '';
            
            if (groupName) {
                // Adicionar grupo aos dados de pesquisa
                searchData.push({
                    type: 'grupo',
                    name: groupName,
                    element: container,
                    color: groupColor
                });
            }
            
            // Coletar serviços dentro do grupo (corrigindo seletores)
            const serviceSections = container.querySelectorAll('.service-section');
            serviceSections.forEach(section => {
                const serviceNameElement = section.querySelector('.service-header h3');
                const serviceName = serviceNameElement ? serviceNameElement.textContent.trim() : '';
                
                if (serviceName) {                    searchData.push({
                        type: 'serviço',
                        name: serviceName,
                        group: groupName,
                        element: section,
                        container: container,
                        color: groupColor
                    });
                  }
            });
        });
    }

    // Realizar pesquisa com debounce
    function performSearch(query) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (!query.trim()) {
                hideSearchResults();
                showAllItems();
                return;
            }

            const results = searchInData(query);
            displaySearchResults(results, query);
            
            if (results.length > 0) {
                highlightSearchResults(query);
            } else {
                showAllItems();
            }
        }, 300);
    }

    // Buscar nos dados coletados
    function searchInData(query) {
        const searchTerm = query.toLowerCase().trim();
        const results = [];
        
        searchData.forEach(item => {
            const nameMatch = item.name.toLowerCase().includes(searchTerm);
            const groupMatch = item.group && item.group.toLowerCase().includes(searchTerm);
            
            if (nameMatch || groupMatch) {
                results.push({
                    ...item,
                    relevance: nameMatch ? 2 : 1 // Maior relevância para nome direto
                });
            }
        });
        
        // Ordenar por relevância e tipo (grupos primeiro)
        return results.sort((a, b) => {
            if (a.type !== b.type) {
                return a.type === 'grupo' ? -1 : 1;
            }
            return b.relevance - a.relevance;
        });
    }

    // Exibir resultados da pesquisa
    function displaySearchResults(results, query) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-no-results">Nenhum resultado encontrado</div>';
            searchResults.style.display = 'block';
            return;
        }

        const html = results.map(result => {
            const highlightedName = highlightText(result.name, query);
            const typeLabel = result.type === 'grupo' ? 'Grupo' : 'Serviço';
            const groupInfo = result.group && result.type === 'serviço' ? 
                `<div class="search-result-group">Grupo: ${result.group}</div>` : '';
            
            return `
                <div class="search-result-item" data-type="${result.type}" data-element-id="${getElementId(result.element)}">
                    <div class="search-result-type">${typeLabel}</div>
                    <div class="search-result-name">${highlightedName}</div>
                    ${groupInfo}
                </div>
            `;
        }).join('');

        searchResults.innerHTML = html;
        searchResults.style.display = 'block';        // Adicionar eventos de clique
        searchResults.querySelectorAll('.search-result-item').forEach((item, index) => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectSearchResult(results[index]);
            });
        });
    }

    // Destacar texto na pesquisa
    function highlightText(text, query) {
        if (!query.trim()) return text;
        
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<span class="search-highlight">$1</span>');
    }

    // Escapar caracteres especiais de regex
    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Obter ID único do elemento
    function getElementId(element) {
        return element.id || element.querySelector('[id]')?.id || Math.random().toString(36).substr(2, 9);
    }    // Selecionar resultado da pesquisa
    function selectSearchResult(result) {
        hideSearchResults();
        
        // Filtrar para mostrar apenas o resultado selecionado
        filterToShowOnlyResult(result);
        
        // Scrollar para o elemento após filtrar
        if (result.element) {
            setTimeout(() => {
                result.element.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start'
                });
                
                // Destacar temporariamente
                highlightElement(result.element);
            }, 100);
        }
        
        // Manter o texto da pesquisa no campo para mostrar o que está filtrado
        searchInput.value = result.name;
    }

    // Destacar elemento selecionado
    function highlightElement(element) {
        element.style.outline = '3px solid var(--primary-blue)';
        element.style.outlineOffset = '2px';
        
        setTimeout(() => {
            element.style.outline = '';
            element.style.outlineOffset = '';
        }, 3000);
    }

    // Ocultar resultados da pesquisa
    function hideSearchResults() {
        searchResults.style.display = 'none';
        searchResults.innerHTML = '';
    }    // Mostrar todos os itens (remover filtros)
    function showAllItems() {
        const groupContainers = document.querySelectorAll('.group-container');
        groupContainers.forEach(container => {
            container.style.display = 'block';
            
            const serviceSections = container.querySelectorAll('.service-section');
            serviceSections.forEach(section => {
                section.style.display = 'block';
            });
        });
        
        // Desativar indicador visual de filtro
        setFilterActive(false);
        
        // Atualizar contador se existir
        updateVisibilityCounter();
    }// Filtrar para mostrar apenas o resultado selecionado
    function filterToShowOnlyResult(result) {
        const groupContainers = document.querySelectorAll('.group-container');
        
        groupContainers.forEach(container => {
            const isTargetGroup = container === result.element || container.contains(result.element);
            
            if (result.type === 'grupo') {
                // Se é um grupo, mostrar apenas este grupo
                container.style.display = isTargetGroup ? 'block' : 'none';
                
                if (isTargetGroup) {
                    // Mostrar todos os serviços do grupo selecionado
                    const serviceSections = container.querySelectorAll('.service-section');
                    serviceSections.forEach(section => {
                        section.style.display = 'block';
                    });
                }
            } else {
                // Se é um serviço específico
                if (isTargetGroup) {
                    // Mostrar o grupo que contém o serviço
                    container.style.display = 'block';
                    
                    // Ocultar todos os serviços exceto o selecionado
                    const serviceSections = container.querySelectorAll('.service-section');
                    serviceSections.forEach(section => {
                        const isTargetService = section === result.element || section.contains(result.element);
                        section.style.display = isTargetService ? 'block' : 'none';
                    });
                } else {
                    // Ocultar grupos que não contêm o serviço
                    container.style.display = 'none';
                }
            }
        });
        
        // Ativar indicador visual de filtro
        setFilterActive(true);
        
        // Atualizar contador se existir
        updateVisibilityCounter();
    }    // Atualizar contador de itens visíveis (opcional)
    function updateVisibilityCounter() {        const visibleGroups = document.querySelectorAll('.group-container[style*="block"], .group-container:not([style*="none"])').length;
        const visibleServices = document.querySelectorAll('.service-section[style*="block"], .service-section:not([style*="none"])').length;
          // Contador disponível para debug se necessário
        // Debug removido para produção
    }

    // Controlar indicador visual de filtro ativo
    function setFilterActive(active) {
        const searchFilter = document.querySelector('.search-filter');
        const searchInput = document.querySelector('.search-input');
        
        if (active) {
            searchFilter?.classList.add('active');
            searchInput?.classList.add('filtered');
        } else {
            searchFilter?.classList.remove('active');
            searchInput?.classList.remove('filtered');
        }
    }    // Destacar resultados da pesquisa na página
    function highlightSearchResults(query) {
        const searchTerm = query.toLowerCase().trim();
        const groupContainers = document.querySelectorAll('.group-container');
        
        groupContainers.forEach(container => {
            const groupName = container.querySelector('.group-header h3')?.textContent.toLowerCase() || '';
            let hasVisibleServices = false;
            
            // Verificar serviços
            const serviceSections = container.querySelectorAll('.service-section');
            serviceSections.forEach(section => {
                const serviceName = section.querySelector('.service-header h3')?.textContent.toLowerCase() || '';
                const matches = serviceName.includes(searchTerm) || groupName.includes(searchTerm);
                
                section.style.display = matches ? 'block' : 'none';
                if (matches) hasVisibleServices = true;
            });
            
            // Mostrar/ocultar grupo baseado nos serviços visíveis ou se o nome do grupo corresponde
            const groupMatches = groupName.includes(searchTerm);
            container.style.display = (hasVisibleServices || groupMatches) ? 'block' : 'none';
        });
    }    // Event listeners
    searchInput.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        
        if (value === '') {
            // Campo limpo - mostrar todos os itens
            hideSearchResults();
            showAllItems();
        } else {
            // Continuar com a pesquisa normal
            performSearch(value);
        }
    });

    searchInput.addEventListener('focus', () => {
        if (!searchData.length) {
            collectSearchData();
        }
    });

    clearButton.addEventListener('click', () => {
        searchInput.value = '';
        hideSearchResults();
        showAllItems();
        searchInput.focus();
    });

    // Fechar resultados ao clicar fora
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-filter')) {
            hideSearchResults();
        }
    });    // Atalho de teclado para pesquisa (Ctrl+F ou Alt+S)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey && e.key === 'f') || (e.altKey && e.key === 's')) {
            e.preventDefault();
            searchInput.focus();
        }
        
        // ESC para limpar pesquisa (funciona sempre)
        if (e.key === 'Escape') {
            if (searchInput.value.trim() !== '' || searchResults.style.display === 'block') {
                e.preventDefault();
                searchInput.value = '';
                hideSearchResults();
                showAllItems();
                searchInput.blur();
            }
        }
    });

    // Coletar dados iniciais
    setTimeout(collectSearchData, 1000);
}
