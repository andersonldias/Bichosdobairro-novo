<?php
require_once '../src/init.php';

echo "<h1>🔄 Forçar Geração de Ocorrências</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = getDb();
    
    echo "<h2>1. Verificando Agendamentos Recorrentes Ativos</h2>";
    
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.ativo = TRUE";
    
    $stmt = $pdo->query($sql);
    $recorrentes = $stmt->fetchAll();
    
    if (empty($recorrentes)) {
        echo "<p style='color: orange;'>⚠️ Nenhum agendamento recorrente ativo encontrado</p>";
    } else {
        echo "<p style='color: green;'>✅ " . count($recorrentes) . " agendamentos recorrentes ativos encontrados</p>";
        
        foreach ($recorrentes as $recorrente) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>ID " . $recorrente['id'] . ": " . htmlspecialchars($recorrente['cliente_nome']) . " - " . htmlspecialchars($recorrente['pet_nome']) . "</strong><br>";
            echo "Tipo: " . $recorrente['tipo_recorrencia'] . " | Dia: " . $recorrente['dia_semana'] . " | Hora: " . $recorrente['hora_inicio'] . "<br>";
            echo "Início: " . $recorrente['data_inicio'] . " | Fim: " . ($recorrente['data_fim'] ?? 'Indefinido');
            echo "</div>";
        }
    }
    
    echo "<h2>2. Gerando Ocorrências para Próximos 60 Dias</h2>";
    
    $dataInicio = date('Y-m-d');
    $dataFim = date('Y-m-d', strtotime('+60 days'));
    
    echo "<p><strong>Período:</strong> $dataInicio até $dataFim</p>";
    
    if (class_exists('AgendamentoRecorrente')) {
        $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($dataInicio, $dataFim);
        
        if (!empty($ocorrencias)) {
            echo "<p style='color: green;'>✅ " . count($ocorrencias) . " ocorrências geradas</p>";
            
            // Agrupar por recorrência
            $agrupadas = [];
            foreach ($ocorrencias as $ocorrencia) {
                $recId = $ocorrencia['recorrencia_id'];
                if (!isset($agrupadas[$recId])) {
                    $agrupadas[$recId] = [];
                }
                $agrupadas[$recId][] = $ocorrencia;
            }
            
            foreach ($agrupadas as $recId => $ocorrenciasRec) {
                echo "<h3>Recorrência ID $recId (" . count($ocorrenciasRec) . " ocorrências)</h3>";
                foreach (array_slice($ocorrenciasRec, 0, 5) as $ocorrencia) {
                    echo "<div style='background: #e6ffe6; padding: 8px; margin: 5px 0; border-radius: 3px;'>";
                    echo "Data: " . $ocorrencia['data'] . " | Hora: " . $ocorrencia['hora'];
                    echo "</div>";
                }
                if (count($ocorrenciasRec) > 5) {
                    echo "<p style='color: gray;'>... e mais " . (count($ocorrenciasRec) - 5) . " ocorrências</p>";
                }
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma ocorrência gerada para o período</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Classe AgendamentoRecorrente não encontrada</p>";
    }
    
    echo "<h2>3. Verificando Agendamentos Criados</h2>";
    
    $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome, ar.tipo_recorrencia
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN pets p ON a.pet_id = p.id
            LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
            WHERE a.data BETWEEN :data_inicio AND :data_fim
            ORDER BY a.data, a.hora";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim
    ]);
    
    $agendamentos = $stmt->fetchAll();
    
    echo "<p style='color: green;'>✅ " . count($agendamentos) . " agendamentos encontrados no período</p>";
    
    $recorrentesBanco = array_filter($agendamentos, function($a) {
        return !empty($a['recorrencia_id']);
    });
    
    echo "<p style='color: green;'>✅ " . count($recorrentesBanco) . " agendamentos recorrentes no banco</p>";
    
    if (!empty($recorrentesBanco)) {
        echo "<h3>Próximos Agendamentos Recorrentes:</h3>";
        foreach (array_slice($recorrentesBanco, 0, 10) as $agendamento) {
            echo "<div style='background: #f0f8ff; padding: 8px; margin: 5px 0; border-radius: 3px;'>";
            echo "<strong>" . htmlspecialchars($agendamento['cliente_nome']) . " - " . htmlspecialchars($agendamento['pet_nome']) . "</strong><br>";
            echo "Data: " . $agendamento['data'] . " | Hora: " . $agendamento['hora'] . " | Recorrência ID: " . $agendamento['recorrencia_id'];
            echo "</div>";
        }
    }
    
    echo "<h2>4. Verificando Especificamente 25/07/2025</h2>";
    
    $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome, ar.tipo_recorrencia
            FROM agendamentos a
            JOIN clientes c ON a.cliente_id = c.id
            JOIN pets p ON a.pet_id = p.id
            LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
            WHERE a.data = '2025-07-25'
            ORDER BY a.hora";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $agendamentos25 = $stmt->fetchAll();
    
    if (!empty($agendamentos25)) {
        echo "<p style='color: green;'>✅ " . count($agendamentos25) . " agendamentos encontrados para 25/07/2025</p>";
        foreach ($agendamentos25 as $agendamento) {
            echo "<div style='background: #e6ffe6; padding: 10px; margin: 5px 0; border-radius: 5px; border-left: 4px solid #10b981;'>";
            echo "<strong>" . htmlspecialchars($agendamento['cliente_nome']) . " - " . htmlspecialchars($agendamento['pet_nome']) . "</strong><br>";
            echo "Hora: " . $agendamento['hora'] . " | Serviço: " . htmlspecialchars($agendamento['servico']);
            if ($agendamento['recorrencia_id']) {
                echo " | <span style='color: blue;'>RECORRENTE (ID: " . $agendamento['recorrencia_id'] . ")</span>";
            }
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>❌ Nenhum agendamento encontrado para 25/07/2025</p>";
        
        // Forçar geração específica para 25/07/2025
        echo "<h3>5. Forçando Geração para 25/07/2025</h3>";
        
        if (class_exists('AgendamentoRecorrente')) {
            $ocorrencias25 = AgendamentoRecorrente::gerarOcorrencias('2025-07-25', '2025-07-25');
            
            if (!empty($ocorrencias25)) {
                echo "<p style='color: green;'>✅ " . count($ocorrencias25) . " ocorrências geradas para 25/07/2025</p>";
                
                foreach ($ocorrencias25 as $ocorrencia) {
                    $resultado = AgendamentoRecorrente::criarAgendamentoOcorrencia($ocorrencia);
                    if ($resultado) {
                        echo "<p style='color: green;'>✅ Agendamento criado com ID: $resultado</p>";
                    } else {
                        echo "<p style='color: red;'>❌ Erro ao criar agendamento</p>";
                    }
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Nenhuma ocorrência calculada para 25/07/2025</p>";
            }
        }
    }
    
    echo "<h2>🎉 Resultado Final</h2>";
    
    if (!empty($agendamentos25)) {
        echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
        echo "<h3 style='color: #059669; text-align: center;'>✅ SUCESSO!</h3>";
        echo "<p style='color: #059669; text-align: center;'>Agendamentos recorrentes foram gerados e devem aparecer no calendário.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fef3c7; padding: 20px; border-radius: 8px; border: 2px solid #f59e0b;'>";
        echo "<h3 style='color: #d97706; text-align: center;'>⚠️ ATENÇÃO</h3>";
        echo "<p style='color: #d97706; text-align: center;'>Verifique se a data 25/07/2025 corresponde ao padrão de recorrência configurado.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='agendamentos.php?dia=2025-07-25'>Calendário 25/07/2025</a> | <a href='agendamentos-recorrentes.php'>Agendamentos Recorrentes</a></p>";
echo "<p><a href='debug-recorrente-calendario.php'>Debug Detalhado</a> | <a href='teste-calendario-recorrentes.php'>Teste Geral</a></p>";

echo "<p><strong>Script executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 