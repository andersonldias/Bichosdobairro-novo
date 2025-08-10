<?php
require_once '../src/init.php';

// Simular a requisição
$_GET['action'] = 'listar';

// Capturar a saída
ob_start();

// Incluir apenas a parte do endpoint
$todos = Agendamento::listarTodos();

// Endpoint para o FullCalendar: retorna agendamentos em JSON
if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    header('Content-Type: application/json');
    $eventos = [];
    foreach ($todos as $a) {
        $eventos[] = [
            'id' => $a['id'],
            'title' => $a['pet_nome'] . ' - ' . $a['servico'],
            'start' => $a['data'] . 'T' . $a['hora'],
            'end' => $a['data'] . 'T' . $a['hora'], // Pode ser ajustado para duração
            'extendedProps' => [
                'pet_id' => $a['pet_id'],
                'cliente_id' => $a['cliente_id'],
                'servico' => $a['servico'],
                'status' => $a['status'],
                'observacoes' => $a['observacoes'],
                'pet_nome' => $a['pet_nome'],
                'cliente_nome' => $a['cliente_nome'],
            ]
        ];
    }
    // Adicionar eventos 'Fechado' para os dias fechados do mês
    $config_file = __DIR__ . '/../config_agenda.json';
    $config = [ 'abertos' => [1,1,1,1,1,1,1] ];
    if (file_exists($config_file)) {
        $json = file_get_contents($config_file);
        $data_config = json_decode($json, true);
        if (is_array($data_config)) {
            $config = array_merge($config, $data_config);
        }
    }
    
    echo "<!-- DEBUG: Config carregada: " . json_encode($config) . " -->\n";
    
    // Descobrir o mês exibido
    $ano = date('Y');
    $mes = date('m');
    if (isset($_GET['start'])) {
        $ano = substr($_GET['start'], 0, 4);
        $mes = substr($_GET['start'], 5, 2);
    }
    
    echo "<!-- DEBUG: Mês/Ano: $mes/$ano -->\n";
    
    $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    for ($dia = 1; $dia <= $diasNoMes; $dia++) {
        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        $diaSemana = date('w', strtotime($data));
        
        echo "<!-- DEBUG: Dia $dia ($data) - Dia da semana: $diaSemana - Aberto: " . ($config['abertos'][$diaSemana] ?? 'undefined') . " -->\n";
        
        if (!isset($config['abertos'][$diaSemana]) || $config['abertos'][$diaSemana] != 1) {
            echo "<!-- DEBUG: Adicionando evento Fechado para $data -->\n";
            $eventos[] = [
                'title' => 'Fechado',
                'start' => $data,
                'allDay' => true,
                'backgroundColor' => '#f8d7da',
                'borderColor' => '#f8d7da',
                'textColor' => '#b71c1c'
            ];
        }
    }
    
    echo "<!-- DEBUG: Total de eventos: " . count($eventos) . " -->\n";
    echo json_encode($eventos);
    exit;
}

$output = ob_get_clean();
echo $output;
?> 