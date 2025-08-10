<?php
require_once '../../../src/init.php';
require_once '../../../src/Config.php';

// Configurar headers para API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetConfig();
            break;
        case 'POST':
            handlePostUsage();
            break;
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

/**
 * Retorna configurações da API Google Maps
 */
function handleGetConfig() {
    $config = [
        'api_key' => Config::get('GOOGLE_MAPS_API_KEY'),
        'monthly_limit' => (int) Config::get('GOOGLE_MAPS_MONTHLY_LIMIT', 10000),
        'warning_threshold' => (float) Config::get('GOOGLE_MAPS_WARNING_THRESHOLD', 0.8),
        'enabled' => !empty(Config::get('GOOGLE_MAPS_API_KEY'))
    ];
    
    // Não retornar a chave da API se não estiver configurada
    if (!$config['enabled']) {
        unset($config['api_key']);
    }
    
    echo json_encode($config);
}

/**
 * Atualiza contador de uso
 */
function handlePostUsage() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['usage_count'])) {
        throw new Exception('Contador de uso é obrigatório');
    }
    
    $usageCount = (int) $input['usage_count'];
    
    // Aqui você pode salvar no banco de dados se necessário
    // Por enquanto, apenas retornamos sucesso
    
    echo json_encode([
        'success' => true,
        'usage_count' => $usageCount,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>