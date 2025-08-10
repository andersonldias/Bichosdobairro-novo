<?php
require_once '../src/Agendamento.php';
if (isset($_GET['excluir_todos'])) {
    global $pdo;
    $pdo->exec('DELETE FROM agendamentos');
    echo '<p style="color:red;font-weight:bold;">Todos os agendamentos foram excluídos!</p>';
}
$agendamentos = Agendamento::listarTodos();
echo '<h2>Todos os agendamentos no banco</h2>';
echo '<form method="get"><button type="submit" name="excluir_todos" value="1" style="background:#d00;color:#fff;padding:8px 16px;border:none;border-radius:4px;">Excluir todos os agendamentos</button></form>';
echo '<table border="1" cellpadding="6" style="border-collapse:collapse;">';
echo '<tr><th>ID</th><th>Data</th><th>Hora</th><th>Cliente</th><th>Pet</th><th>Serviço</th><th>Status</th></tr>';
foreach ($agendamentos as $a) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($a['id']) . '</td>';
    echo '<td>' . htmlspecialchars($a['data']) . '</td>';
    echo '<td>' . htmlspecialchars($a['hora']) . '</td>';
    echo '<td>' . htmlspecialchars($a['cliente_nome']) . '</td>';
    echo '<td>' . htmlspecialchars($a['pet_nome']) . '</td>';
    echo '<td>' . htmlspecialchars($a['servico']) . '</td>';
    echo '<td>' . htmlspecialchars($a['status']) . '</td>';
    echo '</tr>';
}
echo '</table>'; 