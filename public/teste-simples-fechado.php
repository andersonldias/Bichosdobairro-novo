<?php
require_once '../src/init.php';

echo "<h1>Teste Simples - Eventos Fechado</h1>";

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

echo "<h2>Configuração Carregada:</h2>";
echo "<pre>" . print_r($config, true) . "</pre>";

// Testar para julho de 2025
$ano = 2025;
$mes = 7;
$diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

echo "<h2>Testando para Julho de 2025 ($diasNoMes dias):</h2>";

$eventos = [];
$diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

for ($dia = 1; $dia <= $diasNoMes; $dia++) {
    $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    $diaSemana = date('w', strtotime($data));
    $nomeDia = $diasSemana[$diaSemana];
    $aberto = $config['abertos'][$diaSemana] ?? 1;
    
    echo "<p>Dia $dia ($data) - $nomeDia - Aberto: " . ($aberto ? 'SIM' : 'NÃO') . "</p>";
    
    if (!isset($config['abertos'][$diaSemana]) || $config['abertos'][$diaSemana] != 1) {
        $eventos[] = [
            'title' => 'Fechado',
            'start' => $data,
            'allDay' => true,
            'backgroundColor' => '#f8d7da',
            'borderColor' => '#f8d7da',
            'textColor' => '#b71c1c'
        ];
        echo "<p style='color: red;'>→ ADICIONADO evento 'Fechado' para $data</p>";
    }
}

echo "<h2>Eventos 'Fechado' Gerados:</h2>";
echo "<p>Total: " . count($eventos) . " eventos</p>";
echo "<pre>" . print_r($eventos, true) . "</pre>";

// Verificar domingos e segundas de julho
echo "<h2>Verificação Específica:</h2>";
$domingos = [];
$segundas = [];

for ($dia = 1; $dia <= $diasNoMes; $dia++) {
    $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    $diaSemana = date('w', strtotime($data));
    
    if ($diaSemana == 0) { // Domingo
        $domingos[] = $data;
    } elseif ($diaSemana == 1) { // Segunda
        $segundas[] = $data;
    }
}

echo "<p>Domingos de julho: " . implode(', ', $domingos) . "</p>";
echo "<p>Segundas de julho: " . implode(', ', $segundas) . "</p>";

// Verificar se esses eventos estão na lista
foreach ($domingos as $data) {
    $encontrado = false;
    foreach ($eventos as $evento) {
        if ($evento['start'] === $data) {
            $encontrado = true;
            break;
        }
    }
    echo "<p>Domingo $data: " . ($encontrado ? '✅ Incluído' : '❌ NÃO incluído') . "</p>";
}

foreach ($segundas as $data) {
    $encontrado = false;
    foreach ($eventos as $evento) {
        if ($evento['start'] === $data) {
            $encontrado = true;
            break;
        }
    }
    echo "<p>Segunda $data: " . ($encontrado ? '✅ Incluído' : '❌ NÃO incluído') . "</p>";
}
?> 