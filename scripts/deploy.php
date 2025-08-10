<?php
/**
 * Script de Deploy
 * Sistema Bichos do Bairro
 */

require_once __DIR__ . '/../src/init.php';

class DeployManager {
    private $deployPath;
    private $backupPath;
    
    public function __construct() {
        $this->deployPath = __DIR__ . '/../';
        $this->backupPath = __DIR__ . '/../backups';
    }
    
    /**
     * Executa deploy completo
     */
    public function deploy() {
        try {
            echo "🚀 Iniciando deploy...\n";
            
            // 1. Verificar ambiente
            $this->checkEnvironment();
            
            // 2. Fazer backup antes do deploy
            $this->preDeployBackup();
            
            // 3. Atualizar configurações
            $this->updateConfigurations();
            
            // 4. Limpar cache
            $this->clearCache();
            
            // 5. Verificar permissões
            $this->checkPermissions();
            
            // 6. Testar sistema
            $this->testSystem();
            
            echo "✅ Deploy concluído com sucesso!\n";
            Logger::info('Deploy executado com sucesso');
            
        } catch (Exception $e) {
            echo "❌ Erro no deploy: " . $e->getMessage() . "\n";
            Logger::error('Erro no deploy', ['error' => $e->getMessage()]);
            return false;
        }
        
        return true;
    }
    
    /**
     * Verifica ambiente
     */
    private function checkEnvironment() {
        echo "🔍 Verificando ambiente...\n";
        
        // Verificar PHP
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            throw new Exception("PHP 7.4+ é necessário. Versão atual: " . PHP_VERSION);
        }
        
        // Verificar extensões
        $requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json', 'openssl'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                throw new Exception("Extensão $ext não está carregada");
            }
        }
        
        // Verificar conexão com banco
        global $pdo;
        try {
            $stmt = $pdo->query("SELECT 1");
            $stmt->fetch();
        } catch (Exception $e) {
            throw new Exception("Erro na conexão com banco: " . $e->getMessage());
        }
        
        echo "✅ Ambiente verificado\n";
    }
    
    /**
     * Backup antes do deploy
     */
    private function preDeployBackup() {
        echo "💾 Fazendo backup antes do deploy...\n";
        
        if (class_exists('BackupManager')) {
            require_once __DIR__ . '/backup.php';
            $backup = new BackupManager();
            $backup->executeBackup();
        } else {
            echo "⚠️ Sistema de backup não disponível\n";
        }
    }
    
    /**
     * Atualiza configurações
     */
    private function updateConfigurations() {
        echo "⚙️ Atualizando configurações...\n";
        
        // Verificar arquivo .env
        $envFile = $this->deployPath . '.env';
        if (!file_exists($envFile)) {
            $envExample = $this->deployPath . 'env.example';
            if (file_exists($envExample)) {
                copy($envExample, $envFile);
                echo "✅ Arquivo .env criado\n";
            }
        }
        
        // Configurar modo produção
        $this->setProductionMode();
        
        echo "✅ Configurações atualizadas\n";
    }
    
    /**
     * Define modo produção
     */
    private function setProductionMode() {
        $envFile = $this->deployPath . '.env';
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            
            // Atualizar configurações para produção
            $content = preg_replace('/APP_ENV=development/', 'APP_ENV=production', $content);
            $content = preg_replace('/APP_DEBUG=true/', 'APP_DEBUG=false', $content);
            $content = preg_replace('/DEVELOPMENT_MODE=true/', 'DEVELOPMENT_MODE=false', $content);
            $content = preg_replace('/SHOW_ERRORS=true/', 'SHOW_ERRORS=false', $content);
            
            file_put_contents($envFile, $content);
            echo "✅ Modo produção configurado\n";
        }
    }
    
    /**
     * Limpa cache
     */
    private function clearCache() {
        echo "🧹 Limpando cache...\n";
        
        if (class_exists('Cache')) {
            Cache::clear();
            echo "✅ Cache limpo\n";
        }
        
        // Limpar cache do PHP
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "✅ OPcache limpo\n";
        }
    }
    
    /**
     * Verifica permissões
     */
    private function checkPermissions() {
        echo "🔐 Verificando permissões...\n";
        
        $directories = ['logs', 'backups', 'cache', 'uploads'];
        
        foreach ($directories as $dir) {
            $path = $this->deployPath . $dir;
            if (is_dir($path)) {
                if (!is_writable($path)) {
                    chmod($path, 0755);
                    echo "✅ Permissões corrigidas para $dir\n";
                }
            }
        }
        
        echo "✅ Permissões verificadas\n";
    }
    
    /**
     * Testa sistema
     */
    private function testSystem() {
        echo "🧪 Testando sistema...\n";
        
        // Testar classes principais
        $classes = ['Config', 'Utils', 'Cliente', 'Pet', 'Agendamento'];
        foreach ($classes as $class) {
            if (!class_exists($class)) {
                throw new Exception("Classe $class não encontrada");
            }
        }
        
        // Testar funcionalidades básicas
        try {
            $clientes = Cliente::listarTodos();
            echo "✅ Teste de listagem: " . count($clientes) . " clientes\n";
        } catch (Exception $e) {
            throw new Exception("Erro no teste de listagem: " . $e->getMessage());
        }
        
        echo "✅ Sistema testado\n";
    }
    
    /**
     * Rollback do deploy
     */
    public function rollback() {
        echo "🔄 Iniciando rollback...\n";
        
        try {
            // Listar backups disponíveis
            if (class_exists('BackupManager')) {
                require_once __DIR__ . '/backup.php';
                $backup = new BackupManager();
                $backups = $backup->listBackups();
                
                if (!empty($backups)) {
                    $latestBackup = $backups[0]['filename'];
                    echo "📦 Restaurando backup: $latestBackup\n";
                    $backup->restoreBackup($latestBackup);
                    echo "✅ Rollback concluído\n";
                } else {
                    echo "⚠️ Nenhum backup disponível para rollback\n";
                }
            }
            
        } catch (Exception $e) {
            echo "❌ Erro no rollback: " . $e->getMessage() . "\n";
            Logger::error('Erro no rollback', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Status do sistema
     */
    public function status() {
        echo "📊 Status do Sistema\n";
        echo "==================\n";
        
        // Informações do sistema
        echo "PHP Version: " . PHP_VERSION . "\n";
        echo "Sistema: " . PHP_OS . "\n";
        echo "Memória: " . ini_get('memory_limit') . "\n";
        echo "Upload: " . ini_get('upload_max_filesize') . "\n";
        
        // Status do banco
        global $pdo;
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
            $clientes = $stmt->fetch()['total'];
            echo "Clientes: $clientes\n";
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
            $pets = $stmt->fetch()['total'];
            echo "Pets: $pets\n";
            
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
            $agendamentos = $stmt->fetch()['total'];
            echo "Agendamentos: $agendamentos\n";
        } catch (Exception $e) {
            echo "Erro ao verificar banco: " . $e->getMessage() . "\n";
        }
        
        // Status de cache
        if (class_exists('Cache')) {
            $cacheStats = Cache::getStats();
            echo "Cache: {$cacheStats['files']} arquivos, {$cacheStats['size_formatted']}\n";
        }
        
        // Status de logs
        if (class_exists('Logger')) {
            $logStats = Logger::getStats();
            echo "Logs: " . count($logStats) . " níveis configurados\n";
        }
    }
}

// Execução via linha de comando
if (php_sapi_name() === 'cli') {
    $deploy = new DeployManager();
    
    $action = $argv[1] ?? 'deploy';
    
    switch ($action) {
        case 'deploy':
            $deploy->deploy();
            break;
            
        case 'rollback':
            $deploy->rollback();
            break;
            
        case 'status':
            $deploy->status();
            break;
            
        default:
            echo "Uso: php deploy.php [deploy|rollback|status]\n";
            break;
    }
}
?> 