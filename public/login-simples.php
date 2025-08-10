<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se já está logado
if (isset($_SESSION['usuario_id'])) {
    echo "<h1>Já está logado!</h1>";
    echo "<p>Usuário: " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'N/A') . "</p>";
    echo "<a href='logout.php'>Sair</a>";
    exit;
}

$erro = '';
$sucesso = '';

// Verificar mensagem de logout
if (isset($_GET['msg']) && $_GET['msg'] === 'logout') {
    $sucesso = 'Logout realizado com sucesso!';
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        try {
            require_once '../src/init.php';
            require_once '../src/Auth.php';
            
            $auth = new Auth();
            $resultado = $auth->login($email, $senha);
            
            if ($resultado['sucesso']) {
                // Redirecionar imediatamente para o dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $erro = $resultado['erro'];
            }
        } catch (Exception $e) {
            $erro = 'Erro: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Simples - Bichos do Bairro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9fafb;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover {
            background: #5a6fd8;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #fcc;
        }
        .success {
            background: #efe;
            color: #363;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #cfc;
        }
        .info {
            background: #e3f2fd;
            color: #1976d2;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bbdefb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🐾 Bichos do Bairro</h1>
            <p>Sistema de Agendamento</p>
        </div>



        <?php if (!empty($erro)): ?>
            <div class="error">
                ❌ <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="success">
                ✅ <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="login-simples.php">
            <div class="form-group">
                <label for="email">📧 E-mail:</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    placeholder="seu@email.com"
                >
            </div>

            <div class="form-group">
                <label for="senha">🔒 Senha:</label>
                <input 
                    type="password" 
                    id="senha" 
                    name="senha" 
                    required 
                    placeholder="Sua senha"
                >
            </div>

            <button type="submit">
                🚀 Entrar no Sistema
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="recuperar-senha.php" style="color: #667eea; text-decoration: none; font-size: 0.9em;">
                🔑 Esqueci minha senha
            </a>
        </div>

        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p style="color: #666; font-size: 0.9em;">
                Sistema de Agendamento - Bichos do Bairro<br>
                Versão 1.0.0
            </p>
        </div>
    </div>
</body>
</html> 