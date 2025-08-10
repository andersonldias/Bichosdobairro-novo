<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/init.php';

$msg = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDb();
        
        // Criar tabela de usuários
        $sql_usuarios = file_get_contents('../sql/create_usuarios_table.sql');
        $pdo->exec($sql_usuarios);
        
        // Criar tabela de logs de atividade
        $sql_logs = file_get_contents('../sql/create_logs_atividade_table.sql');
        $pdo->exec($sql_logs);
        
        $msg = 'Tabelas criadas com sucesso! Sistema de login instalado.';
        
    } catch (Exception $e) {
        $erro = 'Erro ao criar tabelas: ' . $e->getMessage();
    }
}

// Verificar se as tabelas já existem
$tabelas_existem = false;
try {
    $pdo = getDb();
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    $tabelas_existem = $stmt->rowCount() > 0;
} catch (Exception $e) {
    // Ignorar erro
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalar Sistema de Login - Bichos do Bairro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen p-4">
    <div class="max-w-2xl mx-auto mt-8">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Instalar Sistema de Login</h1>
                <p class="text-gray-600 mt-2">Bichos do Bairro - Sistema de Agendamento</p>
            </div>

            <?php if ($tabelas_existem): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    Sistema de login já está instalado!
                </div>
                
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6">
                    <h3 class="font-semibold mb-2">Credenciais Padrão:</h3>
                    <p><strong>Email:</strong> admin@bichosdobairro.com</p>
                    <p><strong>Senha:</strong> admin123</p>
                    <p class="text-sm mt-2 text-red-600">
                        ⚠️ IMPORTANTE: Altere a senha padrão após o primeiro login!
                    </p>
                </div>
                
                <div class="text-center">
                    <a href="login.php" class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 font-semibold transition-colors inline-flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Ir para Login
                    </a>
                </div>
                
            <?php else: ?>
                <?php if (!empty($msg)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erro)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?= htmlspecialchars($erro) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-6">
                    <h3 class="font-semibold mb-2">O que será instalado:</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Tabela de usuários com sistema de autenticação</li>
                        <li>Tabela de logs de login para auditoria</li>
                        <li>Tabela de logs de atividade dos usuários</li>
                        <li>Usuário administrador padrão</li>
                    </ul>
                </div>

                <form method="post" action="instalar-login.php" class="text-center">
                    <button type="submit" class="bg-green-500 text-white px-8 py-3 rounded-lg hover:bg-green-600 font-semibold transition-colors inline-flex items-center">
                        <i class="fas fa-download mr-2"></i>Instalar Sistema de Login
                    </button>
                </form>
            <?php endif; ?>

            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="font-semibold text-gray-800 mb-4">Características de Segurança:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div class="flex items-start">
                        <i class="fas fa-lock text-green-500 mr-2 mt-1"></i>
                        <span>Hash seguro de senhas com salt</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-shield-alt text-green-500 mr-2 mt-1"></i>
                        <span>Proteção contra força bruta</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-clock text-green-500 mr-2 mt-1"></i>
                        <span>Timeout de sessão configurável</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-list text-green-500 mr-2 mt-1"></i>
                        <span>Logs completos de auditoria</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-user-shield text-green-500 mr-2 mt-1"></i>
                        <span>Níveis de acesso (usuário/admin)</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-cookie-bite text-green-500 mr-2 mt-1"></i>
                        <span>Cookies seguros HttpOnly</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 