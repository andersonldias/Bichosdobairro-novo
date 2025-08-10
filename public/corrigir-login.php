<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correção Automática do Sistema de Login</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

$correcoes = [];
$erros = [];

// 1. Verificar e carregar configurações
echo "<h2>1. Verificando Configurações</h2>";
try {
    require_once '../src/init.php';
    require_once '../src/Config.php';
    echo "<p class='success'>✅ Configurações carregadas</p>";
    $correcoes[] = "Configurações carregadas com sucesso";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao carregar configurações: " . $e->getMessage() . "</p>";
    $erros[] = "Erro ao carregar configurações: " . $e->getMessage();
}

// 2. Verificar conexão com banco
echo "<h2>2. Verificando Conexão com Banco</h2>";
try {
    $pdo = getDb();
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    $correcoes[] = "Conexão com banco estabelecida";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
    $erros[] = "Erro na conexão com banco: " . $e->getMessage();
    exit;
}

// 3. Verificar e criar tabelas se necessário
echo "<h2>3. Verificando Tabelas</h2>";
$tabelasNecessarias = ['usuarios', 'logs_login'];

foreach ($tabelasNecessarias as $tabela) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Tabela '$tabela' existe</p>";
        $correcoes[] = "Tabela '$tabela' encontrada";
    } else {
        echo "<p class='warning'>⚠️ Tabela '$tabela' não existe - criando...</p>";
        
        try {
            if ($tabela === 'usuarios') {
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
                echo "<p class='success'>✅ Tabela 'usuarios' criada</p>";
                $correcoes[] = "Tabela 'usuarios' criada";
                
            } elseif ($tabela === 'logs_login') {
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
                    INDEX idx_sucesso (sucesso),
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
                )";
                $pdo->exec($sql);
                echo "<p class='success'>✅ Tabela 'logs_login' criada</p>";
                $correcoes[] = "Tabela 'logs_login' criada";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao criar tabela '$tabela': " . $e->getMessage() . "</p>";
            $erros[] = "Erro ao criar tabela '$tabela': " . $e->getMessage();
        }
    }
}

// 4. Verificar se há usuário administrador
echo "<h2>4. Verificando Usuário Administrador</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE email = 'admin@bichosdobairro.com'");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total > 0) {
        echo "<p class='success'>✅ Usuário administrador existe</p>";
        $correcoes[] = "Usuário administrador encontrado";
    } else {
        echo "<p class='warning'>⚠️ Usuário administrador não existe - criando...</p>";
        
        // Criar usuário administrador padrão
        $senhaHash = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['Administrador', 'admin@bichosdobairro.com', $senhaHash, 'admin']);
        
        echo "<p class='success'>✅ Usuário administrador criado</p>";
        echo "<p class='info'>📋 Credenciais padrão:</p>";
        echo "<ul>";
        echo "<li><strong>E-mail:</strong> admin@bichosdobairro.com</li>";
        echo "<li><strong>Senha:</strong> admin123</li>";
        echo "</ul>";
        echo "<p class='warning'>⚠️ IMPORTANTE: Altere a senha após o primeiro login!</p>";
        $correcoes[] = "Usuário administrador criado";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar/criar usuário administrador: " . $e->getMessage() . "</p>";
    $erros[] = "Erro ao verificar/criar usuário administrador: " . $e->getMessage();
}

// 5. Verificar e corrigir permissões de arquivos
echo "<h2>5. Verificando Permissões de Arquivos</h2>";
$arquivos = [
    '../logs/error.log',
    '../logs/app.log'
];

foreach ($arquivos as $arquivo) {
    $diretorio = dirname($arquivo);
    
    if (!is_dir($diretorio)) {
        if (mkdir($diretorio, 0755, true)) {
            echo "<p class='success'>✅ Diretório criado: $diretorio</p>";
            $correcoes[] = "Diretório criado: $diretorio";
        } else {
            echo "<p class='error'>❌ Erro ao criar diretório: $diretorio</p>";
            $erros[] = "Erro ao criar diretório: $diretorio";
        }
    }
    
    if (!file_exists($arquivo)) {
        if (touch($arquivo)) {
            chmod($arquivo, 0644);
            echo "<p class='success'>✅ Arquivo criado: $arquivo</p>";
            $correcoes[] = "Arquivo criado: $arquivo";
        } else {
            echo "<p class='error'>❌ Erro ao criar arquivo: $arquivo</p>";
            $erros[] = "Erro ao criar arquivo: $arquivo";
        }
    } else {
        if (is_writable($arquivo)) {
            echo "<p class='success'>✅ Arquivo gravável: $arquivo</p>";
        } else {
            if (chmod($arquivo, 0644)) {
                echo "<p class='success'>✅ Permissões corrigidas: $arquivo</p>";
                $correcoes[] = "Permissões corrigidas: $arquivo";
            } else {
                echo "<p class='error'>❌ Erro ao corrigir permissões: $arquivo</p>";
                $erros[] = "Erro ao corrigir permissões: $arquivo";
            }
        }
    }
}

// 6. Testar classe Auth
echo "<h2>6. Testando Classe Auth</h2>";
try {
    require_once '../src/Auth.php';
    $auth = new Auth();
    echo "<p class='success'>✅ Classe Auth carregada</p>";
    
    // Testar busca de usuário
    $usuario = $auth->buscarUsuario('admin@bichosdobairro.com');
    if ($usuario) {
        echo "<p class='success'>✅ Busca de usuário funcionando</p>";
        $correcoes[] = "Classe Auth funcionando corretamente";
    } else {
        echo "<p class='warning'>⚠️ Busca de usuário não retornou resultados</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na classe Auth: " . $e->getMessage() . "</p>";
    $erros[] = "Erro na classe Auth: " . $e->getMessage();
}

// 7. Resumo das correções
echo "<h2>7. Resumo das Correções</h2>";
if (!empty($correcoes)) {
    echo "<div style='background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; border-radius:5px; margin:20px 0;'>";
    echo "<h3>✅ Correções Aplicadas:</h3>";
    echo "<ul>";
    foreach ($correcoes as $correcao) {
        echo "<li>$correcao</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($erros)) {
    echo "<div style='background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:15px; border-radius:5px; margin:20px 0;'>";
    echo "<h3>❌ Erros Encontrados:</h3>";
    echo "<ul>";
    foreach ($erros as $erro) {
        echo "<li>$erro</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// 8. Teste final de login
echo "<h2>8. Teste Final de Login</h2>";
if (empty($erros)) {
    echo "<p class='success'>✅ Sistema corrigido! Agora você pode fazer login.</p>";
    echo "<p class='info'>📋 Credenciais para teste:</p>";
    echo "<ul>";
    echo "<li><strong>E-mail:</strong> admin@bichosdobairro.com</li>";
    echo "<li><strong>Senha:</strong> admin123</li>";
    echo "</ul>";
} else {
    echo "<p class='warning'>⚠️ Alguns problemas persistem. Verifique os erros acima.</p>";
}

echo "<div style='margin-top:30px;'>";
echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para Login</a></p>";
echo "<p><a href='diagnostico-login.php' style='background:#6c757d; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Diagnóstico Detalhado</a></p>";
echo "</div>";
?> 