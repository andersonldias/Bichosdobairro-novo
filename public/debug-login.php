<?php
// Script de debug para verificar o processo de login
// Execute este script no navegador para ver o que está acontecendo

echo "<h1>🔍 Debug do Sistema de Login</h1>";
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
    
    echo "<h2>2. Verificando Usuário no Banco</h2>";
    
    $email = 'admin@bichosdobairro.com';
    $senha = 'admin123';
    
    // Buscar usuário como a classe Auth faz
    $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
    $stmt->execute([$email]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        echo "<p style='color: green;'>✅ Usuário encontrado!</p>";
        echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<h3>Dados do Usuário:</h3>";
        echo "<p><strong>ID:</strong> " . $usuario['id'] . "</p>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($usuario['nome']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($usuario['email']) . "</p>";
        echo "<p><strong>Nível:</strong> " . $usuario['nivel_acesso'] . "</p>";
        echo "<p><strong>Ativo:</strong> " . ($usuario['ativo'] ? 'Sim' : 'Não') . "</p>";
        echo "<p><strong>Tentativas:</strong> " . $usuario['tentativas_login'] . "</p>";
        echo "<p><strong>Bloqueado até:</strong> " . ($usuario['bloqueado_ate'] ?? 'Não') . "</p>";
        echo "<p><strong>Hash da senha:</strong> " . $usuario['senha_hash'] . "</p>";
        echo "</div>";
        
        echo "<h2>3. Verificando Bloqueio</h2>";
        
        $maxTentativas = 5;
        $estaBloqueado = false;
        
        if ($usuario['tentativas_login'] >= $maxTentativas && $usuario['bloqueado_ate']) {
            $bloqueadoAte = strtotime($usuario['bloqueado_ate']);
            if (time() < $bloqueadoAte) {
                $estaBloqueado = true;
                echo "<p style='color: red;'>❌ Usuário está bloqueado até: " . date('d/m/Y H:i:s', $bloqueadoAte) . "</p>";
            } else {
                echo "<p style='color: orange;'>⚠️ Bloqueio expirou, deve ser resetado</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Usuário não está bloqueado</p>";
        }
        
        if (!$estaBloqueado) {
            echo "<h2>4. Testando Verificação de Senha</h2>";
            
            // Testar password_verify
            $senhaCorreta = password_verify($senha, $usuario['senha_hash']);
            
            echo "<p><strong>Senha testada:</strong> $senha</p>";
            echo "<p><strong>Hash no banco:</strong> " . $usuario['senha_hash'] . "</p>";
            echo "<p><strong>password_verify retorna:</strong> " . ($senhaCorreta ? 'TRUE' : 'FALSE') . "</p>";
            
            if ($senhaCorreta) {
                echo "<p style='color: green;'>✅ Senha está correta!</p>";
                
                echo "<h2>5. Simulando Login Completo</h2>";
                
                // Simular o processo completo de login
                echo "<p>1. Resetando tentativas...</p>";
                $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = ?');
                $stmt->execute([$usuario['id']]);
                
                echo "<p>2. Atualizando último login...</p>";
                $stmt = $pdo->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?');
                $stmt->execute([$usuario['id']]);
                
                echo "<p>3. Logando tentativa bem-sucedida...</p>";
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                $stmt = $pdo->prepare('INSERT INTO logs_login (usuario_id, email, ip_address, user_agent, sucesso) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$usuario['id'], $email, $ip, $userAgent, 1]);
                
                echo "<p style='color: green;'>✅ Login simulado com sucesso!</p>";
                
                echo "<h2>6. Verificando Sessão</h2>";
                
                // Iniciar sessão
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
                $_SESSION['login_time'] = time();
                
                echo "<p style='color: green;'>✅ Sessão iniciada!</p>";
                echo "<p><strong>Sessão ID:</strong> " . session_id() . "</p>";
                echo "<p><strong>Usuário ID na sessão:</strong> " . ($_SESSION['usuario_id'] ?? 'N/A') . "</p>";
                
                echo "<div style='background: #d1fae5; padding: 20px; border-radius: 8px; border: 2px solid #10b981;'>";
                echo "<h3 style='color: #059669; text-align: center;'>✅ LOGIN FUNCIONANDO!</h3>";
                echo "<p style='color: #059669; text-align: center;'>O sistema de login está funcionando corretamente.</p>";
                echo "</div>";
                
                echo "<h3>🔗 Próximos Passos</h3>";
                echo "<p><a href='dashboard.php' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir para o Dashboard</a></p>";
                echo "<p><a href='logout.php' style='background: #ef4444; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Fazer Logout</a></p>";
                
            } else {
                echo "<p style='color: red;'>❌ Senha está incorreta!</p>";
                
                echo "<h3>Corrigindo a senha...</h3>";
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
                $stmt->execute([$novoHash, $usuario['id']]);
                
                echo "<p style='color: green;'>✅ Senha corrigida! Novo hash: " . $novoHash . "</p>";
                echo "<p><strong>Teste novamente o login com:</strong></p>";
                echo "<p>Email: $email</p>";
                echo "<p>Senha: $senha</p>";
            }
        } else {
            echo "<h3>Desbloqueando usuário...</h3>";
            $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = ?');
            $stmt->execute([$usuario['id']]);
            echo "<p style='color: green;'>✅ Usuário desbloqueado!</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Usuário não encontrado!</p>";
        
        // Criar usuário se não existir
        echo "<h3>Criando usuário administrador...</h3>";
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Administrador', $email, $senhaHash, 'admin']);
        
        echo "<p style='color: green;'>✅ Usuário criado com sucesso!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERRO: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='login-simples.php'>Página de Login</a> | <a href='dashboard.php'>Dashboard</a></p>";

echo "<p><strong>Debug executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
?> 