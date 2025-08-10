<?php
/**
 * Diagnóstico Completo do Sistema
 */

echo "<h1>🔍 Diagnóstico Completo do Sistema</h1>";
echo "<style>body{font-family:Arial;} .ok{color:green;} .error{color:red;} .warning{color:orange;}</style>";

// 1. Teste básico PHP
echo "<h2>1. 🔧 Teste Básico PHP</h2>";
echo "<p class='ok'>✅ PHP funcionando: " . phpversion() . "</p>";

// 2. Teste de arquivos
echo "<h2>2. 📁 Verificação de Arquivos</h2>";
$arquivos = [
    '../src/Config.php',
    '../src/db.php',
    '../src/init.php',
    '../.env'
];

foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        echo "<p class='ok'>✅ $arquivo existe</p>";
    } else {
        echo "<p class='error'>❌ $arquivo NÃO EXISTE</p>";
    }
}

// 3. Teste de carregamento Config
echo "<h2>3. ⚙️ Teste Config</h2>";
try {
    require_once '../src/Config.php';
    Config::load();
    echo "<p class='ok'>✅ Config carregado</p>";
    
    if (Config::isDebug()) {
        echo "<p class='warning'>🐛 Modo DEBUG ativo</p>";
    } else {
        echo "<p class='ok'>🔒 Modo PRODUÇÃO</p>";
    }
    
    $dbConfig = Config::getDbConfig();
    echo "<p class='ok'>✅ DB Config: Host = {$dbConfig['host']}</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro Config: " . $e->getMessage() . "</p>";
}

// 4. Teste de conexão DB (sem init.php)
echo "<h2>4. 🗄️ Teste Conexão DB (Direto)</h2>";
try {
    require_once '../src/db.php';
    echo "<p class='ok'>✅ db.php carregado</p>";
    
    // Tentar conectar manualmente
    $pdo = initDb();
    echo "<p class='ok'>✅ Conexão DB estabelecida</p>";
    
    // Teste simples
    $stmt = $pdo->query('SELECT 1 as teste');
    $result = $stmt->fetch();
    echo "<p class='ok'>✅ Query teste: " . $result['teste'] . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro DB: " . $e->getMessage() . "</p>";
    echo "<p class='warning'>⚠️ Este pode ser o problema!</p>";
}

// 5. Teste init.php progressivo
echo "<h2>5. 🚀 Teste Init.php Progressivo</h2>";
try {
    echo "<p>🔄 Carregando init.php...</p>";
    
    // Ativar buffer de saída para capturar erros
    ob_start();
    
    require_once '../src/init.php';
    
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "<p class='ok'>✅ Init.php carregado sem erros</p>";
    } else {
        echo "<p class='warning'>⚠️ Output capturado: <pre>$output</pre></p>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "<p class='error'>❌ Erro Init: " . $e->getMessage() . "</p>";
    echo "<p class='error'>📁 Arquivo: " . $e->getFile() . "</p>";
    echo "<p class='error'>📍 Linha: " . $e->getLine() . "</p>";
}

// 6. Teste de headers
echo "<h2>6. 📤 Teste Headers</h2>";
if (headers_sent($file, $line)) {
    echo "<p class='error'>❌ Headers já enviados em $file:$line</p>";
} else {
    echo "<p class='ok'>✅ Headers não enviados ainda</p>";
}

// 7. Informações do servidor
echo "<h2>7. 🖥️ Informações do Servidor</h2>";
echo "<p>📊 Memória: " . ini_get('memory_limit') . "</p>";
echo "<p>⏱️ Timeout: " . ini_get('max_execution_time') . "s</p>";
echo "<p>📝 Log Errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p>👁️ Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";

echo "<h2>✅ Diagnóstico Concluído</h2>";
echo "<p><a href='corrigir-headers.php'>Testar Headers</a> | <a href='clientes.php'>Testar Clientes</a></p>";
?>