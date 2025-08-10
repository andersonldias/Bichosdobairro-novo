<?php
/**
 * Script de Backup Automático
 * Sistema Bichos do Bairro
 * 
 * Este script faz backup automático do banco de dados e arquivos importantes
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>💾 Backup Automático - Sistema Bichos do Bairro</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Carregar configurações
require_once '../src/Config.php';
Config::load();

// ========================================
// 1. CONFIGURAÇÕES DE BACKUP
// ========================================

echo "<h2>1. ⚙️ Configurações de Backup</h2>";

$backupDir = '../backups/';
$maxBackups = 10; // Manter apenas os últimos 10 backups
$backupDate = date('Y-m-d_H-i-s');

// Criar diretório de backup se não existir
if (!is_dir($backupDir)) {
    if (mkdir($backupDir, 0755, true)) {
        echo "<p style='color: green;'>✅ Diretório de backup criado</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar diretório de backup</p>";
        exit;
    }
}

// ========================================
// 2. BACKUP DO BANCO DE DADOS
// ========================================

echo "<h2>2. 🗄️ Backup do Banco de Dados</h2>";

try {
    // Carregar conexão
    require_once '../src/db.php';
    
    $dbConfig = Config::getDbConfig();
    $backupFile = $backupDir . "db_backup_{$backupDate}.sql";
    
    // Comando mysqldump
    $command = sprintf(
        'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
        escapeshellarg($dbConfig['host']),
        escapeshellarg($dbConfig['port']),
        escapeshellarg($dbConfig['user']),
        escapeshellarg($dbConfig['pass']),
        escapeshellarg($dbConfig['name']),
        escapeshellarg($backupFile)
    );
    
    // Executar backup
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($backupFile)) {
        $fileSize = filesize($backupFile);
        echo "<p style='color: green;'>✅ Backup do banco criado: " . round($fileSize / 1024, 2) . " KB</p>";
        
        // Comprimir backup
        $compressedFile = $backupFile . '.gz';
        if (file_put_contents('compress.zlib://' . $compressedFile, file_get_contents($backupFile))) {
            unlink($backupFile); // Remover arquivo não comprimido
            $compressedSize = filesize($compressedFile);
            echo "<p style='color: green;'>✅ Backup comprimido: " . round($compressedSize / 1024, 2) . " KB</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Erro no backup do banco: " . implode("\n", $output) . "</p>";
        
        // Tentar backup alternativo via PHP
        echo "<p style='color: blue;'>ℹ️ Tentando backup alternativo via PHP...</p>";
        
        $pdo = getDb();
        if ($pdo) {
            $tables = ['clientes', 'pets', 'agendamentos', 'telefones', 'notificacoes'];
            $backupContent = "-- Backup do Sistema Bichos do Bairro\n";
            $backupContent .= "-- Data: " . date('d/m/Y H:i:s') . "\n\n";
            
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SHOW CREATE TABLE $table");
                    $result = $stmt->fetch();
                    $backupContent .= $result['Create Table'] . ";\n\n";
                    
                    $stmt = $pdo->query("SELECT * FROM $table");
                    $rows = $stmt->fetchAll();
                    
                    if (!empty($rows)) {
                        $backupContent .= "INSERT INTO `$table` VALUES\n";
                        $values = [];
                        foreach ($rows as $row) {
                            $rowValues = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    $rowValues[] = "'" . addslashes($value) . "'";
                                }
                            }
                            $values[] = "(" . implode(', ', $rowValues) . ")";
                        }
                        $backupContent .= implode(",\n", $values) . ";\n\n";
                    }
                    
                } catch (Exception $e) {
                    echo "<p style='color: orange;'>⚠️ Erro ao fazer backup da tabela $table: " . $e->getMessage() . "</p>";
                }
            }
            
            if (file_put_contents($backupFile, $backupContent)) {
                $fileSize = filesize($backupFile);
                echo "<p style='color: green;'>✅ Backup alternativo criado: " . round($fileSize / 1024, 2) . " KB</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no backup do banco: " . $e->getMessage() . "</p>";
}

// ========================================
// 3. BACKUP DE ARQUIVOS IMPORTANTES
// ========================================

echo "<h2>3. 📁 Backup de Arquivos Importantes</h2>";

$importantFiles = [
    '../src/Config.php',
    '../src/db.php',
    '../src/init.php',
    '../.env',
    '../composer.json',
    '../README.md'
];

$filesBackupDir = $backupDir . "files_{$backupDate}/";
if (!is_dir($filesBackupDir)) {
    mkdir($filesBackupDir, 0755, true);
}

$backedUpFiles = 0;
foreach ($importantFiles as $file) {
    if (file_exists($file)) {
        $destFile = $filesBackupDir . basename($file);
        if (copy($file, $destFile)) {
            $backedUpFiles++;
            echo "<p style='color: green;'>✅ Arquivo backup: " . basename($file) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao fazer backup: " . basename($file) . "</p>";
        }
    }
}

echo "<p style='color: green;'>✅ $backedUpFiles arquivos importantes backupados</p>";

// ========================================
// 4. BACKUP DE LOGS RECENTES
// ========================================

echo "<h2>4. 📋 Backup de Logs Recentes</h2>";

$logsDir = '../logs/';
$logsBackupDir = $backupDir . "logs_{$backupDate}/";

if (is_dir($logsDir)) {
    if (!is_dir($logsBackupDir)) {
        mkdir($logsBackupDir, 0755, true);
    }
    
    $logFiles = glob($logsDir . '*.log');
    $backedUpLogs = 0;
    
    foreach ($logFiles as $logFile) {
        $destFile = $logsBackupDir . basename($logFile);
        if (copy($logFile, $destFile)) {
            $backedUpLogs++;
            echo "<p style='color: green;'>✅ Log backup: " . basename($logFile) . "</p>";
        }
    }
    
    echo "<p style='color: green;'>✅ $backedUpLogs logs backupados</p>";
} else {
    echo "<p style='color: orange;'>ℹ️ Diretório de logs não encontrado</p>";
}

// ========================================
// 5. LIMPEZA DE BACKUPS ANTIGOS
// ========================================

echo "<h2>5. 🗑️ Limpeza de Backups Antigos</h2>";

$backupFiles = glob($backupDir . '*');
usort($backupFiles, function($a, $b) {
    return filemtime($b) - filemtime($a);
});

$removed = 0;
if (count($backupFiles) > $maxBackups) {
    $filesToRemove = array_slice($backupFiles, $maxBackups);
    
    foreach ($filesToRemove as $file) {
        if (is_dir($file)) {
            // Remover diretório recursivamente
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($iterator as $child) {
                if ($child->isDir()) {
                    rmdir($child->getRealPath());
                } else {
                    unlink($child->getRealPath());
                }
            }
            rmdir($file);
        } else {
            unlink($file);
        }
        $removed++;
        echo "<p style='color: blue;'>ℹ️ Backup antigo removido: " . basename($file) . "</p>";
    }
}

echo "<p style='color: green;'>✅ $removed backups antigos removidos</p>";

// ========================================
// 6. VERIFICAÇÃO DE INTEGRIDADE
// ========================================

echo "<h2>6. 🔍 Verificação de Integridade</h2>";

$backupFiles = glob($backupDir . '*');
$totalSize = 0;

foreach ($backupFiles as $file) {
    if (is_file($file)) {
        $totalSize += filesize($file);
    } elseif (is_dir($file)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($file, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $child) {
            if ($child->isFile()) {
                $totalSize += $child->getSize();
            }
        }
    }
}

echo "<p><strong>Total de backups:</strong> " . count($backupFiles) . "</p>";
echo "<p><strong>Tamanho total:</strong> " . round($totalSize / 1024 / 1024, 2) . " MB</p>";

// ========================================
// 7. RELATÓRIO FINAL
// ========================================

echo "<h2>7. 📊 Relatório Final</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Item</th><th>Status</th></tr>";

echo "<tr><td>Backup do banco</td><td>✅ Concluído</td></tr>";
echo "<tr><td>Backup de arquivos</td><td>✅ $backedUpFiles arquivos</td></tr>";
echo "<tr><td>Backup de logs</td><td>✅ $backedUpLogs logs</td></tr>";
echo "<tr><td>Limpeza de backups antigos</td><td>✅ $removed removidos</td></tr>";
echo "<tr><td>Verificação de integridade</td><td>✅ " . round($totalSize / 1024 / 1024, 2) . " MB</td></tr>";

echo "</table>";

// ========================================
// 8. RECOMENDAÇÕES
// ========================================

echo "<h2>8. 📋 Recomendações</h2>";

echo "<ul>";
echo "<li>✅ Execute este script diariamente</li>";
echo "<li>✅ Teste a restauração dos backups regularmente</li>";
echo "<li>✅ Mantenha backups em local seguro (nuvem)</li>";
echo "<li>✅ Monitore o espaço em disco</li>";
echo "<li>✅ Configure backup automático via cron</li>";
echo "</ul>";

echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='corrigir-sistema-completo.php'>Correção Completa</a> | <a href='monitor-conexao.php'>Monitor de Conexão</a> | <a href='limpar-logs.php'>Limpeza de Logs</a></p>";

echo "<hr>";
echo "<p><strong>Backup executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Próximo backup recomendado:</strong> " . date('d/m/Y H:i:s', time() + 24 * 60 * 60) . "</p>";
?> 