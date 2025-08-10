<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnóstico do Sistema de Login</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// 1. Verificar se os arquivos necessários existem
echo "<h2>1. Verificação de Arquivos</h2>";
$arquivos = [
    '../src/init.php',
    '../src/Auth.php',
    '../src/db.php',
    '../src/Config.php',
    '../.env'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p class='success'>✅ $arquivo - Existe</p>";
    } else {
        echo "<p class='error'>❌ $arquivo - Não encontrado</p>";
    }
}

// 2. Verificar configurações
echo "<h2>2. Verificação de Configurações</h2>";
try {
    require_once '../src/init.php';
    require_once '../src/Config.php';
    
    $config = Config::all();
    echo "<p class='info'>📋 Configurações carregadas:</p>";
    echo "<pre>";
    foreach ($config as $key => $value) {
        if (strpos($key, 'PASS') !== false || strpos($key, 'KEY') !== false) {
            echo "$key: [PROTEGIDO]\n";
        } else {
            echo "$key: $value\n";
        }
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao carregar configurações: " . $e->getMessage() . "</p>";
}

// 3. Verificar conexão com banco
echo "<h2>3. Verificação de Conexão com Banco</h2>";
try {
    $pdo = getDb();
    echo "<p class='success'>✅ Conexão com banco estabelecida</p>";
    
    // Verificar se a tabela usuarios existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Tabela 'usuarios' existe</p>";
        
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE usuarios");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p class='info'>📋 Estrutura da tabela usuarios:</p>";
        echo "<pre>";
        foreach ($colunas as $coluna) {
            echo "{$coluna['Field']} - {$coluna['Type']} - {$coluna['Null']} - {$coluna['Key']} - {$coluna['Default']}\n";
        }
        echo "</pre>";
        
        // Verificar se há usuários cadastrados
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p class='info'>📊 Total de usuários: $total</p>";
        
        if ($total > 0) {
            // Mostrar alguns usuários (sem senhas)
            $stmt = $pdo->query("SELECT id, nome, email, nivel_acesso, ativo, tentativas_login, ultimo_login FROM usuarios LIMIT 5");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<p class='info'>📋 Usuários cadastrados:</p>";
            echo "<pre>";
            foreach ($usuarios as $usuario) {
                echo "ID: {$usuario['id']} | Nome: {$usuario['nome']} | Email: {$usuario['email']} | Nível: {$usuario['nivel_acesso']} | Ativo: {$usuario['ativo']} | Tentativas: {$usuario['tentativas_login']}\n";
            }
            echo "</pre>";
        } else {
            echo "<p class='warning'>⚠️ Nenhum usuário cadastrado</p>";
        }
        
    } else {
        echo "<p class='error'>❌ Tabela 'usuarios' não existe</p>";
    }
    
    // Verificar se a tabela logs_login existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'logs_login'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Tabela 'logs_login' existe</p>";
    } else {
        echo "<p class='warning'>⚠️ Tabela 'logs_login' não existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na conexão com banco: " . $e->getMessage() . "</p>";
}

// 4. Testar classe Auth
echo "<h2>4. Teste da Classe Auth</h2>";
try {
    require_once '../src/Auth.php';
    $auth = new Auth();
    echo "<p class='success'>✅ Classe Auth carregada com sucesso</p>";
    
    // Testar busca de usuário
    $usuario = $auth->buscarUsuario('admin@bichosdobairro.com');
    if ($usuario) {
        echo "<p class='success'>✅ Usuário admin@bichosdobairro.com encontrado</p>";
        echo "<pre>";
        foreach ($usuario as $key => $value) {
            if ($key === 'senha_hash') {
                echo "$key: [PROTEGIDO]\n";
            } else {
                echo "$key: $value\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p class='warning'>⚠️ Usuário admin@bichosdobairro.com não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro na classe Auth: " . $e->getMessage() . "</p>";
}

// 5. Verificar sessão
echo "<h2>5. Verificação de Sessão</h2>";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<p class='info'>📋 Status da sessão: " . session_status() . "</p>";
echo "<p class='info'>📋 ID da sessão: " . session_id() . "</p>";

if (isset($_SESSION['usuario_id'])) {
    echo "<p class='success'>✅ Usuário logado: {$_SESSION['usuario_id']}</p>";
} else {
    echo "<p class='info'>📋 Nenhum usuário logado</p>";
}

// 6. Testar login
echo "<h2>6. Teste de Login</h2>";
if (isset($_POST['teste_login'])) {
    try {
        $auth = new Auth();
        $resultado = $auth->login($_POST['email'], $_POST['senha']);
        
        if ($resultado['sucesso']) {
            echo "<p class='success'>✅ Login bem-sucedido!</p>";
            echo "<pre>";
            foreach ($resultado['usuario'] as $key => $value) {
                if ($key === 'senha_hash') {
                    echo "$key: [PROTEGIDO]\n";
                } else {
                    echo "$key: $value\n";
                }
            }
            echo "</pre>";
        } else {
            echo "<p class='error'>❌ Login falhou: " . $resultado['erro'] . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erro no teste de login: " . $e->getMessage() . "</p>";
    }
}

// Formulário de teste
echo "<form method='post' style='margin:20px 0; padding:20px; border:1px solid #ccc; border-radius:5px;'>";
echo "<h3>Teste de Login</h3>";
echo "<p><label>Email: <input type='email' name='email' value='admin@bichosdobairro.com' required></label></p>";
echo "<p><label>Senha: <input type='password' name='senha' required></label></p>";
echo "<p><input type='submit' name='teste_login' value='Testar Login' style='background:#007cba; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;'></p>";
echo "</form>";

// 7. Verificar logs
echo "<h2>7. Verificação de Logs</h2>";
$logFiles = [
    '../logs/error.log',
    '../logs/app.log'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        echo "<p class='info'>📋 $logFile - Tamanho: " . number_format($size) . " bytes</p>";
        
        if ($size > 0) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES);
            $recentLines = array_slice($lines, -5);
            echo "<p class='info'>📋 Últimas 5 linhas:</p>";
            echo "<pre>";
            foreach ($recentLines as $line) {
                echo htmlspecialchars($line) . "\n";
            }
            echo "</pre>";
        }
    } else {
        echo "<p class='warning'>⚠️ $logFile - Não existe</p>";
    }
}

echo "<h2>8. Recomendações</h2>";
echo "<ul>";
echo "<li>Verifique se todas as tabelas necessárias foram criadas</li>";
echo "<li>Confirme se há pelo menos um usuário administrador cadastrado</li>";
echo "<li>Verifique se as configurações do banco estão corretas</li>";
echo "<li>Monitore os logs para identificar erros específicos</li>";
echo "</ul>";

echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Voltar ao Login</a></p>";
?> 