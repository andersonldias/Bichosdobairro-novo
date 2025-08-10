<?php
// Teste para verificar se a classe Auth está sendo carregada corretamente
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Carregamento da Classe Auth</h1>";

// Verificar se a sessão está ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h2>1. Verificando inclusão do init.php:</h2>";
try {
    $initPath = __DIR__ . '/../src/init.php';
    if (file_exists($initPath)) {
        require_once $initPath;
        echo "<p>✅ init.php carregado com sucesso</p>";
    } else {
        echo "<p>❌ Arquivo init.php não encontrado em: " . htmlspecialchars($initPath) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar init.php: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p>❌ Erro fatal ao carregar init.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>2. Verificando inclusão do Auth.php:</h2>";
try {
    $authPath = __DIR__ . '/../src/Auth.php';
    if (file_exists($authPath)) {
        require_once $authPath;
        echo "<p>✅ Auth.php carregado com sucesso</p>";
    } else {
        echo "<p>❌ Arquivo Auth.php não encontrado em: " . htmlspecialchars($authPath) . "</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Erro ao carregar Auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p>❌ Erro fatal ao carregar Auth.php: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>3. Verificando se a classe Auth existe:</h2>";
if (class_exists('Auth')) {
    echo "<p>✅ Classe Auth encontrada</p>";
} else {
    echo "<p>❌ Classe Auth não encontrada</p>";
}

echo "<h2>4. Verificando se a função getDb existe:</h2>";
if (function_exists('getDb')) {
    echo "<p>✅ Função getDb encontrada</p>";
} else {
    echo "<p>❌ Função getDb não encontrada</p>";
}

echo "<h2>5. Testando criação da instância Auth:</h2>";
try {
    $auth = new Auth();
    echo "<p>✅ Instância Auth criada com sucesso</p>";
    
    // Testar método buscarUsuario
    echo "<h2>6. Testando método buscarUsuario:</h2>";
    $usuario = $auth->buscarUsuario('admin@bichosdobairro.com');
    if ($usuario) {
        echo "<p>✅ Usuário admin encontrado</p>";
        echo "<p><strong>Nome:</strong> " . htmlspecialchars($usuario['nome']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($usuario['email']) . "</p>";
        echo "<p><strong>Nível:</strong> " . htmlspecialchars($usuario['nivel_acesso']) . "</p>";
    } else {
        echo "<p>⚠️ Usuário admin não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao criar instância Auth: " . htmlspecialchars($e->getMessage()) . "</p>";
} catch (Error $e) {
    echo "<p>❌ Erro fatal ao criar instância Auth: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h2>7. Verificando status da sessão:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p>✅ Usuário logado: " . htmlspecialchars($_SESSION['usuario_nome'] ?? 'N/A') . "</p>";
    echo "<p><strong>Nível:</strong> " . htmlspecialchars($_SESSION['usuario_nivel'] ?? 'N/A') . "</p>";
} else {
    echo "<p>⚠️ Nenhum usuário logado</p>";
}

echo "<h2>Links Úteis:</h2>";
echo "<p><a href='admin-usuarios.php'>🔗 Testar Admin Usuários</a></p>";
echo "<p><a href='login-simples.php'>🔗 Login</a></p>";
echo "<p><a href='dashboard.php'>🔗 Dashboard</a></p>";
?> 