<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste de Erro - Dashboard</h2>";

// 1. Testar carregamento básico
echo "<h3>1. Testando carregamento básico...</h3>";
try {
    echo "✅ PHP funcionando<br>";
    
    // Testar se o arquivo init.php carrega
    echo "Carregando init.php...<br>";
    require_once '../src/init.php';
    echo "✅ init.php carregado<br>";
    
} catch (Exception $e) {
    echo "❌ Erro no carregamento: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    exit;
} catch (Error $e) {
    echo "❌ Erro fatal: " . $e->getMessage() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    exit;
}

// 2. Testar conexão
echo "<h3>2. Testando conexão...</h3>";
try {
    $pdo = getDb();
    if ($pdo) {
        echo "✅ Conexão OK<br>";
    } else {
        echo "❌ Conexão NULL<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

// 3. Testar Auth
echo "<h3>3. Testando Auth...</h3>";
try {
    require_once '../src/Auth.php';
    $auth = new Auth();
    echo "✅ Auth criada<br>";
    
    if ($auth->estaLogado()) {
        echo "✅ Usuário logado<br>";
    } else {
        echo "⚠️ Usuário não logado<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro na Auth: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Teste concluído</h3>";
echo "Se chegou até aqui, o problema não é fatal.<br>";
?>