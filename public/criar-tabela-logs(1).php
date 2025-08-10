<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    echo "=== CRIANDO TABELA logs_login ===\n";
    
    // Verificar se a tabela já existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs_login'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'logs_login' já existe!\n";
    } else {
        // Criar tabela logs_login
        $sql = "
        CREATE TABLE logs_login (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            email VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            sucesso BOOLEAN NOT NULL,
            data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_email (email),
            INDEX idx_data_hora (data_hora),
            INDEX idx_sucesso (sucesso),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        echo "✅ Tabela 'logs_login' criada com sucesso!\n";
    }
    
    // Verificar se a tabela logs_atividade existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs_atividade'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Tabela 'logs_atividade' já existe!\n";
    } else {
        // Criar tabela logs_atividade
        $sql = "
        CREATE TABLE logs_atividade (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NULL,
            acao VARCHAR(100) NOT NULL,
            detalhes TEXT,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT,
            data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_acao (acao),
            INDEX idx_data_hora (data_hora),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        )";
        
        $pdo->exec($sql);
        echo "✅ Tabela 'logs_atividade' criada com sucesso!\n";
    }
    
    // Verificar todas as tabelas
    echo "\n=== TABELAS EXISTENTES ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "✅ {$row[0]}\n";
    }
    
    // Testar inserção de log
    echo "\n=== TESTANDO INSERÇÃO DE LOG ===\n";
    try {
        $sql = "INSERT INTO logs_login (email, ip_address, user_agent, sucesso) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['teste@teste.com', '127.0.0.1', 'Teste', true]);
        echo "✅ Inserção de log funcionando!\n";
        
        // Limpar log de teste
        $pdo->exec("DELETE FROM logs_login WHERE email = 'teste@teste.com'");
        echo "✅ Log de teste removido!\n";
    } catch (Exception $e) {
        echo "❌ Erro ao testar inserção: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 TODAS AS TABELAS CRIADAS COM SUCESSO!\n";
    echo "🔐 Sistema de login pronto para uso!\n";
    echo "🌐 Teste em: http://localhost:8000/login-simples.php\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 