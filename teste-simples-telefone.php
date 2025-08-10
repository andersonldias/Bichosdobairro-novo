<?php
require_once 'src/init.php';

echo "Teste de Validação de Telefone\n";
echo "==============================\n\n";

try {
    // Teste 1: Criar cliente com telefone único
    echo "1. Criando cliente com telefone (11) 99999-9999...\n";
    $dados1 = [
        'nome' => 'João Teste',
        'telefone' => '(11) 99999-9999',
        'cpf' => '123.456.789-00'
    ];
    
    $id1 = Cliente::criar($dados1);
    echo "   Cliente criado com ID: $id1\n\n";
    
    // Teste 2: Tentar criar cliente com telefone duplicado
    echo "2. Tentando criar cliente com telefone duplicado...\n";
    $dados2 = [
        'nome' => 'Maria Teste',
        'telefone' => '(11) 99999-9999', // MESMO TELEFONE!
        'cpf' => '987.654.321-00'
    ];
    
    try {
        $id2 = Cliente::criar($dados2);
        echo "   ERRO: Cliente foi criado com telefone duplicado!\n";
    } catch (Exception $e) {
        echo "   SUCESSO: " . $e->getMessage() . "\n";
    }
    
    // Limpeza
    if (isset($id1)) {
        Cliente::deletar($id1);
        echo "\nCliente de teste removido.\n";
    }
    
    echo "\nTeste concluído!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}
?>
