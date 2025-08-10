<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Criar Usuário Administrador</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;} pre{background:#f5f5f5;padding:10px;border-radius:5px;}</style>";

// Verificar se já existe um admin
try {
    require_once '../src/init.php';
    require_once '../src/Auth.php';
    
    $auth = new Auth();
    $usuario = $auth->buscarUsuario('admin@bichosdobairro.com');
    
    if ($usuario) {
        echo "<p class='warning'>⚠️ Usuário administrador já existe!</p>";
        echo "<pre>";
        foreach ($usuario as $key => $value) {
            if ($key === 'senha_hash') {
                echo "$key: [PROTEGIDO]\n";
            } else {
                echo "$key: $value\n";
            }
        }
        echo "</pre>";
        echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para Login</a></p>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro ao verificar usuário: " . $e->getMessage() . "</p>";
    exit;
}

// Processar criação do admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmar_senha'] ?? '';
    
    $erros = [];
    
    // Validações
    if (empty($nome)) {
        $erros[] = "Nome é obrigatório";
    }
    
    if (empty($email)) {
        $erros[] = "E-mail é obrigatório";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido";
    }
    
    if (empty($senha)) {
        $erros[] = "Senha é obrigatória";
    } elseif (strlen($senha) < 6) {
        $erros[] = "Senha deve ter pelo menos 6 caracteres";
    }
    
    if ($senha !== $confirmarSenha) {
        $erros[] = "Senhas não conferem";
    }
    
    if (empty($erros)) {
        try {
            $resultado = $auth->criarUsuario($nome, $email, $senha, 'admin');
            
            if ($resultado['sucesso']) {
                echo "<div style='background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; border-radius:5px; margin:20px 0;'>";
                echo "<h3>✅ Usuário Administrador Criado com Sucesso!</h3>";
                echo "<p><strong>ID:</strong> {$resultado['id']}</p>";
                echo "<p><strong>Nome:</strong> $nome</p>";
                echo "<p><strong>E-mail:</strong> $email</p>";
                echo "<p><strong>Nível:</strong> Administrador</p>";
                echo "</div>";
                
                echo "<p class='info'>📋 Agora você pode fazer login com as credenciais criadas.</p>";
                echo "<p><a href='login.php' style='background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Ir para Login</a></p>";
                exit;
                
            } else {
                echo "<p class='error'>❌ Erro ao criar usuário: " . $resultado['erro'] . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro ao criar usuário: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<div style='background:#f8d7da; border:1px solid #f5c6cb; color:#721c24; padding:15px; border-radius:5px; margin:20px 0;'>";
        echo "<h3>❌ Erros de Validação:</h3>";
        echo "<ul>";
        foreach ($erros as $erro) {
            echo "<li>$erro</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
}

// Formulário de criação
?>
<form method="post" style="max-width:500px; margin:20px 0;">
    <h2>Criar Usuário Administrador</h2>
    
    <div style="margin-bottom:15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Nome Completo:</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? 'Administrador') ?>" 
               style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" required>
    </div>
    
    <div style="margin-bottom:15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">E-mail:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@bichosdobairro.com') ?>" 
               style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" required>
    </div>
    
    <div style="margin-bottom:15px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Senha:</label>
        <input type="password" name="senha" 
               style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" 
               placeholder="Mínimo 6 caracteres" required>
    </div>
    
    <div style="margin-bottom:20px;">
        <label style="display:block; margin-bottom:5px; font-weight:bold;">Confirmar Senha:</label>
        <input type="password" name="confirmar_senha" 
               style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" 
               placeholder="Digite a senha novamente" required>
    </div>
    
    <div style="margin-bottom:20px;">
        <input type="submit" value="Criar Administrador" 
               style="background:#28a745; color:white; padding:12px 24px; border:none; border-radius:5px; cursor:pointer; font-size:16px;">
    </div>
</form>

<div style="background:#fff3cd; border:1px solid #ffeaa7; color:#856404; padding:15px; border-radius:5px; margin:20px 0;">
    <h3>⚠️ Informações Importantes:</h3>
    <ul>
        <li>Este script cria um usuário administrador com acesso total ao sistema</li>
        <li>Use uma senha forte e segura</li>
        <li>Após criar o usuário, você poderá fazer login normalmente</li>
        <li>Recomenda-se deletar este arquivo após criar o usuário por segurança</li>
    </ul>
</div>

<p><a href="diagnostico-login.php" style="background:#6c757d; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Diagnóstico do Sistema</a></p>
<p><a href="login.php" style="background:#007cba; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;">Voltar ao Login</a></p> 