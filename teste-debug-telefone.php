<?php
require_once 'src/init.php';

echo "Teste de Debug - Validação de Telefone\n";
echo "======================================\n\n";

try {
    echo "1. Testando método verificarDuplicidadeTelefone...\n";
    
    // Teste com telefone que não existe
    $resultado = Cliente::verificarDuplicidadeTelefone('(11) 99999-9999');
    echo "   Telefone (11) 99999-9999 existe? " . ($resultado ? 'SIM' : 'NÃO') . "\n";
    
    // Teste com telefone vazio
    $resultado = Cliente::verificarDuplicidadeTelefone('');
    echo "   Telefone vazio existe? " . ($resultado ? 'SIM' : 'NÃO') . "\n";
    
    echo "\n2. Testando criação de cliente...\n";
    
    $dados = [
        'nome' => 'João Debug',
        'telefone' => '(11) 99999-9999',
        'cpf' => '123.456.789-00'
    ];
    
    echo "   Dados: " . json_encode($dados) . "\n";
    
    // Verificar se telefone já existe antes de criar
    $existe = Cliente::verificarDuplicidadeTelefone($dados['telefone']);
    echo "   Telefone já existe? " . ($existe ? 'SIM' : 'NÃO') . "\n";
    
    if ($existe) {
        echo "   ERRO: Telefone já existe, não deveria criar!\n";
    } else {
        echo "   OK: Telefone não existe, pode criar.\n";
        
        // Tentar criar
        $id = Cliente::criar($dados);
        if ($id) {
            echo "   Cliente criado com ID: $id\n";
            
            // Verificar se agora existe
            $existe = Cliente::verificarDuplicidadeTelefone($dados['telefone']);
            echo "   Após criação, telefone existe? " . ($existe ? 'SIM' : 'NÃO') . "\n";
            
            // Limpar
            Cliente::deletar($id);
            echo "   Cliente removido.\n";
        } else {
            echo "   ERRO: Falha ao criar cliente.\n";
        }
    }
    
    echo "\nTeste concluído!\n";
    
} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
