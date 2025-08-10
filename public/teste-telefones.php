<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';

echo "<h2>Teste de Inserção e Leitura de Telefones</h2>";

try {
    // 1. Inserir cliente de teste
    $nome = 'Cliente Teste Telefones';
    $email = 'teste.telefone@exemplo.com';
    $cpf = '123.456.789-00';
    $endereco = 'Rua dos Testes, 123';

    // Verifica se já existe
    $stmt = $pdo->prepare('SELECT id FROM clientes WHERE email = ?');
    $stmt->execute([$email]);
    $cliente = $stmt->fetch();
    if ($cliente) {
        $cliente_id = $cliente['id'];
        echo "<p>Cliente de teste já existe (ID: $cliente_id)</p>";
    } else {
        $stmt = $pdo->prepare('INSERT INTO clientes (nome, email, cpf, endereco) VALUES (?, ?, ?, ?)');
        $stmt->execute([$nome, $email, $cpf, $endereco]);
        $cliente_id = $pdo->lastInsertId();
        echo "<p>Cliente de teste inserido (ID: $cliente_id)</p>";
    }

    // 2. Inserir telefones de teste
    $telefones = [
        ['nome' => 'Celular', 'numero' => '(11) 99999-0001'],
        ['nome' => 'Residencial', 'numero' => '(11) 4002-8922']
    ];
    $inseridos = 0;
    foreach ($telefones as $tel) {
        // Verifica se já existe
        $stmt = $pdo->prepare('SELECT id FROM telefones WHERE cliente_id = ? AND numero = ?');
        $stmt->execute([$cliente_id, $tel['numero']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare('INSERT INTO telefones (cliente_id, nome, numero) VALUES (?, ?, ?)');
            $stmt->execute([$cliente_id, $tel['nome'], $tel['numero']]);
            $inseridos++;
        }
    }
    echo "<p>Telefones inseridos: $inseridos</p>";

    // 3. Listar todos os telefones do cliente e mostrar endereço detalhado
    $stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = ?');
    $stmt->execute([$cliente_id]);
    $cli = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cli) {
        echo "<h3>Endereço cadastrado:</h3>";
        echo "<ul>";
        echo "<li>Endereço completo: " . htmlspecialchars($cli['endereco']) . "</li>";
        echo "<li>CEP: " . htmlspecialchars($cli['cep']) . "</li>";
        echo "<li>Logradouro: " . htmlspecialchars($cli['logradouro']) . "</li>";
        echo "<li>Número: " . htmlspecialchars($cli['numero']) . "</li>";
        echo "<li>Complemento: " . htmlspecialchars($cli['complemento']) . "</li>";
        echo "<li>Bairro: " . htmlspecialchars($cli['bairro']) . "</li>";
        echo "<li>Cidade: " . htmlspecialchars($cli['cidade']) . "</li>";
        echo "<li>Estado: " . htmlspecialchars($cli['estado']) . "</li>";
        echo "</ul>";
    }

    // 4. Listar todos os telefones do cliente
    $stmt = $pdo->prepare('SELECT * FROM telefones WHERE cliente_id = ?');
    $stmt->execute([$cliente_id]);
    $telefones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($telefones) {
        echo "<h3>Telefones cadastrados para o cliente:</h3>";
        echo "<ul>";
        foreach ($telefones as $tel) {
            echo "<li>" . htmlspecialchars($tel['nome']) . ": " . htmlspecialchars($tel['numero']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhum telefone cadastrado para este cliente.</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro: " . $e->getMessage() . "</p>";
} 