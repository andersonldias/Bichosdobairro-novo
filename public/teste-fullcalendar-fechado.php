<?php
require_once '../src/init.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste FullCalendar - Eventos Fechado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <style>
        /* CSS específico para eventos "Fechado" */
        .fc-event[data-event-title="Fechado"] {
            background-color: #ff0000 !important;
            border-color: #ff0000 !important;
            color: #ffffff !important;
        }
        
        .fc-event[data-event-title="Fechado"] .fc-event-title {
            color: #ffffff !important;
            font-weight: bold !important;
        }
        
        /* Garantir que eventos allDay sejam visíveis */
        .fc-daygrid-event {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .fc-daygrid-day-events {
            min-height: 20px !important;
        }
        
        /* Debug: destacar células com eventos */
        .fc-daygrid-day.has-events {
            background-color: #f0f8ff !important;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Teste FullCalendar - Eventos "Fechado"</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">Debug Info</h3>
                <div id="debug-info" class="text-sm space-y-2"></div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">Eventos Recebidos</h3>
                <div id="eventos-info" class="text-sm space-y-2"></div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Logs de Debug:</h3>
            <div id="debug-logs" class="text-sm font-mono bg-gray-100 p-3 rounded max-h-40 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        function log(message) {
            const logsDiv = document.getElementById('debug-logs');
            const timestamp = new Date().toLocaleTimeString();
            logsDiv.innerHTML += `[${timestamp}] ${message}\n`;
            logsDiv.scrollTop = logsDiv.scrollHeight;
            console.log(message);
        }

        function displayDebugInfo(info) {
            const debugDiv = document.getElementById('debug-info');
            debugDiv.innerHTML = `
                <div><strong>Start:</strong> ${info.startStr}</div>
                <div><strong>End:</strong> ${info.endStr}</div>
                <div><strong>View:</strong> ${info.view.type}</div>
            `;
        }

        function displayEventos(eventos) {
            const eventosDiv = document.getElementById('eventos-info');
            const fechados = eventos.filter(e => e.title === 'Fechado');
            const agendamentos = eventos.filter(e => e.title !== 'Fechado');
            
            let html = `
                <div><strong>Total:</strong> ${eventos.length}</div>
                <div><strong>Agendamentos:</strong> ${agendamentos.length}</div>
                <div><strong>Fechado:</strong> ${fechados.length}</div>
            `;
            
            if (fechados.length > 0) {
                html += '<div class="mt-2"><strong>Datas Fechado:</strong></div>';
                html += '<ul class="list-disc list-inside ml-4 text-xs">';
                fechados.forEach(evento => {
                    html += `<li>${evento.start}</li>`;
                });
                html += '</ul>';
            }
            
            eventosDiv.innerHTML = html;
        }

        document.addEventListener('DOMContentLoaded', function() {
            log('Iniciando teste do FullCalendar...');
            
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: '2025-07-01',
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
                events: function(info, successCallback) {
                    log('Buscando eventos para: ' + info.startStr + ' até ' + info.endStr);
                    displayDebugInfo(info);
                    
                    // Adicionar cache-busting
                    const url = 'agendamentos.php?action=listar&t=' + Date.now();
                    log('Fazendo requisição para: ' + url);
                    
                    fetch(url)
                        .then(r => {
                            log('Status da resposta: ' + r.status);
                            return r.text();
                        })
                        .then(text => {
                            log('Resposta recebida (primeiros 200 chars): ' + text.substring(0, 200));
                            return JSON.parse(text);
                        })
                        .then(eventos => {
                            log('Eventos parseados: ' + eventos.length + ' eventos');
                            displayEventos(eventos);
                            
                            // Log detalhado dos eventos "Fechado"
                            const fechados = eventos.filter(e => e.title === 'Fechado');
                            log('Eventos "Fechado" encontrados: ' + fechados.length);
                            fechados.forEach(evento => {
                                log('Evento Fechado: ' + evento.start + ' - ' + evento.title);
                            });
                            
                            successCallback(eventos);
                        })
                        .catch(error => {
                            log('Erro: ' + error.message);
                        });
                },
                eventDidMount: function(info) {
                    if (info.event.title === 'Fechado') {
                        log('Evento "Fechado" montado: ' + info.event.startStr);
                        // Adicionar classe para debug
                        info.el.classList.add('evento-fechado');
                    }
                },
                eventContent: function(arg) {
                    if (arg.event.title === 'Fechado') {
                        log('Renderizando evento Fechado: ' + arg.event.startStr);
                        return {
                            html: '<div style="color:#ffffff;font-weight:bold;background:#ff0000;border-radius:4px;padding:4px 8px;text-align:center;font-size:12px;min-height:20px;display:flex;align-items:center;justify-content:center;border:1px solid #ff0000;">FECHADO</div>'
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
</html> 