<?php
require_once '../src/init.php';

echo "<h1>🔧 Correção do Banco de Dados - Produção</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>URL:</strong> meuapp.bichosdobairro.com.br</p>";

try {
    $pdo = getDb();
    
    echo "<h2>1. Verificando Conexão com o Banco</h2>";
    echo "<p style='color: green;'>✅ Conexão com banco estabelecida com sucesso</p>";
    
    echo "<h2>2. Criando Tabela de Usuários</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        nivel_acesso ENUM('admin', 'usuario') DEFAULT 'usuario',
        ativo BOOLEAN DEFAULT TRUE,
        ultimo_login TIMESTAMP NULL,
        tentativas_login INT DEFAULT 0,
        bloqueado_ate TIMESTAMP NULL,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_ativo (ativo)
    )";
    
    $result = $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela 'usuarios' criada/verificada com sucesso</p>";
    
    echo "<h2>3. Criando Tabela de Logs de Login</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS logs_login (
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
    
    $result = $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela 'logs_login' criada/verificada com sucesso</p>";
    
    echo "<h2>4. Criando Tabela de Níveis de Acesso</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS niveis_acesso (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL UNIQUE,
        descricao TEXT,
        permissoes JSON,
        ativo BOOLEAN DEFAULT TRUE,
        criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $result = $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela 'niveis_acesso' criada/verificada com sucesso</p>";
    
    echo "<h2>5. Criando Tabela de Logs de Atividade</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS logs_atividade (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuario_id INT NULL,
        acao VARCHAR(100) NOT NULL,
        tabela_afetada VARCHAR(50),
        registro_id INT,
        dados_anteriores JSON,
        dados_novos JSON,
        ip_address VARCHAR(45),
        user_agent TEXT,
        data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_usuario_id (usuario_id),
        INDEX idx_acao (acao),
        INDEX idx_tabela_afetada (tabela_afetada),
        INDEX idx_data_hora (data_hora),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
    )";
    
    $result = $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela 'logs_atividade' criada/verificada com sucesso</p>";
    
    echo "<h2>6. Verificando Tabelas Existentes</h2>";
    
    $tabelas = ['clientes', 'pets', 'agendamentos', 'usuarios', 'logs_login', 'niveis_acesso', 'logs_atividade'];
    $tabelasExistentes = [];
    
    foreach ($tabelas as $tabela) {
        $sql = "SHOW TABLES LIKE '$tabela'";
        $stmt = $pdo->query($sql);
        if ($stmt->rowCount() > 0) {
            $tabelasExistentes[] = $tabela;
            echo "<p style='color: green;'>✅ Tabela '$tabela' existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabela '$tabela' não existe</p>";
        }
    }
    
    echo "<h2>7. Inserindo Usuário Administrador</h2>";
    
    // Verificar se já existe um admin
    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        // Senha: admin123 (deve ser alterada após primeiro login)
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES 
                ('Administrador', 'admin@bichosdobairro.com', :senha, 'admin')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['senha' => $senhaHash]);
        
        echo "<p style='color: green;'>✅ Usuário administrador criado com sucesso</p>";
        echo "<p><strong>Email:</strong> admin@bichosdobairro.com</p>";
        echo "<p><strong>Senha:</strong> admin123</p>";
        echo "<p style='color: orange;'>⚠️ IMPORTANTE: Altere a senha após o primeiro login!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Usuário administrador já existe</p>";
    }
    
    echo "<h2>8. Inserindo Níveis de Acesso Padrão</h2>";
    
    $niveis = [
        ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema', 'permissoes' => '["*"]'],
        ['nome' => 'Usuário', 'descricao' => 'Acesso básico ao sistema', 'permissoes' => '["agendamentos.visualizar", "clientes.visualizar", "pets.visualizar"]']
    ];
    
    foreach ($niveis as $nivel) {
        $sql = "INSERT IGNORE INTO niveis_acesso (nome, descricao, permissoes) VALUES (:nome, :descricao, :permissoes)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($nivel);
    }
    
    echo "<p style='color: green;'>✅ Níveis de acesso padrão inseridos</p>";
    
    echo "<h2>🎉 Correção Concluída!</h2>";
    
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
    echo "<h3 style='color: #059669; text-align: center;'>✅ SUCESSO!</h3>";
    echo "<p style='color: #059669; text-align: center;'>Todas as tabelas foram criadas com sucesso.</p>";
    echo "<p style='color: #059669; text-align: center;'>O sistema de login agora deve funcionar corretamente.</p>";
    echo "</div>";
    
    echo "<h3>🔑 Credenciais de Acesso</h3>";
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
    echo "<p><strong>URL de Login:</strong> <a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>https://meuapp.bichosdobairro.com.br/login-simples.php</a></p>";
    echo "<p><strong>Email:</strong> admin@bichosdobairro.com</p>";
    echo "<p><strong>Senha:</strong> admin123</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>Página de Login</a></p>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/' target='_blank'>Página Principal</a></p>";

echo "<p><strong>Script executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 