<?php
require_once 'src/init.php';

echo "<h1>🧪 Teste de Cadastro Completo</h1>";

try {
    $pdo = getDb();
    echo "✅ Conexão com banco estabelecida<br><br>";
    
    // Dados de teste para cliente
    $dadosCliente = [
        'nome' => 'João Silva Teste',
        'email' => 'joao.teste@email.com',
        'telefone' => '(11) 99999-9999',
        'cpf' => '123.456.789-00',
        'cep' => '01234-567',
        'logradouro' => 'Rua das Flores',
        'numero' => '123',
        'complemento' => 'Apto 45',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'observacoes' => 'Cliente de teste para verificar cadastro completo',
        'telefones' => [
            [
                'nome' => 'Celular',
                'numero' => '(11) 99999-9999'
            ],
            [
                'nome' => 'Residencial',
                'numero' => '(11) 3333-3333'
            ]
        ],
        'pets' => [
            [
                'nome' => 'Rex',
                'especie' => 'Cão',
                'raca' => 'Labrador',
                'idade' => 3
            ],
            [
                'nome' => 'Mimi',
                'especie' => 'Gato',
                'raca' => 'Siamês',
                'idade' => 2
            ]
        ]
    ];
    
    echo "<h2>1. Criando cliente com dados completos...</h2>";
    echo "Nome: " . $dadosCliente['nome'] . "<br>";
    echo "Email: " . $dadosCliente['email'] . "<br>";
    echo "CPF: " . $dadosCliente['cpf'] . "<br>";
    echo "Telefones: " . count($dadosCliente['telefones']) . " números<br>";
    echo "Pets: " . count($dadosCliente['pets']) . " animais<br><br>";
    
    // Criar cliente
    $clienteId = Cliente::criar($dadosCliente);
    
    if ($clienteId) {
        echo "✅ Cliente criado com ID: $clienteId<br><br>";
        
        // Verificar dados salvos
        echo "<h2>2. Verificando dados salvos...</h2>";
        
        // Verificar cliente
        $cliente = Cliente::buscarPorId($clienteId);
        if ($cliente) {
            echo "✅ Cliente encontrado:<br>";
            echo "- Nome: " . $cliente['nome'] . "<br>";
            echo "- Email: " . $cliente['email'] . "<br>";
            echo "- Telefone: " . $cliente['telefone'] . "<br>";
            echo "- Endereço: " . $cliente['logradouro'] . ", " . $cliente['numero'] . " - " . $cliente['bairro'] . ", " . $cliente['cidade'] . "/" . $cliente['estado'] . "<br><br>";
        }
        
        // Verificar telefones
        $telefones = Cliente::buscarTelefones($clienteId);
        if (!empty($telefones)) {
            echo "✅ Telefones salvos (" . count($telefones) . "):<br>";
            foreach ($telefones as $tel) {
                echo "- " . $tel['nome'] . ": " . $tel['numero'] . "<br>";
            }
            echo "<br>";
        } else {
            echo "❌ Nenhum telefone encontrado<br><br>";
        }
        
        // Verificar pets
        $stmt = $pdo->prepare("SELECT * FROM pets WHERE cliente_id = ?");
        $stmt->execute([$clienteId]);
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($pets)) {
            echo "✅ Pets salvos (" . count($pets) . "):<br>";
            foreach ($pets as $pet) {
                echo "- " . $pet['nome'] . " (" . $pet['especie'] . ", " . $pet['raca'] . ", " . $pet['idade'] . " anos)<br>";
            }
            echo "<br>";
        } else {
            echo "❌ Nenhum pet encontrado<br><br>";
        }
        
        // Limpar dados de teste
        echo "<h2>3. Limpando dados de teste...</h2>";
        Cliente::deletar($clienteId);
        echo "✅ Cliente de teste removido<br>";
        
    } else {
        echo "❌ Erro ao criar cliente<br>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro: " . $e->getMessage() . "</h2>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
