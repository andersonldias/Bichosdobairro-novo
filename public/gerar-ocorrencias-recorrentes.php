<?php
/**
 * Gerar Ocorrências de Agendamentos Recorrentes
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
$resultados = [];

// Processar geração
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gerar'])) {
    try {
        $pdo = getDb();
        
        // Buscar agendamentos recorrentes ativos
        $sql = "SELECT * FROM agendamentos_recorrentes WHERE ativo = TRUE";
        $stmt = $pdo->query($sql);
        $agendamentos_recorrentes = $stmt->fetchAll();
        
        $total_gerados = 0;
        $total_erros = 0;
        
        foreach ($agendamentos_recorrentes as $ar) {
            try {
                // Calcular próximas datas baseadas no tipo de recorrência
                $datas = calcularProximasDatas($ar);
                
                foreach ($datas as $data) {
                    // Verificar se já existe agendamento para esta data
                    $sql_check = "SELECT id FROM agendamentos 
                                 WHERE recorrencia_id = ? AND data = ?";
                    $stmt_check = $pdo->prepare($sql_check);
                    $stmt_check->execute([$ar['id'], $data]);
                    
                    if ($stmt_check->rowCount() == 0) {
                        // Inserir nova ocorrência
                        $sql_insert = "INSERT INTO agendamentos 
                                      (cliente_id, pet_id, data, hora_inicio, duracao, servico, observacoes, 
                                       recorrencia_id, data_original, status, created_at, updated_at) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'confirmado', NOW(), NOW())";
                        
                        $stmt_insert = $pdo->prepare($sql_insert);
                        $stmt_insert->execute([
                            $ar['cliente_id'],
                            $ar['pet_id'],
                            $data,
                            $ar['hora_inicio'],
                            $ar['duracao'],
                            'Agendamento Recorrente',
                            $ar['observacoes'],
                            $ar['id'],
                            $data
                        ]);
                        
                        $agendamento_id = $pdo->lastInsertId();
                        
                        // Log da criação
                        $sql_log = "INSERT INTO logs_agendamentos_recorrentes 
                                   (recorrencia_id, agendamento_id, acao, dados_novos, usuario_id, observacoes) 
                                   VALUES (?, ?, 'criado', ?, ?, ?)";
                        
                        $dados_novos = json_encode([
                            'data' => $data,
                            'hora_inicio' => $ar['hora_inicio'],
                            'duracao' => $ar['duracao'],
                            'servico' => 'Agendamento Recorrente'
                        ]);
                        
                        $stmt_log = $pdo->prepare($sql_log);
                        $stmt_log->execute([$ar['id'], $agendamento_id, $dados_novos, $_SESSION['usuario_id'], 'Ocorrência gerada automaticamente']);
                        
                        $total_gerados++;
                        $resultados[] = "✅ Gerada ocorrência para " . date('d/m/Y', strtotime($data));
                    }
                }
                
            } catch (Exception $e) {
                $total_erros++;
                $resultados[] = "❌ Erro ao gerar ocorrências para agendamento ID {$ar['id']}: " . $e->getMessage();
            }
        }
        
        $sucesso = "Geração concluída! $total_gerados ocorrências geradas, $total_erros erros.";
        
    } catch (Exception $e) {
        $erro = "Erro ao gerar ocorrências: " . $e->getMessage();
    }
}

/**
 * Calcular próximas datas baseadas no tipo de recorrência
 */
function calcularProximasDatas($agendamento) {
    $datas = [];
    $data_atual = new DateTime();
    $data_inicio = new DateTime($agendamento['data_inicio']);
    $data_fim = $agendamento['data_fim'] ? new DateTime($agendamento['data_fim']) : null;
    
    // Se a data de início é no futuro, usar ela como base
    if ($data_inicio > $data_atual) {
        $data_base = $data_inicio;
    } else {
        $data_base = clone $data_atual;
    }
    
    // Ajustar para o próximo dia da semana correto
    $dia_semana_atual = $data_base->format('N'); // 1=Segunda, 7=Domingo
    $dias_para_adicionar = ($agendamento['dia_semana'] - $dia_semana_atual + 7) % 7;
    
    if ($dias_para_adicionar > 0) {
        $data_base->add(new DateInterval("P{$dias_para_adicionar}D"));
    }
    
    // Gerar próximas 12 ocorrências
    for ($i = 0; $i < 12; $i++) {
        $data_ocorrencia = clone $data_base;
        
        // Verificar se está dentro do período válido
        if ($data_fim && $data_ocorrencia > $data_fim) {
            break;
        }
        
        // Para mensal, ajustar para a semana correta do mês
        if ($agendamento['tipo_recorrencia'] === 'mensal' && $agendamento['semana_mes']) {
            $data_ocorrencia = ajustarParaSemanaDoMes($data_ocorrencia, $agendamento['semana_mes']);
        }
        
        $datas[] = $data_ocorrencia->format('Y-m-d');
        
        // Calcular próxima data baseada no tipo de recorrência
        switch ($agendamento['tipo_recorrencia']) {
            case 'semanal':
                $data_base->add(new DateInterval('P7D'));
                break;
            case 'quinzenal':
                $data_base->add(new DateInterval('P14D'));
                break;
            case 'mensal':
                $data_base->add(new DateInterval('P1M'));
                break;
        }
    }
    
    return $datas;
}

/**
 * Ajustar data para a semana correta do mês
 */
function ajustarParaSemanaDoMes($data, $semana_mes) {
    $ano = $data->format('Y');
    $mes = $data->format('m');
    $dia_semana = $data->format('N'); // 1=Segunda, 7=Domingo
    
    // Primeiro dia do mês
    $primeiro_dia = new DateTime("$ano-$mes-01");
    $primeiro_dia_semana = $primeiro_dia->format('N');
    
    // Calcular o primeiro dia da semana desejada
    $dias_para_primeiro = ($dia_semana - $primeiro_dia_semana + 7) % 7;
    $primeiro_dia_semana_desejada = clone $primeiro_dia;
    $primeiro_dia_semana_desejada->add(new DateInterval("P{$dias_para_primeiro}D"));
    
    // Adicionar semanas conforme necessário
    if ($semana_mes == 5) {
        // Última semana do mês
        $ultimo_dia = new DateTime("$ano-$mes-" . $primeiro_dia->format('t'));
        $ultimo_dia_semana = $ultimo_dia->format('N');
        $dias_para_ultimo = ($dia_semana - $ultimo_dia_semana - 7) % 7;
        $data_ajustada = clone $ultimo_dia;
        $data_ajustada->add(new DateInterval("P{$dias_para_ultimo}D"));
    } else {
        // Semana específica (1-4)
        $semanas_para_adicionar = $semana_mes - 1;
        $data_ajustada = clone $primeiro_dia_semana_desejada;
        $data_ajustada->add(new DateInterval("P" . ($semanas_para_adicionar * 7) . "D"));
    }
    
    return $data_ajustada;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerar Ocorrências - Agendamentos Recorrentes - Bichos do Bairro</title>
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
                            <i class="fas fa-magic text-blue-600 mr-2"></i>
                            Gerar Ocorrências Recorrentes
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

            <?php if ($sucesso): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <!-- Informações -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Como Funciona
                </h3>
                <div class="text-sm text-blue-800 space-y-2">
                    <p>Este script gera automaticamente as próximas ocorrências para todos os agendamentos recorrentes ativos.</p>
                    <p><strong>Semanal:</strong> Gera ocorrências para as próximas 12 semanas</p>
                    <p><strong>Quinzenal:</strong> Gera ocorrências para as próximas 12 quinzenas</p>
                    <p><strong>Mensal:</strong> Gera ocorrências para os próximos 12 meses</p>
                    <p>Ocorrências já existentes não serão duplicadas.</p>
                </div>
            </div>

            <!-- Formulário -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        <i class="fas fa-cogs text-blue-600 mr-2"></i>
                        Gerar Ocorrências
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Clique no botão abaixo para gerar as próximas ocorrências
                    </p>
                </div>
                
                <div class="px-4 py-5 sm:p-6">
                    <form method="POST">
                        <div class="text-center">
                            <button type="submit" name="gerar" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-medium"
                                    onclick="return confirm('Gerar ocorrências para todos os agendamentos recorrentes ativos?')">
                                <i class="fas fa-magic mr-2"></i>
                                Gerar Ocorrências
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resultados -->
            <?php if (!empty($resultados)): ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg mt-6">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            <i class="fas fa-list text-blue-600 mr-2"></i>
                            Resultados da Geração
                        </h3>
                    </div>
                    
                    <div class="px-4 py-5 sm:p-6">
                        <div class="space-y-2 max-h-96 overflow-y-auto">
                            <?php foreach ($resultados as $resultado): ?>
                                <div class="text-sm">
                                    <?= $resultado ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html> 