<?php
require_once '../src/init.php';

echo "<h1>🔧 Teste de Conexão Simples</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    echo "<h2>1. Testando getDb()</h2>";
    $pdo = getDb();
    
    if ($pdo instanceof PDO) {
        echo "<p style='color: green;'>✅ getDb() retornou PDO válido</p>";
        
        echo "<h2>2. Testando Query Simples</h2>";
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        if ($result && $result['test'] == 1) {
            echo "<p style='color: green;'>✅ Query de teste funcionou</p>";
        } else {
            echo "<p style='color: red;'>❌ Query de teste falhou</p>";
        }
        
        echo "<h2>3. Testando Cliente::listarTodos()</h2>";
        $clientes = Cliente::listarTodos();
        
        if (is_array($clientes)) {
            echo "<p style='color: green;'>✅ Cliente::listarTodos() funcionou: " . count($clientes) . " clientes</p>";
        } else {
            echo "<p style='color: red;'>❌ Cliente::listarTodos() falhou</p>";
        }
        
        echo "<h2>4. Testando Pet::listarTodos()</h2>";
        $pets = Pet::listarTodos();
        
        if (is_array($pets)) {
            echo "<p style='color: green;'>✅ Pet::listarTodos() funcionou: " . count($pets) . " pets</p>";
        } else {
            echo "<p style='color: red;'>❌ Pet::listarTodos() falhou</p>";
        }
        
        echo "<h2>5. Testando Agendamento::listarTodos()</h2>";
        $agendamentos = Agendamento::listarTodos();
        
        if (is_array($agendamentos)) {
            echo "<p style='color: green;'>✅ Agendamento::listarTodos() funcionou: " . count($agendamentos) . " agendamentos</p>";
        } else {
            echo "<p style='color: red;'>❌ Agendamento::listarTodos() falhou</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ getDb() não retornou PDO válido</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='teste-final-sistema.php'>Voltar ao Teste Final</a></p>";
?> 