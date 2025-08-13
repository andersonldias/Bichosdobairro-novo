<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Teste da Classe Auth</h2>";

// 1. Testar carregamento básico
echo "<h3>1. Carregando dependências...</h3>";
try {
    require_once '../src/init.php';
    echo "✅ init.php carregado<br>";
    
    require_once '../src/Auth.php';
    echo "✅ Auth.php carregado<br>";
} catch (Exception $e) {
    echo "❌ Erro no carregamento: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Testar conexão direta
echo "<h3>2. Testando getDb()...</h3>";
try {
    $pdo = getDb();
    if ($pdo) {
        echo "✅ getDb() retornou PDO válido<br>";
        echo "Tipo: " . get_class($pdo) . "<br>";
    } else {
        echo "❌ getDb() retornou null<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro em getDb(): " . $e->getMessage() . "<br>";
}

// 3. Testar criação da classe Auth
echo "<h3>3. Testando criação da classe Auth...</h3>";
try {
    $auth = new Auth();
    echo "✅ Classe Auth criada com sucesso<br>";
    
    // Testar se o PDO está disponível na classe
    $reflection = new ReflectionClass($auth);
    $pdoProperty = $reflection->getProperty('pdo');
    $pdoProperty->setAccessible(true);
    $authPdo = $pdoProperty->getValue($auth);
    
    if ($authPdo) {
        echo "✅ PDO na classe Auth está válido<br>";
        echo "Tipo: " . get_class($authPdo) . "<br>";
    } else {
        echo "❌ PDO na classe Auth é null<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erro na criação da Auth: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

// 4. Testar método específico
echo "<h3>4. Testando buscarUsuario()...</h3>";
try {
    if (isset($auth)) {
        // Testar com email fictício
        $resultado = $auth->buscarUsuario('teste@teste.com');
        echo "✅ Método buscarUsuario() executado sem erro<br>";
        echo "Resultado: " . ($resultado ? 'Usuário encontrado' : 'Usuário não encontrado') . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro em buscarUsuario(): " . $e->getMessage() . "<br>";
    echo "Linha: " . $e->getLine() . "<br>";
    echo "Arquivo: " . $e->getFile() . "<br>";
}

echo "<h3>5. Informações de Debug</h3>";
echo "Sessão ativa: " . (session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não') . "<br>";
echo "Config carregada: " . (class_exists('Config') ? 'Sim' : 'Não') . "<br>";
if (class_exists('Config')) {
    echo "DB_HOST: " . Config::get('DB_HOST') . "<br>";
    echo "APP_ENV: " . Config::get('APP_ENV') . "<br>";
}
?>