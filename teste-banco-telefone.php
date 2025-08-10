<?php
require_once 'src/init.php';

echo "Teste Direto do Banco - Validação de Telefone\n";
echo "=============================================\n\n";

try {
    $pdo = getDb();
    
    // 1. Verificar se a tabela clientes tem o campo telefone
    echo "1. Verificando estrutura da tabela clientes...\n";
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $temTelefone = false;
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'telefone') {
            $temTelefone = true;
            echo "   ✅ Campo 'telefone' encontrado na tabela clientes\n";
            break;
        }
    }
    
    if (!$temTelefone) {
        echo "   ❌ Campo 'telefone' NÃO encontrado na tabela clientes\n";
        exit;
    }
    
    // 2. Verificar se já existem clientes com o telefone de teste
    echo "\n2. Verificando telefone de teste no banco...\n";
    $telefoneTeste = '(11) 99999-9999';
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE telefone = ?");
    $stmt->execute([$telefoneTeste]);
    $resultado = $stmt->fetch();
    
    echo "   Telefone '$telefoneTeste' já existe? " . ($resultado['total'] > 0 ? 'SIM' : 'NÃO') . "\n";
    
    if ($resultado['total'] > 0) {
        echo "   Removendo clientes existentes com este telefone...\n";
        $stmt = $pdo->prepare("DELETE FROM clientes WHERE telefone = ?");
        $stmt->execute([$telefoneTeste]);
        echo "   Clientes removidos.\n";
    }
    
    // 3. Testar o método verificarDuplicidadeTelefone
    echo "\n3. Testando método verificarDuplicidadeTelefone...\n";
    $existe = Cliente::verificarDuplicidadeTelefone($telefoneTeste);
    echo "   Resultado: " . ($existe ? 'EXISTE' : 'NÃO EXISTE') . "\n";
    
    // 4. Criar um cliente de teste
    echo "\n4. Criando cliente de teste...\n";
    $dados = [
        'nome' => 'João Teste Banco',
        'telefone' => $telefoneTeste,
        'cpf' => '123.456.789-00'
    ];
    
    $id = Cliente::criar($dados);
    if ($id) {
        echo "   ✅ Cliente criado com ID: $id\n";
        
        // 5. Verificar se agora o telefone existe
        echo "\n5. Verificando se telefone agora existe...\n";
        $existe = Cliente::verificarDuplicidadeTelefone($telefoneTeste);
        echo "   Resultado: " . ($existe ? 'EXISTE' : 'NÃO EXISTE') . "\n";
        
        // 6. Tentar criar outro cliente com o mesmo telefone
        echo "\n6. Tentando criar cliente com telefone duplicado...\n";
        $dados2 = [
            'nome' => 'Maria Teste Banco',
            'telefone' => $telefoneTeste, // MESMO TELEFONE!
            'cpf' => '987.654.321-00'
        ];
        
        try {
            $id2 = Cliente::criar($dados2);
            echo "   ❌ ERRO: Cliente foi criado com telefone duplicado! ID: $id2\n";
        } catch (Exception $e) {
            echo "   ✅ SUCESSO: " . $e->getMessage() . "\n";
        }
        
        // 7. Limpeza
        echo "\n7. Limpeza...\n";
        Cliente::deletar($id);
        echo "   Cliente de teste removido.\n";
        
    } else {
        echo "   ❌ ERRO: Falha ao criar cliente.\n";
    }
    
    echo "\n✅ Teste concluído com sucesso!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
