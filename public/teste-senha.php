<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Validação de Senha</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

try {
    require_once '../src/init.php';
    require_once '../src/Auth.php';
    
    $pdo = getDb();
    $auth = new Auth();
    
    // Buscar usuário admin
    $usuario = $auth->buscarUsuario('admin@bichosdobairro.com');
    
    if (!$usuario) {
        echo "<p class='error'>❌ Usuário admin@bichosdobairro.com não encontrado</p>";
        exit;
    }
    
    echo "<h2>Informações do Usuário</h2>";
    echo "<pre>";
    foreach ($usuario as $key => $value) {
        if ($key === 'senha_hash') {
            echo "$key: $value\n";
        } else {
            echo "$key: $value\n";
        }
    }
    echo "</pre>";
    
    // Testar diferentes senhas
    $senhas = [
        'admin123',
        'admin',
        '123',
        'password',
        'senha',
        ''
    ];
    
    echo "<h2>Teste de Validação de Senhas</h2>";
    
    foreach ($senhas as $senha) {
        $resultado = password_verify($senha, $usuario['senha_hash']);
        $status = $resultado ? '✅ VÁLIDA' : '❌ INVÁLIDA';
        echo "<p><strong>Senha:</strong> '$senha' - $status</p>";
    }
    
    // Testar login completo
    echo "<h2>Teste de Login Completo</h2>";
    
    $resultado = $auth->login('admin@bichosdobairro.com', 'admin123');
    
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
    
    // Gerar novo hash para admin123
    echo "<h2>Gerar Novo Hash para 'admin123'</h2>";
    $novoHash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "<p><strong>Novo hash:</strong> $novoHash</p>";
    
    // Testar se o novo hash funciona
    $testeNovoHash = password_verify('admin123', $novoHash);
    echo "<p><strong>Teste do novo hash:</strong> " . ($testeNovoHash ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "</p>";
    
    // Comparar com hash atual
    echo "<h2>Comparação de Hash</h2>";
    echo "<p><strong>Hash atual no banco:</strong> " . $usuario['senha_hash'] . "</p>";
    echo "<p><strong>Hash padrão esperado:</strong> \$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</p>";
    
    $hashPadrao = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
    $testeHashPadrao = password_verify('admin123', $hashPadrao);
    echo "<p><strong>Teste do hash padrão:</strong> " . ($testeHashPadrao ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "</p>";
    
    // Formulário para atualizar senha
    echo "<h2>Atualizar Senha do Administrador</h2>";
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_senha'])) {
        $novaSenha = $_POST['nova_senha'] ?? '';
        
        if (empty($novaSenha)) {
            echo "<p class='error'>❌ Senha não pode estar vazia</p>";
        } else {
            $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE email = ?');
            if ($stmt->execute([$novoHash, 'admin@bichosdobairro.com'])) {
                echo "<p class='success'>✅ Senha atualizada com sucesso!</p>";
                echo "<p class='info'>📋 Nova senha: $novaSenha</p>";
                
                // Testar login com nova senha
                $resultado = $auth->login('admin@bichosdobairro.com', $novaSenha);
                if ($resultado['sucesso']) {
                    echo "<p class='success'>✅ Login com nova senha funcionando!</p>";
                } else {
                    echo "<p class='error'>❌ Erro no login com nova senha: " . $resultado['erro'] . "</p>";
                }
            } else {
                echo "<p class='error'>❌ Erro ao atualizar senha</p>";
            }
        }
    }
    
    ?>
    <form method="post" style="margin:20px 0; padding:20px; border:1px solid #ccc; border-radius:5px;">
        <h3>Atualizar Senha</h3>
        <p><label>Nova Senha: <input type="password" name="nova_senha" required></label></p>
        <p><input type="submit" name="atualizar_senha" value="Atualizar Senha" style="background:#28a745; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;"></p>
    </form>
    
    <p><a href="login.php" style="background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Voltar ao Login</a></p>
    <?php
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
}
?> 