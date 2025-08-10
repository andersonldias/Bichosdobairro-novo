<?php
/**
 * Formulário de Agendamentos Recorrentes
 * Sistema Bichos do Bairro
 */

session_start();
require_once '../src/init.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$erro = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cliente_id = $_POST['cliente_id'] ?? '';
        $pet_id = $_POST['pet_id'] ?? '';
        $tipo_recorrencia = $_POST['tipo_recorrencia'] ?? '';
        $dia_semana = $_POST['dia_semana'] ?? '';
        $semana_mes = $_POST['semana_mes'] ?? '';
        $hora_inicio = $_POST['hora_inicio'] ?? '';
        $duracao = $_POST['duracao'] ?? 60;
        $data_inicio = $_POST['data_inicio'] ?? '';
        $data_fim = $_POST['data_fim'] ?? '';
        $observacoes = $_POST['observacoes'] ?? '';

        // Validações
        if (empty($cliente_id) || empty($pet_id) || empty($tipo_recorrencia) || 
            empty($dia_semana) || empty($hora_inicio) || empty($data_inicio)) {
            throw new Exception('Todos os campos obrigatórios devem ser preenchidos');
        }

        // Inserir agendamento recorrente
        $sql = "INSERT INTO agendamentos_recorrentes 
                (cliente_id, pet_id, tipo_recorrencia, dia_semana, semana_mes, 
                 hora_inicio, duracao, data_inicio, data_fim, observacoes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id, $pet_id, $tipo_recorrencia, $dia_semana, $semana_mes,
            $hora_inicio, $duracao, $data_inicio, $data_fim ?: null, $observacoes
        ]);

        $recorrencia_id = $pdo->lastInsertId();

        // Log da criação
        $sql_log = "INSERT INTO logs_agendamentos_recorrentes 
                    (recorrencia_id, acao, dados_novos, usuario_id) 
                    VALUES (?, 'criado', ?, ?)";
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([
            $recorrencia_id,
            json_encode($_POST),
            $_SESSION['usuario_id']
        ]);

        $mensagem = 'Agendamento recorrente criado com sucesso!';
        
        // Redirecionar para lista após 2 segundos
        header('Refresh: 2; URL=agendamentos-recorrentes.php');
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Buscar clientes e pets
$clientes = Cliente::listarTodos();
$pets = Pet::listarTodos();

function render_content() {
    global $mensagem, $erro, $clientes, $pets;
?>
    <div class="max-w-4xl mx-auto">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-plus-circle text-blue-500 mr-3"></i>
                    Novo Agendamento Recorrente
                </h1>
                <p class="text-gray-600 mt-2">Configure um agendamento que se repete automaticamente</p>
            </div>
            <a href="agendamentos-recorrentes.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>

        <!-- Mensagens -->
        <?php if ($mensagem): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <!-- Seção: Cliente e Pet -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-user text-blue-500 mr-2"></i>
                    Cliente e Pet
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="cliente_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="cliente_id" name="cliente_id" required>
                            <option value="">Selecione um cliente</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?= $cliente['id'] ?>">
                                    <?= htmlspecialchars($cliente['nome']) ?> 
                                    (<?= htmlspecialchars($cliente['telefone'] ?? 'Sem telefone') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="pet_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Pet <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="pet_id" name="pet_id" required>
                            <option value="">Selecione um pet</option>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?= $pet['id'] ?>" data-cliente="<?= $pet['cliente_id'] ?>">
                                    <?= htmlspecialchars($pet['nome']) ?> 
                                    (<?= htmlspecialchars($pet['especie']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Seção: Recorrência -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-redo text-green-500 mr-2"></i>
                    Configuração da Recorrência
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tipo_recorrencia" class="block text-sm font-medium text-gray-700 mb-1">
                            Tipo de Recorrência <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="tipo_recorrencia" name="tipo_recorrencia" required>
                            <option value="">Selecione o tipo</option>
                            <option value="semanal">Semanal</option>
                            <option value="quinzenal">Quinzenal</option>
                            <option value="mensal">Mensal</option>
                        </select>
                    </div>
                    <div>
                        <label for="dia_semana" class="block text-sm font-medium text-gray-700 mb-1">
                            Dia da Semana <span class="text-red-500">*</span>
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="dia_semana" name="dia_semana" required>
                            <option value="">Selecione o dia</option>
                            <option value="1">Segunda-feira</option>
                            <option value="2">Terça-feira</option>
                            <option value="3">Quarta-feira</option>
                            <option value="4">Quinta-feira</option>
                            <option value="5">Sexta-feira</option>
                            <option value="6">Sábado</option>
                            <option value="0">Domingo</option>
                        </select>
                    </div>
                    <div id="semana_mes_container" class="hidden">
                        <label for="semana_mes" class="block text-sm font-medium text-gray-700 mb-1">
                            Semana do Mês
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="semana_mes" name="semana_mes">
                            <option value="">Todas as semanas</option>
                            <option value="1">1ª semana</option>
                            <option value="2">2ª semana</option>
                            <option value="3">3ª semana</option>
                            <option value="4">4ª semana</option>
                            <option value="5">5ª semana</option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Apenas para recorrência mensal</p>
                    </div>
                </div>
            </div>

            <!-- Seção: Horário -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-clock text-yellow-500 mr-2"></i>
                    Horário
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="hora_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Hora de Início <span class="text-red-500">*</span>
                        </label>
                        <input type="time" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               id="hora_inicio" name="hora_inicio" required>
                    </div>
                    <div>
                        <label for="duracao" class="block text-sm font-medium text-gray-700 mb-1">
                            Duração (minutos)
                        </label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                id="duracao" name="duracao">
                            <option value="30">30 minutos</option>
                            <option value="60" selected>1 hora</option>
                            <option value="90">1 hora e 30 minutos</option>
                            <option value="120">2 horas</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Seção: Período -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                    Período
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Início <span class="text-red-500">*</span>
                        </label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               id="data_inicio" name="data_inicio" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div>
                        <label for="data_fim" class="block text-sm font-medium text-gray-700 mb-1">
                            Data de Fim (opcional)
                        </label>
                        <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               id="data_fim" name="data_fim">
                        <p class="text-xs text-gray-500 mt-1">Deixe em branco para recorrência indefinida</p>
                    </div>
                </div>
            </div>

            <!-- Seção: Observações -->
            <div class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-sticky-note text-orange-500 mr-2"></i>
                    Observações
                </h3>
                <div>
                    <label for="observacoes" class="block text-sm font-medium text-gray-700 mb-1">
                        Observações
                    </label>
                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              id="observacoes" name="observacoes" rows="3" 
                              placeholder="Informações adicionais sobre o agendamento recorrente..."></textarea>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-between">
                <a href="agendamentos-recorrentes.php" class="bg-gray-500 text-white px-6 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
                <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <i class="fas fa-save"></i>
                    Criar Agendamento Recorrente
                </button>
            </div>
        </form>
    </div>

    <script>
        // Mostrar/ocultar campo semana do mês
        document.getElementById('tipo_recorrencia').addEventListener('change', function() {
            const semanaContainer = document.getElementById('semana_mes_container');
            if (this.value === 'mensal') {
                semanaContainer.classList.remove('hidden');
            } else {
                semanaContainer.classList.add('hidden');
                document.getElementById('semana_mes').value = '';
            }
        });

        // Filtrar pets por cliente
        document.getElementById('cliente_id').addEventListener('change', function() {
            const clienteId = this.value;
            const petSelect = document.getElementById('pet_id');
            const petOptions = petSelect.querySelectorAll('option');
            
            petSelect.value = '';
            
            petOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                } else {
                    const petClienteId = option.getAttribute('data-cliente');
                    if (petClienteId === clienteId) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
        });
    </script>
<?php
}

include 'layout.php';
?> 