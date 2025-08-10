<?php
require_once '../src/init.php';

// Verificar se é uma requisição AJAX
if (isAjax()) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_agendamentos':
            $data = $_GET['data'] ?? date('Y-m-d');
            $agendamentos = Agendamento::getAgendamentosPorData($data);
            jsonResponse($agendamentos);
            break;
            
        case 'salvar_agendamento':
            $dados = [
                'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
                'pet_id' => (int)($_POST['pet_id'] ?? 0),
                'data' => $_POST['data'] ?? '',
                'hora' => $_POST['hora'] ?? '',
                'servico' => sanitize($_POST['servico'] ?? ''),
                'observacoes' => sanitize($_POST['observacoes'] ?? ''),
                'status' => 'agendado'
            ];
            
            if (empty($dados['cliente_id']) || empty($dados['data']) || empty($dados['hora'])) {
                jsonResponse(['success' => false, 'message' => 'Dados obrigatórios não preenchidos'], 400);
            }
            
            // Verificar disponibilidade
            if (!Agendamento::verificarDisponibilidade($dados['data'], $dados['hora'])) {
                jsonResponse(['success' => false, 'message' => 'Horário não disponível'], 400);
            }
            
            $id = Agendamento::criar($dados);
            if ($id) {
                jsonResponse(['success' => true, 'id' => $id, 'message' => 'Agendamento criado com sucesso']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Erro ao criar agendamento'], 500);
            }
            break;
            
        case 'atualizar_status':
            $id = (int)($_POST['id'] ?? 0);
            $status = $_POST['status'] ?? '';
            
            if ($id && $status) {
                $sucesso = Agendamento::atualizarStatus($id, $status);
                jsonResponse(['success' => $sucesso, 'message' => $sucesso ? 'Status atualizado' : 'Erro ao atualizar']);
            } else {
                jsonResponse(['success' => false, 'message' => 'Dados inválidos'], 400);
            }
            break;
            
        case 'get_horarios_disponiveis':
            $data = $_GET['data'] ?? date('Y-m-d');
            $horarios = Agendamento::getHorariosDisponiveis($data);
            jsonResponse($horarios);
            break;
            
        default:
            jsonResponse(['error' => 'Ação não reconhecida'], 400);
    }
}

// Buscar dados para o formulário
$clientes = Cliente::listarTodos();
$pets = Pet::listarTodos();
$agendamentos = Agendamento::getAgendamentosProximos();

// Gerar token CSRF
$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Agendamentos - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
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
                        <i class="fas fa-calendar-alt text-blue-600 text-2xl mr-3"></i>
                        <h1 class="text-2xl font-bold text-gray-900">Sistema de Agendamentos</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="dashboard.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        <a href="clientes.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-users mr-1"></i> Clientes
                        </a>
                        <a href="pets.php" class="text-gray-600 hover:text-gray-900">
                            <i class="fas fa-paw mr-1"></i> Pets
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Estatísticas Rápidas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-calendar-day text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Hoje</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosPorData(date('Y-m-d'))) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Concluídos</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosPorStatus('concluido')) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pendentes</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosPorStatus('agendado')) ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-times-circle text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Cancelados</p>
                            <p class="text-2xl font-semibold text-gray-900"><?= count(Agendamento::getAgendamentosPorStatus('cancelado')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Calendário -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Calendário de Agendamentos</h2>
                        </div>
                        <div class="p-6">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>

                <!-- Painel de Controles -->
                <div class="space-y-6">
                    <!-- Novo Agendamento -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Novo Agendamento</h3>
                        </div>
                        <div class="p-6">
                            <form id="formAgendamento" class="space-y-4">
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Cliente</label>
                                    <select name="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">Selecione um cliente</option>
                                        <?php foreach ($clientes as $cliente): ?>
                                            <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Pet</label>
                                    <select name="pet_id" id="pet_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">Selecione um pet</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data</label>
                                        <input type="date" name="data" id="data" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Hora</label>
                                        <select name="hora" id="hora" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                            <option value="">Selecione</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Serviço</label>
                                    <select name="servico" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                        <option value="">Selecione o serviço</option>
                                        <option value="banho">Banho</option>
                                        <option value="tosa">Tosa</option>
                                        <option value="banho_tosa">Banho + Tosa</option>
                                        <option value="vacinacao">Vacinação</option>
                                        <option value="consulta">Consulta Veterinária</option>
                                        <option value="exame">Exame</option>
                                        <option value="outro">Outro</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Observações</label>
                                    <textarea name="observacoes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Observações adicionais..."></textarea>
                                </div>

                                <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <i class="fas fa-plus mr-2"></i>Agendar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Próximos Agendamentos -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Próximos Agendamentos</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4" id="proximosAgendamentos">
                                <?php foreach (array_slice($agendamentos, 0, 5) as $agendamento): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900"><?= htmlspecialchars($agendamento['cliente_nome']) ?></p>
                                            <p class="text-sm text-gray-600"><?= htmlspecialchars($agendamento['pet_nome']) ?> - <?= htmlspecialchars($agendamento['servico']) ?></p>
                                            <p class="text-xs text-gray-500"><?= formatDate($agendamento['data']) ?> às <?= $agendamento['hora'] ?></p>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button onclick="atualizarStatus(<?= $agendamento['id'] ?>, 'concluido')" class="text-green-600 hover:text-green-800">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="atualizarStatus(<?= $agendamento['id'] ?>, 'cancelado')" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Inicializar calendário
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch(`?action=get_agendamentos&data=${info.startStr}`)
                        .then(response => response.json())
                        .then(data => {
                            const events = data.map(item => ({
                                id: item.id,
                                title: `${item.cliente_nome} - ${item.pet_nome}`,
                                start: `${item.data}T${item.hora}`,
                                backgroundColor: getStatusColor(item.status),
                                borderColor: getStatusColor(item.status),
                                extendedProps: {
                                    servico: item.servico,
                                    status: item.status
                                }
                            }));
                            successCallback(events);
                        })
                        .catch(error => {
                            console.error('Erro ao carregar agendamentos:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    Swal.fire({
                        title: 'Detalhes do Agendamento',
                        html: `
                            <div class="text-left">
                                <p><strong>Cliente:</strong> ${info.event.title}</p>
                                <p><strong>Serviço:</strong> ${info.event.extendedProps.servico}</p>
                                <p><strong>Data:</strong> ${info.event.start.toLocaleDateString('pt-BR')}</p>
                                <p><strong>Hora:</strong> ${info.event.start.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})}</p>
                                <p><strong>Status:</strong> ${info.event.extendedProps.status}</p>
                            </div>
                        `,
                        icon: 'info',
                        confirmButtonText: 'Fechar'
                    });
                }
            });
            calendar.render();

            // Carregar pets quando cliente for selecionado
            document.getElementById('cliente_id').addEventListener('change', function() {
                const clienteId = this.value;
                const petSelect = document.getElementById('pet_id');
                petSelect.innerHTML = '<option value="">Selecione um pet</option>';
                
                if (clienteId) {
                    const pets = <?= json_encode($pets) ?>;
                    const petsCliente = pets.filter(pet => pet.cliente_id == clienteId);
                    
                    petsCliente.forEach(pet => {
                        const option = document.createElement('option');
                        option.value = pet.id;
                        option.textContent = pet.nome;
                        petSelect.appendChild(option);
                    });
                }
            });

            // Carregar horários disponíveis quando data for selecionada
            document.getElementById('data').addEventListener('change', function() {
                const data = this.value;
                const horaSelect = document.getElementById('hora');
                horaSelect.innerHTML = '<option value="">Selecione</option>';
                
                if (data) {
                    fetch(`?action=get_horarios_disponiveis&data=${data}`)
                        .then(response => response.json())
                        .then(horarios => {
                            horarios.forEach(horario => {
                                const option = document.createElement('option');
                                option.value = horario;
                                option.textContent = horario;
                                horaSelect.appendChild(option);
                            });
                        })
                        .catch(error => console.error('Erro ao carregar horários:', error));
                }
            });

            // Submissão do formulário
            document.getElementById('formAgendamento').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('action', 'salvar_agendamento');
                
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
                        }).then(() => {
                            this.reset();
                            calendar.refetchEvents();
                            location.reload(); // Recarregar para atualizar estatísticas
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
                    console.error('Erro:', error);
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Erro ao processar solicitação',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            });
        });

        // Função para obter cor do status
        function getStatusColor(status) {
            switch (status) {
                case 'agendado': return '#3B82F6'; // blue
                case 'concluido': return '#10B981'; // green
                case 'cancelado': return '#EF4444'; // red
                case 'em_andamento': return '#F59E0B'; // yellow
                default: return '#6B7280'; // gray
            }
        }

        // Função para atualizar status
        function atualizarStatus(id, status) {
            const formData = new FormData();
            formData.append('action', 'atualizar_status');
            formData.append('id', id);
            formData.append('status', status);
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
                    }).then(() => {
                        location.reload();
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
                console.error('Erro:', error);
                Swal.fire({
                    title: 'Erro!',
                    text: 'Erro ao atualizar status',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    </script>
</body>
</html> 