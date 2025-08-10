<?php
/**
 * Teste de Listagem e Exibição de Agendamentos
 * Sistema Bichos do Bairro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>📋 Teste de Listagem e Exibição de Agendamentos</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// ========================================
// 1. CARREGAR SISTEMA
// ========================================
echo "<div class='section'>";
echo "<h2>1. 🔌 Carregamento do Sistema</h2>";

try {
    require_once __DIR__ . '/../src/init.php';
    echo "<div class='success'>✅ Sistema carregado</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
echo "</div>";

// ========================================
// 2. CRIAR AGENDAMENTO DE TESTE
// ========================================
echo "<div class='section'>";
echo "<h2>2. ➕ Criando Agendamento de Teste</h2>";

try {
    // Pegar dados válidos
    $stmt = $pdo->query("SELECT id FROM clientes LIMIT 1");
    $cliente = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM pets LIMIT 1");
    $pet = $stmt->fetch();
    
    if (!$cliente || !$pet) {
        echo "<div class='error'>❌ Dados insuficientes para teste</div>";
        exit;
    }
    
    // Criar agendamento de teste
    $dados = [
        'cliente_id' => $cliente['id'],
        'pet_id' => $pet['id'],
        'data' => date('Y-m-d'),
        'hora' => '17:00',
        'servico' => 'Teste de Listagem',
        'observacoes' => 'Agendamento para testar listagem',
        'status' => 'Pendente'
    ];
    
    echo "<h3>Dados do agendamento de teste:</h3>";
    echo "<pre>" . print_r($dados, true) . "</pre>";
    
    $agendamentoId = Agendamento::criar($dados);
    
    if ($agendamentoId) {
        echo "<div class='success'>✅ Agendamento criado com ID: $agendamentoId</div>";
    } else {
        echo "<div class='error'>❌ Falha ao criar agendamento</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao criar agendamento: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
echo "</div>";

// ========================================
// 3. TESTE DE LISTAGEM DIRETA
// ========================================
echo "<div class='section'>";
echo "<h2>3. 📋 Teste de Listagem Direta</h2>";

try {
    // Teste 1: listarTodos()
    echo "<h3>3.1 Teste do método listarTodos():</h3>";
    $todosAgendamentos = Agendamento::listarTodos();
    
    if (is_array($todosAgendamentos)) {
        echo "<div class='success'>✅ listarTodos() retornou array com " . count($todosAgendamentos) . " agendamentos</div>";
        
        if (count($todosAgendamentos) > 0) {
            echo "<h4>Agendamentos encontrados:</h4>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Pet</th><th>Data</th><th>Hora</th><th>Serviço</th><th>Status</th></tr>";
            
            foreach ($todosAgendamentos as $agendamento) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($agendamento['id']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['pet_nome'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['data']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['hora']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['servico']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['status']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ Nenhum agendamento encontrado</div>";
        }
    } else {
        echo "<div class='error'>❌ listarTodos() não retornou array</div>";
    }
    
    // Teste 2: getAgendamentosPorData()
    echo "<h3>3.2 Teste do método getAgendamentosPorData():</h3>";
    $hoje = date('Y-m-d');
    $agendamentosHoje = Agendamento::getAgendamentosPorData($hoje);
    
    if (is_array($agendamentosHoje)) {
        echo "<div class='success'>✅ getAgendamentosPorData() retornou array com " . count($agendamentosHoje) . " agendamentos para hoje ($hoje)</div>";
        
        if (count($agendamentosHoje) > 0) {
            echo "<h4>Agendamentos de hoje:</h4>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Cliente</th><th>Pet</th><th>Data</th><th>Hora</th><th>Serviço</th><th>Status</th></tr>";
            
            foreach ($agendamentosHoje as $agendamento) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($agendamento['id']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['pet_nome'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['data']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['hora']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['servico']) . "</td>";
                echo "<td>" . htmlspecialchars($agendamento['status']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='warning'>⚠️ Nenhum agendamento encontrado para hoje</div>";
        }
    } else {
        echo "<div class='error'>❌ getAgendamentosPorData() não retornou array</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro na listagem: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 4. TESTE DE CONSULTA DIRETA NO BANCO
// ========================================
echo "<div class='section'>";
echo "<h2>4. 🗄️ Teste de Consulta Direta no Banco</h2>";

try {
    // Consulta direta na tabela agendamentos
    $stmt = $pdo->query("SELECT * FROM agendamentos ORDER BY data DESC, hora DESC");
    $agendamentosDiretos = $stmt->fetchAll();
    
    echo "<div class='info'>📊 Consulta direta retornou " . count($agendamentosDiretos) . " agendamentos</div>";
    
    if (count($agendamentosDiretos) > 0) {
        echo "<h4>Agendamentos no banco (consulta direta):</h4>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Cliente ID</th><th>Pet ID</th><th>Data</th><th>Hora</th><th>Serviço</th><th>Status</th><th>Criado em</th></tr>";
        
        foreach ($agendamentosDiretos as $agendamento) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($agendamento['id']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['cliente_id']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['pet_id']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['data']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['hora']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['servico']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['status']) . "</td>";
            echo "<td>" . htmlspecialchars($agendamento['criado_em']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Teste de JOIN para verificar se os dados estão corretos
    $stmt = $pdo->query("
        SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        LEFT JOIN pets p ON a.pet_id = p.id 
        ORDER BY a.data DESC, a.hora DESC
    ");
    $agendamentosComJoin = $stmt->fetchAll();
    
    echo "<h4>Agendamentos com JOIN (igual ao método listarTodos):</h4>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Cliente</th><th>Pet</th><th>Data</th><th>Hora</th><th>Serviço</th><th>Status</th></tr>";
    
    foreach ($agendamentosComJoin as $agendamento) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($agendamento['id']) . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['pet_nome'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['data']) . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['hora']) . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['servico']) . "</td>";
        echo "<td>" . htmlspecialchars($agendamento['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro na consulta direta: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 5. LIMPEZA DO TESTE
// ========================================
echo "<div class='section'>";
echo "<h2>5. 🧹 Limpeza do Teste</h2>";

try {
    // Remover o agendamento de teste
    $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
    $stmt->execute([$agendamentoId]);
    
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✅ Agendamento de teste removido com sucesso</div>";
    } else {
        echo "<div class='warning'>⚠️ Agendamento de teste não foi removido</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao remover agendamento de teste: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>✅ Teste de Listagem Concluído</h2>";
echo "<p>Este teste verificou se os agendamentos estão sendo listados e exibidos corretamente.</p>";
echo "</div>";
?> 