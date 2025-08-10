<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';

echo "<h1>Telefones Cadastrados</h1>";

try {
    $sql = "SELECT t.id, c.nome AS cliente, t.nome AS tipo, t.numero FROM telefones t JOIN clientes c ON t.cliente_id = c.id ORDER BY t.id DESC";
    $stmt = $pdo->query($sql);
    $telefones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($telefones)) {
        echo "<p>Nenhum telefone cadastrado.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'><th>ID</th><th>Cliente</th><th>Tipo</th><th>Número</th></tr>";
        foreach ($telefones as $tel) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($tel['id']) . "</td>";
            echo "<td>" . htmlspecialchars($tel['cliente']) . "</td>";
            echo "<td>" . htmlspecialchars($tel['tipo']) . "</td>";
            echo "<td>" . htmlspecialchars($tel['numero']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao consultar telefones: " . $e->getMessage() . "</p>";
} 