<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    echo "=== CORRIGINDO TABELA usuarios ===\n";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela 'usuarios' não existe!\n";
        exit;
    }
    
    // Lista de colunas para adicionar
    $colunas_para_adicionar = [
        'nivel_acesso ENUM("admin", "usuario") DEFAULT "usuario"',
        'ativo BOOLEAN DEFAULT TRUE',
        'ultimo_login TIMESTAMP NULL',
        'tentativas_login INT DEFAULT 0',
        'bloqueado_ate TIMESTAMP NULL'
    ];
    
    // Verificar colunas existentes
    $stmt = $pdo->query("DESCRIBE usuarios");
    $colunas_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colunas_existentes[] = $row['Field'];
    }
    
    echo "Colunas existentes: " . implode(', ', $colunas_existentes) . "\n\n";
    
    // Adicionar colunas faltantes
    foreach ($colunas_para_adicionar as $coluna) {
        $nome_coluna = explode(' ', $coluna)[0];
        
        if (!in_array($nome_coluna, $colunas_existentes)) {
            echo "➕ Adicionando coluna: $nome_coluna\n";
            $sql = "ALTER TABLE usuarios ADD COLUMN $coluna";
            $pdo->exec($sql);
            echo "✅ Coluna $nome_coluna adicionada com sucesso!\n";
        } else {
            echo "✅ Coluna $nome_coluna já existe\n";
        }
    }
    
    // Adicionar índices se não existirem
    echo "\n=== ADICIONANDO ÍNDICES ===\n";
    
    $indices = [
        'idx_email' => 'email',
        'idx_ativo' => 'ativo'
    ];
    
    foreach ($indices as $nome_indice => $coluna) {
        try {
            $sql = "CREATE INDEX $nome_indice ON usuarios ($coluna)";
            $pdo->exec($sql);
            echo "✅ Índice $nome_indice criado\n";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "✅ Índice $nome_indice já existe\n";
            } else {
                echo "❌ Erro ao criar índice $nome_indice: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Criar usuário admin se não existir
    echo "\n=== CRIANDO USUÁRIO ADMIN ===\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@bichosdobairro.com']);
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso, ativo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'Administrador',
            'admin@bichosdobairro.com',
            '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // admin123
            'admin',
            1
        ]);
        echo "✅ Usuário admin criado com sucesso!\n";
    } else {
        echo "✅ Usuário admin já existe\n";
    }
    
    // Verificar estrutura final
    echo "\n=== ESTRUTURA FINAL ===\n";
    $stmt = $pdo->query("DESCRIBE usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    echo "\n🎉 TABELA usuarios CORRIGIDA COM SUCESSO!\n";
    echo "🔐 Credenciais: admin@bichosdobairro.com / admin123\n";
    echo "🌐 Teste em: http://localhost:8000/login-simples.php\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 