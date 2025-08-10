<?php
echo "=== TESTE DE CONEXÃO ===\n";
echo "Data/Hora: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";

// Testar conexão com banco
try {
    require_once '../src/init.php';
    $pdo = getDb();
    echo "✅ Conexão com banco: OK\n";
    
    // Verificar tabelas
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela usuarios: OK\n";
    } else {
        echo "❌ Tabela usuarios: NÃO ENCONTRADA\n";
    }
    
    // Verificar usuário admin
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@bichosdobairro.com']);
    $count = $stmt->fetchColumn();
    if ($count > 0) {
        echo "✅ Usuário admin: OK\n";
    } else {
        echo "❌ Usuário admin: NÃO ENCONTRADO\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
}

// Testar arquivos necessários
$arquivos = [
    '../src/init.php',
    '../src/Auth.php',
    '../src/AuthMiddleware.php'
];

echo "\n=== VERIFICAÇÃO DE ARQUIVOS ===\n";
foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "✅ $arquivo: OK\n";
    } else {
        echo "❌ $arquivo: NÃO ENCONTRADO\n";
    }
}

// Testar sessão
echo "\n=== TESTE DE SESSÃO ===\n";
session_start();
echo "✅ Sessão iniciada\n";
echo "Session ID: " . session_id() . "\n";

echo "\n=== TESTE COMPLETO ===\n";
echo "Se você consegue ver esta mensagem, o PHP está funcionando!\n";
echo "Acesse: http://localhost:8000/login.php\n";
?> 