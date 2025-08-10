<?php
/**
 * Teste simples sem incluir init.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste Simples - SEM init.php</h1>";
echo "<p>Este arquivo está funcionando!</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Hora atual: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Headers enviados: " . (headers_sent() ? 'SIM' : 'NÃO') . "</p>";

// Testar se conseguimos acessar arquivos
if (file_exists('../src/init.php')) {
    echo "<p>✅ init.php existe</p>";
} else {
    echo "<p>❌ init.php NÃO existe</p>";
}

if (file_exists('../src/Config.php')) {
    echo "<p>✅ Config.php existe</p>";
} else {
    echo "<p>❌ Config.php NÃO existe</p>";
}

echo "<p><strong>Se você está vendo esta mensagem, o problema NÃO é com o servidor PHP!</strong></p>";
?>