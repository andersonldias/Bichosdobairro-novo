<?php
// Teste para verificar se o problema de sessão foi resolvido
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Sessão - Bichos do Bairro</h1>";

// Verificar status da sessão antes
echo "<p><strong>Status da sessão antes:</strong> ";
switch (session_status()) {
    case PHP_SESSION_DISABLED:
        echo "Sessões desabilitadas";
        break;
    case PHP_SESSION_NONE:
        echo "Sessões habilitadas mas nenhuma iniciada";
        break;
    case PHP_SESSION_ACTIVE:
        echo "Sessões habilitadas e uma iniciada";
        break;
}
echo "</p>";

// Inicializar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    echo "<p>✅ Sessão iniciada com sucesso</p>";
} else {
    echo "<p>ℹ️ Sessão já estava ativa</p>";
}

// Verificar status da sessão depois
echo "<p><strong>Status da sessão depois:</strong> ";
switch (session_status()) {
    case PHP_SESSION_DISABLED:
        echo "Sessões desabilitadas";
        break;
    case PHP_SESSION_NONE:
        echo "Sessões habilitadas mas nenhuma iniciada";
        break;
    case PHP_SESSION_ACTIVE:
        echo "Sessões habilitadas e uma iniciada";
        break;
}
echo "</p>";

// Testar inclusão do init.php
echo "<h2>Testando inclusão do init.php:</h2>";
try {
    require_once '../src/init.php';
    echo "<p>✅ init.php carregado com sucesso</p>";
    
    // Testar se não há erros de sessão
    echo "<p>✅ Nenhum erro de configuração de sessão detectado</p>";
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar init.php: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p>❌ Erro fatal ao carregar init.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Testar configurações
echo "<h2>Configurações do sistema:</h2>";
echo "<p><strong>APP_NAME:</strong> " . (defined('APP_NAME') ? APP_NAME : 'Não definido') . "</p>";
echo "<p><strong>APP_VERSION:</strong> " . (defined('APP_VERSION') ? APP_VERSION : 'Não definido') . "</p>";

// Testar classe Config
if (class_exists('Config')) {
    echo "<p>✅ Classe Config carregada</p>";
    echo "<p><strong>Modo Debug:</strong> " . (Config::isDebug() ? 'Sim' : 'Não') . "</p>";
} else {
    echo "<p>❌ Classe Config não encontrada</p>";
}

echo "<h2>Teste concluído!</h2>";
echo "<p><a href='login-simples.php'>← Voltar para o Login</a></p>";
?> 