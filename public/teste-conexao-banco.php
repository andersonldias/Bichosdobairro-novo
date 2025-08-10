<?php
/**
 * Teste Simples de Conexão com Banco
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Conexão com Banco de Dados</h1>";

// 1. Verificar arquivo .env
echo "<h2>1. Verificando arquivo .env</h2>";
$envFile = '../.env';
if (file_exists($envFile)) {
    echo "✅ Arquivo .env encontrado<br>";
} else {
    echo "❌ Arquivo .env não encontrado<br>";
    echo "Criando arquivo .env...<br>";
    copy('../env.example', $envFile);
    echo "✅ Arquivo .env criado<br>";
}

// 2. Carregar configurações
echo "<h2>2. Carregando configurações</h2>";
try {
    require_once '../src/Config.php';
    Config::load();
    echo "✅ Configurações carregadas<br>";
    
    $dbConfig = Config::getDbConfig();
    echo "Host: " . $dbConfig['host'] . "<br>";
    echo "Database: " . $dbConfig['name'] . "<br>";
    echo "User: " . $dbConfig['user'] . "<br>";
    echo "Charset: " . $dbConfig['charset'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar configurações: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Testar conexão
echo "<h2>3. Testando conexão</h2>";
try {
    require_once '../src/db.php';
    $pdo = getDb();
    echo "✅ Conexão estabelecida com sucesso<br>";
    
    // Verificar versão do MySQL
    $stmt = $pdo->query('SELECT VERSION() as version');
    $version = $stmt->fetch();
    echo "Versão MySQL: " . $version['version'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Verificar tabelas
echo "<h2>4. Verificando tabelas</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tabelas encontradas: " . count($tabelas) . "<br>";
    
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
    
    foreach ($tabelasEsperadas as $tabela) {
        if (in_array($tabela, $tabelas)) {
            echo "✅ Tabela $tabela existe<br>";
        } else {
            echo "❌ Tabela $tabela não encontrada<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "<br>";
}

// 5. Verificar dados
echo "<h2>5. Verificando dados</h2>";
try {
    $tabelasComDados = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    
    foreach ($tabelasComDados as $tabela) {
        if (in_array($tabela, $tabelas)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
            $result = $stmt->fetch();
            echo "Tabela $tabela: " . $result['total'] . " registros<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao verificar dados: " . $e->getMessage() . "<br>";
}

echo "<h2>✅ Teste concluído!</h2>";
echo "<a href='dashboard.php'>Voltar ao Dashboard</a>";
?> 