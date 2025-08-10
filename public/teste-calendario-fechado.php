<?php
require_once '../src/init.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste Calendário - Eventos Fechado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <style>
        /* CSS para garantir que eventos "Fechado" apareçam corretamente */
        .fc-event-title {
            color: inherit !important;
            opacity: 1 !important;
            display: block !important;
        }
        
        .fc-event-main {
            color: inherit !important;
            opacity: 1 !important;
            display: block !important;
        }
        
        /* Forçar exibição do texto "Fechado" */
        .fc-event[data-event-title="Fechado"] .fc-event-title,
        .fc-event[data-event-title="Fechado"] .fc-event-main {
            color: #ffffff !important;
            font-weight: bold !important;
            opacity: 1 !important;
            display: block !important;
            background-color: #ff0000 !important;
        }
        
        /* Estilo específico para eventos "Fechado" */
        .fc-event[data-event-title="Fechado"] {
            background-color: #ff0000 !important;
            border-color: #ff0000 !important;
            color: #ffffff !important;
        }
        
        /* Garantir que eventos allDay sejam visíveis */
        .fc-daygrid-event {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        .fc-daygrid-day-events {
            min-height: 20px !important;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Teste - Eventos "Fechado" no Calendário</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Instruções de Teste:</h3>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>Verifique se o texto "Fechado" aparece nos domingos e sábados</li>
                <li>Abra o DevTools (F12) e veja o console para logs de debug</li>
                <li>Inspecione os elementos dos dias "Fechado" para ver se o texto está no DOM</li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                events: function(info, successCallback) {
                    // Buscar agendamentos e adicionar eventos 'Fechado'
                    fetch('agendamentos.php?action=listar')
                        .then(r => r.text())
                        .then(eventos => {
                            fetch('configuracoes.php?action=config')
                                .then(r => r.text())
                                .then(config => {
                                    var diasFechados = [];
                                    for (var i = 0; i < 7; i++) {
                                        if (!config.abertos[i] || parseInt(config.abertos[i]) !== 1) diasFechados.push(i);
                                    }
                                    console.log('Dias fechados configurados:', diasFechados);
                                    
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
                                                backgroundColor: 'transparent',
                                                borderColor: 'transparent',
                                                textColor: '#ff0000',
                                                extendedProps: { fechado: true }
                                            });
                                        }
                                        data.setDate(data.getDate() + 1);
                                    }
                                    
                                    var todosEventos = eventos.concat(fechados);
                                    console.log('Eventos "Fechado" criados:', fechados);
                                    console.log('Todos os eventos:', todosEventos);
                                    successCallback(todosEventos);
                                });
                        });
                },
                eventContent: function(arg) {
                    if (arg.event.title === 'Fechado') {
                        console.log('Renderizando evento Fechado:', arg.event);
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
        });
    </script>
</body>
</html> 