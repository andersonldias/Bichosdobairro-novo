<?php
require_once '../src/init.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste Calendário Simples</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Teste Calendário Simples</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <div class="mt-6 bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">Status:</h3>
            <div id="status" class="text-sm"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var statusDiv = document.getElementById('status');
            
            statusDiv.innerHTML = 'Iniciando calendário...';
            
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
                    statusDiv.innerHTML = 'Buscando eventos...';
                    
                    fetch('agendamentos.php?action=listar&t=' + Date.now())
                        .then(r => {
                            statusDiv.innerHTML = 'Resposta recebida: ' + r.status;
                            return r.json();
                        })
                        .then(eventos => {
                            statusDiv.innerHTML = 'Eventos carregados: ' + eventos.length + ' eventos';
                            console.log('Eventos:', eventos);
                            successCallback(eventos);
                        })
                        .catch(error => {
                            statusDiv.innerHTML = 'Erro: ' + error.message;
                            console.error('Erro:', error);
                            successCallback([]);
                        });
                },
                eventContent: function(arg) {
                    if (arg.event.title === 'Fechado') {
                        return {
                            html: '<div style="color:#000000;font-weight:bold;background:transparent;border-radius:4px;padding:2px 4px;text-align:center;font-size:12px;min-height:20px;display:flex;align-items:center;justify-content:center;">FECHADO</div>'
                        };
                    }
                },
                eventDisplay: 'block',
                dayMaxEvents: false,
                allDaySlot: true,
                allDayMaintainDuration: true,
            });
            
            calendar.render();
            statusDiv.innerHTML = 'Calendário renderizado com sucesso!';
        });
    </script>
</body>
</html> 