<?php
echo "<h1>Teste de Caminhos</h1>";

echo "<h2>1. Verificando diretório atual</h2>";
echo "Diretório atual: " . getcwd() . "<br>";

echo "<h2>2. Verificando se src/init.php existe</h2>";
if (file_exists('src/init.php')) {
    echo "✅ src/init.php existe<br>";
} else {
    echo "❌ src/init.php NÃO existe<br>";
}

echo "<h2>3. Verificando se src/Config.php existe</h2>";
if (file_exists('src/Config.php')) {
    echo "✅ src/Config.php existe<br>";
} else {
    echo "❌ src/Config.php NÃO existe<br>";
}

echo "<h2>4. Verificando se src/db.php existe</h2>";
if (file_exists('src/db.php')) {
    echo "✅ src/db.php existe<br>";
} else {
    echo "❌ src/db.php NÃO existe<br>";
}

echo "<h2>5. Testando include de src/init.php</h2>";
try {
    require_once 'src/init.php';
    echo "✅ src/init.php incluído com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao incluir src/init.php: " . $e->getMessage() . "<br>";
}

echo "<h2>6. Testando include de src/Config.php</h2>";
try {
    require_once 'src/Config.php';
    echo "✅ src/Config.php incluído com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao incluir src/Config.php: " . $e->getMessage() . "<br>";
}

echo "<h2>7. Testando include de src/db.php</h2>";
try {
    require_once 'src/db.php';
    echo "✅ src/db.php incluído com sucesso<br>";
} catch (Exception $e) {
    echo "❌ Erro ao incluir src/db.php: " . $e->getMessage() . "<br>";
}
?>
