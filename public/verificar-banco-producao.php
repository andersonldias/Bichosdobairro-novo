<?php
// Configurações do banco de dados de produção
$config = [
    'host' => 'mysql.bichosdobairro.com.br',
    'dbname' => 'bichosdobairro',
    'username' => 'bichosdobairro',
    'password' => '7oH57vlG#',
    'charset' => 'utf8mb4'
];

echo "<h1>🔧 Verificação e Correção do Banco - Produção</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Host:</strong> " . $config['host'] . "</p>";
echo "<p><strong>Banco:</strong> " . $config['dbname'] . "</p>";

try {
    // Conectar ao banco de produção
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexão com banco de produção estabelecida</p>";
    
    echo "<h2>1. Verificando Tabelas Existentes</h2>";
    
    $sql = "SHOW TABLES";
    $stmt = $pdo->query($sql);
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    
    if (!empty($tabelas)) {
        echo "<ul>";
        foreach ($tabelas as $tabela) {
            echo "<li>✅ $tabela</li>";
        }
        echo "</ul>";
    }
    
    // Verificar tabelas críticas
    $tabelasCriticas = ['usuarios', 'clientes', 'pets', 'agendamentos', 'logs_login'];
    $tabelasFaltando = [];
    
    foreach ($tabelasCriticas as $tabela) {
        if (!in_array($tabela, $tabelas)) {
            $tabelasFaltando[] = $tabela;
            echo "<p style='color: red;'>❌ Tabela '$tabela' não encontrada</p>";
        } else {
            echo "<p style='color: green;'>✅ Tabela '$tabela' existe</p>";
        }
    }
    
    if (!empty($tabelasFaltando)) {
        echo "<h2>2. Criando Tabelas Faltantes</h2>";
        
        // Criar tabela de usuários
        if (in_array('usuarios', $tabelasFaltando)) {
            echo "<h3>Criando tabela 'usuarios'...</h3>";
            
            $sql = "CREATE TABLE usuarios (
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
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Tabela 'usuarios' criada com sucesso</p>";
        }
        
        // Criar tabela de logs de login
        if (in_array('logs_login', $tabelasFaltando)) {
            echo "<h3>Criando tabela 'logs_login'...</h3>";
            
            $sql = "CREATE TABLE logs_login (
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
                INDEX idx_sucesso (sucesso)
            )";
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Tabela 'logs_login' criada com sucesso</p>";
        }
        
        // Criar outras tabelas se necessário
        $outrasTabelas = [
            'niveis_acesso' => "CREATE TABLE niveis_acesso (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(50) NOT NULL UNIQUE,
                descricao TEXT,
                permissoes JSON,
                ativo BOOLEAN DEFAULT TRUE,
                criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            'logs_atividade' => "CREATE TABLE logs_atividade (
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
                INDEX idx_data_hora (data_hora)
            )"
        ];
        
        foreach ($outrasTabelas as $tabela => $sql) {
            if (!in_array($tabela, $tabelas)) {
                echo "<h3>Criando tabela '$tabela'...</h3>";
                $pdo->exec($sql);
                echo "<p style='color: green;'>✅ Tabela '$tabela' criada com sucesso</p>";
            }
        }
    }
    
    echo "<h2>3. Verificando Usuário Administrador</h2>";
    
    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'";
    $stmt = $pdo->query($sql);
    $result = $stmt->fetch();
    
    if ($result['total'] == 0) {
        echo "<p style='color: orange;'>⚠️ Nenhum usuário administrador encontrado</p>";
        
        // Criar usuário administrador
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
        echo "<p style='color: green;'>✅ Usuário administrador já existe</p>";
        
        // Mostrar usuários admin
        $sql = "SELECT id, nome, email, nivel_acesso, ativo FROM usuarios WHERE nivel_acesso = 'admin'";
        $stmt = $pdo->query($sql);
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            echo "<p>👤 <strong>{$admin['nome']}</strong> ({$admin['email']}) - {$admin['nivel_acesso']}</p>";
        }
    }
    
    echo "<h2>4. Verificando Tabelas de Agendamentos Recorrentes</h2>";
    
    $tabelasRecorrentes = ['agendamentos_recorrentes', 'logs_agendamentos_recorrentes'];
    
    foreach ($tabelasRecorrentes as $tabela) {
        if (!in_array($tabela, $tabelas)) {
            echo "<p style='color: orange;'>⚠️ Tabela '$tabela' não encontrada - criando...</p>";
            
            if ($tabela === 'agendamentos_recorrentes') {
                $sql = "CREATE TABLE agendamentos_recorrentes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    cliente_id INT NOT NULL,
                    pet_id INT NOT NULL,
                    tipo_recorrencia ENUM('semanal', 'quinzenal', 'mensal') NOT NULL,
                    dia_semana INT NOT NULL COMMENT '0=Domingo, 1=Segunda, ..., 6=Sábado',
                    semana_mes INT NULL COMMENT '1=1ª semana, 2=2ª semana, etc. (apenas para mensal)',
                    hora_inicio TIME NOT NULL,
                    duracao_minutos INT DEFAULT 60,
                    data_inicio DATE NOT NULL,
                    data_fim DATE NULL,
                    servico VARCHAR(100) NOT NULL,
                    observacoes TEXT,
                    ativo BOOLEAN DEFAULT TRUE,
                    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_ativo (ativo),
                    INDEX idx_data_inicio (data_inicio),
                    INDEX idx_tipo_recorrencia (tipo_recorrencia)
                )";
            } else {
                $sql = "CREATE TABLE logs_agendamentos_recorrentes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    recorrencia_id INT NOT NULL,
                    acao VARCHAR(50) NOT NULL,
                    dados JSON,
                    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_recorrencia_id (recorrencia_id),
                    INDEX idx_data_hora (data_hora)
                )";
            }
            
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Tabela '$tabela' criada com sucesso</p>";
        } else {
            echo "<p style='color: green;'>✅ Tabela '$tabela' já existe</p>";
        }
    }
    
    echo "<h2>5. Verificando Coluna de Recorrência em Agendamentos</h2>";
    
    // Verificar se a coluna recorrencia_id existe na tabela agendamentos
    $sql = "SHOW COLUMNS FROM agendamentos LIKE 'recorrencia_id'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ Coluna 'recorrencia_id' não encontrada - adicionando...</p>";
        
        $sql = "ALTER TABLE agendamentos ADD COLUMN recorrencia_id INT NULL AFTER observacoes";
        $pdo->exec($sql);
        
        echo "<p style='color: green;'>✅ Coluna 'recorrencia_id' adicionada com sucesso</p>";
    } else {
        echo "<p style='color: green;'>✅ Coluna 'recorrencia_id' já existe</p>";
    }
    
    echo "<h2>🎉 Verificação Concluída!</h2>";
    
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
    echo "<h3 style='color: #059669; text-align: center;'>✅ SUCESSO!</h3>";
    echo "<p style='color: #059669; text-align: center;'>Banco de dados verificado e corrigido com sucesso.</p>";
    echo "<p style='color: #059669; text-align: center;'>O sistema de login agora deve funcionar corretamente.</p>";
    echo "</div>";
    
    echo "<h3>🔑 Credenciais de Acesso</h3>";
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
    echo "<p><strong>URL de Login:</strong> <a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>https://meuapp.bichosdobairro.com.br/login-simples.php</a></p>";
    echo "<p><strong>Email:</strong> admin@bichosdobairro.com</p>";
    echo "<p><strong>Senha:</strong> admin123</p>";
    echo "</div>";
    
    echo "<h3>📊 Resumo das Tabelas</h3>";
    
    // Listar todas as tabelas novamente
    $sql = "SHOW TABLES";
    $stmt = $pdo->query($sql);
    $tabelasFinais = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Total de tabelas:</strong> " . count($tabelasFinais) . "</p>";
    echo "<ul>";
    foreach ($tabelasFinais as $tabela) {
        echo "<li>📋 $tabela</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>Página de Login</a></p>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/' target='_blank'>Página Principal</a></p>";

echo "<p><strong>Verificação executada em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 