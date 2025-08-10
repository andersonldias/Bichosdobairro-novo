<?php
require_once '../src/db.php';
require_once '../src/Cliente.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$cliente_id = $_GET['cliente_id'] ?? '';

if (empty($cliente_id)) {
    echo json_encode(['error' => 'ID do cliente é obrigatório']);
    exit;
}

try {
    $telefones = Cliente::buscarTelefones($cliente_id);
    echo json_encode($telefones);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 