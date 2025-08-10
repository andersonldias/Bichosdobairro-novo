<?php
require_once '../src/db.php';

echo '<h2>Teste de Conexão com o Banco de Dados MySQL</h2>';

try {
    $pdo->query('SELECT 1');
    echo '<p style="color:green;">✅ Conexão bem-sucedida!</p>';
    echo '<p>Conectado ao MySQL: ' . getenv('DB_HOST') . '</p>';
    echo '<p>Banco: ' . getenv('DB_NAME') . '</p>';
    echo '<p><a href="index.php">Ir para o Dashboard</a></p>';
} catch (PDOException $e) {
    echo '<p style="color:red;">❌ Erro na conexão: ' . $e->getMessage() . '</p>';
    echo '<p>Verifique se o servidor MySQL está acessível e as credenciais estão corretas.</p>';
} 