<?php
require_once '../src/init.php';

$dados = [
    'cliente_id' => 20, // ID válido
    'pet_id' => 2,      // ID válido
    'data' => '2025-07-15',
    'hora' => '08:00',
    'servico' => 'Teste direto',
    'observacoes' => 'Teste manual',
    'status' => 'Pendente'
];

$result = Agendamento::criar($dados);

if ($result) {
    echo '<h2>Inserção OK</h2>ID do novo agendamento: ' . $result;
} else {
    echo '<h2>Falha ao inserir!</h2>';
} 