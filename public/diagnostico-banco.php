git.add<?php
/**
 * Diagnóstico Completo do Banco de Dados
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
    <title>Diagnóstico do Banco de Dados - Bichos do Bairro</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
</head>
<body class='bg-gray-50 p-4'>
    <div class='max-w-6xl mx-auto'>
        <div class='bg-white rounded-lg shadow-lg p-6 mb-6'>
            <h1 class='text-2xl font-bold text-gray-800 mb-4 flex items-center'>
                <i class='fas fa-database text-blue-500 mr-3'></i>
                Diagnóstico Completo do Banco de Dados
            </h1>
            <p class='text-gray-600 mb-6'>Verificação completa da conexão e estrutura do banco de dados.</p>
        </div>";

// Função para exibir resultado
function exibirResultado($titulo, $status, $mensagem, $detalhes = null) {
    $statusClass = $status ? 'text-green-600' : 'text-red-600';
    $statusIcon = $status ? 'fa-check-circle' : 'fa-times-circle';
    $bgClass = $status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
    
    echo "<div class='bg-white rounded-lg shadow p-4 mb-4 border-l-4 border-l-4 {$bgClass}'>
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

// 1. Verificar se o arquivo .env existe
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>1. Configuração do Ambiente</h2>";

$envFile = '../.env';
$envExists = file_exists($envFile);
exibirResultado(
    'Arquivo .env',
    $envExists,
    $envExists ? 'Arquivo .env encontrado' : 'Arquivo .env não encontrado',
    $envExists ? 'Caminho: ' . realpath($envFile) : 'Crie o arquivo .env baseado no env.example'
);

// 2. Verificar configurações do banco
echo "<h3 class='font-semibold text-gray-800 mt-4 mb-2'>Configurações do Banco:</h3>";

// Carregar configurações
require_once '../src/Config.php';
Config::load();

$dbConfig = Config::getDbConfig();
$configs = [
    'Host' => $dbConfig['host'],
    'Database' => $dbConfig['name'],
    'User' => $dbConfig['user'],
    'Charset' => $dbConfig['charset'],
    'Port' => $dbConfig['port']
];

foreach ($configs as $key => $value) {
    $isEmpty = empty($value) || $value === 'seu_banco_de_dados' || $value === 'seu_usuario_banco' || $value === 'sua_senha_banco';
    exibirResultado(
        "Configuração $key",
        !$isEmpty,
        $isEmpty ? "Configuração $key não definida ou inválida" : "Configuração $key: $value",
        $isEmpty ? "Configure $key no arquivo .env" : null
    );
}

echo "</div>";

// 3. Testar conexão com o banco
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>2. Teste de Conexão</h2>";

try {
    require_once '../src/db.php';
    $pdo = getDb();
    
    // Testar conexão básica
    $stmt = $pdo->query('SELECT 1 as test');
    $result = $stmt->fetch();
    
    exibirResultado(
        'Conexão PDO',
        true,
        'Conexão estabelecida com sucesso',
        'Versão do MySQL: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION)
    );
    
    // Verificar charset
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set_database'");
    $charset = $stmt->fetch();
    
    exibirResultado(
        'Charset do Banco',
        $charset['Value'] === 'utf8mb4',
        "Charset atual: {$charset['Value']}",
        $charset['Value'] !== 'utf8mb4' ? 'Recomendado: utf8mb4 para suporte completo a emojis' : null
    );
    
    // Verificar timezone
    $stmt = $pdo->query("SELECT @@time_zone as timezone");
    $timezone = $stmt->fetch();
    
    exibirResultado(
        'Timezone do Banco',
        true,
        "Timezone: {$timezone['timezone']}",
        null
    );
    
} catch (Exception $e) {
    exibirResultado(
        'Conexão PDO',
        false,
        'Erro na conexão: ' . $e->getMessage(),
        'Verifique as configurações no arquivo .env'
    );
}

echo "</div>";

// 4. Verificar tabelas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>3. Estrutura das Tabelas</h2>";

if (isset($pdo)) {
    // Listar todas as tabelas
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
    
    echo "<h3 class='font-semibold text-gray-800 mb-2'>Tabelas Encontradas:</h3>";
    
    foreach ($tabelasEsperadas as $tabela) {
        $existe = in_array($tabela, $tabelas);
        exibirResultado(
            "Tabela: $tabela",
            $existe,
            $existe ? "Tabela $tabela existe" : "Tabela $tabela não encontrada",
            $existe ? null : "Execute o script SQL para criar a tabela $tabela"
        );
    }
    
    // Verificar tabelas extras
    $tabelasExtras = array_diff($tabelas, $tabelasEsperadas);
    if (!empty($tabelasExtras)) {
        echo "<h3 class='font-semibold text-gray-800 mt-4 mb-2'>Tabelas Extras:</h3>";
        foreach ($tabelasExtras as $tabela) {
            exibirResultado(
                "Tabela Extra: $tabela",
                true,
                "Tabela $tabela encontrada (não esperada)",
                null
            );
        }
    }
    
    // Verificar estrutura das tabelas principais
    echo "<h3 class='font-semibold text-gray-800 mt-4 mb-2'>Estrutura das Tabelas Principais:</h3>";
    
    $tabelasPrincipais = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    
    foreach ($tabelasPrincipais as $tabela) {
        if (in_array($tabela, $tabelas)) {
            try {
                $stmt = $pdo->query("DESCRIBE $tabela");
                $colunas = $stmt->fetchAll();
                
                $colunasInfo = [];
                foreach ($colunas as $coluna) {
                    $colunasInfo[] = "{$coluna['Field']} ({$coluna['Type']})";
                }
                
                exibirResultado(
                    "Estrutura: $tabela",
                    true,
                    "Tabela $tabela com " . count($colunas) . " colunas",
                    implode("\n", $colunasInfo)
                );
            } catch (Exception $e) {
                exibirResultado(
                    "Estrutura: $tabela",
                    false,
                    "Erro ao verificar estrutura: " . $e->getMessage(),
                    null
                );
            }
        }
    }
}

echo "</div>";

// 5. Verificar dados
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>4. Verificação de Dados</h2>";

if (isset($pdo)) {
    $tabelasComDados = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    
    foreach ($tabelasComDados as $tabela) {
        if (in_array($tabela, $tabelas)) {
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
                $result = $stmt->fetch();
                $total = $result['total'];
                
                exibirResultado(
                    "Dados em: $tabela",
                    true,
                    "$total registros encontrados",
                    null
                );
            } catch (Exception $e) {
                exibirResultado(
                    "Dados em: $tabela",
                    false,
                    "Erro ao contar registros: " . $e->getMessage(),
                    null
                );
            }
        }
    }
    
    // Verificar usuário administrador
    if (in_array('usuarios', $tabelas)) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'");
            $result = $stmt->fetch();
            $admins = $result['total'];
            
            exibirResultado(
                'Usuários Administradores',
                $admins > 0,
                $admins > 0 ? "$admins administrador(es) encontrado(s)" : "Nenhum administrador encontrado",
                $admins === 0 ? "Crie um usuário administrador" : null
            );
        } catch (Exception $e) {
            exibirResultado(
                'Usuários Administradores',
                false,
                "Erro ao verificar administradores: " . $e->getMessage(),
                null
            );
        }
    }
}

echo "</div>";

// 6. Verificar integridade
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>5. Verificação de Integridade</h2>";

if (isset($pdo)) {
    // Verificar chaves estrangeiras
    if (in_array('pets', $tabelas) && in_array('clientes', $tabelas)) {
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM pets p 
                LEFT JOIN clientes c ON p.cliente_id = c.id 
                WHERE c.id IS NULL
            ");
            $result = $stmt->fetch();
            $petsOrfaos = $result['total'];
            
            exibirResultado(
                'Pets Órfãos',
                $petsOrfaos === 0,
                $petsOrfaos === 0 ? "Todos os pets têm tutores válidos" : "$petsOrfaos pets sem tutor válido",
                $petsOrfaos > 0 ? "Corrija os pets sem tutor" : null
            );
        } catch (Exception $e) {
            exibirResultado(
                'Pets Órfãos',
                false,
                "Erro ao verificar pets órfãos: " . $e->getMessage(),
                null
            );
        }
    }
    
    // Verificar agendamentos órfãos
    if (in_array('agendamentos', $tabelas) && in_array('clientes', $tabelas) && in_array('pets', $tabelas)) {
        try {
            $stmt = $pdo->query("
                SELECT COUNT(*) as total 
                FROM agendamentos a 
                LEFT JOIN clientes c ON a.cliente_id = c.id 
                LEFT JOIN pets p ON a.pet_id = p.id 
                WHERE c.id IS NULL OR p.id IS NULL
            ");
            $result = $stmt->fetch();
            $agendamentosOrfaos = $result['total'];
            
            exibirResultado(
                'Agendamentos Órfãos',
                $agendamentosOrfaos === 0,
                $agendamentosOrfaos === 0 ? "Todos os agendamentos têm cliente e pet válidos" : "$agendamentosOrfaos agendamentos com referências inválidas",
                $agendamentosOrfaos > 0 ? "Corrija os agendamentos com referências inválidas" : null
            );
        } catch (Exception $e) {
            exibirResultado(
                'Agendamentos Órfãos',
                false,
                "Erro ao verificar agendamentos órfãos: " . $e->getMessage(),
                null
            );
        }
    }
}

echo "</div>";

// 7. Verificar performance
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>6. Verificação de Performance</h2>";

if (isset($pdo)) {
    // Verificar índices
    $tabelasComIndices = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    
    foreach ($tabelasComIndices as $tabela) {
        if (in_array($tabela, $tabelas)) {
            try {
                $stmt = $pdo->query("SHOW INDEX FROM $tabela");
                $indices = $stmt->fetchAll();
                
                $indicesInfo = [];
                foreach ($indices as $indice) {
                    if ($indice['Key_name'] !== 'PRIMARY') {
                        $indicesInfo[] = "{$indice['Key_name']} ({$indice['Column_name']})";
                    }
                }
                
                exibirResultado(
                    "Índices: $tabela",
                    !empty($indicesInfo),
                    !empty($indicesInfo) ? count($indicesInfo) . " índice(s) encontrado(s)" : "Nenhum índice adicional encontrado",
                    !empty($indicesInfo) ? implode("\n", $indicesInfo) : "Considere adicionar índices para melhorar performance"
                );
            } catch (Exception $e) {
                exibirResultado(
                    "Índices: $tabela",
                    false,
                    "Erro ao verificar índices: " . $e->getMessage(),
                    null
                );
            }
        }
    }
}

echo "</div>";

// 8. Resumo final
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>7. Resumo e Recomendações</h2>";

$problemas = [];
$sucessos = [];

// Verificar se há problemas críticos
if (!$envExists) {
    $problemas[] = "Arquivo .env não encontrado";
} else {
    $sucessos[] = "Configuração de ambiente OK";
}

if (!isset($pdo)) {
    $problemas[] = "Conexão com banco falhou";
} else {
    $sucessos[] = "Conexão com banco OK";
    
    if (count($tabelas) < count($tabelasEsperadas)) {
        $problemas[] = "Tabelas faltando";
    } else {
        $sucessos[] = "Estrutura de tabelas OK";
    }
}

if (empty($problemas)) {
    echo "<div class='bg-green-50 border border-green-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-check-circle text-green-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-green-800'>Banco de Dados OK!</h3>
        </div>
        <p class='text-green-700 mt-2'>O banco de dados está funcionando corretamente.</p>
    </div>";
} else {
    echo "<div class='bg-red-50 border border-red-200 rounded-lg p-4'>
        <div class='flex items-center'>
            <i class='fas fa-exclamation-triangle text-red-600 text-xl mr-3'></i>
            <h3 class='text-lg font-semibold text-red-800'>Problemas Encontrados</h3>
        </div>
        <ul class='text-red-700 mt-2 list-disc list-inside'>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>
    </div>";
}

if (!empty($sucessos)) {
    echo "<div class='bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4'>
        <h3 class='text-lg font-semibold text-blue-800 mb-2'>Pontos Positivos:</h3>
        <ul class='text-blue-700 list-disc list-inside'>";
    foreach ($sucessos as $sucesso) {
        echo "<li>$sucesso</li>";
    }
    echo "</ul>
    </div>";
}

echo "</div>";

// 9. Ações recomendadas
echo "<div class='bg-white rounded-lg shadow p-4 mb-4'>
    <h2 class='text-xl font-semibold text-gray-800 mb-4'>8. Ações Recomendadas</h2>
    <div class='space-y-2'>";

if (!$envExists) {
    echo "<div class='p-3 bg-yellow-50 border border-yellow-200 rounded'>
        <h4 class='font-semibold text-yellow-800'>1. Criar arquivo .env</h4>
        <p class='text-yellow-700 text-sm'>Copie o arquivo env.example para .env e configure as credenciais do banco.</p>
    </div>";
}

if (!isset($pdo)) {
    echo "<div class='p-3 bg-yellow-50 border border-yellow-200 rounded'>
        <h4 class='font-semibold text-yellow-800'>2. Configurar banco de dados</h4>
        <p class='text-yellow-700 text-sm'>Verifique as configurações de conexão no arquivo .env.</p>
    </div>";
} else {
    if (count($tabelas) < count($tabelasEsperadas)) {
        echo "<div class='p-3 bg-yellow-50 border border-yellow-200 rounded'>
            <h4 class='font-semibold text-yellow-800'>3. Executar scripts SQL</h4>
            <p class='text-yellow-700 text-sm'>Execute os scripts na pasta sql/ para criar as tabelas faltantes.</p>
        </div>";
    }
    
    // Verificar se há usuário admin
    if (in_array('usuarios', $tabelas)) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = 'admin'");
            $result = $stmt->fetch();
            if ($result['total'] === 0) {
                echo "<div class='p-3 bg-yellow-50 border border-yellow-200 rounded'>
                    <h4 class='font-semibold text-yellow-800'>4. Criar usuário administrador</h4>
                    <p class='text-yellow-700 text-sm'>Crie um usuário administrador para acessar o sistema.</p>
                </div>";
            }
        } catch (Exception $e) {
            // Ignorar erro
        }
    }
}

echo "</div>
</div>";

echo "<div class='bg-gray-100 rounded-lg p-4 text-center'>
    <p class='text-gray-600'>Diagnóstico concluído em " . date('d/m/Y H:i:s') . "</p>
    <a href='dashboard.php' class='inline-block mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600'>
        <i class='fas fa-home mr-2'></i>Voltar ao Dashboard
    </a>
</div>";

echo "</div>
</body>
</html>";
?> 