<?php
require_once '../src/init.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$mensagem = '';
$erro = '';

// Processar ações
if (isset($_GET['acao'])) {
    try {
        $id = $_GET['id'] ?? 0;
        
        switch ($_GET['acao']) {
            case 'pausar':
                $sql = "UPDATE agendamentos_recorrentes SET ativo = FALSE WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                
                // Log da ação
                $sql_log = "INSERT INTO logs_agendamentos_recorrentes (recorrencia_id, acao, usuario_id) VALUES (?, 'pausado', ?)";
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([$id, $_SESSION['usuario_id']]);
                
                $mensagem = 'Agendamento recorrente pausado com sucesso!';
                break;
                
            case 'reativar':
                $sql = "UPDATE agendamentos_recorrentes SET ativo = TRUE WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                
                // Log da ação
                $sql_log = "INSERT INTO logs_agendamentos_recorrentes (recorrencia_id, acao, usuario_id) VALUES (?, 'reativado', ?)";
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([$id, $_SESSION['usuario_id']]);
                
                $mensagem = 'Agendamento recorrente reativado com sucesso!';
                break;
                
            case 'excluir':
                $sql = "DELETE FROM agendamentos_recorrentes WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                
                // Log da ação
                $sql_log = "INSERT INTO logs_agendamentos_recorrentes (recorrencia_id, acao, usuario_id) VALUES (?, 'excluido', ?)";
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([$id, $_SESSION['usuario_id']]);
                
                $mensagem = 'Agendamento recorrente excluído com sucesso!';
                break;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao executar ação: ' . $e->getMessage();
    }
}

// Buscar agendamentos recorrentes
try {
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome,
                   COUNT(a.id) as total_ocorrencias,
                   MAX(a.data) as ultima_ocorrencia
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            LEFT JOIN agendamentos a ON ar.id = a.recorrencia_id
            GROUP BY ar.id
            ORDER BY ar.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $agendamentos_recorrentes = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = 'Erro ao carregar agendamentos recorrentes: ' . $e->getMessage();
    $agendamentos_recorrentes = [];
}

// Função para formatar tipo de recorrência
function formatarRecorrencia($tipo, $dia_semana, $semana_mes = null) {
    $dias = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
    $dia = $dias[$dia_semana];
    
    switch ($tipo) {
        case 'semanal':
            return "Toda {$dia}";
        case 'quinzenal':
            return "{$dia} (quinzenal)";
        case 'mensal':
            if ($semana_mes) {
                $semanas = ['', '1ª', '2ª', '3ª', '4ª', '5ª'];
                return "{$semanas[$semana_mes]} {$dia} do mês";
            }
            return "{$dia} (mensal)";
        default:
            return $tipo;
    }
}

function render_content() {
    global $mensagem, $erro, $agendamentos_recorrentes;
?>
    <div class="max-w-7xl mx-auto">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-redo text-blue-500 mr-3"></i>
                    Agendamentos Recorrentes
                </h1>
                <p class="text-gray-600 mt-2">Gerencie seus agendamentos que se repetem automaticamente</p>
            </div>
            <div class="flex gap-3">
                <a href="agendamentos-recorrentes-form.php" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Novo Recorrente
                </a>
                <a href="agendamentos.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center gap-2">
                    <i class="fas fa-calendar"></i>
                    Calendário
                </a>
            </div>
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

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-500 text-white p-4 rounded-lg">
                <h3 class="text-lg font-semibold">Total</h3>
                <p class="text-3xl font-bold"><?= count($agendamentos_recorrentes) ?></p>
            </div>
            <div class="bg-green-500 text-white p-4 rounded-lg">
                <h3 class="text-lg font-semibold">Ativos</h3>
                <p class="text-3xl font-bold"><?= count(array_filter($agendamentos_recorrentes, fn($a) => $a['ativo'])) ?></p>
            </div>
            <div class="bg-yellow-500 text-white p-4 rounded-lg">
                <h3 class="text-lg font-semibold">Pausados</h3>
                <p class="text-3xl font-bold"><?= count(array_filter($agendamentos_recorrentes, fn($a) => !$a['ativo'])) ?></p>
            </div>
            <div class="bg-purple-500 text-white p-4 rounded-lg">
                <h3 class="text-lg font-semibold">Ocorrências</h3>
                <p class="text-3xl font-bold"><?= array_sum(array_column($agendamentos_recorrentes, 'total_ocorrencias')) ?></p>
            </div>
        </div>

        <!-- Lista de Agendamentos Recorrentes -->
        <?php if (empty($agendamentos_recorrentes)): ?>
            <div class="text-center py-12">
                <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Nenhum agendamento recorrente encontrado</h3>
                <p class="text-gray-500 mb-6">Crie seu primeiro agendamento recorrente para começar</p>
                <a href="agendamentos-recorrentes-form.php" class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 flex items-center gap-2 inline-flex">
                    <i class="fas fa-plus"></i>
                    Criar Primeiro Agendamento
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($agendamentos_recorrentes as $agendamento): ?>
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                        <div class="p-4 border-b border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $agendamento['ativo'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $agendamento['ativo'] ? 'Ativo' : 'Pausado' ?>
                                </span>
                                <div class="relative">
                                    <button class="text-gray-400 hover:text-gray-600" onclick="toggleDropdown(<?= $agendamento['id'] ?>)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div id="dropdown-<?= $agendamento['id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                        <a href="agendamentos-recorrentes-edit.php?id=<?= $agendamento['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-edit mr-2"></i> Editar
                                        </a>
                                        <a href="agendamentos-recorrentes-ocorrencias.php?id=<?= $agendamento['id'] ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-list mr-2"></i> Ver Ocorrências
                                        </a>
                                        <hr class="my-1">
                                        <?php if ($agendamento['ativo']): ?>
                                            <a href="?acao=pausar&id=<?= $agendamento['id'] ?>" 
                                               onclick="return confirm('Tem certeza que deseja pausar este agendamento?')"
                                               class="block px-4 py-2 text-sm text-yellow-600 hover:bg-gray-100">
                                                <i class="fas fa-pause mr-2"></i> Pausar
                                            </a>
                                        <?php else: ?>
                                            <a href="?acao=reativar&id=<?= $agendamento['id'] ?>" 
                                               class="block px-4 py-2 text-sm text-green-600 hover:bg-gray-100">
                                                <i class="fas fa-play mr-2"></i> Reativar
                                            </a>
                                        <?php endif; ?>
                                        <a href="?acao=excluir&id=<?= $agendamento['id'] ?>" 
                                           onclick="return confirm('Tem certeza que deseja excluir este agendamento? Esta ação não pode ser desfeita.')"
                                           class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <i class="fas fa-trash mr-2"></i> Excluir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4">
                            <h3 class="font-semibold text-gray-900 mb-2">
                                <i class="fas fa-user text-blue-500 mr-2"></i>
                                <?= htmlspecialchars($agendamento['cliente_nome']) ?>
                            </h3>
                            <p class="text-gray-600 mb-3">
                                <i class="fas fa-paw text-green-500 mr-2"></i>
                                <?= htmlspecialchars($agendamento['pet_nome']) ?>
                            </p>
                            
                            <div class="mb-3">
                                <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                                    <?= formatarRecorrencia($agendamento['tipo_recorrencia'], $agendamento['dia_semana'], $agendamento['semana_mes']) ?>
                                </span>
                            </div>
                            
                            <div class="mb-3 text-sm text-gray-600">
                                <i class="fas fa-clock mr-2"></i>
                                <?= $agendamento['hora_inicio'] ?> (<?= $agendamento['duracao'] ?> min)
                            </div>
                            
                            <?php if ($agendamento['observacoes']): ?>
                                <p class="text-sm text-gray-600 mb-3">
                                    <i class="fas fa-sticky-note mr-2"></i>
                                    <?= htmlspecialchars($agendamento['observacoes']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-gray-500">Ocorrências</p>
                                    <p class="font-semibold text-gray-900"><?= $agendamento['total_ocorrencias'] ?></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Última</p>
                                    <p class="font-semibold text-gray-900">
                                        <?= $agendamento['ultima_ocorrencia'] ? date('d/m/Y', strtotime($agendamento['ultima_ocorrencia'])) : 'Nunca' ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-4 py-3 bg-gray-50 text-xs text-gray-500 rounded-b-lg">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Criado em <?= date('d/m/Y', strtotime($agendamento['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById('dropdown-' + id);
        const allDropdowns = document.querySelectorAll('[id^="dropdown-"]');
        
        // Fechar todos os outros dropdowns
        allDropdowns.forEach(d => {
            if (d.id !== 'dropdown-' + id) {
                d.classList.add('hidden');
            }
        });
        
        // Toggle do dropdown atual
        dropdown.classList.toggle('hidden');
    }
    
    // Fechar dropdowns quando clicar fora
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.relative')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(d => {
                d.classList.add('hidden');
            });
        }
    });
    </script>
<?php
}

include 'layout.php';
?> 