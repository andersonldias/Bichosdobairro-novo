<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/Cliente.php';
require_once '../src/Pet.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Dados recebidos:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    // Testar criação de cliente
    try {
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
        
        echo "<h3>Telefones processados:</h3>";
        echo "<pre>";
        print_r($telefones);
        echo "</pre>";
        
        $cliente_id = Cliente::criar(
            $_POST['nome'],
            $_POST['email'],
            $_POST['cpf'] ?? null,
            $telefones,
            $_POST['endereco'] ?? '',
            $_POST['cep'] ?? '',
            $_POST['logradouro'] ?? '',
            $_POST['numero'] ?? '',
            $_POST['complemento'] ?? '',
            $_POST['bairro'] ?? '',
            $_POST['cidade'] ?? '',
            $_POST['estado'] ?? ''
        );
        
        echo "<h3>Cliente criado com ID: $cliente_id</h3>";
        
        // Testar criação de pets
        if (isset($_POST['pet_nome']) && is_array($_POST['pet_nome'])) {
            $pet_nomes = $_POST['pet_nome'];
            $pet_especies = $_POST['pet_especie'] ?? [];
            $pet_racas = $_POST['pet_raca'] ?? [];
            $pet_idades = $_POST['pet_idade'] ?? [];
            
            echo "<h3>Pets processados:</h3>";
            for ($i = 0; $i < count($pet_nomes); $i++) {
                $nome = trim($pet_nomes[$i]);
                $especie = trim($pet_especies[$i] ?? '');
                $raca = trim($pet_racas[$i] ?? '');
                $idade = trim($pet_idades[$i] ?? '');
                
                if (!empty($nome) || !empty($especie)) {
                    try {
                        Pet::criar($nome, $especie, $raca, $idade, $cliente_id);
                        echo "Pet criado: $nome ($especie)<br>";
                    } catch (Exception $e) {
                        echo "Erro ao criar pet: " . $e->getMessage() . "<br>";
                    }
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<h3>Erro:</h3>";
        echo $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Teste Wizard Simples</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold mb-4">Teste Wizard Simples</h1>
    
    <form method="post" class="space-y-4">
        <div>
            <label>Nome:</label>
            <input type="text" name="nome" value="João Silva" required class="border p-2 w-full">
        </div>
        
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="joao@teste.com" required class="border p-2 w-full">
        </div>
        
        <div>
            <label>CPF:</label>
            <input type="text" name="cpf" value="123.456.789-00" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Telefone Nome:</label>
            <input type="text" name="telefone_nome[]" value="Casa" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Telefone Número:</label>
            <input type="text" name="telefone_numero[]" value="(11) 99999-9999" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Pet Nome:</label>
            <input type="text" name="pet_nome[]" value="Rex" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Pet Espécie:</label>
            <input type="text" name="pet_especie[]" value="Cão" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Pet Raça:</label>
            <input type="text" name="pet_raca[]" value="Labrador" class="border p-2 w-full">
        </div>
        
        <div>
            <label>Pet Idade:</label>
            <input type="number" name="pet_idade[]" value="3" class="border p-2 w-full">
        </div>
        
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Testar</button>
    </form>
</body>
</html> 