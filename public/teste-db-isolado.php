<?php
/**
 * Teste isolado da conexão DB
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste DB Isolado</h1>";

try {
    // Carregar apenas o essencial
    require_once '../src/Config.php';
    Config::load();
    
    echo "<p>✅ Config carregado</p>";
    
    // Testar conexão direta
    $config = Config::getDbConfig();
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_TIMEOUT => 10
    ];
    
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
    echo "<p>✅ Conexão PDO estabelecida</p>";
    
    // Teste simples
    $stmt = $pdo->query('SELECT 1 as teste');
    $result = $stmt->fetch();
    echo "<p>✅ Query teste: {$result['teste']}</p>";
    
    // Teste de timeout
    $pdo->exec("SET SESSION wait_timeout = 600");
    echo "<p>✅ Timeout configurado</p>";
    
    echo "<p><strong>🎉 Conexão funcionando perfeitamente!</strong></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Código: " . $e->getCode() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . ":" . $e->getLine() . "</p>";
}
?>