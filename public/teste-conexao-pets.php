<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';
require_once '../src/Cliente.php';
require_once '../src/Pet.php';

echo "<h1>Teste de Conexão e Pets</h1>";

try {
    // Testar conexão
    echo "<h2>Testando conexão...</h2>";
    $stmt = $pdo->query('SELECT 1');
    echo "✅ Conexão OK<br>";
    
    // Testar criação de cliente
    echo "<h2>Testando criação de cliente...</h2>";
    $cliente_id = Cliente::criar(
        'Teste Cliente',
        'teste@teste.com',
        '123.456.789-00',
        [],
        '',
        '',
        '',
        '',
        '',
        '',
        '',
        ''
    );
    echo "✅ Cliente criado com ID: $cliente_id<br>";
    
    // Testar criação de pet
    echo "<h2>Testando criação de pet...</h2>";
    $result = Pet::criar('Rex', 'Cão', 'Labrador', '3', $cliente_id);
    if ($result) {
        echo "✅ Pet criado com sucesso<br>";
    } else {
        echo "❌ Erro ao criar pet<br>";
    }
    
    // Listar pets
    echo "<h2>Listando pets...</h2>";
    $pets = Pet::listarTodos();
    echo "<pre>";
    print_r($pets);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro:</h2>";
    echo $e->getMessage();
}
?> 