<?php
require_once '../src/db.php';

$stmt = $pdo->query('SELECT pets.id as pet_id, pets.nome as pet_nome, pets.especie, pets.raca, pets.idade, pets.cliente_id, clientes.nome as cliente_nome FROM pets LEFT JOIN clientes ON pets.cliente_id = clientes.id ORDER BY pets.id DESC');
$pets = $stmt->fetchAll();

if (empty($pets)) {
    echo '<h2>Nenhum pet cadastrado.</h2>';
} else {
    echo '<h2>Lista de Pets</h2>';
    echo '<table border="1" cellpadding="5"><tr><th>ID Pet</th><th>Nome Pet</th><th>Espécie</th><th>Raça</th><th>Idade</th><th>ID Cliente</th><th>Nome Cliente</th></tr>';
    foreach ($pets as $pet) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($pet['pet_id']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['pet_nome']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['especie']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['raca']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['idade']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['cliente_id']) . '</td>';
        echo '<td>' . htmlspecialchars($pet['cliente_nome']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
}
?> 