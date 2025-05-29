document.addEventListener("DOMContentLoaded", function () {
    // Verificar se Chart.js foi carregado
    if (typeof Chart === 'undefined') {
        console.error('Chart.js não foi carregado! Verifique os CDNs.');
        return;
    }
    
    // Verificar se ChartDataLabels foi carregado
    if (typeof ChartDataLabels === 'undefined') {
        console.warn('ChartDataLabels plugin não foi carregado! Continuando sem o plugin.');
    } else {
        // Registrar o plugin ChartJS Datalabels se disponível
        try {
            Chart.register(ChartDataLabels);
            console.log('ChartDataLabels plugin registrado com sucesso.');
        } catch (error) {
            console.warn('Erro ao registrar ChartDataLabels:', error);
        }
    }
      // Configurações de cores no tema verde, azul e laranja
    const CORES = {
        pactuado: {
            background: '#0d6efd', // Azul
            border: '#0d6efd'
        },
        agendado: {
            background: '#1e3a8a', // Azul escuro
            border: '#1e3a8a'
        },
        realizado: {
            background: '#fd7e14', // Laranja
            border: '#fd7e14'
        },
        progresso: {
            fill: '#fd7e14',      // Laranja (gauge preenchido)
            empty: '#e0e0e0'      // Cinza claro (gauge vazio)
        }
    };

    // Inicializar todos os gráficos (layout original mantido)
    if (window.dadosGraficos) {
        console.log('Dados recebidos para gráficos:', window.dadosGraficos);
        console.log('Tipo de dadosGraficos:', typeof window.dadosGraficos);
        console.log('É array?', Array.isArray(window.dadosGraficos));
        
        Object.values(window.dadosGraficos).forEach(function(dados, index) {
            console.log(`Processando gráfico ${index}:`, dados);
            console.log(`Tem dadosDiarios?`, dados.dadosDiarios ? 'SIM' : 'NÃO');
            
            // IDs simples como no layout original
            const graficoId = `grafico${dados.id}`;
            const gaugeId = `gauge${dados.id}`;
            
            console.log(`Procurando elementos: ${graficoId}, ${gaugeId}`);
            
            // Verificar se os elementos existem antes de criar os gráficos
            const elementoGrafico = document.getElementById(graficoId);
            const elementoGauge = document.getElementById(gaugeId);
            
            if (elementoGrafico && dados.dadosDiarios) {
                console.log(`Criando gráfico de barras para ${graficoId}`);
                criarGraficoBarras(dados, graficoId);
            } else {
                console.log(`Não foi possível criar gráfico de barras para ${graficoId}:`, {
                    elementoExiste: !!elementoGrafico,
                    temDados: !!dados.dadosDiarios
                });
            }
            
            if (elementoGauge) {
                console.log(`Criando gauge para ${gaugeId}`);
                criarGraficoGauge(dados, gaugeId);
            } else {
                console.log(`Elemento gauge não encontrado para ${gaugeId}`);
            }
        });
    } else {
        console.log('window.dadosGraficos não existe');
    }

    /**
     * Cria um gráfico de barras para visualização dos dados diários
     */
    function criarGraficoBarras(dados, elementId) {
        try {
            const ctx = document.getElementById(elementId).getContext('2d');
              // Preparar os dados para o gráfico de barras
            const labels = dados.dadosDiarios.map(d => `${d.dia}`);
            const pactuados = dados.dadosDiarios.map(d => d.pactuado);
            const agendados = dados.dadosDiarios.map(d => d.agendado);
            const realizados = dados.dadosDiarios.map(d => d.realizado);
            
            // Calcular valor máximo para adicionar espaço superior
            const maxValue = Math.max(...pactuados, ...agendados, ...realizados);
            const maxWithPadding = maxValue + 30;
            
            console.log(`Criando gráfico de barras para ${elementId} com ${labels.length} pontos de dados`);
            console.log(`Valor máximo: ${maxValue}, com padding: ${maxWithPadding}`);
              // Criar o gráfico de barras agrupadas
            const grafico = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pactuado',
                            data: pactuados,
                            backgroundColor: CORES.pactuado.background,
                            borderColor: CORES.pactuado.border,
                            borderWidth: 1,
                            borderRadius: 3,
                            borderSkipped: false
                        },
                        {
                            label: 'Agendados',
                            data: agendados,
                            backgroundColor: CORES.agendado.background,
                            borderColor: CORES.agendado.border,
                            borderWidth: 1,
                            borderRadius: 3,
                            borderSkipped: false
                        },
                        {
                            label: 'Realizados',
                            data: realizados,
                            backgroundColor: CORES.realizado.background,
                            borderColor: CORES.realizado.border,
                            borderWidth: 1,
                            borderRadius: 3,
                            borderSkipped: false
                        }
                    ]
                },                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        datalabels: {
                            display: function(context) {
                                // Mostrar apenas valores significativos
                                return context.dataset.data[context.dataIndex] > 0;
                            },
                            anchor: 'end',
                            align: 'top',
                            formatter: function(value) {
                                return value > 0 ? value : '';
                            },
                            color: '#333',
                            font: {
                                size: 9,
                                weight: 'bold'
                            }                        },                        legend: {
                            display: true,
                            position: 'top',
                            align: 'center',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 11
                                }
                            },                            // Espaçamento apenas da legenda para o gráfico
                            margin: {
                                bottom: 80
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.3)',
                            borderWidth: 1,
                            callbacks: {
                                title: function(tooltipItems) {
                                    const item = tooltipItems[0];
                                    const dia = dados.dadosDiarios[item.dataIndex].dia;
                                    const diaSemana = dados.dadosDiarios[item.dataIndex].dia_semana;
                                    return `Dia ${dia} (${diaSemana})`;
                                },
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.parsed.y}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                autoSkip: false,
                                maxRotation: 0,
                                font: {
                                    size: 10
                                },
                                // Personalizar o formato do rótulo para mostrar dia e dia da semana
                                callback: function(value, index) {
                                    if (dados.dadosDiarios[index]) {
                                        const dia = dados.dadosDiarios[index].dia;
                                        const diaSemana = dados.dadosDiarios[index].dia_semana;
                                        return [`${dia}`, `${diaSemana}`];
                                    }
                                    return value;
                                }
                            }                        },                        y: {
                            beginAtZero: true,
                            // Adicionar espaço de 30 pontos acima da barra mais alta
                            max: maxWithPadding,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                precision: 0,
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : '';
                                }
                            }
                        }
                    },                    layout: {
                        padding: {
                            top: 20  // Padding mínimo, apenas para espaçamento básico
                        }
                    },
                    // Configuração específica para barras agrupadas
                    elements: {
                        bar: {
                            borderWidth: 1
                        }
                    }
                }
            });
            
            console.log(`Gráfico de barras criado com sucesso para ${elementId}`);
            
        } catch (error) {
            console.error(`Erro ao criar gráfico de barras para ${elementId}:`, error);
        }
    }

    /**
     * Cria um gráfico de gauge para visualização do progresso
     * O gauge mostra o total_executados em relação à meta_pdt
     */
    function criarGraficoGauge(dados, elementId) {
        try {
            const ctx = document.getElementById(elementId).getContext('2d');
            
            // Calcular o progresso real baseado nos dados da query
            const total_executados = dados.total_executados;
            const meta_pdt = dados.meta_pdt;
            const progresso_real = meta_pdt > 0 ? Math.min(100, Math.round((total_executados / meta_pdt) * 100)) : 0;
            
            console.log(`Criando gauge para ${elementId}: ${total_executados}/${meta_pdt} = ${progresso_real}%`);
            
            // Usar cor do grupo se disponível
            let corProgresso = CORES.progresso.fill;
            if (dados.grupo_cor && dados.grupo_cor !== '#6B7280') {
                corProgresso = dados.grupo_cor;
            }
            
            // Criar o gráfico de gauge semicircular
            const gauge = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [progresso_real, 100 - progresso_real],
                        backgroundColor: [corProgresso, CORES.progresso.empty],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    rotation: -90,
                    circumference: 180,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        },
                        datalabels: {
                            display: false
                        }
                    }
                }
            });
            
            console.log(`Gauge criado com sucesso para ${elementId}`);
            
        } catch (error) {
            console.error(`Erro ao criar gauge para ${elementId}:`, error);
        }
    }

    // Remover funções de navegação entre abas (não necessário para abas fixas)
    // Layout original mantido com abas informativas apenas

    // Debug: Informações sobre grupos (se disponível)
    if (window.gruposInfo) {
        console.log('Informações dos grupos:', window.gruposInfo);
        window.gruposInfo.forEach(grupo => {
            console.log(`Grupo ${grupo.id}: ${grupo.nome} (${grupo.servicos_count} serviços) - Cor: ${grupo.cor}`);
        });
    }
});
