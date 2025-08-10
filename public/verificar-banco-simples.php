<?php
/**
 * Verificação Simples do Banco de Dados
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação do Banco - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-50 p-4'>
    <div class='max-w-4xl mx-auto'>
        <div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-database text-blue-500 mr-3'></i>
                Verificação do Banco de Dados
            </h1>
        </div>";

// Configurações do banco (hardcoded para teste)
$dbConfig = [
    'host' => 'xmysql.bichosdobairro.com.br',
    'name' => 'bichosdobairro5',
    'user' => 'bichosdobairro5',
    'pass' => '!BdoB.1179!',
    'charset' => 'utf8mb4',
    'port' => '3306'
];

echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Configurações do Banco</h2>
    <div class='grid grid-cols-1 md:grid-cols-2 gap-4'>
        <div><strong>Host:</strong> {$dbConfig['host']}</div>
        <div><strong>Database:</strong> {$dbConfig['name']}</div>
        <div><strong>User:</strong> {$dbConfig['user']}</div>
        <div><strong>Charset:</strong> {$dbConfig['charset']}</div>
    </div>
</div>";

// Testar conexão
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Teste de Conexão</h2>";

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
            <h3 class='font-semibold text-green-800'>Conexão Estabelecida com Sucesso!</h3>
        </div>
        <p class='text-green-700 mt-2'>O banco de dados está acessível.</p>
    </div>";
    
    // Verificar versão do MySQL
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "<p class='text-gray-600 mt-2'><strong>Versão MySQL:</strong> {$version['version']}</p>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-times-circle text-red-600 text-xl mr-3'></i>
            <h3 class='font-semibold text-red-800'>Erro na Conexão</h3>
        </div>
        <p class='text-red-700 mt-2'>{$e->getMessage()}</p>
    </div>";
    exit;
}

echo "</div>";

// Verificar tabelas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Estrutura das Tabelas</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p class='text-gray-600 mb-4'><strong>Total de tabelas encontradas:</strong> " . count($tabelas) . "</p>";
    
    $tabelasEsperadas = [
        'usuarios',
        'niveis_acesso',
        'clientes',
        'telefones',
        'pets',
        'agendamentos',
        'agendamentos_recorrentes',
        'agendamentos_recorrentes_ocorrencias',
        'logs_atividade',
        'logs_login',
        'notificacoes'
    ];
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
    
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelas)) {
            echo "<div class='bg-green-50 border border-green-200 rounded p-3'>
                <div class='flex items-center'>
                    <i class='fas fa-check-circle text-green-600 mr-2'></i>
                    <span class='font-medium'>$tabela</span>
                </div>
            </div>";
        } else {
            echo "<div class='bg-red-50 border border-red-200 rounded p-3'>
                <div class='flex items-center'>
                    <i class='fas fa-times-circle text-red-600 mr-2'></i>
                    <span class='font-medium'>$tabela</span>
                </div>
            </div>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <p class='text-red-700'>Erro ao verificar tabelas: {$e->getMessage()}</p>
    </div>";
}

echo "</div>";

// Verificar dados
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Dados nas Tabelas</h2>";

try {
    $tabelasComDados = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
    
    foreach ($tabelasComDados as $tabela) {
        if (in_array($tabela, $tabelas)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
            $result = $stmt->fetch();
            $total = $result['total'];
            
            echo "<div class='bg-blue-50 border border-blue-200 rounded p-3'>
                <div class='flex items-center justify-between'>
                    <span class='font-medium'>$tabela</span>
                    <span class='bg-blue-600 text-white px-2 py-1 rounded text-sm'>$total</span>
                </div>
            </div>";
        }
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <p class='text-red-700'>Erro ao verificar dados: {$e->getMessage()}</p>
    </div>";
}

echo "</div>";

// Verificar usuário administrador
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Usuário Administrador</h2>";

try {
    if (in_array('usuarios', $tabelas)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'");
        $result = $stmt->fetch();
        $admins = $result['total'];
        
        if ($admins > 0) {
            echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
                <div class='flex items-center'>
                    <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
                    <h3 class='font-semibold text-green-800'>Administrador Encontrado</h3>
                </div>
                <p class='text-green-700 mt-2'>$admins usuário(s) administrador(es) encontrado(s).</p>
            </div>";
        } else {
            echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4'>
                <div class='flex items-center'>
                    <i class='fas fa-exclamation-triangle text-yellow-600 text-xl mr-3'></i>
                    <h3 class='font-semibold text-yellow-800'>Nenhum Administrador</h3>
                </div>
                <p class='text-yellow-700 mt-2'>Nenhum usuário administrador encontrado.</p>
                <p class='text-yellow-700 mt-2'><strong>Credenciais padrão:</strong></p>
                <p class='text-yellow-700'>Email: admin@bichosdobairro.com</p>
                <p class='text-yellow-700'>Senha: admin123</p>
            </div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <p class='text-red-700'>Erro ao verificar administrador: {$e->getMessage()}</p>
    </div>";
}

echo "</div>";

// Resumo final
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>Resumo</h2>";

$tabelasCriadas = 0;
if (isset($tabelas)) {
    $tabelasCriadas = count(array_intersect($tabelas, $tabelasEsperadas));
}

if ($tabelasCriadas === count($tabelasEsperadas)) {
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-green-800'>Banco de Dados OK!</h3>
        </div>
        <p class='text-green-700 mt-2'>O banco de dados está funcionando corretamente.</p>
    </div>";
} else {
    echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-exclamation-triangle text-yellow-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-yellow-800'>Banco Parcialmente Configurado</h3>
        </div>
        <p class='text-yellow-700 mt-2'>$tabelasCriadas de " . count($tabelasEsperadas) . " tabelas encontradas.</p>
    </div>";
}

echo "</div>";

echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>
    <p class='text-gray-600 mb-4'>Verificação concluída em " . date('d/m/Y H:i:s') . "</p>
    <div class='space-x-4'>
        <a href='criar-banco-completo.php' class='inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
            <i class='fas fa-database mr-2'></i>Criar Banco Completo
        </a>
        <a href='login.php' class='inline-block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600'>
            <i class='fas fa-sign-in-alt mr-2'></i>Acessar Sistema
        </a>
        <a href='dashboard.php' class='inline-block bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600'>
            <i class='fas fa-home mr-2'></i>Dashboard
        </a>
    </div>
</div>";

echo "</div>
</body>
</html>";
?> 