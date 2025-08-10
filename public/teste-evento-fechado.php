<?php
/**
 * Teste Específico - Eventos "Fechado" no Calendário
 * Sistema Bichos do Bairro
 */

// Carregar dependências
if (file_exists(__DIR__ . '/../src/Utils.php')) {
    require_once __DIR__ . '/../src/Utils.php';
}
if (file_exists(__DIR__ . '/../src/BaseModel.php')) {
    require_once __DIR__ . '/../src/BaseModel.php';
}
if (file_exists(__DIR__ . '/../src/init.php')) {
    require_once __DIR__ . '/../src/init.php';
}
if (file_exists(__DIR__ . '/../src/Agendamento.php')) {
    require_once __DIR__ . '/../src/Agendamento.php';
}
if (file_exists(__DIR__ . '/../src/Cliente.php')) {
    require_once __DIR__ . '/../src/Cliente.php';
}
if (file_exists(__DIR__ . '/../src/Pet.php')) {
    require_once __DIR__ . '/../src/Pet.php';
}

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Teste Eventos Fechado - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <style>
        .fc-event[data-event-title='Fechado'] {
            background-color: #ff0000 !important;
            border-color: #ff0000 !important;
            color: #ffffff !important;
        }
        .fc-event[data-event-title='Fechado'] .fc-event-title {
            color: #ffffff !important;
            font-weight: bold !important;
        }
    </style>
</head>
<body class='bg-gray-50 p-8'>
    <div class='max-w-6xl mx-auto'>
        <h1 class='text-2xl font-bold mb-6'>🔍 Teste Específico - Eventos \"Fechado\"</h1>
        
        <div class='grid grid-cols-1 md:grid-cols-2 gap-6 mb-6'>
            <div class='bg-white rounded-lg shadow-lg p-6'>
                <h3 class='font-semibold mb-4'>📋 Configuração Atual</h3>
                <div id='config-info' class='text-sm space-y-2'></div>
            </div>
            
            <div class='bg-white rounded-lg shadow-lg p-6'>
                <h3 class='font-semibold mb-4'>📊 Eventos Gerados</h3>
                <div id='eventos-info' class='text-sm space-y-2'></div>
            </div>
        </div>
        
        <div class='bg-white rounded-lg shadow-lg p-6'>
            <div id='calendar'></div>
        </div>
        
        <div class='mt-6 bg-yellow-50 p-4 rounded-lg'>
            <h3 class='font-semibold mb-2'>🔍 Debug Console:</h3>
            <div id='debug-logs' class='text-sm font-mono bg-gray-100 p-3 rounded max-h-40 overflow-y-auto'></div>
        </div>
    </div>

    <script>
        function log(message) {
            const logsDiv = document.getElementById('debug-logs');
            const timestamp = new Date().toLocaleTimeString();
            logsDiv.innerHTML += `[${timestamp}] ${message}\\n`;
            logsDiv.scrollTop = logsDiv.scrollHeight;
            console.log(message);
        }

        function displayConfig(config) {
            const configDiv = document.getElementById('config-info');
            const diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
            
            let html = '<div class=\"space-y-2\">';
            html += `<div><strong>Horário:</strong> ${config.inicio} - ${config.fim}</div>`;
            html += `<div><strong>Intervalo:</strong> ${config.intervalo} min</div>`;
            html += '<div><strong>Dias de funcionamento:</strong></div>';
            html += '<ul class=\"list-disc list-inside ml-4\">';
            
            config.abertos.forEach((aberto, index) => {
                const status = aberto ? '✅ Aberto' : '❌ Fechado';
                html += `<li>${diasSemana[index]}: ${status}</li>`;
            });
            
            html += '</ul></div>';
            configDiv.innerHTML = html;
        }

        function displayEventos(eventos) {
            const eventosDiv = document.getElementById('eventos-info');
            let html = '<div class=\"space-y-2\">';
            
            const fechados = eventos.filter(e => e.title === 'Fechado');
            const agendamentos = eventos.filter(e => e.title !== 'Fechado');
            
            html += `<div><strong>Total de eventos:</strong> ${eventos.length}</div>`;
            html += `<div><strong>Agendamentos:</strong> ${agendamentos.length}</div>`;
            html += `<div><strong>Eventos \"Fechado\":</strong> ${fechados.length}</div>`;
            
            if (fechados.length > 0) {
                html += '<div class=\"mt-2\"><strong>Datas \"Fechado\":</strong></div>';
                html += '<ul class=\"list-disc list-inside ml-4 text-xs\">';
                fechados.forEach(evento => {
                    html += `<li>${evento.start}</li>`;
                });
                html += '</ul>';
            }
            
            html += '</div>';
            eventosDiv.innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', function() {
            log('Iniciando teste de eventos \"Fechado\"');
            
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                buttonText: {
                    dayGridMonth: 'Mês',
                },
                events: function(info, successCallback, failureCallback) {
                    log('Solicitando eventos para: ' + info.startStr + ' até ' + info.endStr);
                    
                    // Primeiro, buscar configuração
                    fetch('configuracoes.php?action=config')
                        .then(r => {
                            log('Status da resposta config: ' + r.status);
                            return r.text();
                        })
                        .then(configText => {
                            log('Configuração recebida: ' + configText);
                            const config = JSON.parse(configText);
                            displayConfig(config);
                            
                            // Buscar agendamentos
                            return fetch('agendamentos.php?action=listar');
                        })
                        .then(r => {
                            log('Status da resposta agendamentos: ' + r.status);
                            return r.text();
                        })
                        .then(eventosText => {
                            log('Eventos recebidos: ' + eventosText);
                            const eventos = JSON.parse(eventosText);
                            
                            // Verificar se já existem eventos \"Fechado\"
                            const fechadosExistentes = eventos.filter(e => e.title === 'Fechado');
                            log(`Eventos \"Fechado\" já existentes: ${fechadosExistentes.length}`);
                            
                            if (fechadosExistentes.length === 0) {
                                // Buscar configuração novamente para gerar eventos \"Fechado\"
                                return fetch('configuracoes.php?action=config')
                                    .then(r => r.text())
                                    .then(configText => {
                                        const config = JSON.parse(configText);
                                        
                                        // Gerar eventos \"Fechado\" para o mês atual
                                        const fechados = [];
                                        const start = info.start;
                                        const end = info.end;
                                        const data = new Date(start);
                                        
                                        log('Gerando eventos \"Fechado\" para o mês...');
                                        
                                        while (data < end) {
                                            const diaSemana = data.getDay();
                                            const aberto = config.abertos[diaSemana];
                                            
                                            log(`Dia ${data.toISOString().slice(0,10)} (${diaSemana}): aberto=${aberto}`);
                                            
                                            if (!aberto || parseInt(aberto) !== 1) {
                                                const eventoFechado = {
                                                    title: 'Fechado',
                                                    start: data.toISOString().slice(0,10),
                                                    allDay: true,
                                                    backgroundColor: '#ff0000',
                                                    borderColor: '#ff0000',
                                                    textColor: '#ffffff',
                                                    extendedProps: { fechado: true }
                                                };
                                                fechados.push(eventoFechado);
                                                log(`Criado evento \"Fechado\" para ${data.toISOString().slice(0,10)}`);
                                            }
                                            data.setDate(data.getDate() + 1);
                                        }
                                        
                                        const todosEventos = eventos.concat(fechados);
                                        log(`Total de eventos após adicionar \"Fechado\": ${todosEventos.length}`);
                                        displayEventos(todosEventos);
                                        successCallback(todosEventos);
                                    });
                            } else {
                                displayEventos(eventos);
                                successCallback(eventos);
                            }
                        })
                        .catch(error => {
                            log('Erro ao buscar eventos: ' + error.message);
                            failureCallback(error);
                        });
                },
                eventDidMount: function(info) {
                    if (info.event.title === 'Fechado') {
                        log('Evento \"Fechado\" montado: ' + info.event.startStr);
                        info.el.classList.add('evento-fechado');
                    }
                },
                eventContent: function(arg) {
                    if (arg.event.title === 'Fechado') {
                        log('Renderizando evento Fechado: ' + arg.event.startStr);
                        return {
                            html: '<div style=\"color:#ffffff;font-weight:bold;background:#ff0000;border-radius:4px;padding:4px 8px;text-align:center;font-size:12px;min-height:20px;display:flex;align-items:center;justify-content:center;border:1px solid #ff0000;\">FECHADO</div>'
                        };
                    }
                },
                eventDisplay: 'block',
                dayMaxEvents: false,
                allDaySlot: true,
                allDayMaintainDuration: true,
            });
            
            calendar.render();
            log('Calendário renderizado');
        });
    </script>
</body>
</html>";
?> 