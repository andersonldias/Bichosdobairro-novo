<?php
require_once '../src/init.php';

ob_start();
?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Teste de Busca de Endereços</h1>
    
    <!-- Status do Sistema -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Status do Sistema</h2>
        <div id="system-status">
            <p><i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Verificando sistema...</p>
        </div>
    </div>
    
    <!-- Teste de CEP -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Teste de Busca por CEP (ViaCEP)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CEP</label>
                <input type="text" name="cep" id="cep" placeholder="00000-000" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Logradouro</label>
                <input type="text" name="logradouro" id="logradouro" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bairro</label>
                <input type="text" name="bairro" id="bairro" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Cidade</label>
                <input type="text" name="cidade" id="cidade" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
    
    <!-- Teste de Busca por Nome -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Teste de Busca por Nome (Google Maps)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Rua</label>
                <input type="text" name="endereco" id="endereco" 
                       data-address-search="true"
                       placeholder="Digite o nome da rua..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <p class="text-xs text-gray-500 mt-1">Digite pelo menos 3 caracteres</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                <input type="text" name="numero" id="numero" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>
    
    <!-- Estatísticas de Uso -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Estatísticas de Uso</h2>
        <div id="usage-stats">
            <p><i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Carregando estatísticas...</p>
        </div>
        <button onclick="updateStats()" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Atualizar Estatísticas
        </button>
    </div>
</div>

<script>
// Verificar status do sistema
document.addEventListener('DOMContentLoaded', () => {
    const statusDiv = document.getElementById('system-status');
    
    if (window.addressSearch) {
        statusDiv.innerHTML = `
            <div class="space-y-2">
                <p><i class="fas fa-check-circle text-green-600 mr-2"></i>ViaCEP: Funcionando</p>
                <p id="google-maps-status"><i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>Google Maps: Verificando...</p>
            </div>
        `;
        
        // Verificar status do Google Maps
        window.addressSearch.getConfig().then(config => {
            const gmStatus = document.getElementById('google-maps-status');
            if (config.enabled) {
                gmStatus.innerHTML = `<i class="fas fa-check-circle text-green-600 mr-2"></i>Google Maps: Configurado (${config.usage_count}/${config.monthly_limit} consultas)`;
            } else {
                gmStatus.innerHTML = `<i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>Google Maps: Não configurado (API Key necessária)`;
            }
        }).catch(error => {
            const gmStatus = document.getElementById('google-maps-status');
            gmStatus.innerHTML = `<i class="fas fa-times-circle text-red-600 mr-2"></i>Google Maps: Erro de conexão`;
        });
    } else {
        statusDiv.innerHTML = `
            <p><i class="fas fa-times-circle text-red-600 mr-2"></i>Sistema de busca não carregado</p>
            <p class="text-xs mt-2">Verifique se o arquivo address-search.js está incluído no layout.php</p>
        `;
    }
    
    updateStats();
});

// Atualizar estatísticas
function updateStats() {
    const statsDiv = document.getElementById('usage-stats');
    
    if (window.addressSearch) {
        const stats = window.addressSearch.getUsageStats();
        
        statsDiv.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">${stats.monthlyUsage}</p>
                    <p class="text-sm text-gray-600">Uso Atual</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-600">${stats.monthlyLimit}</p>
                    <p class="text-sm text-gray-600">Limite Mensal</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold ${stats.percentage >= 80 ? 'text-red-600' : 'text-green-600'}">${stats.percentage}%</p>
                    <p class="text-sm text-gray-600">Percentual</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-indigo-600">${stats.remaining}</p>
                    <p class="text-sm text-gray-600">Restante</p>
                </div>
            </div>
        `;
    } else {
        statsDiv.innerHTML = '<p class="text-red-600">Sistema não carregado</p>';
    }
}
</script>

<?php
$content = ob_get_clean();
include 'layout.php';
?>