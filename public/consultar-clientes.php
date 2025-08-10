<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=bichosdobairro;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Dados Cadastrados na Tabela Cliente</h1>";
    
    // Consultar todos os clientes
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "<p>Nenhum cliente cadastrado na tabela.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th>";
        echo "<th>Nome</th>";
        echo "<th>E-mail</th>";
        echo "<th>CPF</th>";
        echo "<th>Telefone</th>";
        echo "<th>Endereço</th>";
        echo "<th>Criado em</th>";
        echo "</tr>";
        
        foreach ($clientes as $cliente) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cliente['id']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['cpf']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['telefone']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['endereco']) . "</td>";
            echo "<td>" . htmlspecialchars($cliente['criado_em']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<h2>Estatísticas</h2>";
        echo "<p>Total de clientes cadastrados: " . count($clientes) . "</p>";
        
        // Verificar se há clientes com telefone
        $comTelefone = array_filter($clientes, function($c) { return !empty($c['telefone']); });
        echo "<p>Clientes com telefone: " . count($comTelefone) . "</p>";
        
        // Verificar se há clientes com endereço
        $comEndereco = array_filter($clientes, function($c) { return !empty($c['endereco']); });
        echo "<p>Clientes com endereço: " . count($comEndereco) . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p>Erro ao conectar com o banco de dados: " . $e->getMessage() . "</p>";
}
?> 