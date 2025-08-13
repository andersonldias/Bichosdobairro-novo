<?php
/**
 * Conexão com Banco de Dados - Versão Compatível com Hospedagem Compartilhada
 * Sistema Bichos do Bairro
 * 
 * Esta versão funciona SEM Composer e SEM dependências externas
 */

// Carregar configurações
require_once __DIR__ . '/Config.php';
Config::load();

// Configurações do banco
$dbConfig = Config::getDbConfig();

// DSN de conexão
$dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";

// Opções do PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
];

// Configurações específicas para hospedagem compartilhada
if (Config::get('APP_ENV') === 'production') {
    $options[PDO::ATTR_PERSISTENT] = false; // Desabilitar conexões persistentes em produção
}

// Inicializar variável global
$pdo = null;

try {
    // Criar conexão PDO
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
    
    // Configurar timezone do banco (se suportado)
    try {
        $timezone = Config::get('APP_TIMEZONE');
        if ($timezone === 'America/Sao_Paulo') {
            $timezone = '-03:00'; // Offset para São Paulo
        }
        $pdo->exec("SET time_zone = '$timezone'");
    } catch (Exception $e) {
        // Ignorar erro de timezone se não suportado
    }
    
    // Log de sucesso (apenas em desenvolvimento)
    if (Config::isDevelopment()) {
        error_log("Conexão com banco estabelecida com sucesso");
    }
    
} catch (PDOException $e) {
    // Log do erro
    error_log("Erro na conexão com banco: " . $e->getMessage());
    
    // Definir $pdo como null em caso de erro
    $pdo = null;
    
    // Em produção, mostrar erro genérico
    if (Config::get('APP_ENV') === 'production') {
        die("Erro na conexão com o banco de dados. Entre em contato com o administrador.");
    } else {
        // Em desenvolvimento, mostrar erro detalhado
        die("Erro na conexão com banco: " . $e->getMessage());
    }
}

/**
 * Função helper para executar queries com tratamento de erro
 */
function executeQuery($sql, $params = []) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Erro na query: " . $e->getMessage() . " SQL: " . $sql);
        throw $e;
    }
}

/**
 * Função helper para buscar uma linha
 */
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

/**
 * Função helper para buscar múltiplas linhas
 */
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Função helper para inserir dados
 */
function insert($table, $data) {
    global $pdo;
    
    $fields = array_keys($data);
    $placeholders = ':' . implode(', :', $fields);
    $fieldList = implode(', ', $fields);
    
    $sql = "INSERT INTO $table ($fieldList) VALUES ($placeholders)";
    
    $stmt = executeQuery($sql, $data);
    return $pdo->lastInsertId();
}

/**
 * Função helper para atualizar dados
 */
function update($table, $data, $where, $whereParams = []) {
    $fields = [];
    foreach (array_keys($data) as $field) {
        $fields[] = "$field = :$field";
    }
    
    $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE $where";
    $params = array_merge($data, $whereParams);
    
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Função helper para deletar dados
 */
function delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    $stmt = executeQuery($sql, $params);
    return $stmt->rowCount();
}

/**
 * Função helper para contar registros
 */
function countRecords($table, $where = '1', $params = []) {
    $sql = "SELECT COUNT(*) as total FROM $table WHERE $where";
    $result = fetchOne($sql, $params);
    return (int) $result['total'];
}

/**
 * Função helper para verificar se tabela existe
 */
function tableExists($tableName) {
    global $pdo;
    
    try {
        $sql = "SHOW TABLES LIKE :table";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['table' => $tableName]);
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Função helper para obter informações da tabela
 */
function getTableInfo($tableName) {
    global $pdo;
    
    try {
        $sql = "DESCRIBE $tableName";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
} 

function getDb() {
    global $pdo;
    
    // Se $pdo for null, tentar reconectar
    if ($pdo === null) {
        // Recarregar este arquivo para tentar reconectar
        $dbConfig = Config::getDbConfig();
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset={$dbConfig['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$dbConfig['charset']}"
        ];
        
        try {
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
        } catch (PDOException $e) {
            error_log("Erro na reconexão: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}