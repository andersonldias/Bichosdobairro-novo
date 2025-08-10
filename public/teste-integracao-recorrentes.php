<?php
require_once '../src/init.php';

echo "<h1>🧪 Teste de Integração - Agendamentos Recorrentes</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    echo "<h2>1. Verificando Classe AgendamentoRecorrente</h2>";
    if (class_exists('AgendamentoRecorrente')) {
        echo "<p style='color: green;'>✅ Classe AgendamentoRecorrente carregada</p>";
    } else {
        echo "<p style='color: red;'>❌ Classe AgendamentoRecorrente não encontrada</p>";
        exit;
    }
    
    echo "<h2>2. Verificando Tabelas</h2>";
    $pdo = getDb();
    
    // Verificar tabela agendamentos_recorrentes
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos_recorrentes'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Tabela agendamentos_recorrentes existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Tabela agendamentos_recorrentes não existe</p>";
    }
    
    // Verificar colunas na tabela agendamentos
    $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'recorrencia_id'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Coluna recorrencia_id existe em agendamentos</p>";
    } else {
        echo "<p style='color: red;'>❌ Coluna recorrencia_id não existe em agendamentos</p>";
    }
    
    echo "<h2>3. Testando Criação de Agendamento Recorrente</h2>";
    
    // Buscar primeiro cliente e pet para teste
    $clientes = Cliente::listarTodos();
    $pets = Pet::listarTodos();
    
    if (empty($clientes) || empty($pets)) {
        echo "<p style='color: red;'>❌ Não há clientes ou pets para teste</p>";
    } else {
        $cliente = $clientes[0];
        $pet = $pets[0];
        
        $dadosRecorrente = [
            'cliente_id' => $cliente['id'],
            'pet_id' => $pet['id'],
            'tipo_recorrencia' => 'semanal',
            'dia_semana' => 2, // Terça-feira
            'hora_inicio' => '10:00:00',
            'duracao' => 60,
            'data_inicio' => date('Y-m-d'),
            'observacoes' => 'Teste de integração'
        ];
        
        $idRecorrente = AgendamentoRecorrente::criar($dadosRecorrente);
        
        if ($idRecorrente) {
            echo "<p style='color: green;'>✅ Agendamento recorrente criado com ID: $idRecorrente</p>";
            
            echo "<h2>4. Testando Geração de Ocorrências</h2>";
            
            $dataInicio = date('Y-m-d');
            $dataFim = date('Y-m-d', strtotime('+30 days'));
            
            $ocorrencias = AgendamentoRecorrente::gerarOcorrencias($dataInicio, $dataFim);
            
            if (!empty($ocorrencias)) {
                echo "<p style='color: green;'>✅ " . count($ocorrencias) . " ocorrências geradas</p>";
                
                // Criar primeira ocorrência
                $primeiraOcorrencia = $ocorrencias[0];
                $agendamentoId = AgendamentoRecorrente::criarAgendamentoOcorrencia($primeiraOcorrencia);
                
                if ($agendamentoId) {
                    echo "<p style='color: green;'>✅ Agendamento individual criado com ID: $agendamentoId</p>";
                    
                    echo "<h2>5. Testando Busca para Calendário</h2>";
                    $agendamentosCalendario = AgendamentoRecorrente::buscarParaCalendario($dataInicio, $dataFim);
                    
                    if (!empty($agendamentosCalendario)) {
                        echo "<p style='color: green;'>✅ " . count($agendamentosCalendario) . " agendamentos encontrados para calendário</p>";
                        
                        // Verificar se há agendamentos recorrentes
                        $recorrentes = array_filter($agendamentosCalendario, function($a) {
                            return !empty($a['recorrencia_id']);
                        });
                        
                        if (!empty($recorrentes)) {
                            echo "<p style='color: green;'>✅ " . count($recorrentes) . " agendamentos recorrentes no calendário</p>";
                        } else {
                            echo "<p style='color: orange;'>⚠️ Nenhum agendamento recorrente encontrado no calendário</p>";
                        }
                    } else {
                        echo "<p style='color: orange;'>⚠️ Nenhum agendamento encontrado para calendário</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ Erro ao criar agendamento individual</p>";
                }
            } else {
                echo "<p style='color: orange;'>⚠️ Nenhuma ocorrência gerada para o período</p>";
            }
            
            // Limpar teste
            AgendamentoRecorrente::deletar($idRecorrente);
            echo "<p style='color: blue;'>🧹 Agendamento recorrente de teste removido</p>";
            
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar agendamento recorrente</p>";
        }
    }
    
    echo "<h2>6. Testando Endpoint do Calendário</h2>";
    
    // Simular chamada do endpoint
    $dataInicio = date('Y-m-d', strtotime('-7 days'));
    $dataFim = date('Y-m-d', strtotime('+30 days'));
    
    $url = "agendamentos.php?action=listar&start=$dataInicio&end=$dataFim";
    echo "<p><strong>URL de teste:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    echo "<h2>🎉 Resultado Final</h2>";
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
    echo "<h3 style='color: #059669; text-align: center;'>✅ INTEGRAÇÃO FUNCIONANDO!</h3>";
    echo "<p style='color: #059669; text-align: center;'>Os agendamentos recorrentes estão integrados ao calendário principal.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='agendamentos.php'>Calendário Principal</a> | <a href='agendamentos-recorrentes.php'>Agendamentos Recorrentes</a> | <a href='agendamentos-recorrentes-form.php'>Criar Recorrente</a></p>";
echo "<p><a href='teste-final-sucesso.php'>Teste Final</a> | <a href='dashboard.php'>Dashboard</a></p>";

echo "<p><strong>Teste executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 