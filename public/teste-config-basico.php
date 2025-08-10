<?php
/**
 * Teste básico de configuração
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Configuração Básica</h1>";

try {
    echo "<p>1. Testando require Config.php...</p>";
    require_once '../src/Config.php';
    echo "<p>✅ Config.php carregado</p>";
    
    echo "<p>2. Testando Config::load()...</p>";
    Config::load();
    echo "<p>✅ Config::load() executado</p>";
    
    echo "<p>3. Testando Config::isDebug()...</p>";
    $debug = Config::isDebug();
    echo "<p>✅ Debug mode: " . ($debug ? 'ON' : 'OFF') . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ ERRO: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . "</p>";
    echo "<p>Linha: " . $e->getLine() . "</p>";
}

echo "<p>Headers enviados: " . (headers_sent() ? 'SIM' : 'NÃO') . "</p>";
?>