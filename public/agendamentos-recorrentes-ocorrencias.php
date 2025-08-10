<?php
/**
 * Ocorrências de Agendamento Recorrente
 * Sistema Bichos do Bairro
 */

session_start();
require_once '../src/init.php';

// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;
$mensagem = '';
$erro = '';

if (!$id) {
    header('Location: agendamentos-recorrentes.php');
    exit;
}

// Processar ações
if (isset($_GET['acao'])) {
    try {
        $agendamento_id = $_GET['agendamento_id'] ?? 0;
        
        switch ($_GET['acao']) {
            case 'cancelar':
                $sql = "UPDATE agendamentos SET status = 'cancelado' WHERE id = ? AND recorrencia_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$agendamento_id, $id]);
                
                // Log da ação
                $sql_log = "INSERT INTO logs_agendamentos_recorrentes (recorrencia_id, agendamento_id, acao, usuario_id) VALUES (?, ?, 'cancelado', ?)";
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([$id, $agendamento_id, $_SESSION['usuario_id']]);
                
                $mensagem = 'Ocorrência cancelada com sucesso!';
                break;
                
            case 'remarcar':
                $nova_data = $_GET['nova_data'] ?? '';
                $nova_hora = $_GET['nova_hora'] ?? '';
                
                if ($nova_data && $nova_hora) {
                    $sql = "UPDATE agendamentos SET data = ?, hora = ?, status = 'remarcado' WHERE id = ? AND recorrencia_id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$nova_data, $nova_hora, $agendamento_id, $id]);
                    
                    // Log da ação
                    $sql_log = "INSERT INTO logs_agendamentos_recorrentes (recorrencia_id, agendamento_id, acao, dados_novos, usuario_id) VALUES (?, ?, 'remarcado', ?, ?)";
                    $stmt_log = $pdo->prepare($sql_log);
                    $stmt_log->execute([$id, $agendamento_id, json_encode(['data' => $nova_data, 'hora' => $nova_hora]), $_SESSION['usuario_id']]);
                    
                    $mensagem = 'Ocorrência remarcada com sucesso!';
                }
                break;
        }
    } catch (Exception $e) {
        $erro = 'Erro ao executar ação: ' . $e->getMessage();
    }
}

// Buscar dados do agendamento recorrente
try {
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.id = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $recorrencia = $stmt->fetch();
    
    if (!$recorrencia) {
        header('Location: agendamentos-recorrentes.php');
        exit;
    }
} catch (Exception $e) {
    $erro = 'Erro ao carregar dados: ' . $e->getMessage();
    $recorrencia = null;
}

// Buscar ocorrências
try {
    $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN pets p ON a.pet_id = p.id
            WHERE a.recorrencia_id = ?
            ORDER BY a.data DESC, a.hora DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $ocorrencias = $stmt->fetchAll();
} catch (Exception $e) {
    $erro = 'Erro ao carregar ocorrências: ' . $e->getMessage();
    $ocorrencias = [];
}

// Função para formatar status
function formatarStatus($status) {
    $status_classes = [
        'agendado' => 'bg-primary',
        'confirmado' => 'bg-success',
        'em_andamento' => 'bg-warning',
        'concluido' => 'bg-info',
        'cancelado' => 'bg-danger',
        'remarcado' => 'bg-secondary'
    ];
    
    $status_nomes = [
        'agendado' => 'Agendado',
        'confirmado' => 'Confirmado',
        'em_andamento' => 'Em Andamento',
        'concluido' => 'Concluído',
        'cancelado' => 'Cancelado',
        'remarcado' => 'Remarcado'
    ];
    
    $classe = $status_classes[$status] ?? 'bg-secondary';
    $nome = $status_nomes[$status] ?? $status;
    
    return "<span class='badge {$classe}'>{$nome}</span>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📅 Ocorrências - Agendamento Recorrente - Bichos do Bairro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .ocorrencia-card { transition: transform 0.2s; }
        .ocorrencia-card:hover { transform: translateY(-2px); }
        .status-badge { font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-list"></i> Ocorrências do Agendamento</h1>
                    <a href="agendamentos-recorrentes.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <?php if ($mensagem): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($mensagem) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($erro) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($recorrencia): ?>
                    <!-- Informações do Agendamento Recorrente -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informações do Agendamento Recorrente</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Cliente:</strong> <?= htmlspecialchars($recorrencia['cliente_nome']) ?></p>
                                    <p><strong>Pet:</strong> <?= htmlspecialchars($recorrencia['pet_nome']) ?></p>
                                    <p><strong>Horário:</strong> <?= $recorrencia['hora_inicio'] ?> (<?= $recorrencia['duracao'] ?> min)</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?= $recorrencia['ativo'] ? 'success' : 'secondary' ?>">
                                            <?= $recorrencia['ativo'] ? 'Ativo' : 'Pausado' ?>
                                        </span>
                                    </p>
                                    <p><strong>Início:</strong> <?= date('d/m/Y', strtotime($recorrencia['data_inicio'])) ?></p>
                                    <?php if ($recorrencia['data_fim']): ?>
                                        <p><strong>Fim:</strong> <?= date('d/m/Y', strtotime($recorrencia['data_fim'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($recorrencia['observacoes']): ?>
                                <p><strong>Observações:</strong> <?= htmlspecialchars($recorrencia['observacoes']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>Total</h5>
                                    <h3><?= count($ocorrencias) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>Concluídos</h5>
                                    <h3><?= count(array_filter($ocorrencias, fn($o) => $o['status'] === 'concluido')) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>Pendentes</h5>
                                    <h3><?= count(array_filter($ocorrencias, fn($o) => in_array($o['status'], ['agendado', 'confirmado']))) ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>Cancelados</h5>
                                    <h3><?= count(array_filter($ocorrencias, fn($o) => $o['status'] === 'cancelado')) ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Ocorrências -->
                    <?php if (empty($ocorrencias)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">Nenhuma ocorrência encontrada</h4>
                            <p class="text-muted">As ocorrências serão criadas automaticamente conforme a recorrência</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($ocorrencias as $ocorrencia): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card ocorrencia-card h-100 shadow-sm">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <?= formatarStatus($ocorrencia['status']) ?>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="agendamentos.php?editar=<?= $ocorrencia['id'] ?>">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    </li>
                                                    <?php if (in_array($ocorrencia['status'], ['agendado', 'confirmado'])): ?>
                                                        <li>
                                                            <a class="dropdown-item text-warning" 
                                                               href="?acao=cancelar&id=<?= $id ?>&agendamento_id=<?= $ocorrencia['id'] ?>"
                                                               onclick="return confirm('Tem certeza que deseja cancelar esta ocorrência?')">
                                                                <i class="fas fa-times"></i> Cancelar
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-info" 
                                                               href="#" 
                                                               onclick="abrirModalRemarcar(<?= $ocorrencia['id'] ?>, '<?= $ocorrencia['data'] ?>', '<?= $ocorrencia['hora'] ?>')">
                                                                <i class="fas fa-calendar-alt"></i> Remarcar
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-calendar-day"></i> 
                                                <?= date('d/m/Y', strtotime($ocorrencia['data'])) ?>
                                            </h6>
                                            <p class="card-text">
                                                <i class="fas fa-clock"></i> <?= $ocorrencia['hora'] ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-user"></i> <?= htmlspecialchars($ocorrencia['cliente_nome']) ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-paw"></i> <?= htmlspecialchars($ocorrencia['pet_nome']) ?>
                                            </p>
                                            <p class="card-text">
                                                <i class="fas fa-tools"></i> <?= htmlspecialchars($ocorrencia['servico']) ?>
                                            </p>
                                            <?php if ($ocorrencia['observacoes']): ?>
                                                <p class="card-text small">
                                                    <i class="fas fa-sticky-note"></i> <?= htmlspecialchars($ocorrencia['observacoes']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer text-muted small">
                                            <i class="fas fa-calendar-plus"></i> Criado em <?= date('d/m/Y H:i', strtotime($ocorrencia['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para Remarcar -->
    <div class="modal fade" id="modalRemarcar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remarcar Ocorrência</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRemarcar" method="GET">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="remarcar">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="agendamento_id" id="agendamento_id">
                        
                        <div class="mb-3">
                            <label for="nova_data" class="form-label">Nova Data</label>
                            <input type="date" class="form-control" id="nova_data" name="nova_data" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nova_hora" class="form-label">Nova Hora</label>
                            <input type="time" class="form-control" id="nova_hora" name="nova_hora" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Remarcar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirModalRemarcar(agendamentoId, dataAtual, horaAtual) {
            document.getElementById('agendamento_id').value = agendamentoId;
            document.getElementById('nova_data').value = dataAtual;
            document.getElementById('nova_hora').value = horaAtual;
            
            new bootstrap.Modal(document.getElementById('modalRemarcar')).show();
        }
    </script>
</body>
</html> 