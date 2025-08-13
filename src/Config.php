<?php
/**
 * Classe de Configuração - Versão Compatível com Hospedagem Compartilhada
 * Sistema Bichos do Bairro
 * 
 * Esta versão funciona SEM Composer e SEM dependências externas
 */

class Config {
    private static $config = [];
    private static $loaded = false;
    
    /**
     * Carrega configurações do arquivo .env ou usa valores padrão
     */
    public static function load() {
        if (self::$loaded) {
            return;
        }
        
        // Configurações padrão (fallback)
        self::$config = [
            // Banco de dados
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'seu_banco_de_dados',
            'DB_USER' => 'seu_usuario_banco',
            'DB_PASS' => 'sua_senha_banco',
            'DB_CHARSET' => 'utf8mb4',
            'DB_PORT' => '3306',
            
            // Aplicação
            'APP_NAME' => 'Bichos do Bairro',
            'APP_ENV' => 'development',
            'APP_DEBUG' => true,
            'APP_URL' => 'http://localhost',
            'APP_TIMEZONE' => 'America/Sao_Paulo',
            'APP_LOCALE' => 'pt_BR',
            'APP_VERSION' => '1.0.0',
            
            // Segurança
            'APP_KEY' => 'base64:chave_secreta_padrao_32_caracteres_aqui',
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
            'CSRF_TOKEN_LIFETIME' => '60',
            
            // Logs
            'LOG_CHANNEL' => 'file',
            'LOG_LEVEL' => 'error',
            'LOG_MAX_FILES' => '30',
            
            // Cache
            'CACHE_DRIVER' => 'file',
            'CACHE_TTL' => '3600',
            
            // Backup
            'BACKUP_ENABLED' => true,
            'BACKUP_PATH' => './backups',
            'BACKUP_RETENTION_DAYS' => '30',
            
            // Desenvolvimento
            'DEVELOPMENT_MODE' => false,
            'SHOW_ERRORS' => false,
            'ENABLE_DEBUG_BAR' => false
        ];
        
        // Tentar carregar arquivo .env
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            self::loadEnvFile($envFile);
        }
        
        // Definir timezone
        date_default_timezone_set(self::$config['APP_TIMEZONE']);
        
        self::$loaded = true;
    }
    
    /**
     * Carrega arquivo .env manualmente (sem dependências)
     */
    private static function loadEnvFile($filepath) {
        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Verificar se tem sinal de igual
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remover aspas
                if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                self::$config[$key] = $value;
            }
        }
    }
    
    /**
     * Obtém uma configuração
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config[$key] ?? $default;
    }
    
    /**
     * Define uma configuração
     */
    public static function set($key, $value) {
        if (!self::$loaded) {
            self::load();
        }
        
        self::$config[$key] = $value;
    }
    
    /**
     * Obtém configurações do banco de dados
     */
    public static function getDbConfig() {
        if (!self::$loaded) {
            self::load();
        }
        
        return [
            'host' => self::$config['DB_HOST'],
            'name' => self::$config['DB_NAME'],
            'user' => self::$config['DB_USER'],
            'pass' => self::$config['DB_PASS'],
            'charset' => self::$config['DB_CHARSET'],
            'port' => self::$config['DB_PORT']
        ];
    }
    
    /**
     * Verifica se está em modo desenvolvimento
     */
    public static function isDevelopment() {
        return self::get('APP_ENV') === 'development' || self::get('DEVELOPMENT_MODE') === true;
    }
    
    /**
     * Verifica se debug está ativo
     */
    public static function isDebug() {
        return self::get('APP_DEBUG') === true || self::isDevelopment();
    }
    
    /**
     * Obtém todas as configurações
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config;
    }
    
    /**
     * Obtém configurações da aplicação
     */
    public static function getAppConfig($key = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        $appConfig = [
            'name' => self::$config['APP_NAME'],
            'version' => self::$config['APP_VERSION'],
            'env' => self::$config['APP_ENV'],
            'debug' => self::$config['APP_DEBUG'],
            'url' => self::$config['APP_URL'],
            'timezone' => self::$config['APP_TIMEZONE'],
            'locale' => self::$config['APP_LOCALE'],
            'items_per_page' => 20
        ];
        
        if ($key === null) {
            return $appConfig;
        }
        
        return $appConfig[$key] ?? null;
    }
    
    /**
     * Salva configurações no arquivo .env
     */
    public static function saveEnv($configs = []) {
        $envFile = __DIR__ . '/../.env';
        $content = "# ========================================\n";
        $content .= "# CONFIGURAÇÕES DO SISTEMA BICHOS DO BAIRRO\n";
        $content .= "# ========================================\n\n";
        
        foreach ($configs as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $content .= "$key=$value\n";
        }
        
        return file_put_contents($envFile, $content) !== false;
    }
} 