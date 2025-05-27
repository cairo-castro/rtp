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
            background: '#999999', // Cinza
            border: '#999999'
        },
        realizado: {
            background: '#fd7e14', // Laranja
            border: '#fd7e14'
        },
        progresso: {
            fill: '#fd7e14',      // Laranja (gauge preenchido)
            empty: '#e0e0e0'      // Cinza claro (gauge vazio)
        }
    };// Inicializar todos os gráficos
    if (window.dadosGraficos) {
        console.log('Dados recebidos para gráficos:', window.dadosGraficos);
        console.log('Tipo de dadosGraficos:', typeof window.dadosGraficos);
        console.log('É array?', Array.isArray(window.dadosGraficos));
        
        Object.values(window.dadosGraficos).forEach(function(dados, index) {
            console.log(`Processando gráfico ${index}:`, dados);
            console.log(`Tem dadosDiarios?`, dados.dadosDiarios ? 'SIM' : 'NÃO');
            console.log(`Elemento grafico${dados.id} existe?`, document.getElementById('grafico' + dados.id) ? 'SIM' : 'NÃO');
            console.log(`Elemento gauge${dados.id} existe?`, document.getElementById('gauge' + dados.id) ? 'SIM' : 'NÃO');
            
            // Verificar se os elementos existem antes de criar os gráficos
            const elementoGrafico = document.getElementById('grafico' + dados.id);
            const elementoGauge = document.getElementById('gauge' + dados.id);
            
            if (elementoGrafico && dados.dadosDiarios) {
                console.log(`Criando gráfico de barras para ${dados.id}`);
                criarGraficoBarras(dados);
            } else {
                console.log(`Não foi possível criar gráfico de barras para ${dados.id}:`, {
                    elementoExiste: !!elementoGrafico,
                    temDados: !!dados.dadosDiarios
                });
            }
            
            if (elementoGauge) {
                console.log(`Criando gauge para ${dados.id}`);
                criarGraficoGauge(dados);
            } else {
                console.log(`Elemento gauge não encontrado para ${dados.id}`);
            }
        });
    } else {
        console.log('window.dadosGraficos não existe');
    }
      /**
     * Cria um gráfico de barras para visualização dos dados diários
     */
    function criarGraficoBarras(dados) {
        try {
            const ctx = document.getElementById('grafico' + dados.id).getContext('2d');
            
            // Preparar os dados para o gráfico de barras
            const labels = dados.dadosDiarios.map(d => `${d.dia}`);
            const pactuados = dados.dadosDiarios.map(d => d.pactuado);
            const agendados = dados.dadosDiarios.map(d => d.agendado);
            const realizados = dados.dadosDiarios.map(d => d.realizado);
            
            console.log(`Criando gráfico de barras para ${dados.id} com ${labels.length} pontos de dados`);
            
            // Criar o gráfico de barras
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
                        borderWidth: 1
                    },
                    {
                        label: 'Agendados',
                        data: agendados,
                        backgroundColor: CORES.agendado.background,
                        borderColor: CORES.agendado.border,
                        borderWidth: 1
                    },
                    {
                        label: 'Realizados',
                        data: realizados,
                        backgroundColor: CORES.realizado.background,
                        borderColor: CORES.realizado.border,
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        display: function(context) {
                            // Mostrar apenas valores significativos
                            return context.dataset.data[context.dataIndex] > 5;
                        },
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value;
                        },
                        color: '#333',
                        font: {
                            size: 9,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: false // Ocultar legenda pois já temos uma personalizada
                    },
                    tooltip: {
                        callbacks: {
                            title: function(tooltipItems) {
                                const item = tooltipItems[0];
                                const dia = dados.dadosDiarios[item.dataIndex].dia;
                                const diaSemana = dados.dadosDiarios[item.dataIndex].dia_semana;
                                return `Dia ${dia} (${diaSemana})`;
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
                            autoSkip: true,
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
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            precision: 0
                        }
                    }
                },                layout: {
                    padding: {
                        top: 20
                    }
                }
            }
        });
        
        console.log(`Gráfico de barras criado com sucesso para ${dados.id}`);
        
    } catch (error) {
        console.error(`Erro ao criar gráfico de barras para ${dados.id}:`, error);
    }
}
      /**
     * Cria um gráfico de gauge para visualização do progresso
     * O gauge mostra o total_executados em relação à meta_pdt
     */
    function criarGraficoGauge(dados) {
        try {
            const ctx = document.getElementById('gauge' + dados.id).getContext('2d');
            
            // Calcular o progresso real baseado nos dados da query
            const total_executados = dados.total_executados;
            const meta_pdt = dados.meta_pdt;
            const progresso_real = meta_pdt > 0 ? Math.min(100, Math.round((total_executados / meta_pdt) * 100)) : 0;
            
            console.log(`Criando gauge para ${dados.id}: ${total_executados}/${meta_pdt} = ${progresso_real}%`);
            
            // Criar o gráfico de gauge semicircular
            const gauge = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [progresso_real, 100 - progresso_real],
                    backgroundColor: [CORES.progresso.fill, CORES.progresso.empty],
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
                    },                    datalabels: {
                        display: false
                    }
                }
            }
        });
        
        console.log(`Gauge criado com sucesso para ${dados.id}`);
        
    } catch (error) {
        console.error(`Erro ao criar gauge para ${dados.id}:`, error);
    }
}
});