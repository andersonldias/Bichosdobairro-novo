<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste de Erro</h1>";
echo "PHP funcionando: " . PHP_VERSION . "<br>";

// Teste de sintaxe básica
$teste = [1, 2, 3];
foreach ($teste as $item) {
    echo "Item: $item<br>";
}

echo "Teste concluído com sucesso!";
?>