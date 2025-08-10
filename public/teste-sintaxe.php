<?php
// Teste simples de sintaxe PHP
echo "<h1>✅ Teste de Sintaxe PHP</h1>";
echo "<p>Se você está vendo esta mensagem, a sintaxe PHP está funcionando!</p>";
echo "<p>Versão PHP: " . phpversion() . "</p>";
echo "<p>Data/Hora: " . date('Y-m-d H:i:s') . "</p>";

// Teste de array
$teste = ['a' => 1, 'b' => 2, 'c' => 3];
echo "<p>Teste de array: " . json_encode($teste) . "</p>";

// Teste de função
function testeSimples() {
    return "Função funcionando!";
}
echo "<p>" . testeSimples() . "</p>";

echo "<p><a href='diagnostico.php'>Voltar ao diagnóstico</a></p>";
?>