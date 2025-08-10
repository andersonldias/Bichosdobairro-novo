<?php
/**
 * Script para Importar Backup com Credenciais Configuráveis
 * Sistema Bichos do Bairro
 */

echo "=== IMPORTAÇÃO DE BACKUP - CONFIGURÁVEL ===\n\n";

// Verificar arquivo de backup
$backupFile = 'backups/backup_completo_2025-07-19_20-19-55.sql';

if (!file_exists($backupFile)) {
    echo "❌ Arquivo de backup não encontrado: $backupFile\n";
    echo "Verifique se o arquivo existe na pasta backups/\n";
    exit(1);
}

echo "1. Verificando arquivo de backup...\n";
echo "✅ Arquivo: $backupFile\n";
echo "✅ Tamanho: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n\n";

// Solicitar configurações
echo "2. Configuração de conexão:\n";

$host = readline("Host (localhost): ");
if (empty($host)) $host = 'localhost';

$dbname = readline("Database (bichosdobairro): ");
if (empty($dbname)) $dbname = 'bichosdobairro';

$username = readline("Username: ");
if (empty($username)) {
    echo "❌ Username é obrigatório!\n";
    exit(1);
}

$password = readline("Password: ");

echo "\n3. Conectando ao banco de dados...\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "Username: $username\n";

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n\n";
    echo "Verifique:\n";
    echo "1. Se o banco de dados existe\n";
    echo "2. Se as credenciais estão corretas\n";
    echo "3. Se o usuário tem permissões\n";
    echo "4. Se o host está correto\n";
    exit(1);
}

// Verificar se o banco está limpo
echo "4. Verificando estado do banco...\n";
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "✅ Banco está limpo (sem tabelas)\n\n";
    } else {
        echo "⚠️  Banco ainda tem " . count($tables) . " tabelas:\n";
        foreach ($tables as $table) {
            echo "   - $table\n";
        }
        echo "\n";
        
        $confirm = readline("Deseja limpar o banco antes de importar? (s/N): ");
        if (strtolower($confirm) === 's') {
            echo "Limpando banco...\n";
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS `$table`");
                echo "   - Tabela '$table' removida\n";
            }
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            echo "✅ Banco limpo!\n\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro ao verificar tabelas: " . $e->getMessage() . "\n";
    exit(1);
}

// Importar backup
echo "5. Importando backup...\n";

try {
    // Ler arquivo SQL
    $sql = file_get_contents($backupFile);
    
    if (empty($sql)) {
        echo "❌ Arquivo SQL está vazio!\n";
        exit(1);
    }
    
    echo "✅ Arquivo SQL lido (" . strlen($sql) . " bytes)\n";
    
    // Dividir em comandos SQL
    $commands = array_filter(
        array_map('trim', explode(';', $sql)),
        function($cmd) { return !empty($cmd) && !preg_match('/^--/', $cmd); }
    );
    
    echo "✅ " . count($commands) . " comandos SQL encontrados\n\n";
    
    // Executar comandos
    $pdo->beginTransaction();
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($commands as $i => $command) {
        if (empty(trim($command))) continue;
        
        try {
            $pdo->exec($command);
            $successCount++;
            
            if ($successCount % 10 == 0) {
                echo "   ✅ Executados $successCount comandos...\n";
            }
            
        } catch (PDOException $e) {
            $errorCount++;
            echo "   ❌ Erro no comando " . ($i + 1) . ": " . $e->getMessage() . "\n";
            echo "   Comando: " . substr($command, 0, 100) . "...\n";
        }
    }
    
    if ($errorCount == 0) {
        $pdo->commit();
        echo "\n✅ Importação concluída com sucesso!\n";
        echo "   - Comandos executados: $successCount\n";
        echo "   - Erros: $errorCount\n";
    } else {
        $pdo->rollBack();
        echo "\n❌ Importação falhou!\n";
        echo "   - Comandos executados: $successCount\n";
        echo "   - Erros: $errorCount\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Erro durante importação: " . $e->getMessage() . "\n";
    exit(1);
}

// Verificar importação
echo "\n6. Verificando importação...\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ Tabelas criadas: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }
    
    // Verificar dados principais
    echo "\n7. Verificando dados...\n";
    
    $checks = [
        'usuarios' => 'SELECT COUNT(*) as total FROM usuarios',
        'clientes' => 'SELECT COUNT(*) as total FROM clientes',
        'pets' => 'SELECT COUNT(*) as total FROM pets',
        'agendamentos' => 'SELECT COUNT(*) as total FROM agendamentos',
        'agendamentos_recorrentes' => 'SELECT COUNT(*) as total FROM agendamentos_recorrentes'
    ];
    
    foreach ($checks as $table => $query) {
        try {
            $stmt = $pdo->query($query);
            $result = $stmt->fetch();
            echo "   ✅ $table: " . $result['total'] . " registros\n";
        } catch (PDOException $e) {
            echo "   ⚠️  $table: Erro ao verificar\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Erro ao verificar importação: " . $e->getMessage() . "\n";
}

echo "\n🎉 IMPORTAÇÃO CONCLUÍDA!\n";
echo "O banco de dados foi restaurado com sucesso.\n";
echo "Agora você pode acessar o sistema normalmente.\n";

// Salvar configurações para uso futuro
$config = [
    'host' => $host,
    'dbname' => $dbname,
    'username' => $username,
    'password' => $password
];

file_put_contents('config_banco.json', json_encode($config, JSON_PRETTY_PRINT));
echo "\n✅ Configurações salvas em config_banco.json\n"; 