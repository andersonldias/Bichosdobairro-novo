<?php
/**
 * Fazer logout para testar
 */
session_start();
session_destroy();
echo "<h1>Logout Realizado</h1>";
echo "<p>Sessão destruída com sucesso!</p>";
echo "<p><a href='clientes.php'>Testar clientes.php sem login</a></p>";
echo "<p><a href='login.php'>Fazer login novamente</a></p>";
?>