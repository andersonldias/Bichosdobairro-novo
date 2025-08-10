<?php
require_once 'src/init.php';

echo "<h1>🔍 Verificação dos Dados do Cliente</h1>";

try {
    $pdo = getDb();
    
    echo "<h2>1. Dados do Cliente</h2>";
    $stmt = $pdo->query("SELECT * FROM clientes");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "❌ Nenhum cliente encontrado<br>";
    } else {
        foreach ($clientes as $cliente) {
            echo "<h3>Cliente ID: " . $cliente['id'] . "</h3>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            foreach ($cliente as $campo => $valor) {
                $valor = $valor ?: '<em>vazio</em>';
                echo "<tr><td><strong>$campo:</strong></td><td>$valor</td></tr>";
            }
            echo "</table>";
        }
    }
    
    echo "<h2>2. Telefones do Cliente</h2>";
    $stmt = $pdo->query("SELECT * FROM telefones");
    $telefones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($telefones)) {
        echo "❌ Nenhum telefone encontrado<br>";
    } else {
        foreach ($telefones as $telefone) {
            echo "<p>📱 Telefone ID: " . $telefone['id'] . " - Cliente ID: " . $telefone['cliente_id'] . " - Número: " . $telefone['numero'] . "</p>";
        }
    }
    
    echo "<h2>3. Pets do Cliente</h2>";
    $stmt = $pdo->query("SELECT * FROM pets");
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($pets)) {
        echo "❌ Nenhum pet encontrado<br>";
    } else {
        foreach ($pets as $pet) {
            echo "<p>🐕 Pet ID: " . $pet['id'] . " - Cliente ID: " . $pet['cliente_id'] . " - Nome: " . $pet['nome'] . "</p>";
        }
    }
    
    echo "<h2>4. Estrutura das Tabelas</h2>";
    
    // Verificar estrutura da tabela clientes
    echo "<h3>Tabela clientes:</h3>";
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . $coluna['Field'] . "</td>";
        echo "<td>" . $coluna['Type'] . "</td>";
        echo "<td>" . $coluna['Null'] . "</td>";
        echo "<td>" . $coluna['Key'] . "</td>";
        echo "<td>" . $coluna['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar estrutura da tabela telefones
    echo "<h3>Tabela telefones:</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE telefones");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "❌ Erro ao verificar tabela telefones: " . $e->getMessage() . "<br>";
    }
    
    // Verificar estrutura da tabela pets
    echo "<h3>Tabela pets:</h3>";
    try {
        $stmt = $pdo->query("DESCRIBE pets");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($colunas as $coluna) {
            echo "<tr>";
            echo "<td>" . $coluna['Field'] . "</td>";
            echo "<td>" . $coluna['Type'] . "</td>";
            echo "<td>" . $coluna['Null'] . "</td>";
            echo "<td>" . $coluna['Key'] . "</td>";
            echo "<td>" . $coluna['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "❌ Erro ao verificar tabela pets: " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
