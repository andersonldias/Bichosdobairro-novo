<?php
/**
 * Conexão com banco de dados - Versão otimizada para servidor externo
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
        
        // Configurações específicas para servidor externo
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false, // Evitar conexões persistentes
            PDO::ATTR_TIMEOUT => 30, // Timeout de conexão
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}",
            PDO::MYSQL_ATTR_CONNECT_TIMEOUT => 30,
            PDO::MYSQL_ATTR_READ_TIMEOUT => 30,
            PDO::MYSQL_ATTR_WRITE_TIMEOUT => 30,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_LOCAL_INFILE => false,
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Para servidores sem SSL válido
        ];
        
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        
        // Configurações adicionais para servidor externo
        $pdo->exec("SET SESSION wait_timeout = 600"); // 10 minutos
        $pdo->exec("SET SESSION interactive_timeout = 600");
        
        return $pdo;
    } catch (Exception $e) {
        logError('Erro na conexão com banco de dados externo: ' . $e->getMessage());
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
            logError('Conexão perdida, tentando reconectar: ' . $e->getMessage());
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
                logError("Tentativa $attempts/$maxRetries - Reconectando: " . $e->getMessage());
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
    if (Config::isDebug()) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px;'>";
        echo "<strong>Erro de Conexão com Banco Externo:</strong> " . $e->getMessage();
        echo "</div>";
    }
    // Em produção, registrar erro e continuar
    logError('Falha na inicialização do banco externo: ' . $e->getMessage());
}
?>