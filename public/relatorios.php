<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_estatisticas_gerais':
            $estatisticas = [
                'clientes' => count(Cliente::listarTodos()),
                'pets' => count(Pet::listarTodos()),
                'agendamentos' => Agendamento::getEstatisticas(),
                'servicos_mes' => Agendamento::getRelatorioServicos(
                    date('Y-m-01'), 
                    date('Y-m-t')
                )
            ];
            jsonResponse($estatisticas);
            break;
            
        case 'get_relatorio_periodo':
            $dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
            $dataFim = $_GET['data_fim'] ?? date('Y-m-t');
            
            $relatorio = [
                'agendamentos' => Agendamento::getAgendamentosPorPeriodo($dataInicio, $dataFim),
                'servicos' => Agendamento::getRelatorioServicos($dataInicio, $dataFim),
                'clientes_novos' => Cliente::getClientesPorPeriodo($dataInicio, $dataFim)
            ];
            jsonResponse($relatorio);
            break;
            
        case 'exportar_relatorio':
            $tipo = $_POST['tipo'] ?? '';
            $dataInicio = $_POST['data_inicio'] ?? date('Y-m-01');
            $dataFim = $_POST['data_fim'] ?? date('Y-m-t');
            
            $dados = [];
            switch ($tipo) {
                case 'agendamentos':
                    $dados = Agendamento::getAgendamentosPorPeriodo($dataInicio, $dataFim);
                    break;
                case 'servicos':
                    $dados = Agendamento::getRelatorioServicos($dataInicio, $dataFim);
                    break;
                case 'clientes':
                    $dados = Cliente::getClientesPorPeriodo($dataInicio, $dataFim);
                    break;
            }
            
            // Gerar CSV
            $filename = "relatorio_{$tipo}_" . date('Y-m-d') . ".csv";
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            
            if (!empty($dados)) {
                // Cabeçalho
                fputcsv($output, array_keys($dados[0]));
                
                // Dados
                foreach ($dados as $row) {
                    fputcsv($output, $row);
                }
            }
            
            fclose($output);
            exit;
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

// Buscar dados iniciais
$estatisticas = Agendamento::getEstatisticas();
$servicosMes = Agendamento::getRelatorioServicos(date('Y-m-01'), date('Y-m-t'));
$agendamentosVencidos = Agendamento::getAgendamentosVencidos();

// Gerar token CSRF
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <i class="fas fa-chart-bar text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-gray-900">Relatórios e Análises</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="agendamentos-avancado.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-calendar mr-1"></i> Agendamentos
                        </a>
                        <a href="admin.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-cog mr-1"></i> Administração
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Filtros de Período -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Filtros de Período</h2>
                    <div class="flex items-center space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Início</label>
                            <input type="date" id="dataInicio" value="<?= date('Y-m-01') ?>" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                            <input type="date" id="dataFim" value="<?= date('Y-m-t') ?>" class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button onclick="carregarRelatorio()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                            <i class="fas fa-search mr-2"></i>Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estatísticas Gerais -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-users text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                            <p class="text-2xl font-semibold text-gray-900" id="totalClientes">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-paw text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Pets</p>
                            <p class="text-2xl font-semibold text-gray-900" id="totalPets">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Agendamentos Hoje</p>
                            <p class="text-2xl font-semibold text-gray-900" id="agendamentosHoje">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                            <i class="fas fa-chart-line text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Taxa de Conclusão</p>
                            <p class="text-2xl font-semibold text-gray-900" id="taxaConclusao">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Gráfico de Serviços -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Serviços por Mês</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="graficoServicos" width="400" height="200"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Status -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Status dos Agendamentos</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="graficoStatus" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabelas de Dados -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Serviços -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Top Serviços</h3>
                            <button onclick="exportarRelatorio('servicos')" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download mr-1"></i>Exportar
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Concluídos</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200" id="tabelaServicos">
                                    <!-- Dados carregados via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Agendamentos Vencidos -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Agendamentos Vencidos</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4" id="agendamentosVencidos">
                            <?php if (empty($agendamentosVencidos)): ?>
                                <p class="text-gray-500 text-center py-4">Nenhum agendamento vencido</p>
                            <?php else: ?>
                                <?php foreach (array_slice($agendamentosVencidos, 0, 5) as $agendamento): ?>
                                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($agendamento['cliente_nome']) ?></p>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($agendamento['pet_nome']) ?> - <?= htmlspecialchars($agendamento['servico']) ?></p>
                                            <p class="text-xs text-gray-500"><?= formatDate($agendamento['data']) ?> às <?= $agendamento['hora'] ?></p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-red-600">Vencido</p>
                                            <p class="text-xs text-gray-500"><?= $agendamento['cliente_telefone'] ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Relatório Detalhado -->
            <div class="bg-white rounded-lg shadow mt-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Relatório Detalhado</h3>
                        <div class="flex space-x-2">
                            <button onclick="exportarRelatorio('agendamentos')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                <i class="fas fa-download mr-2"></i>Exportar Agendamentos
                            </button>
                            <button onclick="exportarRelatorio('clientes')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                <i class="fas fa-download mr-2"></i>Exportar Clientes
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pet</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviço</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="tabelaRelatorio">
                                <!-- Dados carregados via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variáveis globais para os gráficos
        let graficoServicos, graficoStatus;

        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            carregarEstatisticas();
            carregarRelatorio();
        });

        // Carregar estatísticas gerais
        function carregarEstatisticas() {
            fetch('?action=get_estatisticas_gerais')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalClientes').textContent = data.clientes;
                    document.getElementById('totalPets').textContent = data.pets;
                    document.getElementById('agendamentosHoje').textContent = data.agendamentos.hoje || 0;
                    
                    const total = data.agendamentos.total || 1;
                    const concluidos = data.agendamentos.concluidos || 0;
                    const taxa = Math.round((concluidos / total) * 100);
                    document.getElementById('taxaConclusao').textContent = taxa + '%';
                    
                    // Atualizar gráficos
                    atualizarGraficoServicos(data.servicos_mes);
                    atualizarGraficoStatus(data.agendamentos);
                })
                .catch(error => console.error('Erro ao carregar estatísticas:', error));
        }

        // Carregar relatório por período
        function carregarRelatorio() {
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            
            fetch(`?action=get_relatorio_periodo&data_inicio=${dataInicio}&data_fim=${dataFim}`)
                .then(response => response.json())
                .then(data => {
                    atualizarTabelaServicos(data.servicos);
                    atualizarTabelaRelatorio(data.agendamentos);
                })
                .catch(error => console.error('Erro ao carregar relatório:', error));
        }

        // Atualizar gráfico de serviços
        function atualizarGraficoServicos(dados) {
            const ctx = document.getElementById('graficoServicos').getContext('2d');
            
            if (graficoServicos) {
                graficoServicos.destroy();
            }
            
            graficoServicos = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dados.map(item => item.servico),
                    datasets: [{
                        label: 'Total de Serviços',
                        data: dados.map(item => item.total),
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Atualizar gráfico de status
        function atualizarGraficoStatus(dados) {
            const ctx = document.getElementById('graficoStatus').getContext('2d');
            
            if (graficoStatus) {
                graficoStatus.destroy();
            }
            
            const statusData = [
                { label: 'Agendados', value: dados.agendados || 0, color: '#3B82F6' },
                { label: 'Concluídos', value: dados.concluidos || 0, color: '#10B981' },
                { label: 'Cancelados', value: dados.cancelados || 0, color: '#EF4444' },
                { label: 'Em Andamento', value: dados.em_andamento || 0, color: '#F59E0B' }
            ];
            
            graficoStatus = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: statusData.map(item => item.label),
                    datasets: [{
                        data: statusData.map(item => item.value),
                        backgroundColor: statusData.map(item => item.color),
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Atualizar tabela de serviços
        function atualizarTabelaServicos(dados) {
            const tbody = document.getElementById('tabelaServicos');
            tbody.innerHTML = '';
            
            dados.forEach(item => {
                const total = item.total || 0;
                const concluidos = item.concluidos || 0;
                const percentual = total > 0 ? Math.round((concluidos / total) * 100) : 0;
                
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.servico}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${total}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${concluidos}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${percentual}%</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Atualizar tabela de relatório
        function atualizarTabelaRelatorio(dados) {
            const tbody = document.getElementById('tabelaRelatorio');
            tbody.innerHTML = '';
            
            dados.forEach(item => {
                const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatarData(item.data)}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.cliente_nome}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.pet_nome}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.servico}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusClass(item.status)}">
                                ${item.status}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.hora}</td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        // Exportar relatório
        function exportarRelatorio(tipo) {
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'exportar_relatorio';
            
            const tipoInput = document.createElement('input');
            tipoInput.type = 'hidden';
            tipoInput.name = 'tipo';
            tipoInput.value = tipo;
            
            const dataInicioInput = document.createElement('input');
            dataInicioInput.type = 'hidden';
            dataInicioInput.name = 'data_inicio';
            dataInicioInput.value = dataInicio;
            
            const dataFimInput = document.createElement('input');
            dataFimInput.type = 'hidden';
            dataFimInput.name = 'data_fim';
            dataFimInput.value = dataFim;
            
            form.appendChild(actionInput);
            form.appendChild(tipoInput);
            form.appendChild(dataInicioInput);
            form.appendChild(dataFimInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        // Funções auxiliares
        function formatarData(data) {
            return new Date(data).toLocaleDateString('pt-BR');
        }

        function getStatusClass(status) {
            switch (status) {
                case 'agendado': return 'bg-blue-100 text-blue-800';
                case 'concluido': return 'bg-green-100 text-green-800';
                case 'cancelado': return 'bg-red-100 text-red-800';
                case 'em_andamento': return 'bg-yellow-100 text-yellow-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }
    </script>
</body>
</html> 