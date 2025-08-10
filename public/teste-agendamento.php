<?php
// Script de teste para salvar agendamento via POST
// Ajuste os IDs conforme necessário
$cliente_id = 1; // Substitua por um ID válido
$pet_id = 1;     // Substitua por um ID válido

$data = [
    'cliente_id' => $cliente_id,
    'pet_id' => $pet_id,
    'data' => '2025-07-15',
    'hora' => '08:00',
    'servico' => 'Teste via PHP',
    'status' => 'Pendente',
    'observacoes' => 'Teste automático PHP'
];

$url = 'http://localhost:8000/agendamentos.php?action=salvar';

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) {
    echo "Erro ao enviar requisição!";
} else {
    echo "Resposta do backend: ";
    echo htmlspecialchars($result);
} 