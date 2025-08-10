<?php
/**
 * Teste CSS FullCalendar
 * Sistema Bichos do Bairro
 * 
 * Este script testa se o CSS do FullCalendar está carregando corretamente
 */

// Carregar configuração diretamente do arquivo
$config_file = __DIR__ . '/../config_agenda.json';
$config = [
    'inicio' => '08:00',
    'fim' => '18:00',
    'intervalo' => 20,
    'abertos' => [1,1,1,1,1,1,1]
];

if (file_exists($config_file)) {
    $json = file_get_contents($config_file);
    $data_config = json_decode($json, true);
    if (is_array($data_config)) {
        $config = array_merge($config, $data_config);
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste CSS FullCalendar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Teste com diferentes CDNs do FullCalendar -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    
    <!-- CSS alternativo caso o principal falhe -->
    <style>
        /* CSS de fallback para eventos "Fechado" */
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
        
        /* Estilo personalizado para eventos "Fechado" */
        .evento-fechado {
            background-color: #ff0000 !important;
            border-color: #ff0000 !important;
            color: #ffffff !important;
            font-weight: bold !important;
        }
        
        .evento-fechado .fc-event-title {
            color: #ffffff !important;
        }
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">🔧 Teste CSS FullCalendar</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">📋 Status dos Recursos</h3>
                <div class="text-sm space-y-2">
                    <div id="css-status">🔍 Verificando CSS...</div>
                    <div id="js-status">🔍 Verificando JavaScript...</div>
                    <div id="fullcalendar-status">🔍 Verificando FullCalendar...</div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">📊 Configuração</h3>
                <div class="text-sm space-y-2">
                    <div><strong>Horário:</strong> <?= $config['inicio'] ?> - <?= $config['fim'] ?></div>
                    <div><strong>Intervalo:</strong> <?= $config['intervalo'] ?> min</div>
                    <div><strong>Dias fechados:</strong></div>
                    <ul class="list-disc list-inside ml-4">
                        <?php 
                        $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                        foreach ($config['abertos'] as $i => $aberto): 
                            if (!$aberto):
                        ?>
                            <li class="text-red-600"><?= $diasSemana[$i] ?>: ❌ Fechado</li>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div id="calendar"></div>
        </div>
        
        <div class="mt-6 bg-yellow-50 p-4 rounded-lg">
            <h3 class="font-semibold mb-2">🔍 Debug Console:</h3>
            <div id="debug-logs" class="text-sm font-mono bg-gray-100 p-3 rounded max-h-40 overflow-y-auto"></div>
        </div>
    </div>

    <script>
        // Configuração carregada do PHP
        const config = <?= json_encode($config) ?>;
        
        function log(message) {
            const logsDiv = document.getElementById('debug-logs');
            const timestamp = new Date().toLocaleTimeString();
            logsDiv.innerHTML += `[${timestamp}] ${message}\n`;
            logsDiv.scrollTop = logsDiv.scrollHeight;
            console.log(message);
        }

        function updateStatus(elementId, status, message) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = `${status} ${message}`;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            log('Iniciando teste CSS FullCalendar');
            
            // Verificar se o CSS foi carregado
            const cssLoaded = document.querySelector('link[href*="fullcalendar"]');
            if (cssLoaded) {
                updateStatus('css-status', '✅', 'CSS FullCalendar carregado');
                log('CSS FullCalendar carregado com sucesso');
            } else {
                updateStatus('css-status', '❌', 'CSS FullCalendar não carregado');
                log('ERRO: CSS FullCalendar não foi carregado');
            }
            
            // Verificar se o JavaScript foi carregado
            if (typeof FullCalendar !== 'undefined') {
                updateStatus('js-status', '✅', 'JavaScript FullCalendar carregado');
                log('JavaScript FullCalendar carregado com sucesso');
            } else {
                updateStatus('js-status', '❌', 'JavaScript FullCalendar não carregado');
                log('ERRO: JavaScript FullCalendar não foi carregado');
            }
            
            // Verificar se o FullCalendar está funcionando
            if (typeof FullCalendar !== 'undefined' && FullCalendar.Calendar) {
                updateStatus('fullcalendar-status', '✅', 'FullCalendar funcionando');
                log('FullCalendar está funcionando corretamente');
            } else {
                updateStatus('fullcalendar-status', '❌', 'FullCalendar não funcionando');
                log('ERRO: FullCalendar não está funcionando');
            }
            
            // Só inicializar o calendário se o FullCalendar estiver disponível
            if (typeof FullCalendar === 'undefined' || !FullCalendar.Calendar) {
                log('ERRO: FullCalendar não disponível, não é possível inicializar o calendário');
                return;
            }
            
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
                    
                    // Gerar eventos "Fechado" para o mês atual
                    const fechados = [];
                    const start = info.start;
                    const end = info.end;
                    const data = new Date(start);
                    
                    log('Gerando eventos "Fechado" para o mês...');
                    
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
                            log(`Criado evento "Fechado" para ${data.toISOString().slice(0,10)}`);
                        }
                        data.setDate(data.getDate() + 1);
                    }
                    
                    log(`Total de eventos "Fechado": ${fechados.length}`);
                    successCallback(fechados);
                },
                eventDidMount: function(info) {
                    if (info.event.title === 'Fechado') {
                        log('Evento "Fechado" montado: ' + info.event.startStr);
                        info.el.classList.add('evento-fechado');
                        
                        // Verificar se o CSS foi aplicado
                        const computedStyle = window.getComputedStyle(info.el);
                        const backgroundColor = computedStyle.backgroundColor;
                        log(`CSS aplicado - background-color: ${backgroundColor}`);
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