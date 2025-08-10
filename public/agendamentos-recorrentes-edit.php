<?php
/**
 * Editar Agendamento Recorrente
 * Sistema Bichos do Bairro
 */

session_start();
require_once '../src/init.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$erro = '';
$sucesso = '';
$agendamento = null;
$clientes = [];
$pets = [];

$id = intval($_GET['id'] ?? 0);

if (!$id) {
    header('Location: agendamentos-recorrentes.php');
    exit;
}

try {
    $pdo = getDb();
    
    // Buscar agendamento recorrente
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $agendamento = $stmt->fetch();
    
    if (!$agendamento) {
        header('Location: agendamentos-recorrentes.php');
        exit;
    }
    
    // Buscar clientes
    $stmt = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome");
    $clientes = $stmt->fetchAll();
    
    // Buscar pets do cliente
    $stmt = $pdo->prepare("SELECT id, nome FROM pets WHERE cliente_id = ? ORDER BY nome");
    $stmt->execute([$agendamento['cliente_id']]);
    $pets = $stmt->fetchAll();
    
} catch (Exception $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cliente_id = intval($_POST['cliente_id']);
        $pet_id = intval($_POST['pet_id']);
        $tipo_recorrencia = $_POST['tipo_recorrencia'];
        $dia_semana = intval($_POST['dia_semana']);
        $semana_mes = $_POST['semana_mes'] ? intval($_POST['semana_mes']) : null;
        $hora_inicio = $_POST['hora_inicio'];
        $duracao = intval($_POST['duracao']);
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'] ?: null;
        $observacoes = trim($_POST['observacoes']);
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        // Validações
        if (!$cliente_id || !$pet_id) {
            throw new Exception("Cliente e pet são obrigatórios");
        }
        
        if (!in_array($tipo_recorrencia, ['semanal', 'quinzenal', 'mensal'])) {
            throw new Exception("Tipo de recorrência inválido");
        }
        
        if ($dia_semana < 1 || $dia_semana > 7) {
            throw new Exception("Dia da semana inválido");
        }
        
        if ($tipo_recorrencia === 'mensal' && (!$semana_mes || $semana_mes < 1 || $semana_mes > 5)) {
            throw new Exception("Semana do mês inválida para recorrência mensal");
        }
        
        if (!$hora_inicio || !$duracao || !$data_inicio) {
            throw new Exception("Hora, duração e data de início são obrigatórios");
        }
        
        // Dados anteriores para log
        $dados_anteriores = json_encode([
            'cliente_id' => $agendamento['cliente_id'],
            'pet_id' => $agendamento['pet_id'],
            'tipo_recorrencia' => $agendamento['tipo_recorrencia'],
            'dia_semana' => $agendamento['dia_semana'],
            'semana_mes' => $agendamento['semana_mes'],
            'hora_inicio' => $agendamento['hora_inicio'],
            'duracao' => $agendamento['duracao'],
            'data_inicio' => $agendamento['data_inicio'],
            'data_fim' => $agendamento['data_fim'],
            'observacoes' => $agendamento['observacoes'],
            'ativo' => $agendamento['ativo']
        ]);
        
        // Atualizar agendamento recorrente
        $sql = "UPDATE agendamentos_recorrentes SET 
                cliente_id = ?, pet_id = ?, tipo_recorrencia = ?, dia_semana = ?, 
                semana_mes = ?, hora_inicio = ?, duracao = ?, data_inicio = ?, 
                data_fim = ?, observacoes = ?, ativo = ?, updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id, $pet_id, $tipo_recorrencia, $dia_semana, $semana_mes,
            $hora_inicio, $duracao, $data_inicio, $data_fim, $observacoes, $ativo, $id
        ]);
        
        // Dados novos para log
        $dados_novos = json_encode([
            'cliente_id' => $cliente_id,
            'pet_id' => $pet_id,
            'tipo_recorrencia' => $tipo_recorrencia,
            'dia_semana' => $dia_semana,
            'semana_mes' => $semana_mes,
            'hora_inicio' => $hora_inicio,
            'duracao' => $duracao,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'observacoes' => $observacoes,
            'ativo' => $ativo
        ]);
        
        // Log da edição
        $sql_log = "INSERT INTO logs_agendamentos_recorrentes 
                    (recorrencia_id, acao, dados_anteriores, dados_novos, usuario_id, observacoes) 
                    VALUES (?, 'editado', ?, ?, ?, ?)";
        
        $stmt_log = $pdo->prepare($sql_log);
        $stmt_log->execute([$id, $dados_anteriores, $dados_novos, $_SESSION['usuario_id'], 'Agendamento recorrente editado']);
        
        $sucesso = "Agendamento recorrente atualizado com sucesso!";
        
        // Recarregar dados
        header("Location: agendamentos-recorrentes.php?sucesso=1");
        exit;
        
    } catch (Exception $e) {
        $erro = "Erro ao atualizar agendamento recorrente: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Agendamento Recorrente - Bichos do Bairro</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-gray-900">
                            <i class="fas fa-edit text-blue-600 mr-2"></i>
                            Editar Agendamento Recorrente
                        </h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="agendamentos-recorrentes.php" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Mensagens -->
            <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <!-- Informações do Agendamento -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informações do Agendamento
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-800">Cliente:</span> 
                        <?= htmlspecialchars($agendamento['cliente_nome']) ?>
                    </div>
                    <div>
                        <span class="font-medium text-blue-800">Pet:</span> 
                        <?= htmlspecialchars($agendamento['pet_nome']) ?>
                    </div>
                    <div>
                        <span class="font-medium text-blue-800">Tipo:</span> 
                        <?= ucfirst($agendamento['tipo_recorrencia']) ?>
                    </div>
                    <div>
                        <span class="font-medium text-blue-800">Status:</span> 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $agendamento['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                            <?= $agendamento['ativo'] ? 'Ativo' : 'Pausado' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Formulário -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <form method="POST" class="space-y-6 p-6">
                    <!-- Cliente e Pet -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700">
                                Cliente <span class="text-red-500">*</span>
                            </label>
                            <select name="cliente_id" id="cliente_id" required 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?= $cliente['id'] ?>" <?= $agendamento['cliente_id'] == $cliente['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cliente['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="pet_id" class="block text-sm font-medium text-gray-700">
                                Pet <span class="text-red-500">*</span>
                            </label>
                            <select name="pet_id" id="pet_id" required 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?= $pet['id'] ?>" <?= $agendamento['pet_id'] == $pet['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($pet['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo de Recorrência -->
                    <div>
                        <label for="tipo_recorrencia" class="block text-sm font-medium text-gray-700">
                            Tipo de Recorrência <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_recorrencia" id="tipo_recorrencia" required 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="semanal" <?= $agendamento['tipo_recorrencia'] === 'semanal' ? 'selected' : '' ?>>
                                Semanal - Toda semana no mesmo dia/hora
                            </option>
                            <option value="quinzenal" <?= $agendamento['tipo_recorrencia'] === 'quinzenal' ? 'selected' : '' ?>>
                                Quinzenal - Semana sim, semana não
                            </option>
                            <option value="mensal" <?= $agendamento['tipo_recorrencia'] === 'mensal' ? 'selected' : '' ?>>
                                Mensal - Todo mês no mesmo dia da semana
                            </option>
                        </select>
                    </div>

                    <!-- Dia da Semana e Semana do Mês -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="dia_semana" class="block text-sm font-medium text-gray-700">
                                Dia da Semana <span class="text-red-500">*</span>
                            </label>
                            <select name="dia_semana" id="dia_semana" required 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="1" <?= $agendamento['dia_semana'] == 1 ? 'selected' : '' ?>>Segunda-feira</option>
                                <option value="2" <?= $agendamento['dia_semana'] == 2 ? 'selected' : '' ?>>Terça-feira</option>
                                <option value="3" <?= $agendamento['dia_semana'] == 3 ? 'selected' : '' ?>>Quarta-feira</option>
                                <option value="4" <?= $agendamento['dia_semana'] == 4 ? 'selected' : '' ?>>Quinta-feira</option>
                                <option value="5" <?= $agendamento['dia_semana'] == 5 ? 'selected' : '' ?>>Sexta-feira</option>
                                <option value="6" <?= $agendamento['dia_semana'] == 6 ? 'selected' : '' ?>>Sábado</option>
                                <option value="7" <?= $agendamento['dia_semana'] == 7 ? 'selected' : '' ?>>Domingo</option>
                            </select>
                        </div>
                        
                        <div id="semana_mes_container" style="display: <?= $agendamento['tipo_recorrencia'] === 'mensal' ? 'block' : 'none' ?>;">
                            <label for="semana_mes" class="block text-sm font-medium text-gray-700">
                                Semana do Mês <span class="text-red-500">*</span>
                            </label>
                            <select name="semana_mes" id="semana_mes" 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="1" <?= $agendamento['semana_mes'] == 1 ? 'selected' : '' ?>>1ª semana</option>
                                <option value="2" <?= $agendamento['semana_mes'] == 2 ? 'selected' : '' ?>>2ª semana</option>
                                <option value="3" <?= $agendamento['semana_mes'] == 3 ? 'selected' : '' ?>>3ª semana</option>
                                <option value="4" <?= $agendamento['semana_mes'] == 4 ? 'selected' : '' ?>>4ª semana</option>
                                <option value="5" <?= $agendamento['semana_mes'] == 5 ? 'selected' : '' ?>>Última semana</option>
                            </select>
                        </div>
                    </div>

                    <!-- Horário e Duração -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="hora_inicio" class="block text-sm font-medium text-gray-700">
                                Hora de Início <span class="text-red-500">*</span>
                            </label>
                            <input type="time" name="hora_inicio" id="hora_inicio" required 
                                   value="<?= htmlspecialchars($agendamento['hora_inicio']) ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="duracao" class="block text-sm font-medium text-gray-700">
                                Duração (minutos) <span class="text-red-500">*</span>
                            </label>
                            <select name="duracao" id="duracao" required 
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                <option value="30" <?= $agendamento['duracao'] == 30 ? 'selected' : '' ?>>30 minutos</option>
                                <option value="60" <?= $agendamento['duracao'] == 60 ? 'selected' : '' ?>>1 hora</option>
                                <option value="90" <?= $agendamento['duracao'] == 90 ? 'selected' : '' ?>>1 hora e 30 minutos</option>
                                <option value="120" <?= $agendamento['duracao'] == 120 ? 'selected' : '' ?>>2 horas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Datas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700">
                                Data de Início <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="data_inicio" id="data_inicio" required 
                                   value="<?= htmlspecialchars($agendamento['data_inicio']) ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700">
                                Data de Fim (opcional)
                            </label>
                            <input type="date" name="data_fim" id="data_fim" 
                                   value="<?= $agendamento['data_fim'] ? htmlspecialchars($agendamento['data_fim']) : '' ?>"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">Deixe em branco para recorrência indefinida</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="ativo" value="1" 
                                   <?= $agendamento['ativo'] ? 'checked' : '' ?>
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Agendamento ativo</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Desmarque para pausar este agendamento recorrente</p>
                    </div>

                    <!-- Observações -->
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700">
                            Observações
                        </label>
                        <textarea name="observacoes" id="observacoes" rows="3" 
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  placeholder="Observações sobre este agendamento recorrente..."><?= htmlspecialchars($agendamento['observacoes']) ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end space-x-3">
                        <a href="agendamentos-recorrentes.php" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        // Mostrar/ocultar campo semana do mês
        document.getElementById('tipo_recorrencia').addEventListener('change', function() {
            const semanaContainer = document.getElementById('semana_mes_container');
            const semanaSelect = document.getElementById('semana_mes');
            
            if (this.value === 'mensal') {
                semanaContainer.style.display = 'block';
                semanaSelect.required = true;
            } else {
                semanaContainer.style.display = 'none';
                semanaSelect.required = false;
                semanaSelect.value = '';
            }
        });

        // Carregar pets quando cliente for alterado
        document.getElementById('cliente_id').addEventListener('change', function() {
            const clienteId = this.value;
            const petSelect = document.getElementById('pet_id');
            
            if (clienteId) {
                fetch(`buscar-pets.php?cliente_id=${clienteId}`)
                    .then(response => response.json())
                    .then(pets => {
                        petSelect.innerHTML = '<option value="">Selecione um pet</option>';
                        pets.forEach(pet => {
                            petSelect.innerHTML += `<option value="${pet.id}">${pet.nome}</option>`;
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao carregar pets:', error);
                    });
            } else {
                petSelect.innerHTML = '<option value="">Selecione um pet</option>';
            }
        });
    </script>
</body>
</html> 