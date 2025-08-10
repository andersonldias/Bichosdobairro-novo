<?php
require_once '../src/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$campo = $_POST['campo'] ?? '';
$valor = $_POST['valor'] ?? '';
$cliente_id = $_POST['cliente_id'] ?? null; // Para edição, excluir o próprio cliente

if (empty($campo) || empty($valor)) {
    echo json_encode(['error' => 'Campo e valor são obrigatórios']);
    exit;
}

try {
    $cliente = new Cliente();
    
    switch ($campo) {
        case 'nome':
            $duplicado = Cliente::verificarDuplicidade('nome', $valor, $cliente_id);
            if ($duplicado) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Já existe um cliente com este nome'
                ]);
            } else {
                echo json_encode([
                    'valido' => true,
                    'mensagem' => ''
                ]);
            }
            break;
            
        case 'email':
            // Se o e-mail estiver vazio, é válido (opcional)
            if (empty($valor)) {
                echo json_encode([
                    'valido' => true,
                    'mensagem' => ''
                ]);
                exit;
            }
            
            // Validar formato do email apenas se não estiver vazio
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Formato de e-mail inválido'
                ]);
                exit;
            }
            
            $duplicado = Cliente::verificarDuplicidade('email', $valor, $cliente_id);
            if ($duplicado) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Este e-mail já está cadastrado'
                ]);
            } else {
                echo json_encode([
                    'valido' => true,
                    'mensagem' => ''
                ]);
            }
            break;
            
        case 'cpf':
            // Remover caracteres especiais do CPF
            $cpf_limpo = preg_replace('/[^0-9]/', '', $valor);
            
            // Validar CPF
            if (!Cliente::validarCPF($cpf_limpo)) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'CPF inválido'
                ]);
                exit;
            }
            
            $duplicado = Cliente::verificarDuplicidade('cpf', $cpf_limpo, $cliente_id);
            if ($duplicado) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Este CPF já está cadastrado'
                ]);
            } else {
                echo json_encode([
                    'valido' => true,
                    'mensagem' => ''
                ]);
            }
            break;
            
        case 'telefone':
            // Remover caracteres especiais do telefone
            $telefone_limpo = preg_replace('/[^0-9]/', '', $valor);
            
            // Validar formato do telefone (mínimo 10 dígitos, máximo 11)
            if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Telefone deve ter 10 ou 11 dígitos'
                ]);
                exit;
            }
            
            $duplicado = Cliente::verificarDuplicidadeTelefone($telefone_limpo, $cliente_id);
            if ($duplicado) {
                echo json_encode([
                    'valido' => false,
                    'mensagem' => 'Este telefone já está cadastrado'
                ]);
            } else {
                echo json_encode([
                    'valido' => true,
                    'mensagem' => ''
                ]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Campo não suportado']);
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?> 