<?php
require_once 'src/init.php';

echo "<h1>Teste de Conexão Debug</h1>";

try {
    echo "<p>1. Init.php carregado com sucesso</p>";
    
    $pdo = getDb();
    echo "<p>2. Conexão PDO obtida com sucesso</p>";
    
    $stmt = $pdo->query('SELECT 1 as teste');
    $result = $stmt->fetch();
    echo "<p>3. Query executada: " . $result['teste'] . "</p>";
    
    echo "<p style='color: green;'>✅ Tudo funcionando!</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>