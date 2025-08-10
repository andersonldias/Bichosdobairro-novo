<?php
require_once '../src/autoload.php';

// Verificar se é uma requisição AJAX
if (Utils::isAjax()) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'clear_cache':
            $cleared = Cache::clear();
            Utils::jsonResponse(['success' => true, 'message' => 'Cache limpo com sucesso']);
            break;
            
        case 'clear_logs':
            $cleaned = Logger::cleanOldLogs(7); // Limpar logs com mais de 7 dias
            Utils::jsonResponse(['success' => true, 'message' => "$cleaned arquivos de log removidos"]);
            break;
            
        case 'get_stats':
            $cacheStats = Cache::getStats();
            $logStats = Logger::getStats();
            $dbStats = [
                'clientes' => Cliente::count(),
                'pets' => Pet::count(),
                'agendamentos' => Agendamento::count()
            ];
            
            Utils::jsonResponse([
                'cache' => $cacheStats,
                'logs' => $logStats,
                'database' => $dbStats
            ]);
            break;
            
        case 'get_recent_logs':
            $level = $_GET['level'] ?? null;
            $limit = intval($_GET['limit'] ?? 50);
            $logs = Logger::getRecentLogs($level, $limit);
            Utils::jsonResponse($logs);
            break;
            
        default:
            Utils::jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

function render_content() {
    $cacheStats = Cache::getStats();
    $logStats = Logger::getStats();
    $dbStats = [
        'clientes' => Cliente::count(),
        'pets' => Pet::count(),
        'agendamentos' => Agendamento::count()
    ];
    ?>
    
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900">Administração do Sistema</h1>
            <div class="flex space-x-2">
                <button onclick="clearCache()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-broom mr-2"></i>Limpar Cache
                </button>
                <button onclick="clearLogs()" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600">
                    <i class="fas fa-trash mr-2"></i>Limpar Logs Antigos
                </button>
            </div>
        </div>

        <!-- Estatísticas do Sistema -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Cache -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-database text-blue-500 mr-2"></i>Cache
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Arquivos:</span>
                        <span class="font-medium" id="cache-files"><?= $cacheStats['files'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tamanho:</span>
                        <span class="font-medium" id="cache-size"><?= $cacheStats['size_formatted'] ?></span>
                    </div>
                </div>
            </div>

            <!-- Logs -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-file-alt text-green-500 mr-2"></i>Logs
                </h3>
                <div class="space-y-2">
                    <?php foreach ($logStats as $level => $stats): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?= ucfirst($level) ?>:</span>
                        <span class="font-medium"><?= $stats['lines'] ?> linhas</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Banco de Dados -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <i class="fas fa-database text-purple-500 mr-2"></i>Banco de Dados
                </h3>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Clientes:</span>
                        <span class="font-medium"><?= $dbStats['clientes'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Pets:</span>
                        <span class="font-medium"><?= $dbStats['pets'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Agendamentos:</span>
                        <span class="font-medium"><?= $dbStats['agendamentos'] ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Recentes -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history text-gray-500 mr-2"></i>Logs Recentes
                </h3>
                <div class="mt-2 flex space-x-2">
                    <select id="log-level" class="border border-gray-300 rounded px-3 py-1 text-sm">
                        <option value="">Todos os níveis</option>
                        <option value="error">Erro</option>
                        <option value="warning">Aviso</option>
                        <option value="info">Informação</option>
                        <option value="debug">Debug</option>
                    </select>
                    <button onclick="loadLogs()" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">
                        <i class="fas fa-sync-alt mr-1"></i>Atualizar
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="logs-container" class="space-y-2 max-h-96 overflow-y-auto">
                    <div class="text-center text-gray-500">Carregando logs...</div>
                </div>
            </div>
        </div>

        <!-- Configurações do Sistema -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-cog text-gray-500 mr-2"></i>Configurações
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Modo Debug</label>
                    <span class="text-sm text-gray-600">
                        <?= Config::getAppConfig('debug') ? 'Ativado' : 'Desativado' ?>
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                    <span class="text-sm text-gray-600"><?= Config::getAppConfig('timezone') ?></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Versão</label>
                    <span class="text-sm text-gray-600"><?= Config::getAppConfig('version') ?></span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Itens por página</label>
                    <span class="text-sm text-gray-600"><?= Config::getAppConfig('items_per_page') ?></span>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Carregar logs ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        loadLogs();
        loadStats();
    });

    function loadLogs() {
        const level = document.getElementById('log-level').value;
        const container = document.getElementById('logs-container');
        
        container.innerHTML = '<div class="text-center text-gray-500">Carregando...</div>';
        
        fetch(`admin.php?action=get_recent_logs&level=${level}&limit=50`)
            .then(response => response.json())
            .then(logs => {
                if (logs.length === 0) {
                    container.innerHTML = '<div class="text-center text-gray-500">Nenhum log encontrado</div>';
                    return;
                }
                
                container.innerHTML = logs.map(log => `
                    <div class="border-l-4 border-${getLogColor(log.level)} bg-gray-50 p-3">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">${log.message}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    ${log.timestamp} | ${log.level} | ${log.ip}
                                </div>
                                ${log.context ? `<div class="text-xs text-gray-600 mt-1">Contexto: ${JSON.stringify(log.context)}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            })
            .catch(error => {
                container.innerHTML = '<div class="text-center text-red-500">Erro ao carregar logs</div>';
                console.error('Erro:', error);
            });
    }

    function getLogColor(level) {
        const colors = {
            'ERROR': 'red',
            'WARNING': 'yellow',
            'INFO': 'blue',
            'DEBUG': 'gray'
        };
        return colors[level] || 'gray';
    }

    function clearCache() {
        if (!confirm('Tem certeza que deseja limpar o cache?')) return;
        
        fetch('admin.php?action=clear_cache')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadStats();
                } else {
                    alert('Erro ao limpar cache');
                }
            })
            .catch(error => {
                alert('Erro ao limpar cache');
                console.error('Erro:', error);
            });
    }

    function clearLogs() {
        if (!confirm('Tem certeza que deseja limpar os logs antigos?')) return;
        
        fetch('admin.php?action=clear_logs')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    loadLogs();
                    loadStats();
                } else {
                    alert('Erro ao limpar logs');
                }
            })
            .catch(error => {
                alert('Erro ao limpar logs');
                console.error('Erro:', error);
            });
    }

    function loadStats() {
        fetch('admin.php?action=get_stats')
            .then(response => response.json())
            .then(data => {
                document.getElementById('cache-files').textContent = data.cache.files;
                document.getElementById('cache-size').textContent = data.cache.size_formatted;
            })
            .catch(error => {
                console.error('Erro ao carregar estatísticas:', error);
            });
    }
    </script>
    <?php
} 