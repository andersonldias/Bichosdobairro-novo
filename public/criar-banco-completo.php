<?php
/**
 * Script para Criar Banco de Dados Completo
 * Sistema Bichos do Bairro
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
    <title>Criar Banco de Dados - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-50 p-4'>
    <div class='max-w-4xl mx-auto'>
        <div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-database text-blue-500 mr-3'></i>
                Criação do Banco de Dados
            </h1>
            <p class='text-gray-600'>Executando scripts SQL para criar todas as tabelas necessárias.</p>
        </div>";

// Função para exibir resultado
function exibirResultado($titulo, $status, $mensagem, $detalhes = null) {
    $statusClass = $status ? 'text-green-600' : 'text-red-600';
    $statusIcon = $status ? 'fa-check-circle' : 'fa-times-circle';
    $bgClass = $status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    
    echo "<div class='bg-white rounded-lg shadow p-4 mb-4 border-l-4 {$bgClass}'>
        <div class='flex items-center justify-between mb-2'>
            <h3 class='font-semibold text-gray-800'>$titulo</h3>
            <i class='fas $statusIcon $statusClass text-xl'></i>
        </div>
        <p class='text-gray-700 mb-2'>$mensagem</p>";
    
    if ($detalhes) {
        echo "<details class='mt-2'>
            <summary class='cursor-pointer text-sm text-blue-600 hover:text-blue-800'>Ver detalhes</summary>
            <div class='mt-2 p-3 bg-gray-100 rounded text-sm font-mono'>$detalhes</div>
        </details>";
    }
    
    echo "</div>";
}

// 1. Verificar arquivo .env
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>1. Verificação de Configuração</h2>";

$envFile = '../.env';
if (file_exists($envFile)) {
    exibirResultado('Arquivo .env', true, 'Arquivo .env encontrado', 'Caminho: ' . realpath($envFile));
} else {
    exibirResultado('Arquivo .env', false, 'Arquivo .env não encontrado', 'Criando arquivo .env...');
    copy('../env.example', $envFile);
    exibirResultado('Arquivo .env', true, 'Arquivo .env criado com sucesso', null);
}

// 2. Carregar configurações
try {
    require_once '../src/Config.php';
    Config::load();
    $dbConfig = Config::getDbConfig();
    exibirResultado('Configurações', true, 'Configurações carregadas com sucesso', 
        "Host: {$dbConfig['host']}\nDatabase: {$dbConfig['name']}\nUser: {$dbConfig['user']}");
} catch (Exception $e) {
    exibirResultado('Configurações', false, 'Erro ao carregar configurações: ' . $e->getMessage(), null);
    exit;
}

echo "</div>";

// 3. Testar conexão
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>2. Teste de Conexão</h2>";

try {
    require_once '../src/db.php';
    $pdo = getDb();
    
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    
    exibirResultado('Conexão PDO', true, 'Conexão estabelecida com sucesso', 
        'Versão MySQL: ' . $version['version']);
    
} catch (Exception $e) {
    exibirResultado('Conexão PDO', false, 'Erro na conexão: ' . $e->getMessage(), null);
    exit;
}

echo "</div>";

// 4. Executar scripts SQL
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>3. Execução dos Scripts SQL</h2>";

$scripts = [
    'database.sql' => 'Estrutura básica (clientes, pets, agendamentos)',
    'create_usuarios_table.sql' => 'Sistema de usuários e login',
    'create_logs_atividade_table.sql' => 'Logs de atividade',
    'create_logs_login_table.sql' => 'Logs de login',
    'create_notificacoes_table.sql' => 'Sistema de notificações',
    'agendamentos_recorrentes.sql' => 'Agendamentos recorrentes'
];

foreach ($scripts as $script => $descricao) {
    $scriptPath = "../sql/$script";
    
    if (file_exists($scriptPath)) {
        try {
            $sql = file_get_contents($scriptPath);
            
            // Dividir o SQL em comandos individuais
            $commands = array_filter(array_map('trim', explode(';', $sql)));
            
            $sucessos = 0;
            $erros = [];
            
            foreach ($commands as $command) {
                if (!empty($command)) {
                    try {
                        $pdo->exec($command);
                        $sucessos++;
                    } catch (Exception $e) {
                        $erros[] = $e->getMessage();
                    }
                }
            }
            
            if (empty($erros)) {
                exibirResultado("Script: $script", true, "$descricao - $sucessos comando(s) executado(s)", null);
            } else {
                exibirResultado("Script: $script", false, "$descricao - Erros encontrados", implode("\n", $erros));
            }
            
        } catch (Exception $e) {
            exibirResultado("Script: $script", false, "Erro ao executar script: " . $e->getMessage(), null);
        }
    } else {
        exibirResultado("Script: $script", false, "Arquivo não encontrado", "Caminho: $scriptPath");
    }
}

echo "</div>";

// 5. Verificar tabelas criadas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>4. Verificação das Tabelas</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
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
    
    $tabelasCriadas = 0;
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelas)) {
            exibirResultado("Tabela: $tabela", true, "Tabela $tabela criada com sucesso", null);
            $tabelasCriadas++;
        } else {
            exibirResultado("Tabela: $tabela", false, "Tabela $tabela não encontrada", null);
        }
    }
    
    exibirResultado('Resumo', $tabelasCriadas === count($tabelasEsperadas), 
        "$tabelasCriadas de " . count($tabelasEsperadas) . " tabelas criadas", null);
    
} catch (Exception $e) {
    exibirResultado('Verificação de Tabelas', false, 'Erro ao verificar tabelas: ' . $e->getMessage(), null);
}

echo "</div>";

// 6. Verificar dados iniciais
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>5. Verificação de Dados Iniciais</h2>";

try {
    // Verificar usuário administrador
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'");
    $result = $stmt->fetch();
    $admins = $result['total'];
    
    exibirResultado('Usuário Administrador', $admins > 0, 
        $admins > 0 ? "$admins administrador(es) encontrado(s)" : "Nenhum administrador encontrado",
        $admins === 0 ? "Email: admin@bichosdobairro.com\nSenha: admin123" : null);
    
    // Verificar níveis de acesso
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM niveis_acesso");
    $result = $stmt->fetch();
    $niveis = $result['total'];
    
    exibirResultado('Níveis de Acesso', $niveis > 0, 
        "$niveis nível(is) de acesso encontrado(s)", null);
    
} catch (Exception $e) {
    exibirResultado('Dados Iniciais', false, 'Erro ao verificar dados: ' . $e->getMessage(), null);
}

echo "</div>";

// 7. Resumo final
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>6. Resumo Final</h2>";

$tabelasCriadas = 0;
if (isset($tabelas)) {
    $tabelasCriadas = count(array_intersect($tabelas, $tabelasEsperadas));
}

if ($tabelasCriadas === count($tabelasEsperadas)) {
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-green-800'>Banco de Dados Criado com Sucesso!</h3>
        </div>
        <p class='text-green-700 mt-2'>Todas as tabelas foram criadas corretamente.</p>
        <div class='mt-4 space-y-2'>
            <p class='text-green-700'><strong>Próximos passos:</strong></p>
            <ul class='text-green-700 list-disc list-inside ml-4'>
                <li>Acesse o sistema com as credenciais padrão</li>
                <li>Altere a senha do administrador</li>
                <li>Configure as informações do petshop</li>
                <li>Comece a cadastrar clientes e pets</li>
            </ul>
        </div>
    </div>";
} else {
    echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-exclamation-triangle text-yellow-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-yellow-800'>Banco Criado Parcialmente</h3>
        </div>
        <p class='text-yellow-700 mt-2'>Algumas tabelas podem não ter sido criadas. Verifique os erros acima.</p>
    </div>";
}

echo "</div>";

echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>
    <p class='text-gray-600 mb-4'>Criação do banco concluída em " . date('d/m/Y H:i:s') . "</p>
    <div class='space-x-4'>
        <a href='teste-conexao-banco.php' class='inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
            <i class='fas fa-database mr-2'></i>Testar Conexão
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