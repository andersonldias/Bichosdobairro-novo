<?php
require_once '../src/init.php';

echo "<h2>Limpando Agendamentos de Teste</h2>";

// Listar agendamentos antes
$agendamentos = Agendamento::listarTodos();
echo "<p>Agendamentos antes da limpeza: " . count($agendamentos) . "</p>";

if (count($agendamentos) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Data</th><th>Hora</th><th>Cliente</th><th>Pet</th><th>Serviço</th></tr>";
    foreach($agendamentos as $a) {
        echo "<tr>";
        echo "<td>" . $a['id'] . "</td>";
        echo "<td>" . $a['data'] . "</td>";
        echo "<td>" . $a['hora'] . "</td>";
        echo "<td>" . htmlspecialchars($a['cliente_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($a['pet_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($a['servico']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Deletar agendamentos de teste
    global $pdo;
    $sql = "DELETE FROM agendamentos WHERE servico LIKE '%Teste%'";
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute();
    
    if ($resultado) {
        $linhas_afetadas = $stmt->rowCount();
        echo "<p style='color: green;'>✅ " . $linhas_afetadas . " agendamento(s) de teste removido(s)</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao remover agendamentos de teste</p>";
    }
} else {
    echo "<p>Nenhum agendamento para limpar.</p>";
}

// Listar agendamentos depois
$agendamentos_depois = Agendamento::listarTodos();
echo "<p>Agendamentos após a limpeza: " . count($agendamentos_depois) . "</p>";

echo "<p><a href='agendamentos.php'>→ Ir para Agendamentos</a></p>";
echo "<p><a href='verificar-dados.php'>→ Verificar Dados</a></p>";
?> 