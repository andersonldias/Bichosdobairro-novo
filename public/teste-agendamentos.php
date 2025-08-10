<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste do Sistema de Agendamentos</h1>";

try {
    require_once __DIR__ . '/../src/init.php';
    echo "<p style='color: green;'>✓ Sistema inicializado com sucesso</p>";
    
    // Testar conexão com banco
    global $pdo;
    if ($pdo) {
        echo "<p style='color: green;'>✓ Conexão com banco estabelecida</p>";
    } else {
        echo "<p style='color: red;'>✗ Erro na conexão com banco</p>";
    }
    
    // Testar classes
    if (class_exists('Agendamento')) {
        echo "<p style='color: green;'>✓ Classe Agendamento carregada</p>";
    } else {
        echo "<p style='color: red;'>✗ Classe Agendamento não encontrada</p>";
    }
    
    if (class_exists('Cliente')) {
        echo "<p style='color: green;'>✓ Classe Cliente carregada</p>";
    } else {
        echo "<p style='color: red;'>✗ Classe Cliente não encontrada</p>";
    }
    
    if (class_exists('Pet')) {
        echo "<p style='color: green;'>✓ Classe Pet carregada</p>";
    } else {
        echo "<p style='color: red;'>✗ Classe Pet não encontrada</p>";
    }
    
    // Testar listagem de agendamentos
    try {
        $agendamentos = Agendamento::listarTodos();
        echo "<p style='color: green;'>✓ Listagem de agendamentos: " . count($agendamentos) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro ao listar agendamentos: " . $e->getMessage() . "</p>";
    }
    
    // Testar listagem de clientes
    try {
        $clientes = Cliente::listarTodos();
        echo "<p style='color: green;'>✓ Listagem de clientes: " . count($clientes) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro ao listar clientes: " . $e->getMessage() . "</p>";
    }
    
    // Testar listagem de pets
    try {
        $pets = Pet::listarTodos();
        echo "<p style='color: green;'>✓ Listagem de pets: " . count($pets) . " registros</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro ao listar pets: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>Teste Completo!</h2>";
    echo "<p><a href='agendamentos.php'>Ir para a página de agendamentos</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro geral: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 