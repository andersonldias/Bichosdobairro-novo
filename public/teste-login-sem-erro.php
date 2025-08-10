<?php
// Teste para verificar se o login funciona sem erros de sessão
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Login Sem Erro - Bichos do Bairro</h1>";

// Verificar se há erros antes de começar
$errorOutput = '';
set_error_handler(function($severity, $message, $file, $line) use (&$errorOutput) {
    $errorOutput .= "Erro: $message em $file:$line\n";
});

// Inicializar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>✅ Sessão iniciada com sucesso</p>";
} else {
    echo "<p>ℹ️ Sessão já estava ativa</p>";
}

// Testar inclusão do init.php
echo "<h2>Testando inclusão do init.php:</h2>";
try {
    require_once '../src/init.php';
    echo "<p>✅ init.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar init.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Testar inclusão do Auth.php
echo "<h2>Testando inclusão do Auth.php:</h2>";
try {
    require_once '../src/Auth.php';
    echo "<p>✅ Auth.php carregado com sucesso</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar Auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Testar criação da instância Auth
echo "<h2>Testando criação da instância Auth:</h2>";
try {
    $auth = new Auth();
    echo "<p>✅ Instância Auth criada com sucesso</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao criar instância Auth: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Verificar se houve erros durante o processo
if (!empty($errorOutput)) {
    echo "<h2>❌ Erros detectados:</h2>";
    echo "<pre>" . htmlspecialchars($errorOutput) . "</pre>";
} else {
    echo "<h2>✅ Nenhum erro detectado!</h2>";
}

// Restaurar handler de erro padrão
restore_error_handler();

echo "<h2>Teste concluído!</h2>";
echo "<p><a href='login-simples.php'>← Voltar para o Login</a></p>";
echo "<p><a href='teste-sessao.php'>🧪 Testar Sessão</a></p>";
?> 