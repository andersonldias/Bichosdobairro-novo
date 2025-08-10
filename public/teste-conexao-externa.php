<?php
// Teste específico para conexão com servidor MySQL externo
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Teste de Conexão com Servidor MySQL Externo</h2>";

// Configurações do servidor externo
$config = [
    'host' => 'xmysql.bichosdobairro.com.br',
    'name' => 'bichosdobairro5',
    'user' => 'bichosdobairro5',
    'pass' => '!BdoB.1179!',
    'charset' => 'utf8mb4',
    'port' => 3306
];

echo "<p>📡 Testando conexão com: {$config['host']}:{$config['port']}</p>";

// Teste 1: Verificar se o host é acessível
echo "<h3>1. Teste de Conectividade</h3>";
$connection = @fsockopen($config['host'], $config['port'], $errno, $errstr, 10);
if ($connection) {
    echo "✅ Host acessível na porta {$config['port']}<br>";
    fclose($connection);
} else {
    echo "❌ Erro de conectividade: $errstr ($errno)<br>";
}

// Teste 2: Conexão PDO básica
echo "<h3>2. Teste PDO Básico</h3>";
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
    
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}"
    ]);
    
    echo "✅ Conexão PDO básica estabelecida<br>";
    
    // Teste de query simples
    $stmt = $pdo->query("SELECT VERSION() as version, NOW() as now");
    $result = $stmt->fetch();
    echo "✅ MySQL Version: {$result['version']}<br>";
    echo "✅ Server Time: {$result['now']}<br>";
    
} catch (Exception $e) {
    echo "❌ Erro PDO básico: " . $e->getMessage() . "<br>";
}

// Teste 3: Conexão PDO com configurações para servidor externo
echo "<h3>3. Teste PDO com Configurações Otimizadas</h3>";
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false, // Evitar conexões persistentes em servidores externos
        PDO::ATTR_TIMEOUT => 30, // Timeout de 30 segundos
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}",
        PDO::MYSQL_ATTR_CONNECT_TIMEOUT => 30,
        PDO::MYSQL_ATTR_READ_TIMEOUT => 30,
        PDO::MYSQL_ATTR_WRITE_TIMEOUT => 30,
        // Configurações para servidores externos
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::MYSQL_ATTR_LOCAL_INFILE => false,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false // Para servidores sem SSL válido
    ];
    
    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
    
    echo "✅ Conexão PDO otimizada estabelecida<br>";
    
    // Teste de query com timeout
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10);
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '{$config['name']}';");
    $result = $stmt->fetch();
    echo "✅ Total de tabelas no banco: {$result['total']}<br>";
    
    // Verificar se as tabelas principais existem
    $tables = ['clientes', 'pets', 'agendamentos'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as exists_table FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
        $stmt->execute([$config['name'], $table]);
        $result = $stmt->fetch();
        if ($result['exists_table'] > 0) {
            echo "✅ Tabela '$table' existe<br>";
        } else {
            echo "❌ Tabela '$table' não encontrada<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Erro PDO otimizado: " . $e->getMessage() . "<br>";
}

// Teste 4: Verificar configurações do servidor
echo "<h3>4. Configurações do Servidor MySQL</h3>";
try {
    if (isset($pdo)) {
        $queries = [
            "SELECT @@wait_timeout as wait_timeout",
            "SELECT @@interactive_timeout as interactive_timeout",
            "SELECT @@max_connections as max_connections",
            "SELECT @@max_allowed_packet as max_allowed_packet",
            "SHOW VARIABLES LIKE 'ssl%'"
        ];
        
        foreach ($queries as $query) {
            try {
                $stmt = $pdo->query($query);
                $results = $stmt->fetchAll();
                foreach ($results as $row) {
                    if (isset($row['Variable_name'])) {
                        echo "📋 {$row['Variable_name']}: {$row['Value']}<br>";
                    } else {
                        foreach ($row as $key => $value) {
                            echo "📋 $key: $value<br>";
                        }
                    }
                }
            } catch (Exception $e) {
                echo "⚠️ Erro na query '$query': " . $e->getMessage() . "<br>";
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Erro ao verificar configurações: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Recomendações</h3>";
echo "<ul>";
echo "<li>✅ Use timeouts adequados para conexões externas</li>";
echo "<li>✅ Evite conexões persistentes em servidores compartilhados</li>";
echo "<li>✅ Configure SSL adequadamente se necessário</li>";
echo "<li>✅ Monitore timeouts de wait_timeout e interactive_timeout</li>";
echo "<li>✅ Use reconexão automática em caso de 'MySQL server has gone away'</li>";
echo "</ul>";
?>