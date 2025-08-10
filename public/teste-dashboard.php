<?php
// Teste para verificar se o dashboard está protegido por login
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Proteção do Dashboard</h1>";

// Verificar se a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>Status da Sessão:</h2>";
echo "<p><strong>Status:</strong> ";
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

echo "<h2>Variáveis de Sessão:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p>✅ <strong>usuario_id:</strong> " . htmlspecialchars($_SESSION['usuario_id']) . "</p>";
} else {
    echo "<p>❌ <strong>usuario_id:</strong> Não definido</p>";
}

if (isset($_SESSION['usuario_nome'])) {
    echo "<p>✅ <strong>usuario_nome:</strong> " . htmlspecialchars($_SESSION['usuario_nome']) . "</p>";
} else {
    echo "<p>❌ <strong>usuario_nome:</strong> Não definido</p>";
}

echo "<h2>Teste de Acesso ao Dashboard:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p>✅ Usuário logado - pode acessar o dashboard</p>";
    echo "<p><a href='dashboard.php'>🔗 Acessar Dashboard</a></p>";
} else {
    echo "<p>❌ Usuário não logado - será redirecionado para login</p>";
    echo "<p><a href='login-simples.php'>🔗 Fazer Login</a></p>";
}

echo "<h2>Links Úteis:</h2>";
echo "<p><a href='login-simples.php'>🚀 Login</a></p>";
echo "<p><a href='credenciais-teste.php'>🔑 Credenciais de Teste</a></p>";
echo "<p><a href='dashboard.php'>📊 Dashboard (será redirecionado se não logado)</a></p>";
?> 