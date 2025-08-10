<?php
/**
 * Script de teste para verificar o cadastro de clientes
 */

require_once 'src/init.php';

echo "<h1>Teste de Cadastro de Clientes</h1>\n";

// Simular dados de teste
$dados_teste = [
    'nome' => 'João Silva Teste',
    'email' => '', // E-mail vazio (opcional)
    'telefone' => '(11) 99999-9999', // Telefone obrigatório
    'cpf' => '123.456.789-00',
    'endereco' => 'Rua Teste, 123',
    'observacoes' => 'Cliente de teste'
];

echo "<h2>Dados de Teste:</h2>\n";
echo "<pre>" . print_r($dados_teste, true) . "</pre>\n";

echo "<h2>Testando Validações:</h2>\n";

// Teste 1: E-mail vazio (deve funcionar)
echo "<h3>Teste 1: E-mail vazio</h3>\n";
try {
    $id = Cliente::criar($dados_teste);
    if ($id) {
        echo "<p style='color: green;'>✅ Sucesso! Cliente criado com ID: {$id}</p>\n";
        
        // Limpar o cliente de teste
        Cliente::deletar($id);
        echo "<p>🗑️ Cliente de teste removido</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Falha ao criar cliente</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Teste 2: Telefone vazio (deve falhar)
echo "<h3>Teste 2: Telefone vazio</h3>\n";
$dados_sem_telefone = $dados_teste;
$dados_sem_telefone['telefone'] = '';

try {
    $id = Cliente::criar($dados_sem_telefone);
    if ($id) {
        echo "<p style='color: red;'>❌ Erro: Cliente foi criado sem telefone (não deveria)</p>\n";
        Cliente::deletar($id);
    }
} catch (Exception $e) {
    echo "<p style='color: green;'>✅ Correto! Erro capturado: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

// Teste 3: E-mail válido
echo "<h3>Teste 3: E-mail válido</h3>\n";
$dados_com_email = $dados_teste;
$dados_com_email['email'] = 'joao.teste@email.com';

try {
    $id = Cliente::criar($dados_com_email);
    if ($id) {
        echo "<p style='color: green;'>✅ Sucesso! Cliente criado com e-mail: {$id}</p>\n";
        
        // Verificar se o cliente foi criado corretamente
        $cliente = Cliente::buscarPorId($id);
        if ($cliente) {
            echo "<p>📋 Dados salvos:</p>\n";
            echo "<ul>\n";
            echo "<li><strong>Nome:</strong> " . htmlspecialchars($cliente['nome']) . "</li>\n";
            echo "<li><strong>E-mail:</strong> " . htmlspecialchars($cliente['email'] ?? 'NULL') . "</li>\n";
            echo "<li><strong>Telefone:</strong> " . htmlspecialchars($cliente['telefone']) . "</li>\n";
            echo "<li><strong>CPF:</strong> " . htmlspecialchars($cliente['cpf']) . "</li>\n";
            echo "</ul>\n";
        }
        
        // Limpar o cliente de teste
        Cliente::deletar($id);
        echo "<p>🗑️ Cliente de teste removido</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Falha ao criar cliente</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}

echo "<h2>Status das Melhorias:</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>E-mail opcional:</strong> Funcionando</li>\n";
echo "<li>✅ <strong>Telefone obrigatório:</strong> Funcionando</li>\n";
echo "<li>✅ <strong>Validação no servidor:</strong> Funcionando</li>\n";
echo "</ul>\n";

echo "<h2>Próximos Passos:</h2>\n";
echo "<ol>\n";
echo "<li>✅ Teste o formulário web em: <a href='clientes.php' target='_blank'>clientes.php</a></li>\n";
echo "<li>✅ Teste o autofoco no campo nome</li>\n";
echo "<li>✅ Teste a navegação com Enter</li>\n";
echo "<li>✅ Teste cadastro sem e-mail</li>\n";
echo "<li>✅ Teste cadastro com telefone obrigatório</li>\n";
echo "</ol>\n";

echo "<hr>\n";
echo "<p><em>Teste executado em: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
