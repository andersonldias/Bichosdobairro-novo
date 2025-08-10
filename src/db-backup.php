<?php
/**
 * Conexão com banco de dados
 */

// Variável global para conexão PDO
global $pdo;

/**
 * Inicializar conexão com banco de dados
 */
function initDb() {
    global $pdo;
    
    try {
        $config = Config::getDbConfig();
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
        
        $pdo = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
        ]);
        
        return $pdo;
    } catch (Exception $e) {
        logError('Erro na conexão com banco de dados: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Obter conexão com banco de dados
 */
function getDb() {
    global $pdo;
    
    // Se $pdo não estiver definido, tentar reconectar
    if (!isset($pdo) || $pdo === null) {
        $pdo = initDb();
    }
    
    return $pdo;
}

// Inicializar conexão automaticamente
try {
    initDb();
} catch (Exception $e) {
    if (Config::isDebug()) {
        echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 5px;'>";
        echo "<strong>Erro de Conexão com Banco:</strong> " . $e->getMessage();
        echo "</div>";
    }
    // Em produção, redirecionar para página de erro
}