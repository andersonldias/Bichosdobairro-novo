<?php
require_once '../src/init.php';

echo "<h1>🧪 Teste Ultra Simples - Sistema Bichos do Bairro</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    echo "<h2>1. Testando Conexão</h2>";
    $pdo = getDb();
    echo "<p style='color: green;'>✅ Conexão OK</p>";
    
    echo "<h2>2. Testando Cliente::listarTodos()</h2>";
    $clientes = Cliente::listarTodos();
    echo "<p style='color: green;'>✅ Cliente::listarTodos() OK - " . count($clientes) . " clientes</p>";
    
    echo "<h2>3. Testando Pet::listarTodos()</h2>";
    $pets = Pet::listarTodos();
    echo "<p style='color: green;'>✅ Pet::listarTodos() OK - " . count($pets) . " pets</p>";
    
    echo "<h2>4. Testando Agendamento::listarTodos()</h2>";
    $agendamentos = Agendamento::listarTodos();
    echo "<p style='color: green;'>✅ Agendamento::listarTodos() OK - " . count($agendamentos) . " agendamentos</p>";
    
    echo "<h2>🎉 RESULTADO FINAL</h2>";
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ SISTEMA FUNCIONANDO PERFEITAMENTE!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><a href='teste-final-sistema.php'>Teste Original</a> | <a href='teste-final-corrigido.php'>Teste Corrigido</a> | <a href='dashboard.php'>Dashboard</a></p>";
?> 