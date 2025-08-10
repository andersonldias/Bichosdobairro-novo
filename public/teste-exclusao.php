<?php
require_once '../src/init.php';

echo "<h2>Teste de Exclusão de Agendamentos</h2>";

// Listar agendamentos antes
$agendamentos = Agendamento::listarTodos();
echo "<p>Agendamentos antes do teste: " . count($agendamentos) . "</p>";

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
    
    // Testar exclusão do primeiro agendamento
    $primeiro = $agendamentos[0];
    echo "<h3>Testando exclusão do agendamento ID: " . $primeiro['id'] . "</h3>";
    
    try {
        $resultado = Agendamento::deletar($primeiro['id']);
        if ($resultado) {
            echo "<p style='color: green;'>✅ Agendamento excluído com sucesso!</p>";
            
            // Verificar se foi realmente excluído
            $agendamentos_depois = Agendamento::listarTodos();
            echo "<p>Agendamentos após exclusão: " . count($agendamentos_depois) . "</p>";
            
            if (count($agendamentos_depois) < count($agendamentos)) {
                echo "<p style='color: green;'>✅ Confirmação: Agendamento foi removido do banco!</p>";
            } else {
                echo "<p style='color: red;'>❌ Problema: Agendamento não foi removido do banco!</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao excluir agendamento</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Exceção: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>Nenhum agendamento para testar exclusão.</p>";
}

echo "<p><a href='agendamentos.php'>→ Ir para Agendamentos</a></p>";
echo "<p><a href='verificar-dados.php'>→ Verificar Dados</a></p>";
?> 