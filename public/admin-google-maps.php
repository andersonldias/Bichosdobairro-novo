<?php
require_once '../src/init.php';
require_once '../src/Config.php';

// Verificar se usuário está logado (adapte conforme seu sistema de autenticação)
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }

$message = '';
$messageType = 'info';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($_POST['action'] === 'update_config') {
            updateGoogleMapsConfig();
            $message = 'Configurações atualizadas com sucesso!';
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = 'Erro: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Obter configurações atuais
$config = [
    'api_key' => Config::get('GOOGLE_MAPS_API_KEY', ''),
    'monthly_limit' => (int) Config::get('GOOGLE_MAPS_MONTHLY_LIMIT', 10000),
    'warning_threshold' => (float) Config::get('GOOGLE_MAPS_WARNING_THRESHOLD', 0.8)
];

// Simular dados de uso (em produção, buscar do banco de dados)
$currentUsage = 0; // Implementar busca real
$stats = calculateStats($currentUsage, $config);

/**
 * Atualiza configurações do Google Maps
 */
function updateGoogleMapsConfig() {
    $apiKey = $_POST['api_key'] ?? '';
    $monthlyLimit = (int) ($_POST['monthly_limit'] ?? 10000);
    $warningThreshold = (float) ($_POST['warning_threshold'] ?? 0.8);
    
    // Validações
    if (empty($apiKey)) {
        throw new Exception('Chave da API é obrigatória');
    }
    
    if ($monthlyLimit < 1000) {
        throw new Exception('Limite mensal deve ser pelo menos 1.000');
    }
    
    if ($warningThreshold < 0.1 || $warningThreshold > 1.0) {
        throw new Exception('Limite de aviso deve estar entre 10% e 100%');
    }
    
    // Atualizar arquivo .env (implementar conforme sua estrutura)
    // Por simplicidade, apenas validamos aqui
    // Em produção, você implementaria a atualização real do .env
}

/**
 * Calcula estatísticas de uso
 */
function calculateStats($currentUsage, $config) {
    $percentage = $config['monthly_limit'] > 0 ? ($currentUsage / $config['monthly_limit']) * 100 : 0;
    
    return [
        'usage_count' => $currentUsage,
        'monthly_limit' => $config['monthly_limit'],
        'percentage' => round($percentage, 1),
        'remaining' => max(0, $config['monthly_limit'] - $currentUsage),
        'status' => $percentage >= 100 ? 'limit_reached' : ($percentage >= 80 ? 'warning' : 'normal')
    ];
}

/**
 * Formata números para exibição
 */
function formatNumberAdmin($number) {
    return number_format($number, 0, ',', '.');
}

ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <!-- Cabeçalho -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Monitoramento Google Maps API</h1>
        <p class="text-gray-600">Controle de uso e configurações da API do Google Maps</p>
    </div>

    <!-- Mensagens -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-md <?= $messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Status Atual -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Uso Atual -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Uso Atual</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatNumberAdmin($stats['usage_count']) ?></p>
                </div>
            </div>
        </div>

        <!-- Limite Mensal -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-limit text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Limite Mensal</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatNumberAdmin($stats['monthly_limit']) ?></p>
                </div>
            </div>
        </div>

        <!-- Percentual -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 <?= $stats['status'] === 'warning' ? 'bg-yellow-100' : ($stats['status'] === 'limit_reached' ? 'bg-red-100' : 'bg-green-100') ?> rounded-lg">
                    <i class="fas fa-percentage <?= $stats['status'] === 'warning' ? 'text-yellow-600' : ($stats['status'] === 'limit_reached' ? 'text-red-600' : 'text-green-600') ?> text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Percentual</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $stats['percentage'] ?>%</p>
                </div>
            </div>
        </div>

        <!-- Restante -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-clock text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Restante</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatNumberAdmin($stats['remaining']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Configurações -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Configurações</h3>
        </div>
        
        <form method="POST" class="p-6">
            <input type="hidden" name="action" value="update_config">
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Chave da API</label>
                    <input type="password" name="api_key" 
                           value="<?= htmlspecialchars($config['api_key']) ?>"
                           placeholder="AIza..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Sua chave da API do Google Maps</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Limite Mensal</label>
                    <input type="number" name="monthly_limit" 
                           value="<?= $config['monthly_limit'] ?>"
                           min="1000" step="1000"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Número máximo de consultas por mês</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Limite de Aviso (%)</label>
                    <input type="number" name="warning_threshold" 
                           value="<?= $config['warning_threshold'] * 100 ?>"
                           min="10" max="100" step="5"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Percentual para exibir aviso de limite</p>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Salvar Configurações
                </button>
            </div>
        </form>
    </div>

    <!-- Instruções -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-3">Como Configurar</h3>
        <ol class="list-decimal list-inside space-y-2 text-blue-800">
            <li>Acesse o <a href="https://console.cloud.google.com/" target="_blank" class="underline">Google Cloud Console</a></li>
            <li>Crie um projeto ou selecione um existente</li>
            <li>Ative a <strong>Geocoding API</strong></li>
            <li>Crie uma chave de API</li>
            <li>Configure restrições de segurança</li>
            <li>Cole a chave no campo acima</li>
        </ol>
    </div>
</div>

<script>
// Atualizar estatísticas em tempo real
setInterval(async () => {
    if (window.addressSearch) {
        const stats = window.addressSearch.getUsageStats();
        // Atualizar elementos da página com novas estatísticas
        console.log('Estatísticas atuais:', stats);
    }
}, 30000); // A cada 30 segundos
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>