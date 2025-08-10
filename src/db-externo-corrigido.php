<?php
/**
 * Conexão com banco de dados - Versão compatível para servidor externo
 */

// Variável global para conexão PDO
global $pdo;

/**
 * Inicializar conexão com banco de dados externo
 */
function initDb() {
    global $pdo;
    
    try {
        $config = Config::getDbConfig();
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
        
        // Configurações básicas compatíveis
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false, // Evitar conexões persistentes
            PDO::ATTR_TIMEOUT => 30, // Timeout de conexão
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
        ];
        
        // Adicionar constantes MySQL apenas se existirem
        if (defined('PDO::MYSQL_ATTR_CONNECT_TIMEOUT')) {
            $options[PDO::MYSQL_ATTR_CONNECT_TIMEOUT] = 30;
        }
        if (defined('PDO::MYSQL_ATTR_READ_TIMEOUT')) {
            $options[PDO::MYSQL_ATTR_READ_TIMEOUT] = 30;
        }
        if (defined('PDO::MYSQL_ATTR_WRITE_TIMEOUT')) {
            $options[PDO::MYSQL_ATTR_WRITE_TIMEOUT] = 30;
        }
        if (defined('PDO::MYSQL_ATTR_USE_BUFFERED_QUERY')) {
            $options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = true;
        }
        if (defined('PDO::MYSQL_ATTR_LOCAL_INFILE')) {
            $options[PDO::MYSQL_ATTR_LOCAL_INFILE] = false;
        }
        if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        
        // Configurações adicionais via SQL (mais compatível)
        try {
            $pdo->exec("SET SESSION wait_timeout = 600"); // 10 minutos
            $pdo->exec("SET SESSION interactive_timeout = 600");
        } catch (Exception $e) {
            // Ignorar se não conseguir configurar timeouts
            if (function_exists('logError')) {
                logError('Aviso ao configurar timeouts: ' . $e->getMessage());
            }
        }
        
        return $pdo;
    } catch (Exception $e) {
        if (function_exists('logError')) {
            logError('Erro na conexão com banco de dados externo: ' . $e->getMessage());
        }
        throw $e;
    }
}

/**
 * Obter conexão com banco de dados com reconexão automática
 */
function getDb() {
    global $pdo;
    
    // Verificar se a conexão ainda está ativa
    if (isset($pdo)) {
        try {
            $pdo->query('SELECT 1');
            return $pdo;
        } catch (Exception $e) {
            // Conexão perdida, tentar reconectar
            if (function_exists('logError')) {
                logError('Conexão perdida, tentando reconectar: ' . $e->getMessage());
            }
            $pdo = null;
        }
    }
    
    // Se $pdo não estiver definido ou conexão perdida, reconectar
    if (!isset($pdo) || $pdo === null) {
        $pdo = initDb();
    }
    
    return $pdo;
}

/**
 * Executar query com retry automático em caso de conexão perdida
 */
function executeWithRetry($callback, $maxRetries = 3) {
    $attempts = 0;
    
    while ($attempts < $maxRetries) {
        try {
            return $callback(getDb());
        } catch (Exception $e) {
            $attempts++;
            
            // Se for erro de conexão perdida e ainda há tentativas
            if (strpos($e->getMessage(), 'MySQL server has gone away') !== false && $attempts < $maxRetries) {
                if (function_exists('logError')) {
                    logError("Tentativa $attempts/$maxRetries - Reconectando: " . $e->getMessage());
                }
                global $pdo;
                $pdo = null; // Forçar reconexão
                sleep(1); // Aguardar 1 segundo antes de tentar novamente
                continue;
            }
            
            throw $e;
        }
    }
}

// Inicializar conexão automaticamente
try {
    initDb();
} catch (Exception $e) {
    if (class_exists('Config') && Config::isDebug()) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px;'>";
        echo "<strong>Erro de Conexão com Banco Externo:</strong> " . $e->getMessage();
        echo "</div>";
    }
    // Em produção, registrar erro e continuar
    if (function_exists('logError')) {
        logError('Falha na inicialização do banco externo: ' . $e->getMessage());
    }
}
?>