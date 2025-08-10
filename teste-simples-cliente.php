<?php
/**
 * Teste simples para diagnosticar o cadastro de clientes
 */

echo "Iniciando teste...\n";

try {
    require_once 'src/init.php';
    echo "✅ Init carregado com sucesso\n";
    
    // Teste básico de conexão
    $pdo = getDb();
    echo "✅ Conexão com banco OK\n";
    
    // Teste simples de criação
    $dados = [
        'nome' => 'Teste Simples',
        'email' => '',
        'telefone' => '(11) 11111-1111',
        'cpf' => '111.111.111-11',
        'cep' => '01234-567',
        'logradouro' => 'Rua Teste',
        'numero' => '123',
        'complemento' => 'Apto 1',
        'bairro' => 'Centro',
        'cidade' => 'São Paulo',
        'estado' => 'SP',
        'observacoes' => 'Teste'
    ];
    
    echo "Tentando criar cliente...\n";
    $id = Cliente::criar($dados);
    
    if ($id) {
        echo "✅ Cliente criado com sucesso! ID: $id\n";
        
        // Verificar se foi salvo
        $cliente = Cliente::buscarPorId($id);
        if ($cliente) {
            echo "✅ Cliente encontrado no banco:\n";
            echo "   Nome: " . $cliente['nome'] . "\n";
            echo "   Telefone: " . $cliente['telefone'] . "\n";
        }
        
        // Limpar
        Cliente::deletar($id);
        echo "✅ Cliente de teste removido\n";
    } else {
        echo "❌ Falha ao criar cliente\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}

echo "Teste finalizado\n";
?>
