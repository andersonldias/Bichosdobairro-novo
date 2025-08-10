<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/Cliente.php';
require_once '../src/Pet.php';

echo "<h1>Debug Test</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Dados POST recebidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Testar processamento de telefones
    $telefones = [];
    if (isset($_POST['telefone_nome']) && isset($_POST['telefone_numero'])) {
        $nomes = $_POST['telefone_nome'];
        $numeros = $_POST['telefone_numero'];
        for ($i = 0; $i < count($nomes); $i++) {
            if (!empty($nomes[$i]) || !empty($numeros[$i])) {
                $telefones[] = [
                    'nome' => trim($nomes[$i]),
                    'numero' => trim($numeros[$i])
                ];
            }
        }
    }
    
    echo "<h2>Telefones processados:</h2>";
    echo "<pre>";
    print_r($telefones);
    echo "</pre>";
    
    // Testar processamento de pets
    if (isset($_POST['pet_nome']) && is_array($_POST['pet_nome'])) {
        $pet_nomes = $_POST['pet_nome'];
        $pet_especies = $_POST['pet_especie'] ?? [];
        $pet_racas = $_POST['pet_raca'] ?? [];
        $pet_idades = $_POST['pet_idade'] ?? [];
        
        echo "<h2>Pets processados:</h2>";
        for ($i = 0; $i < count($pet_nomes); $i++) {
            $nome = trim($pet_nomes[$i]);
            $especie = trim($pet_especies[$i] ?? '');
            $raca = trim($pet_racas[$i] ?? '');
            $idade = trim($pet_idades[$i] ?? '');
            
            if (!empty($nome) || !empty($especie)) {
                echo "Pet $i: Nome=$nome, Espécie=$especie, Raça=$raca, Idade=$idade<br>";
            }
        }
    }
}
?>

<form method="post">
    <h3>Teste de Telefones</h3>
    <div>
        <label>Nome do telefone:</label>
        <input type="text" name="telefone_nome[]" value="Casa">
    </div>
    <div>
        <label>Número:</label>
        <input type="text" name="telefone_numero[]" value="(11) 99999-9999">
    </div>
    
    <h3>Teste de Pets</h3>
    <div>
        <label>Nome do pet:</label>
        <input type="text" name="pet_nome[]" value="Rex">
    </div>
    <div>
        <label>Espécie:</label>
        <input type="text" name="pet_especie[]" value="Cão">
    </div>
    <div>
        <label>Raça:</label>
        <input type="text" name="pet_raca[]" value="Labrador">
    </div>
    <div>
        <label>Idade:</label>
        <input type="number" name="pet_idade[]" value="3">
    </div>
    
    <button type="submit">Testar</button>
</form> 