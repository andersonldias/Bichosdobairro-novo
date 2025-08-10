<?php
require_once '../src/init.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste Calendário - Eventos Fechado Estáticos</title>
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
            color: #ff0000 !important;
            font-weight: bold !important;
            opacity: 1 !important;
            display: block !important;
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
        <h1 class="text-2xl font-bold mb-6">Teste - Eventos "Fechado" Estáticos</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <div class="mt-6 bg-green-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Eventos de Teste:</h3>
            <ul class="list-disc list-inside space-y-1 text-sm">
                <li>Eventos "Fechado" estáticos para todos os domingos de julho/2025</li>
                <li>Eventos normais de agendamento</li>
                <li>Verifique se o texto "Fechado" aparece nos domingos</li>
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
                events: [
                    // Eventos "Fechado" estáticos para domingos de julho/2025
                    {
                        title: 'Fechado',
                        start: '2025-07-06',
                        allDay: true,
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        textColor: '#ff0000',
                        extendedProps: { fechado: true }
                    },
                    {
                        title: 'Fechado',
                        start: '2025-07-13',
                        allDay: true,
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        textColor: '#ff0000',
                        extendedProps: { fechado: true }
                    },
                    {
                        title: 'Fechado',
                        start: '2025-07-20',
                        allDay: true,
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        textColor: '#ff0000',
                        extendedProps: { fechado: true }
                    },
                    {
                        title: 'Fechado',
                        start: '2025-07-27',
                        allDay: true,
                        backgroundColor: 'transparent',
                        borderColor: 'transparent',
                        textColor: '#ff0000',
                        extendedProps: { fechado: true }
                    },
                    // Eventos normais de agendamento
                    {
                        id: 6,
                        title: 'Preta - Tosa',
                        start: '2025-07-15T09:00:00',
                        end: '2025-07-15T09:00:00',
                        extendedProps: {
                            pet_id: 7,
                            cliente_id: 22,
                            servico: 'Tosa',
                            status: 'pendente',
                            observacoes: 'Tosar bem baixo',
                            pet_nome: 'Preta',
                            cliente_nome: 'Anderson Luiz Dias'
                        }
                    },
                    {
                        id: 5,
                        title: 'Dora - Banho',
                        start: '2025-07-15T08:00:00',
                        end: '2025-07-15T08:00:00',
                        extendedProps: {
                            pet_id: 2,
                            cliente_id: 20,
                            servico: 'Banho',
                            status: 'pendente',
                            observacoes: '',
                            pet_nome: 'Dora',
                            cliente_nome: 'Ana Claudia'
                        }
                    }
                ],
                eventContent: function(arg) {
                    if (arg.event.title === 'Fechado') {
                        console.log('Renderizando evento Fechado estático:', arg.event);
                        return {
                            html: '<div style="color:#ff0000;font-weight:bold;background:transparent;border-radius:4px;padding:2px 4px;text-align:center;font-size:14px;min-height:20px;display:flex;align-items:center;justify-content:center;">Fechado</div>'
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