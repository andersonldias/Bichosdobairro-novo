<?php
require_once '../src/db.php';
require_once '../src/Cliente.php';

echo "<h2>Teste de Validação de Duplicidade</h2>";

// Listar todos os clientes com CPF
echo "<h3>Clientes cadastrados:</h3>";
$stmt = $pdo->query('SELECT id, nome, cpf FROM clientes WHERE cpf IS NOT NULL AND cpf != "" ORDER BY nome');
$clientes = $stmt->fetchAll();

if (empty($clientes)) {
    echo "<p>Nenhum cliente com CPF cadastrado.</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>CPF</th></tr>";
    foreach ($clientes as $cliente) {
        echo "<tr>";
        echo "<td>{$cliente['id']}</td>";
        echo "<td>{$cliente['nome']}</td>";
        echo "<td>{$cliente['cpf']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Listar todos os telefones
echo "<h3>Telefones cadastrados:</h3>";
$stmt = $pdo->query('SELECT t.id, t.numero, c.nome as cliente_nome FROM telefones t INNER JOIN clientes c ON t.cliente_id = c.id ORDER BY c.nome');
$telefones = $stmt->fetchAll();

if (empty($telefones)) {
    echo "<p>Nenhum telefone cadastrado.</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Número</th><th>Cliente</th></tr>";
    foreach ($telefones as $telefone) {
        echo "<tr>";
        echo "<td>{$telefone['id']}</td>";
        echo "<td>{$telefone['numero']}</td>";
        echo "<td>{$telefone['cliente_nome']}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Testar validação de CPF
echo "<h3>Teste de Validação de CPF:</h3>";
if (!empty($clientes)) {
    $cpf_teste = $clientes[0]['cpf'];
    echo "<p>Testando CPF: {$cpf_teste}</p>";
    
    $cliente = new Cliente();
    $duplicado = $cliente->verificarDuplicidade('cpf', $cpf_teste);
    
    echo "<p>Resultado da verificação: " . ($duplicado ? "DUPLICADO" : "NÃO DUPLICADO") . "</p>";
    
    // Simular a resposta do endpoint
    if ($duplicado) {
        $resposta = [
            'valido' => false,
            'mensagem' => 'Este CPF já está cadastrado'
        ];
    } else {
        $resposta = [
            'valido' => true,
            'mensagem' => ''
        ];
    }
    
    echo "<p>Resposta que deveria ser retornada: " . json_encode($resposta) . "</p>";
}

// Testar validação de telefone
echo "<h3>Teste de Validação de Telefone:</h3>";
if (!empty($telefones)) {
    $telefone_teste = $telefones[0]['numero'];
    echo "<p>Testando telefone: {$telefone_teste}</p>";
    
    $cliente = new Cliente();
    $duplicado = $cliente->verificarDuplicidadeTelefone($telefone_teste);
    
    echo "<p>Resultado da verificação: " . ($duplicado ? "DUPLICADO" : "NÃO DUPLICADO") . "</p>";
    
    // Simular a resposta do endpoint
    if ($duplicado) {
        $resposta = [
            'valido' => false,
            'mensagem' => 'Este telefone já está cadastrado'
        ];
    } else {
        $resposta = [
            'valido' => true,
            'mensagem' => ''
        ];
    }
    
    echo "<p>Resposta que deveria ser retornada: " . json_encode($resposta) . "</p>";
}
?> 