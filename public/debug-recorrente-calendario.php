<?php
require_once '../src/init.php';

echo "<h1>🔍 Debug - Agendamento Recorrente no Calendário</h1>";
echo "<p><strong>Data de Teste:</strong> 25/07/2025</p>";

try {
    $pdo = getDb();
    
    echo "<h2>1. Verificando Agendamento Recorrente ID 2</h2>";
    
    // Buscar o agendamento recorrente específico
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.id = 2";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $recorrente = $stmt->fetch();
    
    if ($recorrente) {
        echo "<div style='background: #e6f3ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>Dados do Agendamento Recorrente:</h3>";
        echo "<p><strong>Cliente:</strong> " . htmlspecialchars($recorrente['cliente_nome']) . "</p>";
        echo "<p><strong>Pet:</strong> " . htmlspecialchars($recorrente['pet_nome']) . "</p>";
        echo "<p><strong>Tipo:</strong> " . $recorrente['tipo_recorrencia'] . "</p>";
        echo "<p><strong>Dia da Semana:</strong> " . $recorrente['dia_semana'] . " (0=Domingo, 1=Segunda, ..., 6=Sábado)</p>";
        echo "<p><strong>Semana do Mês:</strong> " . ($recorrente['semana_mes'] ?? 'Todas') . "</p>";
        echo "<p><strong>Hora:</strong> " . $recorrente['hora_inicio'] . "</p>";
        echo "<p><strong>Data Início:</strong> " . $recorrente['data_inicio'] . "</p>";
        echo "<p><strong>Data Fim:</strong> " . ($recorrente['data_fim'] ?? 'Indefinido') . "</p>";
        echo "<p><strong>Ativo:</strong> " . ($recorrente['ativo'] ? 'SIM' : 'NÃO') . "</p>";
        echo "</div>";
        
        // Verificar se 25/07/2025 é uma sexta-feira (dia 5)
        $dataTeste = '2025-07-25';
        $diaSemana = date('w', strtotime($dataTeste)); // 0=Domingo, 1=Segunda, ..., 6=Sábado
        $semanaMes = ceil(date('j', strtotime($dataTeste)) / 7);
        
        echo "<h2>2. Análise da Data 25/07/2025</h2>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<p><strong>Data:</strong> $dataTeste</p>";
        echo "<p><strong>Dia da Semana:</strong> $diaSemana (0=Domingo, 1=Segunda, ..., 6=Sábado)</p>";
        echo "<p><strong>Semana do Mês:</strong> $semanaMes</p>";
        echo "<p><strong>Dia da Semana Configurado:</strong> " . $recorrente['dia_semana'] . "</p>";
        echo "<p><strong>Semana do Mês Configurada:</strong> " . ($recorrente['semana_mes'] ?? 'Todas') . "</p>";
        
        $deveAparecer = false;
        if ($diaSemana == $recorrente['dia_semana']) {
            if ($recorrente['tipo_recorrencia'] === 'mensal' && $recorrente['semana_mes']) {
                if ($semanaMes == $recorrente['semana_mes']) {
                    $deveAparecer = true;
                }
            } else {
                $deveAparecer = true;
            }
        }
        
        echo "<p><strong>Deve Aparecer:</strong> " . ($deveAparecer ? 'SIM' : 'NÃO') . "</p>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ Agendamento recorrente ID 2 não encontrado</p>";
    }
    
    echo "<h2>3. Testando Geração de Ocorrências</h2>";
    
    if (class_exists('AgendamentoRecorrente')) {
        $dataInicio = '2025-07-25';
        $dataFim = '2025-07-25';
        
        $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($dataInicio, $dataFim);
        
        if (!empty($ocorrencias)) {
            echo "<p style='color: green;'>✅ " . count($ocorrencias) . " ocorrências geradas para 25/07/2025</p>";
            foreach ($ocorrencias as $ocorrencia) {
                echo "<div style='background: #e6ffe6; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
                echo "Recorrência ID: " . $ocorrencia['recorrencia_id'] . " | Data: " . $ocorrencia['data'] . " | Hora: " . $ocorrencia['hora'];
                echo "</div>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma ocorrência gerada para 25/07/2025</p>";
        }
    }
    
    echo "<h2>4. Verificando Agendamentos no Banco</h2>";
    
    $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome, ar.tipo_recorrencia
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN pets p ON a.pet_id = p.id
            LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
            WHERE a.data = '2025-07-25'
            ORDER BY a.hora";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $agendamentos = $stmt->fetchAll();
    
    if (!empty($agendamentos)) {
        echo "<p style='color: green;'>✅ " . count($agendamentos) . " agendamentos encontrados para 25/07/2025</p>";
        foreach ($agendamentos as $agendamento) {
            echo "<div style='background: #f0f8ff; padding: 10px; margin: 5px 0; border-radius: 5px;'>";
            echo "<strong>" . htmlspecialchars($agendamento['cliente_nome']) . " - " . htmlspecialchars($agendamento['pet_nome']) . "</strong><br>";
            echo "Hora: " . $agendamento['hora'] . " | Serviço: " . htmlspecialchars($agendamento['servico']);
            if ($agendamento['recorrencia_id']) {
                echo " | <span style='color: blue;'>RECORRENTE (ID: " . $agendamento['recorrencia_id'] . ")</span>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Nenhum agendamento encontrado para 25/07/2025</p>";
    }
    
    echo "<h2>5. Testando Endpoint do Calendário</h2>";
    
    // Simular chamada do endpoint
    $url = "agendamentos.php?action=listar&start=2025-07-25&end=2025-07-25";
    echo "<p><strong>URL de teste:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    $eventos = json_decode($response, true);
    
    if ($eventos && is_array($eventos)) {
        echo "<p style='color: green;'>✅ " . count($eventos) . " eventos retornados pelo endpoint</p>";
        
        $recorrentes = array_filter($eventos, function($evento) {
            return isset($evento['extendedProps']['is_recorrente']) && $evento['extendedProps']['is_recorrente'];
        });
        
        echo "<p style='color: green;'>✅ " . count($recorrentes) . " eventos são agendamentos recorrentes</p>";
        
        if (!empty($recorrentes)) {
            foreach ($recorrentes as $evento) {
                echo "<div style='background: #e6f3ff; padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #3b82f6;'>";
                echo "<strong>" . $evento['title'] . "</strong><br>";
                echo "Data: " . $evento['start'] . " | Cor: " . $evento['backgroundColor'];
                echo "</div>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao acessar endpoint do calendário</p>";
        if ($response) {
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
    
    echo "<h2>6. Forçar Geração de Ocorrência</h2>";
    
    if (class_exists('AgendamentoRecorrente') && $recorrente) {
        // Forçar geração de ocorrência para 25/07/2025
        $ocorrencia = [
            'recorrencia_id' => 2,
            'cliente_id' => $recorrente['cliente_id'],
            'pet_id' => $recorrente['pet_id'],
            'data' => '2025-07-25',
            'hora' => $recorrente['hora_inicio'],
            'servico' => 'Agendamento Recorrente',
            'status' => 'confirmado',
            'observacoes' => $recorrente['observacoes'],
            'data_original' => '2025-07-25'
        ];
        
        // Verificar se já existe
        $existe = AgendamentoRecorrente::verificarAgendamentoExistente(2, '2025-07-25');
        echo "<p><strong>Já existe agendamento para 25/07/2025:</strong> " . ($existe ? 'SIM' : 'NÃO') . "</p>";
        
        if (!$existe) {
            $resultado = AgendamentoRecorrente::criarAgendamentoOcorrencia($ocorrencia);
            if ($resultado) {
                echo "<p style='color: green;'>✅ Ocorrência criada com sucesso! ID: $resultado</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao criar ocorrência</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Ocorrência já existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='agendamentos.php?dia=2025-07-25'>Calendário 25/07/2025</a> | <a href='agendamentos-recorrentes.php'>Agendamentos Recorrentes</a></p>";
echo "<p><a href='teste-calendario-recorrentes.php'>Teste Geral</a></p>";

echo "<p><strong>Debug executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 