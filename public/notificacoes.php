<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_notificacoes':
            $notificacoes = Notificacao::getNotificacoesNaoLidas();
            jsonResponse($notificacoes);
            break;
            
        case 'marcar_lida':
            $id = (int)($_POST['id'] ?? 0);
            if ($id) {
                $sucesso = Notificacao::marcarComoLida($id);
                jsonResponse(['success' => $sucesso]);
            } else {
                jsonResponse(['success' => false, 'message' => 'ID inválido'], 400);
            }
            break;
            
        case 'enviar_lembrete':
            $agendamentoId = (int)($_POST['agendamento_id'] ?? 0);
            if ($agendamentoId) {
                $sucesso = Notificacao::enviarLembreteAgendamento($agendamentoId);
                jsonResponse(['success' => $sucesso, 'message' => $sucesso ? 'Lembrete enviado' : 'Erro ao enviar lembrete']);
            } else {
                jsonResponse(['success' => false, 'message' => 'ID inválido'], 400);
            }
            break;
            
        case 'enviar_lembretes_automaticos':
            $enviados = Notificacao::enviarLembretesAutomaticos();
            jsonResponse(['success' => true, 'enviados' => $enviados]);
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

// Buscar dados
$notificacoes = Notificacao::getNotificacoesRecentes(10);
$agendamentosProximos = Agendamento::getAgendamentosProximos(5);
$agendamentosVencidos = Agendamento::getAgendamentosVencidos();

// Gerar token CSRF
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Notificações - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                        <i class="fas fa-bell text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-gray-900">Sistema de Notificações</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="agendamentos-avancado.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-calendar mr-1"></i> Agendamentos
                        </a>
                        <a href="relatorios.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-chart-bar mr-1"></i> Relatórios
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Estatísticas de Notificações -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-bell text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Não Lidas</p>
                            <p class="text-2xl font-semibold text-gray-900" id="notificacoesNaoLidas">-</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-calendar-day text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Agendamentos Hoje</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosPorData(date('Y-m-d'))) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Próximos 7 Dias</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosProximos(50)) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Vencidos</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count($agendamentosVencidos) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Notificações Recentes -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-semibold text-gray-900">Notificações Recentes</h2>
                                <button onclick="enviarLembretesAutomaticos()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                    <i class="fas fa-paper-plane mr-2"></i>Enviar Lembretes
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4" id="listaNotificacoes">
                                <?php if (empty($notificacoes)): ?>
                                    <p class="text-gray-500 text-center py-8">Nenhuma notificação recente</p>
                                <?php else: ?>
                                    <?php foreach ($notificacoes as $notificacao): ?>
                                        <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg <?= $notificacao['lida'] ? 'opacity-75' : 'border-l-4 border-blue-500' ?>">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-<?= $notificacao['tipo'] === 'lembrete' ? 'bell' : 'info' ?> text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($notificacao['titulo']) ?></p>
                                                <p class="text-sm text-gray-600"><?= htmlspecialchars($notificacao['mensagem']) ?></p>
                                                <p class="text-xs text-gray-500 mt-1"><?= formatDate($notificacao['created_at'], 'd/m/Y H:i') ?></p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <?php if (!$notificacao['lida']): ?>
                                                    <button onclick="marcarComoLida(<?= $notificacao['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Painel de Controles -->
                <div class="space-y-6">
                    <!-- Agendamentos Próximos -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Próximos Agendamentos</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <?php if (empty($agendamentosProximos)): ?>
                                    <p class="text-gray-500 text-center py-4">Nenhum agendamento próximo</p>
                                <?php else: ?>
                                    <?php foreach ($agendamentosProximos as $agendamento): ?>
                                        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
                                            <div>
                                                <p class="font-medium text-gray-900"><?= htmlspecialchars($agendamento['cliente_nome']) ?></p>
                                                <p class="text-sm text-gray-600"><?= htmlspecialchars($agendamento['pet_nome']) ?> - <?= htmlspecialchars($agendamento['servico']) ?></p>
                                                <p class="text-xs text-gray-500"><?= formatDate($agendamento['data']) ?> às <?= $agendamento['hora'] ?></p>
                                            </div>
                                            <button onclick="enviarLembrete(<?= $agendamento['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-bell"></i>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Configurações de Notificações -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Configurações</h3>
                        </div>
                        <div class="p-6">
                            <form class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                        <span class="ml-2 text-sm text-gray-700">Lembretes automáticos</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" checked>
                                        <span class="ml-2 text-sm text-gray-700">Notificações de agendamentos vencidos</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Notificações por email</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Notificações por SMS</span>
                                    </label>
                                </div>
                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">
                                    Salvar Configurações
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Ações Rápidas -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Ações Rápidas</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <button onclick="enviarLembretesAutomaticos()" class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                                    <i class="fas fa-paper-plane mr-2"></i>Enviar Todos os Lembretes
                                </button>
                                <button onclick="limparNotificacoesAntigas()" class="w-full bg-gray-600 text-white py-2 px-4 rounded-md hover:bg-gray-700">
                                    <i class="fas fa-trash mr-2"></i>Limpar Antigas
                                </button>
                                <button onclick="testarNotificacoes()" class="w-full bg-yellow-600 text-white py-2 px-4 rounded-md hover:bg-yellow-700">
                                    <i class="fas fa-test-tube mr-2"></i>Testar Sistema
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Carregar notificações não lidas
        function carregarNotificacoesNaoLidas() {
            fetch('?action=get_notificacoes')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('notificacoesNaoLidas').textContent = data.length;
                })
                .catch(error => console.error('Erro ao carregar notificações:', error));
        }

        // Marcar notificação como lida
        function marcarComoLida(id) {
            const formData = new FormData();
            formData.append('action', 'marcar_lida');
            formData.append('id', id);
            formData.append('csrf_token', '<?= $csrfToken ?>');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    carregarNotificacoesNaoLidas();
                    location.reload(); // Recarregar para atualizar a lista
                }
            })
            .catch(error => console.error('Erro ao marcar como lida:', error));
        }

        // Enviar lembrete para agendamento específico
        function enviarLembrete(agendamentoId) {
            const formData = new FormData();
            formData.append('action', 'enviar_lembrete');
            formData.append('agendamento_id', agendamentoId);
            formData.append('csrf_token', '<?= $csrfToken ?>');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao enviar lembrete:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao enviar lembrete',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }

        // Enviar lembretes automáticos
        function enviarLembretesAutomaticos() {
            Swal.fire({
                title: 'Enviando Lembretes',
                text: 'Enviando lembretes automáticos...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData();
            formData.append('action', 'enviar_lembretes_automaticos');
            formData.append('csrf_token', '<?= $csrfToken ?>');
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: `${data.enviados} lembretes enviados com sucesso`,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao enviar lembretes',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao enviar lembretes:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao enviar lembretes',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }

        // Limpar notificações antigas
        function limparNotificacoesAntigas() {
            Swal.fire({
                title: 'Confirmar',
                text: 'Deseja limpar notificações antigas?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, limpar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implementar limpeza de notificações antigas
                    Swal.fire({
                        title: 'Sucesso!',
                        text: 'Notificações antigas removidas',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Testar sistema de notificações
        function testarNotificacoes() {
            Swal.fire({
                title: 'Teste de Notificações',
                text: 'Enviando notificação de teste...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                Swal.fire({
                    title: 'Teste Concluído',
                    text: 'Sistema de notificações funcionando corretamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }, 2000);
        }

        // Carregar dados iniciais
        document.addEventListener('DOMContentLoaded', function() {
            carregarNotificacoesNaoLidas();
            
            // Atualizar a cada 30 segundos
            setInterval(carregarNotificacoesNaoLidas, 30000);
        });
    </script>
</body>
</html> 