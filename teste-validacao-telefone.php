<?php
require_once 'src/init.php';

echo "<h1>🧪 Teste de Validação de Telefone Duplicado</h1>";

try {
    $pdo = getDb();
    echo "✅ Conexão com banco estabelecida<br><br>";
    
    // Teste 1: Criar primeiro cliente
    echo "<h2>Teste 1: Criando primeiro cliente</h2>";
    $dadosCliente1 = [
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
        'observacoes' => 'Cliente de teste para verificar validação de telefone'
    ];
    
    $clienteId1 = Cliente::criar($dadosCliente1);
    if ($clienteId1) {
        echo "✅ Cliente 1 criado com ID: $clienteId1<br>";
    } else {
        echo "❌ Erro ao criar cliente 1<br>";
        return;
    }
    
    // Teste 2: Tentar criar segundo cliente com mesmo telefone
    echo "<h2>Teste 2: Tentando criar segundo cliente com telefone duplicado</h2>";
    $dadosCliente2 = [
        'nome' => 'Maria Santos Teste',
        'email' => 'maria.teste@email.com',
        'telefone' => '(11) 99999-9999', // MESMO TELEFONE!
        'cpf' => '987.654.321-00',
        'cep' => '87654-321',
        'logradouro' => 'Rua das Palmeiras',
        'numero' => '456',
        'complemento' => 'Casa 12',
        'bairro' => 'Jardim',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'observacoes' => 'Cliente de teste para verificar validação de telefone'
    ];
    
    try {
        $clienteId2 = Cliente::criar($dadosCliente2);
        echo "❌ ERRO: Cliente 2 foi criado com telefone duplicado! ID: $clienteId2<br>";
    } catch (Exception $e) {
        echo "✅ VALIDAÇÃO FUNCIONANDO: " . $e->getMessage() . "<br>";
    }
    
    // Teste 3: Tentar atualizar cliente 1 com telefone de outro cliente
    echo "<h2>Teste 3: Tentando atualizar cliente 1 com telefone de outro cliente</h2>";
    
    // Primeiro criar um cliente 2 com telefone diferente
    $dadosCliente2['telefone'] = '(11) 88888-8888';
    $clienteId2 = Cliente::criar($dadosCliente2);
    if ($clienteId2) {
        echo "✅ Cliente 2 criado com telefone diferente, ID: $clienteId2<br>";
        
        // Agora tentar atualizar cliente 1 com telefone do cliente 2
        $dadosAtualizacao = $dadosCliente1;
        $dadosAtualizacao['telefone'] = '(11) 88888-8888'; // Telefone do cliente 2
        
        try {
            $resultado = Cliente::atualizar($clienteId1, $dadosAtualizacao);
            echo "❌ ERRO: Cliente 1 foi atualizado com telefone duplicado!<br>";
        } catch (Exception $e) {
            echo "✅ VALIDAÇÃO FUNCIONANDO: " . $e->getMessage() . "<br>";
        }
    }
    
    // Teste 4: Verificar se cliente 1 pode manter seu próprio telefone na atualização
    echo "<h2>Teste 4: Verificando se cliente 1 pode manter seu próprio telefone</h2>";
    try {
        $dadosAtualizacao = $dadosCliente1;
        $dadosAtualizacao['observacoes'] = 'Observação atualizada';
        
        $resultado = Cliente::atualizar($clienteId1, $dadosAtualizacao);
        if ($resultado) {
            echo "✅ Cliente 1 atualizado com sucesso mantendo seu telefone<br>";
        } else {
            echo "❌ Erro ao atualizar cliente 1<br>";
        }
    } catch (Exception $e) {
        echo "❌ ERRO: " . $e->getMessage() . "<br>";
    }
    
    echo "<h2 style='color: green;'>🎉 Teste de validação concluído!</h2>";
    
    // Limpeza: remover clientes de teste
    echo "<h2>Limpeza: Removendo clientes de teste</h2>";
    if (Cliente::deletar($clienteId1)) {
        echo "✅ Cliente 1 removido<br>";
    }
    if (isset($clienteId2) && Cliente::deletar($clienteId2)) {
        echo "✅ Cliente 2 removido<br>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erro: " . $e->getMessage() . "</h2>";
    echo "<p>Arquivo: " . $e->getFile() . " Linha: " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
