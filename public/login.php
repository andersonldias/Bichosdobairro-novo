<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurações de segurança da sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();
require_once '../src/init.php';
require_once '../src/Auth.php';

$auth = new Auth();
$erro = '';
$sucesso = '';

// Se já estiver logado, redireciona para dashboard
if ($auth->estaLogado()) {
    header('Location: dashboard.php');
    exit;
}

// Processa login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $resultado = $auth->login($email, $senha);
        
        if ($resultado['sucesso']) {
            // Login bem-sucedido
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = $resultado['erro'];
        }
    }
}

// Proteção contra ataques de força bruta
$tentativas = 0;
if (isset($_SESSION['tentativas_login'])) {
    $tentativas = $_SESSION['tentativas_login'];
    $ultimaTentativa = $_SESSION['ultima_tentativa'] ?? 0;
    
    // Resetar tentativas após 15 minutos
    if (time() - $ultimaTentativa > 900) {
        $tentativas = 0;
        unset($_SESSION['tentativas_login']);
        unset($_SESSION['ultima_tentativa']);
    }
}

// Se muitas tentativas, mostrar captcha ou delay
$mostrarCaptcha = $tentativas >= 3;
$tempoEspera = 0;

if ($tentativas >= 5) {
    $tempoEspera = 300; // 5 minutos
    $tempoRestante = $tempoEspera - (time() - $ultimaTentativa);
    if ($tempoRestante > 0) {
        $erro = "Muitas tentativas. Aguarde " . ceil($tempoRestante / 60) . " minutos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bichos do Bairro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Sistema de agendamento - Login">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-paw text-white text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Bichos do Bairro</h1>
            <p class="text-gray-600 mt-2">Sistema de Agendamento</p>
        </div>

        <!-- Mensagens -->
        <?php if (!empty($erro)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <?php if ($tempoEspera > 0 && $tempoRestante > 0): ?>
            <div class="text-center py-8">
                <i class="fas fa-clock text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Acesso Temporariamente Bloqueado</h3>
                <p class="text-gray-600">Por segurança, aguarde antes de tentar novamente.</p>
                <div class="mt-4 text-sm text-gray-500">
                    Tempo restante: <span id="countdown"><?= ceil($tempoRestante / 60) ?></span> minutos
                </div>
            </div>
        <?php else: ?>
            <form method="post" action="login.php" id="loginForm" class="space-y-6">
                <!-- Campo Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2"></i>E-mail
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                        placeholder="seu@email.com"
                        autocomplete="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        <?= $tempoEspera > 0 ? 'disabled' : '' ?>
                    >
                </div>

                <!-- Campo Senha -->
                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Senha
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            id="senha" 
                            name="senha" 
                            required 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors pr-12"
                            placeholder="Sua senha"
                            autocomplete="current-password"
                            <?= $tempoEspera > 0 ? 'disabled' : '' ?>
                        >
                        <button 
                            type="button" 
                            onclick="togglePassword()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Captcha simples (se necessário) -->
                <?php if ($mostrarCaptcha && $tempoEspera == 0): ?>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-shield-alt mr-2"></i>Verificação de Segurança
                        </label>
                        <div class="flex items-center space-x-4">
                            <div class="bg-white px-3 py-2 border rounded text-center font-mono text-lg select-none">
                                <?= rand(1000, 9999) ?>
                            </div>
                            <input 
                                type="text" 
                                name="captcha" 
                                required 
                                class="flex-1 px-3 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                placeholder="Digite o código"
                                maxlength="4"
                            >
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botão de Login -->
                <button 
                    type="submit" 
                    class="w-full bg-blue-500 text-white py-3 rounded-lg hover:bg-blue-600 font-semibold transition-colors flex items-center justify-center"
                    <?= $tempoEspera > 0 ? 'disabled' : '' ?>
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    <?= $tempoEspera > 0 ? 'Aguarde...' : 'Entrar' ?>
                </button>
            </form>

            <!-- Links úteis -->
            <div class="mt-6 text-center">
                <a href="alterar-senha.php" class="text-blue-500 hover:text-blue-700 text-sm">
                    <i class="fas fa-key mr-1"></i>Esqueci minha senha
                </a>
            </div>
        <?php endif; ?>

        <!-- Informações de segurança -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="text-xs text-gray-500 text-center">
                <p><i class="fas fa-shield-alt mr-1"></i>Sistema protegido com criptografia SSL</p>
                <p class="mt-1">Tentativas restantes: <?= max(0, 5 - $tentativas) ?></p>
            </div>
        </div>

        <!-- Links de diagnóstico (apenas em desenvolvimento) -->
        <?php if (Config::isDebug() || Config::isDevelopment()): ?>
        <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-500 text-center">
                <p class="mb-2"><strong>Ferramentas de Diagnóstico:</strong></p>
                <div class="flex justify-center space-x-2">
                    <a href="corrigir-login.php" class="text-red-500 hover:text-red-700 text-xs">
                        <i class="fas fa-wrench mr-1"></i>Corrigir
                    </a>
                    <a href="corrigir-senha-admin.php" class="text-pink-500 hover:text-pink-700 text-xs">
                        <i class="fas fa-lock mr-1"></i>Corrigir Senha
                    </a>
                    <a href="diagnostico-login.php" class="text-blue-500 hover:text-blue-700 text-xs">
                        <i class="fas fa-stethoscope mr-1"></i>Diagnóstico
                    </a>
                    <a href="teste-senha.php" class="text-purple-500 hover:text-purple-700 text-xs">
                        <i class="fas fa-key mr-1"></i>Teste Senha
                    </a>
                    <a href="criar-tabelas.php" class="text-green-500 hover:text-green-700 text-xs">
                        <i class="fas fa-database mr-1"></i>Criar Tabelas
                    </a>
                    <a href="criar-admin.php" class="text-orange-500 hover:text-orange-700 text-xs">
                        <i class="fas fa-user-plus mr-1"></i>Criar Admin
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    // Toggle de visibilidade da senha
    function togglePassword() {
        const senhaInput = document.getElementById('senha');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (senhaInput.type === 'password') {
            senhaInput.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            senhaInput.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }

    // Countdown para bloqueio
    <?php if ($tempoEspera > 0 && $tempoRestante > 0): ?>
    let tempoRestante = <?= $tempoRestante ?>;
    const countdownElement = document.getElementById('countdown');
    
    const countdown = setInterval(function() {
        tempoRestante--;
        const minutos = Math.ceil(tempoRestante / 60);
        countdownElement.textContent = minutos;
        
        if (tempoRestante <= 0) {
            clearInterval(countdown);
            location.reload();
        }
    }, 1000);
    <?php endif; ?>

    // Prevenção de múltiplos submits
    document.getElementById('loginForm')?.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn.disabled) {
            e.preventDefault();
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Entrando...';
    });

    // Foco automático no primeiro campo
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.disabled) {
            emailInput.focus();
        }
    });
    </script>
</body>
</html> 