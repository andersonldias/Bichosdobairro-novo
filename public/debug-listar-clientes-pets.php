<?php
require_once '../src/init.php';

echo "<h2>Clientes</h2>";
try {
    global $pdo;
    $clientes = $pdo->query('SELECT id, nome FROM clientes')->fetchAll(PDO::FETCH_ASSOC);
    if ($clientes) {
        echo '<ul>';
        foreach ($clientes as $c) {
            echo '<li>ID: ' . $c['id'] . ' - Nome: ' . htmlspecialchars($c['nome']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo 'Nenhum cliente encontrado.';
    }
} catch (Exception $e) {
    echo 'Erro ao buscar clientes: ' . $e->getMessage();
}

echo "<h2>Pets</h2>";
try {
    $pets = $pdo->query('SELECT id, nome FROM pets')->fetchAll(PDO::FETCH_ASSOC);
    if ($pets) {
        echo '<ul>';
        foreach ($pets as $p) {
            echo '<li>ID: ' . $p['id'] . ' - Nome: ' . htmlspecialchars($p['nome']) . '</li>';
        }
        echo '</ul>';
    } else {
        echo 'Nenhum pet encontrado.';
    }
} catch (Exception $e) {
    echo 'Erro ao buscar pets: ' . $e->getMessage();
} 