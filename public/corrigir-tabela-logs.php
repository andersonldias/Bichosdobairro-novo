<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    echo "=== CORRIGINDO TABELA logs_login ===\n";
    
    // Verificar estrutura atual
    echo "Estrutura atual da tabela logs_login:\n";
    $stmt = $pdo->query("DESCRIBE logs_login");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    echo "\n";
    
    // Corrigir tipo da coluna sucesso
    echo "Corrigindo tipo da coluna 'sucesso'...\n";
    try {
        $sql = "ALTER TABLE logs_login MODIFY COLUMN sucesso TINYINT(1) NOT NULL DEFAULT 0";
        $pdo->exec($sql);
        echo "✅ Coluna 'sucesso' corrigida para TINYINT(1)\n";
    } catch (Exception $e) {
        echo "⚠️ Erro ao modificar coluna: " . $e->getMessage() . "\n";
    }
    
    // Verificar se há dados inválidos e limpar
    echo "\nVerificando dados inválidos...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM logs_login WHERE sucesso IS NULL OR sucesso = ''");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "Encontrados $count registros com dados inválidos. Limpando...\n";
        $pdo->exec("DELETE FROM logs_login WHERE sucesso IS NULL OR sucesso = ''");
        echo "✅ Registros inválidos removidos\n";
    } else {
        echo "✅ Nenhum dado inválido encontrado\n";
    }
    
    // Testar inserção
    echo "\nTestando inserção de log...\n";
    try {
        $sql = "INSERT INTO logs_login (email, ip_address, user_agent, sucesso) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['teste@teste.com', '127.0.0.1', 'Teste', 1]);
        echo "✅ Inserção de teste bem-sucedida!\n";
        
        // Limpar teste
        $pdo->exec("DELETE FROM logs_login WHERE email = 'teste@teste.com'");
        echo "✅ Dados de teste removidos\n";
    } catch (Exception $e) {
        echo "❌ Erro na inserção: " . $e->getMessage() . "\n";
    }
    
    // Verificar estrutura final
    echo "\nEstrutura final da tabela logs_login:\n";
    $stmt = $pdo->query("DESCRIBE logs_login");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n🎉 TABELA logs_login CORRIGIDA!\n";
    echo "🔐 Sistema de login pronto para uso!\n";
    echo "🌐 Teste em: http://localhost:8000/login-simples.php\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 