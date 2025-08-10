<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../src/init.php';

// Endpoint para testar carregamento de eventos
if (isset($_GET['action']) && $_GET['action'] === 'teste_eventos') {
    header('Content-Type: application/json');
    
    // Criar alguns eventos de teste
    $eventos_teste = [
        [
            'id' => 'teste1',
            'title' => 'Rex - Banho',
            'start' => date('Y-m-d') . 'T10:00:00',
            'end' => date('Y-m-d') . 'T11:00:00',
            'extendedProps' => [
                'pet_id' => 1,
                'cliente_id' => 1,
                'servico' => 'Banho',
                'status' => 'Pendente',
                'observacoes' => 'Teste de evento',
                'pet_nome' => 'Rex',
                'cliente_nome' => 'João Silva',
            ]
        ],
        [
            'id' => 'teste2',
            'title' => 'Luna - Tosa',
            'start' => date('Y-m-d', strtotime('+1 day')) . 'T14:00:00',
            'end' => date('Y-m-d', strtotime('+1 day')) . 'T15:00:00',
            'extendedProps' => [
                'pet_id' => 2,
                'cliente_id' => 2,
                'servico' => 'Tosa',
                'status' => 'Pendente',
                'observacoes' => 'Teste de evento 2',
                'pet_nome' => 'Luna',
                'cliente_nome' => 'Maria Santos',
            ]
        ]
    ];
    
    echo json_encode($eventos_teste);
    exit;
}

// Endpoint para testar configurações
if (isset($_GET['action']) && $_GET['action'] === 'teste_config') {
    header('Content-Type: application/json');
    
    $config_teste = [
        'inicio' => '08:00',
        'fim' => '18:00',
        'intervalo' => 20,
        'abertos' => [0, 1, 1, 1, 1, 1, 0], // Domingo e sábado fechados
        'intervalos' => [20, 20, 20, 20, 20, 20, 20]
    ];
    
    echo json_encode($config_teste);
    exit;
}

include 'layout.php';

function render_content() {
?>
<div class="max-w-6xl mx-auto mt-8 space-y-6">
    <h1 class="text-3xl font-bold text-center mb-8">Teste Completo do FullCalendar</h1>
    
    <!-- Status do sistema -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Status do Sistema</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="font-semibold text-blue-800">FullCalendar</h3>
                <p class="text-blue-600">Versão: 6.1.11</p>
                <p class="text-blue-600">Status: <span id="fc-status">Carregando...</span></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="font-semibold text-green-800">Eventos</h3>
                <p class="text-green-600">Total: <span id="eventos-count">0</span></p>
                <p class="text-green-600">Status: <span id="eventos-status">Carregando...</span></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <h3 class="font-semibold text-purple-800">Configurações</h3>
                <p class="text-purple-600">Dias abertos: <span id="dias-abertos">-</span></p>
                <p class="text-purple-600">Status: <span id="config-status">Carregando...</span></p>
            </div>
        </div>
    </div>

    <!-- FullCalendar CSS/JS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <!-- Container do calendário -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Calendário de Teste</h2>
            <div class="flex gap-2">
                <button onclick="testarCarregamentoEventos()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    Testar Eventos
                </button>
                <button onclick="testarConfiguracoes()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                    Testar Config
                </button>
                <button onclick="limparLogs()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Limpar Logs
                </button>
            </div>
        </div>
        
        <div id="calendar" class="mb-8"></div>
    </div>

    <!-- Logs de debug -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Logs de Debug</h2>
        <div id="debug-logs" class="bg-gray-100 p-4 rounded-lg h-64 overflow-y-auto font-mono text-sm">
            <div class="text-gray-500">Logs aparecerão aqui...</div>
        </div>
    </div>

    <!-- Testes manuais -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Testes Manuais</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="font-semibold mb-2">Teste de Eventos</h3>
                <button onclick="adicionarEventoTeste()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 mb-2">
                    Adicionar Evento Teste
                </button>
                <button onclick="removerEventosTeste()" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    Remover Eventos Teste
                </button>
            </div>
            <div>
                <h3 class="font-semibold mb-2">Teste de Interação</h3>
                <button onclick="testarCliqueData()" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 mb-2">
                    Simular Clique na Data
                </button>
                <button onclick="testarNavegacao()" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    Testar Navegação
                </button>
            </div>
        </div>
    </div>

    <script>
    var calendar;
    var eventosTeste = [];

    // Função para adicionar logs
    function adicionarLog(mensagem, tipo = 'info') {
        var logs = document.getElementById('debug-logs');
        var timestamp = new Date().toLocaleTimeString();
        var cor = tipo === 'error' ? 'text-red-600' : tipo === 'success' ? 'text-green-600' : 'text-blue-600';
        logs.innerHTML += '<div class="' + cor + '">[' + timestamp + '] ' + mensagem + '</div>';
        logs.scrollTop = logs.scrollHeight;
    }

    // Função para limpar logs
    function limparLogs() {
        document.getElementById('debug-logs').innerHTML = '<div class="text-gray-500">Logs limpos...</div>';
    }

    // Função para testar carregamento de eventos
    function testarCarregamentoEventos() {
        adicionarLog('Testando carregamento de eventos...');
        fetch('teste-fullcalendar-completo.php?action=teste_eventos')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erro na resposta: ' + response.status);
                }
                return response.json();
            })
            .then(function(eventos) {
                adicionarLog('Eventos carregados com sucesso: ' + eventos.length + ' eventos', 'success');
                document.getElementById('eventos-count').textContent = eventos.length;
                document.getElementById('eventos-status').textContent = 'OK';
                return eventos;
            })
            .catch(function(error) {
                adicionarLog('Erro ao carregar eventos: ' + error.message, 'error');
                document.getElementById('eventos-status').textContent = 'ERRO';
            });
    }

    // Função para testar configurações
    function testarConfiguracoes() {
        adicionarLog('Testando carregamento de configurações...');
        fetch('teste-fullcalendar-completo.php?action=teste_config')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erro na resposta: ' + response.status);
                }
                return response.json();
            })
            .then(function(config) {
                var diasAbertos = config.abertos.filter(function(dia) { return dia === 1; }).length;
                adicionarLog('Configurações carregadas: ' + diasAbertos + ' dias abertos', 'success');
                document.getElementById('dias-abertos').textContent = diasAbertos;
                document.getElementById('config-status').textContent = 'OK';
                return config;
            })
            .catch(function(error) {
                adicionarLog('Erro ao carregar configurações: ' + error.message, 'error');
                document.getElementById('config-status').textContent = 'ERRO';
            });
    }

    // Função para adicionar evento de teste
    function adicionarEventoTeste() {
        var evento = {
            id: 'teste-' + Date.now(),
            title: 'Evento Teste ' + new Date().toLocaleTimeString(),
            start: new Date().toISOString(),
            end: new Date(Date.now() + 3600000).toISOString(),
            backgroundColor: '#ff6b6b',
            borderColor: '#ff6b6b'
        };
        
        calendar.addEvent(evento);
        eventosTeste.push(evento);
        adicionarLog('Evento de teste adicionado: ' + evento.title, 'success');
    }

    // Função para remover eventos de teste
    function removerEventosTeste() {
        eventosTeste.forEach(function(evento) {
            calendar.getEventById(evento.id).remove();
        });
        eventosTeste = [];
        adicionarLog('Eventos de teste removidos', 'success');
    }

    // Função para testar clique na data
    function testarCliqueData() {
        var hoje = new Date();
        var info = {
            dateStr: hoje.toISOString().slice(0, 10),
            date: hoje
        };
        adicionarLog('Simulando clique na data: ' + info.dateStr);
        // Simular o evento dateClick
        if (calendar) {
            calendar.trigger('dateClick', info);
        }
    }

    // Função para testar navegação
    function testarNavegacao() {
        if (calendar) {
            calendar.next();
            adicionarLog('Navegando para o próximo mês', 'success');
        }
    }

    // Inicialização do FullCalendar
    document.addEventListener('DOMContentLoaded', function() {
        adicionarLog('Inicializando FullCalendar...');
        
        var calendarEl = document.getElementById('calendar');
        if (!calendarEl) {
            adicionarLog('Elemento calendar não encontrado!', 'error');
            return;
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next',
                center: 'title',
                right: 'timeGridDay,dayGridMonth'
            },
            buttonText: {
                dayGridMonth: 'Mês',
                timeGridDay: 'Dia',
            },
            events: function(info, successCallback) {
                adicionarLog('Carregando eventos para: ' + info.startStr + ' até ' + info.endStr);
                
                // Usar eventos de teste
                fetch('teste-fullcalendar-completo.php?action=teste_eventos')
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Erro na resposta do servidor: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function(eventos) {
                        // Buscar configurações para dias fechados
                        return fetch('teste-fullcalendar-completo.php?action=teste_config')
                            .then(function(response) {
                                if (!response.ok) {
                                    console.warn('Erro ao carregar configurações, usando padrão');
                                    return null;
                                }
                                return response.json();
                            })
                            .then(function(config) {
                                var diasFechados = [];
                                if (config && config.abertos && Array.isArray(config.abertos)) {
                                    for (var i = 0; i < 7; i++) {
                                        if (parseInt(config.abertos[i]) !== 1) diasFechados.push(i);
                                    }
                                }
                                
                                // Gerar eventos 'Fechado' para o mês atual
                                var fechados = [];
                                var start = info.start;
                                var end = info.end;
                                var data = new Date(start);
                                while (data < end) {
                                    var diaSemana = data.getDay();
                                    if (diasFechados.includes(diaSemana)) {
                                        fechados.push({
                                            title: 'Fechado',
                                            start: data.toISOString().slice(0,10),
                                            allDay: true,
                                            display: 'background',
                                            backgroundColor: '#f8d7da',
                                            borderColor: '#f8d7da',
                                            textColor: '#b71c1c',
                                            extendedProps: { fechado: true }
                                        });
                                    }
                                    data.setDate(data.getDate() + 1);
                                }
                                
                                var todosEventos = eventos.concat(fechados);
                                adicionarLog('Total de eventos carregados: ' + todosEventos.length, 'success');
                                successCallback(todosEventos);
                            })
                            .catch(function(error) {
                                adicionarLog('Erro ao carregar configuração: ' + error.message, 'error');
                                successCallback(eventos || []);
                            });
                    })
                    .catch(function(error) {
                        adicionarLog('Erro ao carregar agendamentos: ' + error.message, 'error');
                        successCallback([]);
                    });
            },
            dateClick: function(info) {
                adicionarLog('Clique na data: ' + info.dateStr, 'success');
                alert('Clique na data: ' + info.dateStr);
            },
            eventClick: function(info) {
                adicionarLog('Clique no evento: ' + info.event.title, 'success');
                alert('Clique no evento: ' + info.event.title);
            },
            loading: function(isLoading) {
                adicionarLog('Calendário carregando: ' + (isLoading ? 'SIM' : 'NÃO'));
            },
            eventDidMount: function(info) {
                adicionarLog('Evento montado: ' + info.event.title);
            },
            datesSet: function(info) {
                adicionarLog('Período alterado: ' + info.startStr + ' até ' + info.endStr);
            }
        });
        
        calendar.render();
        adicionarLog('FullCalendar inicializado com sucesso!', 'success');
        document.getElementById('fc-status').textContent = 'OK';
        
        // Estilizar botões
        setTimeout(function() {
            var btns = document.querySelectorAll('.fc-button');
            btns.forEach(function(btn) {
                btn.classList.add('bg-blue-500','text-white','hover:bg-blue-600','font-semibold','rounded','border-0');
            });
            adicionarLog('Botões estilizados');
        }, 300);
        
        // Executar testes automáticos
        setTimeout(function() {
            testarCarregamentoEventos();
            testarConfiguracoes();
        }, 1000);
    });
    </script>
</div>
<?php
}
?> 