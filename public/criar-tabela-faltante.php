<?php
/**
 * Criar Tabela Faltante
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
    <title>Criar Tabela Faltante - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-50 p-4'>
    <div class='max-w-4xl mx-auto'>
        <div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-database text-blue-500 mr-3'></i>
                Criar Tabela Faltante
            </h1>
            <p class='text-gray-600'>Criando a tabela agendamentos_recorrentes_ocorrencias.</p>
        </div>";

// Configurações do banco
$dbConfig = [
    'host' => 'xmysql.bichosdobairro.com.br',
    'name' => 'bichosdobairro5',
    'user' => 'bichosdobairro5',
    'pass' => '!BdoB.1179!',
    'charset' => 'utf8mb4',
    'port' => '3306'
];

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

// 1. Testar conexão
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>1. Teste de Conexão</h2>";

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    exibirResultado('Conexão PDO', true, 'Conexão estabelecida com sucesso', null);
    
} catch (Exception $e) {
    exibirResultado('Conexão PDO', false, 'Erro na conexão: ' . $e->getMessage(), null);
    exit;
}

echo "</div>";

// 2. Verificar se a tabela já existe
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>2. Verificação da Tabela</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos_recorrentes_ocorrencias'");
    $tabelaExiste = $stmt->rowCount() > 0;
    
    if ($tabelaExiste) {
        exibirResultado('Tabela Existente', true, 'A tabela agendamentos_recorrentes_ocorrencias já existe', null);
        echo "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4'>
            <p class='text-blue-700'>A tabela já foi criada anteriormente. Nenhuma ação necessária.</p>
        </div>";
    } else {
        exibirResultado('Tabela Não Encontrada', false, 'A tabela agendamentos_recorrentes_ocorrencias não existe', null);
    }
    
} catch (Exception $e) {
    exibirResultado('Verificação', false, 'Erro ao verificar tabela: ' . $e->getMessage(), null);
    exit;
}

echo "</div>";

// 3. Criar tabela se não existir
if (!$tabelaExiste) {
    echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
        <h2 class='text-xl font-semibold text-gray-800 mb-4'>3. Criação da Tabela</h2>";
    
    try {
        $sql = file_get_contents('../sql/create_agendamentos_recorrentes_ocorrencias.sql');
        
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
            exibirResultado('Criação da Tabela', true, 'Tabela agendamentos_recorrentes_ocorrencias criada com sucesso', 
                "$sucessos comando(s) executado(s)");
        } else {
            exibirResultado('Criação da Tabela', false, 'Erros encontrados durante a criação', 
                implode("\n", $erros));
        }
        
    } catch (Exception $e) {
        exibirResultado('Criação da Tabela', false, 'Erro ao executar script: ' . $e->getMessage(), null);
    }
    
    echo "</div>";
}

// 4. Verificação final
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>4. Verificação Final</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos_recorrentes_ocorrencias'");
    $tabelaCriada = $stmt->rowCount() > 0;
    
    if ($tabelaCriada) {
        // Verificar estrutura da tabela
        $stmt = $pdo->query("DESCRIBE agendamentos_recorrentes_ocorrencias");
        $colunas = $stmt->fetchAll();
        
        $colunasInfo = [];
        foreach ($colunas as $coluna) {
            $colunasInfo[] = "{$coluna['Field']} ({$coluna['Type']})";
        }
        
        exibirResultado('Verificação Final', true, 'Tabela agendamentos_recorrentes_ocorrencias criada e verificada', 
            "Colunas criadas:\n" . implode("\n", $colunasInfo));
        
        echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
            <div class='flex items-center'>
                <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
                <h3 class='font-semibold text-green-800'>Sucesso!</h3>
            </div>
            <p class='text-green-700 mt-2'>A tabela agendamentos_recorrentes_ocorrencias foi criada com sucesso.</p>
        </div>";
        
    } else {
        exibirResultado('Verificação Final', false, 'A tabela não foi criada corretamente', null);
        
        echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
            <div class='flex items-center'>
                <i class='fas fa-times-circle text-red-600 text-xl mr-3'></i>
                <h3 class='font-semibold text-red-800'>Erro!</h3>
            </div>
            <p class='text-red-700 mt-2'>A tabela não foi criada. Verifique os erros acima.</p>
        </div>";
    }
    
} catch (Exception $e) {
    exibirResultado('Verificação Final', false, 'Erro na verificação final: ' . $e->getMessage(), null);
}

echo "</div>";

// 5. Verificar todas as tabelas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>5. Status Completo das Tabelas</h2>";

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
    echo "<div class='grid grid-cols-1 md:grid-cols-2 gap-4'>";
    
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelas)) {
            echo "<div class='bg-green-50 border border-green-200 rounded p-3'>
                <div class='flex items-center'>
                    <i class='fas fa-check-circle text-green-600 mr-2'></i>
                    <span class='font-medium'>$tabela</span>
                </div>
            </div>";
            $tabelasCriadas++;
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
    
    if ($tabelasCriadas === count($tabelasEsperadas)) {
        echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4 mt-4'>
            <div class='flex items-center'>
                <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
                <h3 class='font-semibold text-green-800'>Banco Completo!</h3>
            </div>
            <p class='text-green-700 mt-2'>Todas as $tabelasCriadas tabelas estão presentes.</p>
        </div>";
    } else {
        echo "<div class='bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4'>
            <div class='flex items-center'>
                <i class='fas fa-exclamation-triangle text-yellow-600 text-xl mr-3'></i>
                <h3 class='font-semibold text-yellow-800'>Banco Parcialmente Completo</h3>
            </div>
            <p class='text-yellow-700 mt-2'>$tabelasCriadas de " . count($tabelasEsperadas) . " tabelas presentes.</p>
        </div>";
    }
    
} catch (Exception $e) {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <p class='text-red-700'>Erro ao verificar tabelas: {$e->getMessage()}</p>
    </div>";
}

echo "</div>";

echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>
    <p class='text-gray-600 mb-4'>Processo concluído em " . date('d/m/Y H:i:s') . "</p>
    <div class='space-x-4'>
        <a href='verificar-banco-simples.php' class='inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
            <i class='fas fa-database mr-2'></i>Verificar Banco
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