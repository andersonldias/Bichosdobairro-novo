<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';
require_once '../src/Cliente.php';
require_once '../src/Pet.php';

echo "<h1>Verificação de Dados no Banco</h1>";

try {
    // Verificar clientes
    echo "<h2>📋 Clientes</h2>";
    $clientes = Cliente::listarTodos();
    echo "<p>Total de clientes: " . count($clientes) . "</p>";
    
    if (count($clientes) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>CPF</th><th>Endereço</th></tr>";
        foreach ($clientes as $cliente) {
            echo "<tr>";
            echo "<td>" . $cliente['id'] . "</td>";
            echo "<td>" . htmlspecialchars($cliente['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['cpf'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['endereco'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhum cliente encontrado</p>";
    }
    
    // Verificar telefones
    echo "<h2>📞 Telefones</h2>";
    $stmt = $pdo->query('SELECT t.*, c.nome as cliente_nome FROM telefones t JOIN clientes c ON t.cliente_id = c.id ORDER BY t.cliente_id, t.id');
    $telefones = $stmt->fetchAll();
    echo "<p>Total de telefones: " . count($telefones) . "</p>";
    
    if (count($telefones) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Cliente ID</th><th>Cliente Nome</th><th>Nome</th><th>Número</th></tr>";
        foreach ($telefones as $telefone) {
            echo "<tr>";
            echo "<td>" . $telefone['id'] . "</td>";
            echo "<td>" . $telefone['cliente_id'] . "</td>";
            echo "<td>" . htmlspecialchars($telefone['cliente_nome']) . "</td>";
            echo "<td>" . htmlspecialchars($telefone['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($telefone['numero']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhum telefone encontrado</p>";
    }
    
    // Verificar pets
    echo "<h2>🐕 Pets</h2>";
    $pets = Pet::listarTodos();
    echo "<p>Total de pets: " . count($pets) . "</p>";
    
    if (count($pets) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Espécie</th><th>Raça</th><th>Idade</th><th>Cliente ID</th><th>Cliente Nome</th></tr>";
        foreach ($pets as $pet) {
            echo "<tr>";
            echo "<td>" . $pet['id'] . "</td>";
            echo "<td>" . htmlspecialchars($pet['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($pet['especie']) . "</td>";
            echo "<td>" . htmlspecialchars($pet['raca']) . "</td>";
            echo "<td>" . htmlspecialchars($pet['idade']) . "</td>";
            echo "<td>" . $pet['cliente_id'] . "</td>";
            echo "<td>" . htmlspecialchars($pet['cliente_nome']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ Nenhum pet encontrado</p>";
    }
    
    // Verificar estrutura das tabelas (MySQL)
    echo "<h2>🏗️ Estrutura das Tabelas</h2>";
    
    // Estrutura da tabela clientes
    echo "<h3>Tabela 'clientes':</h3>";
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "<td>" . $coluna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Estrutura da tabela telefones
    echo "<h3>Tabela 'telefones':</h3>";
    $stmt = $pdo->query("DESCRIBE telefones");
    $colunas = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "<td>" . $coluna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Estrutura da tabela pets
    echo "<h3>Tabela 'pets':</h3>";
    $stmt = $pdo->query("DESCRIBE pets");
    $colunas = $stmt->fetchAll();
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "<td>" . $coluna['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h2 { color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; }
h3 { color: #666; }
</style> 