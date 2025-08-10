<?php
/**
 * Teste Melhorado - Eventos "Fechado" no Calendário
 * Sistema Bichos do Bairro
 * 
 * Este script carrega a configuração diretamente do arquivo JSON
 * e não depende do servidor estar rodando
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

// Carregar agendamentos diretamente
$agendamentos = [];
if (file_exists(__DIR__ . '/../src/Agendamento.php')) {
    require_once __DIR__ . '/../src/Utils.php';
    require_once __DIR__ . '/../src/BaseModel.php';
    require_once __DIR__ . '/../src/init.php';
    require_once __DIR__ . '/../src/Agendamento.php';
    
    if (class_exists('Agendamento')) {
        $agendamentos = Agendamento::listarTodos();
    }
}

// Converter agendamentos para formato do FullCalendar
$eventos = [];
foreach ($agendamentos as $a) {
    $eventos[] = [
        'id' => $a['id'],
        'title' => ($a['pet_nome'] ?? 'Pet') . ' - ' . $a['servico'],
        'start' => $a['data'] . 'T' . $a['hora'],
        'end' => $a['data'] . 'T' . $a['hora'],
        'extendedProps' => [
            'pet_id' => $a['pet_id'],
            'cliente_id' => $a['cliente_id'],
            'servico' => $a['servico'],
            'status' => $a['status'],
            'observacoes' => $a['observacoes'],
            'pet_nome' => $a['pet_nome'] ?? 'Pet',
            'cliente_nome' => $a['cliente_nome'] ?? 'Cliente',
        ]
    ];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste Melhorado - Eventos "Fechado"</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <style>
        /* CSS para garantir que eventos "Fechado" apareçam corretamente */
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
    </style>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">🔧 Teste Melhorado - Eventos "Fechado"</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">📋 Configuração Carregada</h3>
                <div class="text-sm space-y-2">
                    <div><strong>Horário:</strong> <?= $config['inicio'] ?> - <?= $config['fim'] ?></div>
                    <div><strong>Intervalo:</strong> <?= $config['intervalo'] ?> min</div>
                    <div><strong>Dias de funcionamento:</strong></div>
                    <ul class="list-disc list-inside ml-4">
                        <?php 
                        $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
                        foreach ($config['abertos'] as $i => $aberto): 
                            $status = $aberto ? '✅ Aberto' : '❌ Fechado';
                        ?>
                            <li><?= $diasSemana[$i] ?>: <?= $status ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="font-semibold mb-4">📊 Agendamentos</h3>
                <div class="text-sm space-y-2">
                    <div><strong>Total de agendamentos:</strong> <?= count($agendamentos) ?></div>
                    <?php if (count($agendamentos) > 0): ?>
                        <div><strong>Últimos agendamentos:</strong></div>
                        <ul class="list-disc list-inside ml-4 text-xs">
                            <?php foreach (array_slice($agendamentos, 0, 3) as $a): ?>
                                <li><?= $a['data'] ?> <?= $a['hora'] ?> - <?= ($a['cliente_nome'] ?? 'Cliente') ?> (<?= ($a['pet_nome'] ?? 'Pet') ?>)</li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
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
        const eventosExistentes = <?= json_encode($eventos) ?>;
        
        function log(message) {
            const logsDiv = document.getElementById('debug-logs');
            const timestamp = new Date().toLocaleTimeString();
            logsDiv.innerHTML += `[${timestamp}] ${message}\n`;
            logsDiv.scrollTop = logsDiv.scrollHeight;
            console.log(message);
        }

        document.addEventListener('DOMContentLoaded', function() {
            log('Iniciando teste melhorado de eventos "Fechado"');
            log('Configuração carregada: ' + JSON.stringify(config));
            log('Agendamentos carregados: ' + eventosExistentes.length);
            
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
                    
                    const todosEventos = eventosExistentes.concat(fechados);
                    log(`Total de eventos: ${todosEventos.length} (${eventosExistentes.length} agendamentos + ${fechados.length} fechados)`);
                    successCallback(todosEventos);
                },
                eventDidMount: function(info) {
                    if (info.event.title === 'Fechado') {
                        log('Evento "Fechado" montado: ' + info.event.startStr);
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