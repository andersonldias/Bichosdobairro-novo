<?php
echo '<h2>Diagnóstico de Conexão</h2>';

// Verificar se o autoload existe
echo '<h3>1. Verificando Autoload</h3>';
$autoload_path = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload_path)) {
    echo '<p style="color:green;">✅ Autoload encontrado</p>';
    require_once $autoload_path;
} else {
    echo '<p style="color:red;">❌ Autoload não encontrado</p>';
    echo '<p>Execute: composer install</p>';
    exit;
}

// Verificar se o arquivo .env existe
echo '<h3>2. Verificando arquivo .env</h3>';
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    echo '<p style="color:green;">✅ Arquivo .env encontrado</p>';
    echo '<p>Conteúdo:</p>';
    echo '<pre>' . htmlspecialchars(file_get_contents($env_path)) . '</pre>';
} else {
    echo '<p style="color:red;">❌ Arquivo .env não encontrado</p>';
    exit;
}

// Tentar carregar as variáveis de ambiente
echo '<h3>3. Carregando variáveis de ambiente</h3>';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    echo '<p style="color:green;">✅ Variáveis carregadas com sucesso</p>';
    
    echo '<p>DB_HOST: ' . ($_ENV['DB_HOST'] ?? 'não definida') . '</p>';
    echo '<p>DB_NAME: ' . ($_ENV['DB_NAME'] ?? 'não definida') . '</p>';
    echo '<p>DB_USER: ' . ($_ENV['DB_USER'] ?? 'não definida') . '</p>';
    echo '<p>DB_PASS: ' . ($_ENV['DB_PASS'] ? '***' : 'não definida') . '</p>';
    
} catch (Exception $e) {
    echo '<p style="color:red;">❌ Erro ao carregar .env: ' . $e->getMessage() . '</p>';
    exit;
}

// Testar conexão MySQL
echo '<h3>4. Testando conexão MySQL</h3>';
try {
    $host = $_ENV['DB_HOST'] ?? 'xmysql.bichosdobairro.com.br';
    $db   = $_ENV['DB_NAME'] ?? 'bichosdobairro5';
    $user = $_ENV['DB_USER'] ?? 'bichosdobairro5';
    $pass = $_ENV['DB_PASS'] ?? '!BdoB.1179!';
    $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    echo '<p style="color:green;">✅ Conexão MySQL bem-sucedida!</p>';
    
    // Testar query
    $result = $pdo->query('SELECT 1 as test');
    $row = $result->fetch();
    echo '<p>Query de teste: ' . $row['test'] . '</p>';
    
} catch (PDOException $e) {
    echo '<p style="color:red;">❌ Erro na conexão MySQL: ' . $e->getMessage() . '</p>';
    echo '<p>Código do erro: ' . $e->getCode() . '</p>';
}

echo '<h3>5. Verificando extensões PHP</h3>';
echo '<p>PDO MySQL: ' . (extension_loaded('pdo_mysql') ? '✅ Carregada' : '❌ Não carregada') . '</p>';
echo '<p>PDO: ' . (extension_loaded('pdo') ? '✅ Carregada' : '❌ Não carregada') . '</p>'; 