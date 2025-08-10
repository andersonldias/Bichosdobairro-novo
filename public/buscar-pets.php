<?php
require_once '../src/Pet.php';
header('Content-Type: application/json');
$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
$pets = [];
if ($cliente_id > 0) {
    $todos = Pet::listarTodos();
    foreach ($todos as $pet) {
        if ($pet['cliente_id'] == $cliente_id) {
            $pets[] = [
                'nome' => $pet['nome'],
                'especie' => $pet['especie'],
                'raca' => $pet['raca'],
                'idade' => $pet['idade']
            ];
        }
    }
}
echo json_encode($pets); 