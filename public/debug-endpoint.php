<?php
require_once '../src/init.php';

// Simular diferentes meses para teste
$meses_teste = [
    '2025-06-01', // Junho
    '2025-07-01', // Julho  
    '2025-08-01', // Agosto
    '2025-09-01'  // Setembro
];

foreach ($meses_teste as $start_date) {
    echo "<h3>Testando mês: $start_date</h3>";
    
    // Simular GET parameter
    $_GET['start'] = $start_date;
    
    // Carregar configuração
    $config_file = __DIR__ . '/../config_agenda.json';
    $config = [ 'abertos' => [1,1,1,1,1,1,1] ];
    if (file_exists($config_file)) {
        $json = file_get_contents($config_file);
        $data_config = json_decode($json, true);
        if (is_array($data_config)) {
            $config = array_merge($config, $data_config);
        }
    }
    
    // Descobrir o mês exibido
    $ano = date('Y');
    $mes = date('m');
    if (isset($_GET['start']) && !empty($_GET['start'])) {
        $ano = substr($_GET['start'], 0, 4);
        $mes = substr($_GET['start'], 5, 2);
    }
    
    echo "Mês: $mes, Ano: $ano<br>";
    echo "Configuração: " . implode(', ', $config['abertos']) . "<br>";
    
    $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);
    $eventosFechado = [];
    
    for ($dia = 1; $dia <= $diasNoMes; $dia++) {
        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
        $diaSemana = date('w', strtotime($data));
        $nomeDia = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'][$diaSemana];
        
        if (!isset($config['abertos'][$diaSemana]) || $config['abertos'][$diaSemana] != 1) {
            $eventosFechado[] = [
                'title' => 'Fechado',
                'start' => $data,
                'allDay' => true,
                'backgroundColor' => 'transparent',
                'borderColor' => 'transparent',
                'textColor' => '#000000'
            ];
            echo "<span style='color:red;'>$dia ($nomeDia) - FECHADO</span> ";
        } else {
            echo "<span style='color:green;'>$dia ($nomeDia) - ABERTO</span> ";
        }
        
        if ($dia % 7 == 0) echo "<br>";
    }
    
    echo "<br><strong>Eventos 'Fechado' gerados: " . count($eventosFechado) . "</strong><br>";
    foreach ($eventosFechado as $evento) {
        echo "- " . $evento['start'] . "<br>";
    }
    echo "<hr>";
}
?> 