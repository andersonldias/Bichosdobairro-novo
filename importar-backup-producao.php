<?php
/**
 * Script para Importar Backup na Produção
 * Sistema Bichos do Bairro
 */

// Carregar configurações
require_once 'vendor/autoload.php';

// Configurações de produção (ajuste conforme necessário)
$config = [
    'host' => 'localhost',
    'dbname' => 'bichosdobairro',
    'username' => 'root', // Ajuste para o usuário correto
    'password' => '', // Ajuste para a senha correta
    'charset' => 'utf8mb4'
];

echo "=== IMPORTAÇÃO DE BACKUP NA PRODUÇÃO ===\n\n";

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

// Solicitar credenciais
echo "2. Configuração de conexão:\n";
echo "Host: {$config['host']}\n";
echo "Database: {$config['dbname']}\n";
echo "Username: {$config['username']}\n";

$password = readline("Password (deixe vazio se não tiver): ");
if ($password) {
    $config['password'] = $password;
}

echo "\n3. Conectando ao banco de dados...\n";

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
    ]);
    
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n\n";
    echo "Verifique:\n";
    echo "1. Se o banco de dados existe\n";
    echo "2. Se as credenciais estão corretas\n";
    echo "3. Se o usuário tem permissões\n";
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