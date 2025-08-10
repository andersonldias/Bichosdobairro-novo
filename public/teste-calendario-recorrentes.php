<?php
require_once '../src/init.php';

echo "<h1>🧪 Teste - Agendamentos Recorrentes no Calendário</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    echo "<h2>1. Verificando Agendamentos Recorrentes Existentes</h2>";
    
    $pdo = getDb();
    $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            WHERE ar.ativo = TRUE";
    
    $stmt = $pdo->query($sql);
    $recorrentes = $stmt->fetchAll();
    
    if (empty($recorrentes)) {
        echo "<p style='color: orange;'>⚠️ Nenhum agendamento recorrente ativo encontrado</p>";
        echo "<p>Crie um agendamento recorrente primeiro em: <a href='agendamentos-recorrentes-form.php'>Novo Recorrente</a></p>";
    } else {
        echo "<p style='color: green;'>✅ " . count($recorrentes) . " agendamentos recorrentes ativos encontrados</p>";
        
        foreach ($recorrentes as $recorrente) {
            echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>" . htmlspecialchars($recorrente['cliente_nome']) . " - " . htmlspecialchars($recorrente['pet_nome']) . "</strong><br>";
            echo "Tipo: " . $recorrente['tipo_recorrencia'] . " | Dia: " . $recorrente['dia_semana'] . " | Hora: " . $recorrente['hora_inicio'] . "<br>";
            echo "Início: " . $recorrente['data_inicio'] . " | Fim: " . ($recorrente['data_fim'] ?? 'Indefinido');
            echo "</div>";
        }
    }
    
    echo "<h2>2. Testando Geração de Ocorrências</h2>";
    
    $dataInicio = date('Y-m-d');
    $dataFim = date('Y-m-d', strtotime('+30 days'));
    
    if (class_exists('AgendamentoRecorrente')) {
        $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($dataInicio, $dataFim);
        
        if (!empty($ocorrencias)) {
            echo "<p style='color: green;'>✅ " . count($ocorrencias) . " ocorrências geradas para os próximos 30 dias</p>";
            
            echo "<h3>Próximas Ocorrências:</h3>";
            foreach (array_slice($ocorrencias, 0, 5) as $ocorrencia) {
                echo "<div style='background: #f0f8ff; padding: 8px; margin: 5px 0; border-radius: 3px;'>";
                echo "Data: " . $ocorrencia['data'] . " | Hora: " . $ocorrencia['hora'] . " | Recorrência ID: " . $ocorrencia['recorrencia_id'];
                echo "</div>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Nenhuma ocorrência gerada para o período</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Classe AgendamentoRecorrente não encontrada</p>";
    }
    
    echo "<h2>3. Testando Endpoint do Calendário</h2>";
    
    // Simular chamada do endpoint
    $url = "agendamentos.php?action=listar&start=$dataInicio&end=$dataFim";
    echo "<p><strong>URL de teste:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    // Fazer a chamada real
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
        
        // Contar agendamentos recorrentes
        $recorrentes = array_filter($eventos, function($evento) {
            return isset($evento['extendedProps']['is_recorrente']) && $evento['extendedProps']['is_recorrente'];
        });
        
        echo "<p style='color: green;'>✅ " . count($recorrentes) . " eventos são agendamentos recorrentes</p>";
        
        if (!empty($recorrentes)) {
            echo "<h3>Agendamentos Recorrentes no Calendário:</h3>";
            foreach (array_slice($recorrentes, 0, 3) as $evento) {
                echo "<div style='background: #e6f3ff; padding: 8px; margin: 5px 0; border-radius: 3px; border-left: 4px solid #3b82f6;'>";
                echo "<strong>" . $evento['title'] . "</strong><br>";
                echo "Data: " . $evento['start'] . "<br>";
                echo "Cor: " . $evento['backgroundColor'] . " (Azul = Recorrente)<br>";
                echo "Recorrência ID: " . $evento['extendedProps']['recorrencia_id'];
                echo "</div>";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao acessar endpoint do calendário</p>";
        if ($response) {
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }
    }
    
    echo "<h2>4. Verificando Agendamentos no Banco</h2>";
    
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
    
    echo "<p style='color: green;'>✅ " . count($agendamentos) . " agendamentos encontrados no banco para o período</p>";
    
    $recorrentesBanco = array_filter($agendamentos, function($a) {
        return !empty($a['recorrencia_id']);
    });
    
    echo "<p style='color: green;'>✅ " . count($recorrentesBanco) . " agendamentos recorrentes no banco</p>";
    
    if (!empty($recorrentesBanco)) {
        echo "<h3>Agendamentos Recorrentes no Banco:</h3>";
        foreach (array_slice($recorrentesBanco, 0, 3) as $agendamento) {
            echo "<div style='background: #f0f8ff; padding: 8px; margin: 5px 0; border-radius: 3px;'>";
            echo "<strong>" . htmlspecialchars($agendamento['cliente_nome']) . " - " . htmlspecialchars($agendamento['pet_nome']) . "</strong><br>";
            echo "Data: " . $agendamento['data'] . " | Hora: " . $agendamento['hora'] . "<br>";
            echo "Serviço: " . htmlspecialchars($agendamento['servico']) . " | Recorrência: " . $agendamento['tipo_recorrencia'];
            echo "</div>";
        }
    }
    
    echo "<h2>🎉 Resultado Final</h2>";
    
    if (!empty($recorrentes) && !empty($eventos) && count($recorrentes) > 0) {
        echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
        echo "<h3 style='color: #059669; text-align: center;'>✅ FUNCIONANDO PERFEITAMENTE!</h3>";
        echo "<p style='color: #059669; text-align: center;'>Os agendamentos recorrentes estão aparecendo no calendário nos seus respectivos dias e horários.</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #fef3c7; padding: 20px; border-radius: 8px; border: 2px solid #f59e0b;'>";
        echo "<h3 style='color: #d97706; text-align: center;'>⚠️ ATENÇÃO</h3>";
        echo "<p style='color: #d97706; text-align: center;'>Crie agendamentos recorrentes primeiro para testar a funcionalidade.</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='agendamentos.php'>Calendário Principal</a> | <a href='agendamentos-recorrentes.php'>Agendamentos Recorrentes</a> | <a href='agendamentos-recorrentes-form.php'>Novo Recorrente</a></p>";
echo "<p><a href='teste-menu-integrado.php'>Teste do Menu</a> | <a href='teste-integracao-recorrentes.php'>Teste de Integração</a></p>";

echo "<p><strong>Teste executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 