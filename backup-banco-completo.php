<?php
/**
 * Backup Completo do Banco de Dados
 * Sistema Bichos do Bairro
 */

echo "=== BACKUP COMPLETO DO BANCO DE DADOS ===\n\n";

// Configurações do banco
$dbConfig = [
    'host' => 'xmysql.bichosdobairro.com.br',
    'name' => 'bichosdobairro5',
    'user' => 'bichosdobairro5',
    'pass' => '!BdoB.1179!',
    'charset' => 'utf8mb4'
];

// Criar diretório de backup se não existir
if (!is_dir('backups')) {
    mkdir('backups', 0755, true);
    echo "✅ Diretório backups criado\n";
}

// Nome do arquivo de backup
$timestamp = date('Y-m-d_H-i-s');
$backupFile = "backups/backup_completo_{$timestamp}.sql";
$backupZip = "backups/backup_completo_{$timestamp}.zip";

echo "1. Conectando ao banco de dados...\n";

try {
    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "✅ Conexão estabelecida\n\n";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "\n";
    exit(1);
}

echo "2. Obtendo lista de tabelas...\n";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "✅ " . count($tabelas) . " tabelas encontradas:\n";
    foreach ($tabelas as $tabela) {
        echo "   - $tabela\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro ao listar tabelas: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Iniciando backup...\n";

// Abrir arquivo para escrita
$handle = fopen($backupFile, 'w');
if (!$handle) {
    echo "❌ Erro ao criar arquivo de backup\n";
    exit(1);
}

// Cabeçalho do backup
$header = "-- ========================================\n";
$header .= "-- BACKUP COMPLETO - SISTEMA BICHOS DO BAIRRO\n";
$header .= "-- Data/Hora: " . date('d/m/Y H:i:s') . "\n";
$header .= "-- Banco: {$dbConfig['name']}\n";
$header .= "-- Host: {$dbConfig['host']}\n";
$header .= "-- ========================================\n\n";
$header .= "SET FOREIGN_KEY_CHECKS=0;\n";
$header .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
$header .= "SET AUTOCOMMIT = 0;\n";
$header .= "START TRANSACTION;\n";
$header .= "SET time_zone = \"+00:00\";\n\n";

fwrite($handle, $header);

$totalTabelas = count($tabelas);
$tabelasProcessadas = 0;

foreach ($tabelas as $tabela) {
    $tabelasProcessadas++;
    echo "   Processando tabela $tabelasProcessadas/$totalTabelas: $tabela\n";
    
    try {
        // Obter estrutura da tabela
        $stmt = $pdo->query("SHOW CREATE TABLE `$tabela`");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $createTable = $result['Create Table'];
        
        // Escrever estrutura
        fwrite($handle, "-- Estrutura da tabela `$tabela`\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$tabela`;\n");
        fwrite($handle, $createTable . ";\n\n");
        
        // Obter dados da tabela
        $stmt = $pdo->query("SELECT * FROM `$tabela`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            fwrite($handle, "-- Dados da tabela `$tabela`\n");
            
            // Obter nomes das colunas
            $columns = array_keys($rows[0]);
            $columnNames = '`' . implode('`, `', $columns) . '`';
            
            // Inserir dados em lotes
            $batchSize = 100;
            $totalRows = count($rows);
            
            for ($i = 0; $i < $totalRows; $i += $batchSize) {
                $batch = array_slice($rows, $i, $batchSize);
                
                fwrite($handle, "INSERT INTO `$tabela` ($columnNames) VALUES\n");
                
                $values = [];
                foreach ($batch as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                
                fwrite($handle, implode(",\n", $values) . ";\n\n");
            }
            
            echo "     ✅ $totalRows registros exportados\n";
        } else {
            echo "     ℹ️  Tabela vazia\n";
        }
        
    } catch (Exception $e) {
        echo "     ❌ Erro na tabela $tabela: " . $e->getMessage() . "\n";
        continue;
    }
}

// Finalizar backup
$footer = "SET FOREIGN_KEY_CHECKS=1;\n";
$footer .= "COMMIT;\n\n";
$footer .= "-- Backup concluído em " . date('d/m/Y H:i:s') . "\n";

fwrite($handle, $footer);
fclose($handle);

echo "\n4. Backup concluído!\n";
echo "✅ Arquivo: $backupFile\n";

// Verificar tamanho do arquivo
$fileSize = filesize($backupFile);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);
echo "✅ Tamanho: $fileSizeMB MB\n";

// Criar arquivo ZIP
echo "\n5. Criando arquivo ZIP...\n";

$zip = new ZipArchive();
if ($zip->open($backupZip, ZipArchive::CREATE) === TRUE) {
    $zip->addFile($backupFile, basename($backupFile));
    $zip->close();
    
    echo "✅ Arquivo ZIP criado: $backupZip\n";
    
    $zipSize = filesize($backupZip);
    $zipSizeMB = round($zipSize / 1024 / 1024, 2);
    echo "✅ Tamanho ZIP: $zipSizeMB MB\n";
    
} else {
    echo "⚠️  Erro ao criar arquivo ZIP\n";
}

// Criar arquivo de informações
$infoFile = "backups/info_backup_{$timestamp}.txt";
$info = "=== INFORMAÇÕES DO BACKUP ===\n\n";
$info .= "Data/Hora: " . date('d/m/Y H:i:s') . "\n";
$info .= "Banco: {$dbConfig['name']}\n";
$info .= "Host: {$dbConfig['host']}\n";
$info .= "Tabelas: " . count($tabelas) . "\n";
$info .= "Arquivo SQL: " . basename($backupFile) . "\n";
$info .= "Arquivo ZIP: " . basename($backupZip) . "\n";
$info .= "Tamanho SQL: $fileSizeMB MB\n";
$info .= "Tamanho ZIP: $zipSizeMB MB\n\n";

$info .= "Tabelas incluídas:\n";
foreach ($tabelas as $tabela) {
    $info .= "- $tabela\n";
}

$info .= "\n=== INSTRUÇÕES DE RESTAURAÇÃO ===\n\n";
$info .= "1. Faça upload do arquivo ZIP para o novo servidor\n";
$info .= "2. Extraia o arquivo SQL\n";
$info .= "3. Execute o comando:\n";
$info .= "   mysql -u usuario -p nome_banco < backup_completo_$timestamp.sql\n";
$info .= "4. Ou use phpMyAdmin para importar o arquivo SQL\n\n";

$info .= "=== VERIFICAÇÃO PÓS-RESTAURAÇÃO ===\n\n";
$info .= "1. Verifique se todas as tabelas foram criadas\n";
$info .= "2. Confirme se os dados estão presentes\n";
$info .= "3. Teste o login do sistema\n";
$info .= "4. Verifique as funcionalidades principais\n";

file_put_contents($infoFile, $info);
echo "✅ Arquivo de informações: $infoFile\n";

echo "\n=== RESUMO DO BACKUP ===\n";
echo "✅ Backup SQL: $backupFile ($fileSizeMB MB)\n";
echo "✅ Backup ZIP: $backupZip ($zipSizeMB MB)\n";
echo "✅ Informações: $infoFile\n";
echo "✅ Tabelas processadas: " . count($tabelas) . "\n";
echo "✅ Status: BACKUP CONCLUÍDO COM SUCESSO!\n\n";

echo "=== PRÓXIMOS PASSOS ===\n";
echo "1. Faça download dos arquivos de backup\n";
echo "2. Envie para o novo servidor\n";
echo "3. Restaure o banco no novo servidor\n";
echo "4. Configure o sistema no novo ambiente\n\n";

echo "🎉 BACKUP PRONTO PARA TRANSFERÊNCIA! 🎉\n";
?> 