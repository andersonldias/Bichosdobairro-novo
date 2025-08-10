<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correção da Senha do Administrador</h1>";
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
        echo "<p><a href='criar-admin.php' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Criar Administrador</a></p>";
        exit;
    }
    
    echo "<h2>Usuário Encontrado</h2>";
    echo "<pre>";
    echo "ID: {$usuario['id']}\n";
    echo "Nome: {$usuario['nome']}\n";
    echo "Email: {$usuario['email']}\n";
    echo "Nível: {$usuario['nivel_acesso']}\n";
    echo "Ativo: {$usuario['ativo']}\n";
    echo "</pre>";
    
    // Processar correção da senha
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novaSenha = $_POST['nova_senha'] ?? 'admin123';
        
        if (empty($novaSenha)) {
            echo "<p class='error'>❌ Senha não pode estar vazia</p>";
        } else {
            // Gerar novo hash
            $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
            
            // Atualizar senha no banco
            $stmt = $pdo->prepare('UPDATE usuarios SET senha_hash = ?, tentativas_login = 0, bloqueado_ate = NULL WHERE email = ?');
            
            if ($stmt->execute([$novoHash, 'admin@bichosdobairro.com'])) {
                echo "<div style='background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; border-radius:5px; margin:20px 0;'>";
                echo "<h3>✅ Senha Corrigida com Sucesso!</h3>";
                echo "<p><strong>E-mail:</strong> admin@bichosdobairro.com</p>";
                echo "<p><strong>Nova Senha:</strong> $novaSenha</p>";
                echo "<p><strong>Hash Gerado:</strong> $novoHash</p>";
                echo "</div>";
                
                // Testar login com nova senha
                echo "<h2>Teste de Login</h2>";
                $resultado = $auth->login('admin@bichosdobairro.com', $novaSenha);
                
                if ($resultado['sucesso']) {
                    echo "<p class='success'>✅ Login funcionando perfeitamente!</p>";
                    echo "<p class='info'>📋 Agora você pode fazer login com as credenciais acima.</p>";
                } else {
                    echo "<p class='error'>❌ Erro no teste de login: " . $resultado['erro'] . "</p>";
                }
                
                // Verificar se a senha antiga ainda funciona
                echo "<h2>Verificação de Segurança</h2>";
                $testeAntiga = password_verify('admin123', $novoHash);
                echo "<p><strong>Senha antiga 'admin123':</strong> " . ($testeAntiga ? '❌ AINDA FUNCIONA' : '✅ NÃO FUNCIONA MAIS') . "</p>";
                
                if ($testeAntiga) {
                    echo "<p class='warning'>⚠️ A senha antiga ainda funciona. Isso pode indicar um problema na atualização.</p>";
                } else {
                    echo "<p class='success'>✅ Senha antiga não funciona mais. Atualização bem-sucedida!</p>";
                }
                
            } else {
                echo "<p class='error'>❌ Erro ao atualizar senha no banco de dados</p>";
            }
        }
    } else {
        // Mostrar formulário
        ?>
        <form method="post" style="max-width:500px; margin:20px 0;">
            <h2>Corrigir Senha do Administrador</h2>
            
            <div style="background:#fff3cd; border:1px solid #ffeaa7; color:#856404; padding:15px; border-radius:5px; margin:20px 0;">
                <h3>⚠️ Informações Importantes:</h3>
                <ul>
                    <li>Este script irá redefinir a senha do administrador</li>
                    <li>A senha atual será substituída pela nova</li>
                    <li>As tentativas de login serão resetadas</li>
                    <li>Qualquer bloqueio será removido</li>
                </ul>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Nova Senha:</label>
                <input type="password" name="nova_senha" value="admin123" 
                       style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" required>
                <small style="color:#666;">Deixe como 'admin123' para usar a senha padrão</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <input type="submit" value="Corrigir Senha" 
                       style="background:#dc3545; color:white; padding:12px 24px; border:none; border-radius:5px; cursor:pointer; font-size:16px;">
            </div>
        </form>
        
        <h3>Status Atual</h3>
        <p><strong>Hash atual:</strong> <?= $usuario['senha_hash'] ?></p>
        <p><strong>Tentativas de login:</strong> <?= $usuario['tentativas_login'] ?></p>
        <p><strong>Bloqueado até:</strong> <?= $usuario['bloqueado_ate'] ?: 'Não bloqueado' ?></p>
        
        <h3>Teste da Senha Atual</h3>
        <?php
        $testeAtual = password_verify('admin123', $usuario['senha_hash']);
        echo "<p><strong>Senha 'admin123' atual:</strong> " . ($testeAtual ? '✅ VÁLIDA' : '❌ INVÁLIDA') . "</p>";
        
        if (!$testeAtual) {
            echo "<p class='warning'>⚠️ A senha atual não está funcionando. É necessário corrigir.</p>";
        } else {
            echo "<p class='success'>✅ A senha atual está funcionando.</p>";
        }
        ?>
        
        <?php
    }
    
    echo "<div style='margin-top:30px;'>";
    echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para Login</a></p>";
    echo "<p><a href='teste-senha.php' style='background:#6f42c1; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Teste de Senha</a></p>";
    echo "<p><a href='diagnostico-login.php' style='background:#6c757d; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Diagnóstico</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p><a href='corrigir-login.php' style='background:#dc3545; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Correção Automática</a></p>";
}
?> 