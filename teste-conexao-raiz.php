<?php
echo "<h1>Teste de Conexão - Executado da Raiz</h1>";

try {
    // Incluir arquivos da pasta src
    require_once 'src/init.php';
    echo "✅ Arquivos incluídos com sucesso<br>";
    
    // Testar conexão
    $pdo = getDb();
    echo "✅ Conexão PDO obtida<br>";
    
    // Testar query simples
    $stmt = $pdo->query('SELECT 1 as teste');
    $result = $stmt->fetch();
    echo "✅ Query de teste executada: " . $result['teste'] . "<br>";
    
    // Verificar tabelas
    $tables = ['usuarios', 'clientes', 'pets', 'agendamentos'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $count = $stmt->fetch()['total'];
            echo "✅ Tabela $table: $count registros<br>";
        } catch (Exception $e) {
            echo "❌ Tabela $table: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2 style='color: green;'>🎉 Sistema funcionando perfeitamente!</h2>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro: " . $e->getMessage() . "</h2>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
