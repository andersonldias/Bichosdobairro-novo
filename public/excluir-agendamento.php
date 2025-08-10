<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (!isAjax()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
    exit;
}

// Verificar se o ID foi fornecido
$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID do agendamento não fornecido']);
    exit;
}

try {
    $id = (int)$id;
    $resultado = Agendamento::deletar($id);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Agendamento excluído com sucesso']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir agendamento']);
    }
} catch (Exception $e) {
    logError('Erro ao excluir agendamento: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?> 