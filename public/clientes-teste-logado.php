<?php
/**
 * Teste do clientes.php com usuário logado
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste Clientes.php - Usuário Logado</h1>";

// Incluir init.php
require_once '../src/init.php';

echo "<p>✅ init.php carregado</p>";
echo "<p>👤 Usuário logado: ID " . ($_SESSION['usuario_id'] ?? 'Nenhum') . "</p>";
echo "<p>🔍 Testando se há redirecionamento no clientes.php...</p>";

// Simular o que o clientes.php faz
if (isAjax()) {
    echo "<p>📡 Requisição AJAX detectada</p>";
} else {
    echo "<p>🌐 Requisição normal (não AJAX)</p>";
}

// Testar busca de clientes
if (class_exists('Cliente')) {
    echo "<p>🔍 Testando Cliente::buscar('')...</p>";
    try {
        $clientes = Cliente::buscar('');
        echo "<p>✅ Busca realizada: " . count($clientes) . " clientes encontrados</p>";
    } catch (Exception $e) {
        echo "<p>❌ Erro na busca: " . $e->getMessage() . "</p>";
    }
}

echo "<p><strong>Se você está vendo esta mensagem, o problema está no clientes.php original!</strong></p>";
echo "<p><a href='clientes.php'>Testar clientes.php original</a></p>";
?>