<?php
require_once '../src/init.php';

echo "<h1>Teste do Endpoint de Agendamentos</h1>";

// Simular a requisição do FullCalendar
$_GET['action'] = 'listar';
$_GET['start'] = '2024-01-01'; // Janeiro de 2024

// Capturar a saída do endpoint
ob_start();
include 'agendamentos.php';
$output = ob_get_clean();

echo "<h2>Resposta do Endpoint:</h2>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

// Decodificar JSON para verificar os eventos
$eventos = json_decode($output, true);
if ($eventos) {
    echo "<h2>Eventos Decodificados:</h2>";
    echo "<p>Total de eventos: " . count($eventos) . "</p>";
    
    $fechados = array_filter($eventos, function($e) {
        return $e['title'] === 'Fechado';
    });
    
    echo "<p>Eventos 'Fechado': " . count($fechados) . "</p>";
    
    if (count($fechados) > 0) {
        echo "<h3>Datas 'Fechado':</h3>";
        echo "<ul>";
        foreach ($fechados as $evento) {
            echo "<li>" . $evento['start'] . " (Dia da semana: " . date('w', strtotime($evento['start'])) . ")</li>";
        }
        echo "</ul>";
    }
    
    $agendamentos = array_filter($eventos, function($e) {
        return $e['title'] !== 'Fechado';
    });
    
    echo "<p>Agendamentos normais: " . count($agendamentos) . "</p>";
} else {
    echo "<p style='color: red;'>Erro ao decodificar JSON!</p>";
}

// Verificar configuração
echo "<h2>Configuração Atual:</h2>";
$config_file = __DIR__ . '/../config_agenda.json';
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
    echo "<pre>" . print_r($config, true) . "</pre>";
    
    $diasSemana = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    echo "<h3>Status dos dias:</h3>";
    foreach ($config['abertos'] as $i => $aberto) {
        $status = $aberto ? '✅ Aberto' : '❌ Fechado';
        echo "<p>{$diasSemana[$i]}: {$status}</p>";
    }
} else {
    echo "<p style='color: red;'>Arquivo de configuração não encontrado!</p>";
}
?> 