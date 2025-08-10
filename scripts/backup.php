<?php
/**
 * Script de Backup Automático
 * Sistema Bichos do Bairro
 */

require_once __DIR__ . '/../src/init.php';

class BackupManager {
    private $backupPath;
    private $retentionDays;
    private $dbConfig;
    
    public function __construct() {
        $this->backupPath = __DIR__ . '/../backups';
        $this->retentionDays = 30;
        $this->dbConfig = Config::getDbConfig();
        
        // Criar diretório de backup se não existir
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Executa backup completo
     */
    public function executeBackup() {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            $filepath = $this->backupPath . '/' . $filename;
            
            echo "Iniciando backup...\n";
            
            // Comando mysqldump
            $command = sprintf(
                'mysqldump -h %s -P %s -u %s -p%s %s > %s',
                escapeshellarg($this->dbConfig['host']),
                escapeshellarg($this->dbConfig['port'] ?? '3306'),
                escapeshellarg($this->dbConfig['user']),
                escapeshellarg($this->dbConfig['pass']),
                escapeshellarg($this->dbConfig['name']),
                escapeshellarg($filepath)
            );
            
            // Executar backup
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            if ($returnCode === 0) {
                // Comprimir arquivo
                $this->compressFile($filepath);
                
                // Limpar backups antigos
                $this->cleanOldBackups();
                
                // Log do sucesso
                Logger::info('Backup executado com sucesso', [
                    'filename' => $filename,
                    'size' => $this->formatBytes(filesize($filepath . '.gz')),
                    'path' => $filepath . '.gz'
                ]);
                
                echo "✅ Backup concluído: $filename.gz\n";
                return true;
            } else {
                throw new Exception("Erro ao executar mysqldump: " . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            Logger::error('Erro no backup', ['error' => $e->getMessage()]);
            echo "❌ Erro no backup: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Comprime arquivo de backup
     */
    private function compressFile($filepath) {
        $gzFile = $filepath . '.gz';
        
        $input = fopen($filepath, 'rb');
        $output = gzopen($gzFile, 'wb');
        
        while (!feof($input)) {
            gzwrite($output, fread($input, 4096));
        }
        
        fclose($input);
        gzclose($output);
        
        // Remover arquivo original
        unlink($filepath);
        
        echo "📦 Arquivo comprimido: " . basename($gzFile) . "\n";
    }
    
    /**
     * Remove backups antigos
     */
    private function cleanOldBackups() {
        $cutoff = time() - ($this->retentionDays * 24 * 60 * 60);
        $files = glob($this->backupPath . '/backup_*.sql.gz');
        
        $removed = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $removed++;
            }
        }
        
        if ($removed > 0) {
            echo "🗑️ Removidos $removed backups antigos\n";
            Logger::info("Backups antigos removidos", ['count' => $removed]);
        }
    }
    
    /**
     * Lista backups disponíveis
     */
    public function listBackups() {
        $files = glob($this->backupPath . '/backup_*.sql.gz');
        $backups = [];
        
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => $this->formatBytes(filesize($file)),
                'date' => date('d/m/Y H:i:s', filemtime($file)),
                'path' => $file
            ];
        }
        
        // Ordenar por data (mais recente primeiro)
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
    
    /**
     * Restaura backup
     */
    public function restoreBackup($filename) {
        try {
            $filepath = $this->backupPath . '/' . $filename;
            
            if (!file_exists($filepath)) {
                throw new Exception("Arquivo de backup não encontrado: $filename");
            }
            
            echo "Iniciando restauração...\n";
            
            // Descomprimir arquivo
            $tempFile = $filepath . '.temp';
            $this->decompressFile($filepath, $tempFile);
            
            // Comando mysql para restauração
            $command = sprintf(
                'mysql -h %s -P %s -u %s -p%s %s < %s',
                escapeshellarg($this->dbConfig['host']),
                escapeshellarg($this->dbConfig['port'] ?? '3306'),
                escapeshellarg($this->dbConfig['user']),
                escapeshellarg($this->dbConfig['pass']),
                escapeshellarg($this->dbConfig['name']),
                escapeshellarg($tempFile)
            );
            
            $output = [];
            $returnCode = 0;
            exec($command . ' 2>&1', $output, $returnCode);
            
            // Remover arquivo temporário
            unlink($tempFile);
            
            if ($returnCode === 0) {
                Logger::info('Backup restaurado com sucesso', ['filename' => $filename]);
                echo "✅ Restauração concluída\n";
                return true;
            } else {
                throw new Exception("Erro na restauração: " . implode("\n", $output));
            }
            
        } catch (Exception $e) {
            Logger::error('Erro na restauração', ['error' => $e->getMessage()]);
            echo "❌ Erro na restauração: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Descomprime arquivo
     */
    private function decompressFile($gzFile, $outputFile) {
        $input = gzopen($gzFile, 'rb');
        $output = fopen($outputFile, 'wb');
        
        while (!gzeof($input)) {
            fwrite($output, gzread($input, 4096));
        }
        
        gzclose($input);
        fclose($output);
    }
    
    /**
     * Formata bytes para leitura humana
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Obtém estatísticas de backup
     */
    public function getStats() {
        $files = glob($this->backupPath . '/backup_*.sql.gz');
        $totalSize = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        return [
            'total_backups' => count($files),
            'total_size' => $this->formatBytes($totalSize),
            'retention_days' => $this->retentionDays,
            'backup_path' => $this->backupPath
        ];
    }
}

// Execução via linha de comando
if (php_sapi_name() === 'cli') {
    $backup = new BackupManager();
    
    $action = $argv[1] ?? 'backup';
    
    switch ($action) {
        case 'backup':
            $backup->executeBackup();
            break;
            
        case 'list':
            $backups = $backup->listBackups();
            echo "Backups disponíveis:\n";
            foreach ($backups as $b) {
                echo "- {$b['filename']} ({$b['size']}) - {$b['date']}\n";
            }
            break;
            
        case 'restore':
            $filename = $argv[2] ?? null;
            if ($filename) {
                $backup->restoreBackup($filename);
            } else {
                echo "Uso: php backup.php restore <filename>\n";
            }
            break;
            
        case 'stats':
            $stats = $backup->getStats();
            echo "Estatísticas de backup:\n";
            foreach ($stats as $key => $value) {
                echo "- $key: $value\n";
            }
            break;
            
        default:
            echo "Uso: php backup.php [backup|list|restore|stats]\n";
            break;
    }
}
?> 