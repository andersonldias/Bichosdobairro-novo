<?php
echo "=== TESTE DE CONEXÃO COM BANCO ===\n\n";

$host = readline("Host (localhost): ");
if (empty($host)) $host = 'localhost';

$dbname = readline("Database: ");
if (empty($dbname)) {
    echo "❌ Database é obrigatório!\n";
    exit(1);
}

$username = readline("Username: ");
if (empty($username)) {
    echo "❌ Username é obrigatório!\n";
    exit(1);
}

$password = readline("Password: ");

echo "\nTestando conexão...\n";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ CONEXÃO BEM-SUCEDIDA!\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "✅ Banco está vazio - perfeito para importar!\n";
    } else {
        echo "⚠️  Banco tem " . count($tables) . " tabelas\n";
    }
    
    $config = [
        'host' => $host,
        'dbname' => $dbname,
        'username' => $username,
        'password' => $password
    ];
    
    file_put_contents('credenciais_corretas.json', json_encode($config, JSON_PRETTY_PRINT));
    echo "✅ Credenciais salvas!\n";
    
} catch (PDOException $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Verifique as credenciais!\n";
} 