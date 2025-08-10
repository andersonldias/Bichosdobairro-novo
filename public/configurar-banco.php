<?php
/**
 * Script para configurar o banco de dados
 * Sistema Bichos do Bairro
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>⚙️ Configuração do Banco de Dados</h1>";

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configs = [
        'DB_HOST' => $_POST['db_host'] ?? 'localhost',
        'DB_NAME' => $_POST['db_name'] ?? 'bichosdobairro',
        'DB_USER' => $_POST['db_user'] ?? 'root',
        'DB_PASS' => $_POST['db_pass'] ?? '',
        'DB_CHARSET' => $_POST['db_charset'] ?? 'utf8mb4',
        'DB_PORT' => $_POST['db_port'] ?? '3306',
        'APP_ENV' => 'development',
        'APP_DEBUG' => 'true',
        'DEVELOPMENT_MODE' => 'true',
        'SHOW_ERRORS' => 'true'
    ];
    
    try {
        // Testar conexão
        $dsn = "mysql:host={$configs['DB_HOST']};port={$configs['DB_PORT']};charset={$configs['DB_CHARSET']}";
        $pdo = new PDO($dsn, $configs['DB_USER'], $configs['DB_PASS']);
        echo "<p>✅ Conexão com servidor MySQL estabelecida</p>";
        
        // Tentar conectar ao banco específico
        $dsn = "mysql:host={$configs['DB_HOST']};port={$configs['DB_PORT']};dbname={$configs['DB_NAME']};charset={$configs['DB_CHARSET']}";
        $pdo = new PDO($dsn, $configs['DB_USER'], $configs['DB_PASS']);
        echo "<p>✅ Conexão com banco '{$configs['DB_NAME']}' estabelecida</p>";
        
        // Salvar configurações
        require_once '../src/Config.php';
        if (Config::saveEnv($configs)) {
            echo "<p>✅ Configurações salvas com sucesso!</p>";
            echo "<p><a href='index.php'>← Ir para o sistema</a></p>";
        } else {
            echo "<p>❌ Erro ao salvar configurações</p>";
        }
        
    } catch (PDOException $e) {
        echo "<p>❌ Erro de conexão: " . $e->getMessage() . "</p>";
        echo "<p>Verifique as credenciais e tente novamente.</p>";
    }
}

// Carregar configurações atuais
require_once '../src/Config.php';
Config::load();
$currentConfig = Config::all();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurar Banco - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Configuração do Banco de Dados
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Configure as credenciais do seu banco de dados MySQL
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="db_host" class="sr-only">Host</label>
                        <input id="db_host" name="db_host" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Host (ex: localhost)" 
                               value="<?= htmlspecialchars($currentConfig['DB_HOST'] ?? 'localhost') ?>">
                    </div>
                    <div>
                        <label for="db_port" class="sr-only">Porta</label>
                        <input id="db_port" name="db_port" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Porta (ex: 3306)" 
                               value="<?= htmlspecialchars($currentConfig['DB_PORT'] ?? '3306') ?>">
                    </div>
                    <div>
                        <label for="db_name" class="sr-only">Nome do Banco</label>
                        <input id="db_name" name="db_name" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Nome do Banco" 
                               value="<?= htmlspecialchars($currentConfig['DB_NAME'] ?? 'bichosdobairro') ?>">
                    </div>
                    <div>
                        <label for="db_user" class="sr-only">Usuário</label>
                        <input id="db_user" name="db_user" type="text" required 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Usuário" 
                               value="<?= htmlspecialchars($currentConfig['DB_USER'] ?? 'root') ?>">
                    </div>
                    <div>
                        <label for="db_pass" class="sr-only">Senha</label>
                        <input id="db_pass" name="db_pass" type="password" 
                               class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                               placeholder="Senha (deixe em branco se não houver)">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Testar e Salvar Configuração
                    </button>
                </div>
            </form>
            
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900">Instruções:</h3>
                <ul class="mt-2 text-sm text-gray-600 space-y-1">
                    <li>• Certifique-se de que o MySQL está rodando</li>
                    <li>• Crie um banco de dados chamado 'bichosdobairro'</li>
                    <li>• Use as credenciais do seu servidor MySQL</li>
                    <li>• Para desenvolvimento local, geralmente é 'root' sem senha</li>
                </ul>
            </div>
            
            <div class="mt-4">
                <a href="diagnostico-banco.php" class="text-blue-600 hover:text-blue-500 text-sm">
                    ← Ver diagnóstico do banco
                </a>
            </div>
        </div>
    </div>
</body>
</html>
