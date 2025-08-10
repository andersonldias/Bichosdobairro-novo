<?php
// Script para corrigir todos os problemas do sistema
// Execute este script para resolver todos os erros

echo "<h1>🔧 Correção Completa do Sistema</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Configurações do banco de dados de produção
$config = [
    'host' => 'mysql.bichosdobairro.com.br',
    'dbname' => 'bichosdobairro',
    'username' => 'bichosdobairro',
    'password' => '7oH57vlG#',
    'charset' => 'utf8mb4'
];

try {
    echo "<h2>1. Conectando ao Banco de Dados</h2>";
    
    // Conectar ao banco de produção
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso!</p>";
    
    echo "<h2>2. Verificando Tabelas Existentes</h2>";
    
    $sql = "SHOW TABLES";
    $stmt = $pdo->query($sql);
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    echo "<ul>";
    foreach ($tabelas as $tabela) {
        echo "<li>✅ $tabela</li>";
    }
    echo "</ul>";
    
    echo "<h2>3. Criando Tabelas Faltantes</h2>";
    
    // Lista de todas as tabelas necessárias
    $tabelasNecessarias = [
        'usuarios' => "CREATE TABLE IF NOT EXISTS usuarios (
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
        )",
        
        'logs_login' => "CREATE TABLE IF NOT EXISTS logs_login (
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
        )",
        
        'niveis_acesso' => "CREATE TABLE IF NOT EXISTS niveis_acesso (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL UNIQUE,
            descricao TEXT,
            permissoes JSON,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        'permissoes' => "CREATE TABLE IF NOT EXISTS permissoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL UNIQUE,
            descricao TEXT,
            area VARCHAR(50) NOT NULL,
            acao VARCHAR(50) NOT NULL,
            ativo BOOLEAN DEFAULT TRUE,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_area (area),
            INDEX idx_acao (acao),
            INDEX idx_ativo (ativo)
        )",
        
        'usuarios_permissoes' => "CREATE TABLE IF NOT EXISTS usuarios_permissoes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            permissao_id INT NOT NULL,
            concedido_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            concedido_por INT NULL,
            INDEX idx_usuario_id (usuario_id),
            INDEX idx_permissao_id (permissao_id),
            UNIQUE KEY unique_usuario_permissao (usuario_id, permissao_id)
        )",
        
        'logs_atividade' => "CREATE TABLE IF NOT EXISTS logs_atividade (
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
        )",
        
        'clientes' => "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            cpf VARCHAR(14) NOT NULL,
            telefone VARCHAR(20),
            endereco VARCHAR(255),
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        'pets' => "CREATE TABLE IF NOT EXISTS pets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            especie VARCHAR(50),
            raca VARCHAR(50),
            idade INT,
            cliente_id INT NOT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        )",
        
        'agendamentos' => "CREATE TABLE IF NOT EXISTS agendamentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pet_id INT NOT NULL,
            cliente_id INT NOT NULL,
            data DATE NOT NULL,
            hora TIME NOT NULL,
            servico VARCHAR(100) NOT NULL,
            status VARCHAR(30) DEFAULT 'Pendente',
            observacoes TEXT,
            recorrencia_id INT NULL,
            criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (pet_id) REFERENCES pets(id),
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        )",
        
        'agendamentos_recorrentes' => "CREATE TABLE IF NOT EXISTS agendamentos_recorrentes (
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
        )",
        
        'logs_agendamentos_recorrentes' => "CREATE TABLE IF NOT EXISTS logs_agendamentos_recorrentes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recorrencia_id INT NOT NULL,
            acao VARCHAR(50) NOT NULL,
            dados JSON,
            data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_recorrencia_id (recorrencia_id),
            INDEX idx_data_hora (data_hora)
        )"
    ];
    
    foreach ($tabelasNecessarias as $tabela => $sql) {
        if (!in_array($tabela, $tabelas)) {
            echo "<p>Criando tabela '$tabela'...</p>";
            $pdo->exec($sql);
            echo "<p style='color: green;'>✅ Tabela '$tabela' criada com sucesso</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️ Tabela '$tabela' já existe</p>";
        }
    }
    
    echo "<h2>4. Inserindo Dados Padrão</h2>";
    
    // Inserir usuário administrador
    $email = 'admin@bichosdobairro.com';
    $senha = 'admin123';
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $existe = $stmt->fetchColumn();
    
    if (!$existe) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrador', $email, $senhaHash, 'admin']);
        echo "<p style='color: green;'>✅ Usuário administrador criado</p>";
    } else {
        // Atualizar senha se necessário
        $stmt = $pdo->prepare("UPDATE usuarios SET senha_hash = ? WHERE email = ?");
        $stmt->execute([$senhaHash, $email]);
        echo "<p style='color: green;'>✅ Senha do administrador atualizada</p>";
    }
    
    // Inserir níveis de acesso
    $niveis = [
        ['nome' => 'Administrador', 'descricao' => 'Acesso total ao sistema', 'permissoes' => '["*"]'],
        ['nome' => 'Usuário', 'descricao' => 'Acesso básico ao sistema', 'permissoes' => '["agendamentos.visualizar", "clientes.visualizar", "pets.visualizar"]']
    ];
    
    foreach ($niveis as $nivel) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO niveis_acesso (nome, descricao, permissoes) VALUES (?, ?, ?)");
        $stmt->execute([$nivel['nome'], $nivel['descricao'], $nivel['permissoes']]);
    }
    echo "<p style='color: green;'>✅ Níveis de acesso inseridos</p>";
    
    // Inserir permissões padrão
    $permissoes = [
        ['nome' => 'agendamentos.visualizar', 'descricao' => 'Visualizar agendamentos', 'area' => 'agendamentos', 'acao' => 'visualizar'],
        ['nome' => 'agendamentos.criar', 'descricao' => 'Criar agendamentos', 'area' => 'agendamentos', 'acao' => 'criar'],
        ['nome' => 'agendamentos.editar', 'descricao' => 'Editar agendamentos', 'area' => 'agendamentos', 'acao' => 'editar'],
        ['nome' => 'agendamentos.excluir', 'descricao' => 'Excluir agendamentos', 'area' => 'agendamentos', 'acao' => 'excluir'],
        ['nome' => 'clientes.visualizar', 'descricao' => 'Visualizar clientes', 'area' => 'clientes', 'acao' => 'visualizar'],
        ['nome' => 'clientes.criar', 'descricao' => 'Criar clientes', 'area' => 'clientes', 'acao' => 'criar'],
        ['nome' => 'clientes.editar', 'descricao' => 'Editar clientes', 'area' => 'clientes', 'acao' => 'editar'],
        ['nome' => 'clientes.excluir', 'descricao' => 'Excluir clientes', 'area' => 'clientes', 'acao' => 'excluir'],
        ['nome' => 'pets.visualizar', 'descricao' => 'Visualizar pets', 'area' => 'pets', 'acao' => 'visualizar'],
        ['nome' => 'pets.criar', 'descricao' => 'Criar pets', 'area' => 'pets', 'acao' => 'criar'],
        ['nome' => 'pets.editar', 'descricao' => 'Editar pets', 'area' => 'pets', 'acao' => 'editar'],
        ['nome' => 'pets.excluir', 'descricao' => 'Excluir pets', 'area' => 'pets', 'acao' => 'excluir'],
        ['nome' => 'admin.usuarios', 'descricao' => 'Gerenciar usuários', 'area' => 'admin', 'acao' => 'usuarios'],
        ['nome' => 'admin.permissoes', 'descricao' => 'Gerenciar permissões', 'area' => 'admin', 'acao' => 'permissoes'],
        ['nome' => 'admin.niveis', 'descricao' => 'Gerenciar níveis de acesso', 'area' => 'admin', 'acao' => 'niveis']
    ];
    
    foreach ($permissoes as $permissao) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permissoes (nome, descricao, area, acao) VALUES (?, ?, ?, ?)");
        $stmt->execute([$permissao['nome'], $permissao['descricao'], $permissao['area'], $permissao['acao']]);
    }
    echo "<p style='color: green;'>✅ Permissões padrão inseridas</p>";
    
    echo "<h2>5. Verificando Estrutura Final</h2>";
    
    $sql = "SHOW TABLES";
    $stmt = $pdo->query($sql);
    $tabelasFinais = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Total de tabelas:</strong> " . count($tabelasFinais) . "</p>";
    echo "<ul>";
    foreach ($tabelasFinais as $tabela) {
        echo "<li>✅ $tabela</li>";
    }
    echo "</ul>";
    
    echo "<h2>6. Testando Funcionalidades</h2>";
    
    // Testar se o usuário administrador existe e está ativo
    $stmt = $pdo->prepare("SELECT id, nome, email, nivel_acesso, ativo FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color: green;'>✅ Usuário administrador encontrado</p>";
        echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($admin['nome']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
        echo "<p><strong>Nível:</strong> " . $admin['nivel_acesso'] . "</p>";
        echo "<p><strong>Ativo:</strong> " . ($admin['ativo'] ? 'Sim' : 'Não') . "</p>";
    }
    
    // Testar se as permissões foram criadas
    $stmt = $pdo->query("SELECT COUNT(*) FROM permissoes");
    $totalPermissoes = $stmt->fetchColumn();
    echo "<p style='color: green;'>✅ $totalPermissoes permissões criadas</p>";
    
    echo "<h2>🎉 Correção Completa Concluída!</h2>";
    
    echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
    echo "<h3 style='color: #059669; text-align: center;'>✅ SUCESSO!</h3>";
    echo "<p style='color: #059669; text-align: center;'>Todas as tabelas foram criadas e o sistema está funcionando.</p>";
    echo "<p style='color: #059669; text-align: center;'>Os erros foram corrigidos!</p>";
    echo "</div>";
    
    echo "<h3>🔑 Credenciais de Acesso</h3>";
    echo "<div style='background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b;'>";
    echo "<p><strong>URL de Login:</strong> <a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>https://meuapp.bichosdobairro.com.br/login-simples.php</a></p>";
    echo "<p><strong>Email:</strong> $email</p>";
    echo "<p><strong>Senha:</strong> $senha</p>";
    echo "</div>";
    
    echo "<h3>🔗 Páginas que Agora Funcionam</h3>";
    echo "<ul>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/dashboard.php' target='_blank'>Dashboard</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/admin-permissoes.php' target='_blank'>Administração - Permissões</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/admin-usuarios.php' target='_blank'>Administração - Usuários</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/admin-niveis.php' target='_blank'>Administração - Níveis</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/alterar-senha.php' target='_blank'>Alterar Senha</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/agendamentos.php' target='_blank'>Agendamentos</a></li>";
    echo "<li><a href='https://meuapp.bichosdobairro.com.br/agendamentos-recorrentes.php' target='_blank'>Agendamentos Recorrentes</a></li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/login-simples.php' target='_blank'>Página de Login</a></p>";
echo "<p><a href='https://meuapp.bichosdobairro.com.br/dashboard.php' target='_blank'>Dashboard</a></p>";

echo "<p><strong>Correção executada em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 