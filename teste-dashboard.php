<?php
// Teste simples para diagnosticar o problema do dashboard
echo "<h1>Teste de Diagnóstico - Dashboard</h1>";

// 1. Testar carregamento do init.php
echo "<h2>1. Testando init.php</h2>";
try {
    require_once 'src/init.php';
    echo "✅ init.php carregado com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao carregar init.php: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Testar se função getDb existe
echo "<h2>2. Testando função getDb</h2>";
if (function_exists('getDb')) {
    echo "✅ Função getDb() existe<br>";
} else {
    echo "❌ Função getDb() NÃO existe<br>";
    exit;
}

// 3. Testar conexão com banco
echo "<h2>3. Testando conexão com banco</h2>";
try {
    $pdo = getDb();
    echo "✅ Conexão com banco estabelecida<br>";
    
    // Testar uma query simples
    $stmt = $pdo->query("SELECT 1 as teste");
    $result = $stmt->fetch();
    echo "✅ Query de teste executada: " . $result['teste'] . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    echo "<br><strong>Detalhes do erro:</strong><br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
}

// 4. Testar carregamento das classes
echo "<h2>4. Testando classes</h2>";
try {
    require_once 'src/Cliente.php';
    echo "✅ Cliente.php carregado<br>";
    
    require_once 'src/Pet.php';
    echo "✅ Pet.php carregado<br>";
    
    require_once 'src/Agendamento.php';
    echo "✅ Agendamento.php carregado<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar classes: " . $e->getMessage() . "<br>";
}

// 5. Testar queries do dashboard
echo "<h2>5. Testando queries do dashboard</h2>";
try {
    $pdo = getDb();
    
    // Verificar se tabelas existem
    $tables = ['clientes', 'pets', 'agendamentos'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabela '$table' existe<br>";
            
            // Contar registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $result = $stmt->fetch();
            echo "&nbsp;&nbsp;&nbsp;→ Total de registros: " . $result['total'] . "<br>";
        } else {
            echo "❌ Tabela '$table' NÃO existe<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro nas queries: " . $e->getMessage() . "<br>";
}

echo "<br><h2>Configurações atuais:</h2>";
echo "<pre>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'não definido') . "\n";
echo "APP_ROOT: " . (defined('APP_ROOT') ? APP_ROOT : 'não definido') . "\n";
echo "APP_NAME: " . (defined('APP_NAME') ? APP_NAME : 'não definido') . "\n";
echo "</pre>";
?>