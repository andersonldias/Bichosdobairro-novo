<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicializar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/init.php';
require_once '../src/Auth.php';

$auth = new Auth();
$mensagem = '';
$tipo_mensagem = '';

// Processar recuperação de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $mensagem = 'Por favor, informe seu e-mail.';
        $tipo_mensagem = 'erro';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = 'E-mail inválido.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $usuario = $auth->buscarUsuario($email);
            if ($usuario) {
                // Gerar senha temporária
                $senha_temp = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
                $senha_hash = password_hash($senha_temp, PASSWORD_DEFAULT);
                
                $stmt = $auth->getPdo()->prepare('UPDATE usuarios SET senha_hash = ? WHERE email = ?');
                $resultado = $stmt->execute([$senha_hash, $email]);
                
                if ($resultado) {
                    $mensagem = "Senha recuperada com sucesso!<br><br><strong>Nova senha:</strong> <code style='background: #f3f4f6; padding: 4px 8px; border-radius: 4px; font-family: monospace;'>$senha_temp</code><br><br><small>⚠️ Por favor, altere esta senha após fazer login.</small>";
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao recuperar senha. Tente novamente.';
                    $tipo_mensagem = 'erro';
                }
            } else {
                $mensagem = 'E-mail não encontrado no sistema.';
                $tipo_mensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro ao processar recuperação: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-key text-blue-600 text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Recuperar Senha</h1>
                <p class="text-gray-600 mt-2">Digite seu e-mail para receber uma nova senha</p>
            </div>

            <!-- Mensagens -->
            <?php if (!empty($mensagem)): ?>
                <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                    <?= $mensagem ?>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="post" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>
                        E-mail
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        placeholder="seu@email.com"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200"
                >
                    <i class="fas fa-paper-plane mr-2"></i>
                    Recuperar Senha
                </button>
            </form>

            <!-- Links -->
            <div class="mt-8 text-center space-y-3">
                <a href="login-simples.php" class="block text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Voltar para o Login
                </a>
                
                <a href="credenciais-teste.php" class="block text-gray-600 hover:text-gray-800 text-sm">
                    <i class="fas fa-info-circle mr-1"></i>
                    Credenciais de Teste
                </a>
            </div>

            <!-- Informações -->
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <h3 class="text-sm font-medium text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Como funciona?
                </h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Digite o e-mail cadastrado no sistema</li>
                    <li>• Uma nova senha será gerada automaticamente</li>
                    <li>• Faça login com a nova senha</li>
                    <li>• Altere a senha após o primeiro acesso</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6">
            <p class="text-white text-sm opacity-80">
                Sistema de Agendamento - Bichos do Bairro
            </p>
        </div>
    </div>
</body>
</html> 