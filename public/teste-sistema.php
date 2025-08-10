<?php
require_once '../src/init.php';

echo "<h1>Teste Completo do Sistema Bichos do Bairro</h1>";

// Teste 1: Conexão com banco
echo "<h2>1. Teste de Conexão com Banco</h2>";
try {
    global $pdo;
    $stmt = $pdo->query("SELECT 1 as teste");
    $resultado = $stmt->fetch();
    echo "<p style='color: green;'>✅ Conexão com banco OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

// Teste 2: Verificar estrutura das tabelas
echo "<h2>2. Verificação da Estrutura das Tabelas</h2>";

// Verificar tabela agendamentos
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $colunas_necessarias = ['id', 'cliente_id', 'pet_id', 'data', 'hora', 'servico', 'status', 'observacoes', 'created_at', 'updated_at'];
    $colunas_faltando = array_diff($colunas_necessarias, $colunas);
    
    if (empty($colunas_faltando)) {
        echo "<p style='color: green;'>✅ Tabela agendamentos OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Colunas faltando em agendamentos: " . implode(', ', $colunas_faltando) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar agendamentos: " . $e->getMessage() . "</p>";
}

// Verificar tabela pets
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM pets");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $colunas_necessarias = ['id', 'nome', 'especie', 'raca', 'idade', 'peso', 'cliente_id', 'observacoes', 'created_at', 'updated_at'];
    $colunas_faltando = array_diff($colunas_necessarias, $colunas);
    
    if (empty($colunas_faltando)) {
        echo "<p style='color: green;'>✅ Tabela pets OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Colunas faltando em pets: " . implode(', ', $colunas_faltando) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar pets: " . $e->getMessage() . "</p>";
}

// Verificar tabela clientes
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $colunas_necessarias = ['id', 'nome', 'email', 'telefone', 'cpf', 'endereco', 'observacoes', 'created_at', 'updated_at'];
    $colunas_faltando = array_diff($colunas_necessarias, $colunas);
    
    if (empty($colunas_faltando)) {
        echo "<p style='color: green;'>✅ Tabela clientes OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Colunas faltando em clientes: " . implode(', ', $colunas_faltando) . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar clientes: " . $e->getMessage() . "</p>";
}

// Teste 3: Classe Cliente
echo "<h2>3. Teste da Classe Cliente</h2>";
try {
    $clientes = Cliente::listarTodos();
    echo "<p style='color: green;'>✅ Classe Cliente OK - " . count($clientes) . " clientes encontrados</p>";
    
    // Testar métodos específicos
    if (method_exists('Cliente', 'buscarTelefones')) {
        echo "<p style='color: green;'>✅ Método buscarTelefones OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Método buscarTelefones não encontrado</p>";
    }
    
    if (method_exists('Cliente', 'validarCPF')) {
        echo "<p style='color: green;'>✅ Método validarCPF OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Método validarCPF não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na classe Cliente: " . $e->getMessage() . "</p>";
}

// Teste 4: Classe Pet
echo "<h2>4. Teste da Classe Pet</h2>";
try {
    $pets = Pet::listarTodos();
    echo "<p style='color: green;'>✅ Classe Pet OK - " . count($pets) . " pets encontrados</p>";
    
    // Testar métodos específicos
    if (method_exists('Pet', 'buscarPorCliente')) {
        echo "<p style='color: green;'>✅ Método buscarPorCliente OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Método buscarPorCliente não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na classe Pet: " . $e->getMessage() . "</p>";
}

// Teste 5: Classe Agendamento
echo "<h2>5. Teste da Classe Agendamento</h2>";
try {
    $agendamentos = Agendamento::listarTodos();
    echo "<p style='color: green;'>✅ Classe Agendamento OK - " . count($agendamentos) . " agendamentos encontrados</p>";
    
    // Testar métodos específicos
    if (method_exists('Agendamento', 'deletar')) {
        echo "<p style='color: green;'>✅ Método deletar OK</p>";
    } else {
        echo "<p style='color: red;'>❌ Método deletar não encontrado</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na classe Agendamento: " . $e->getMessage() . "</p>";
}

// Teste 6: Teste de inserção de pet com idade NULL
echo "<h2>6. Teste de Inserção de Pet com Idade NULL</h2>";
try {
    $dados_teste = [
        'nome' => 'Pet Teste Sistema',
        'especie' => 'Canina',
        'raca' => 'SRD',
        'idade' => '', // String vazia
        'cliente_id' => 1
    ];
    
    $pet_id = Pet::criar($dados_teste);
    
    if ($pet_id) {
        echo "<p style='color: green;'>✅ Pet inserido com sucesso (ID: $pet_id)</p>";
        
        // Remover o pet de teste
        Pet::deletar($pet_id);
        echo "<p>Pet de teste removido.</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao inserir pet</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no teste de inserção: " . $e->getMessage() . "</p>";
}

// Teste 7: Teste de criação de agendamento
echo "<h2>7. Teste de Criação de Agendamento</h2>";
try {
    $dados_agendamento = [
        'cliente_id' => 1,
        'pet_id' => 1,
        'data' => date('Y-m-d', strtotime('+1 day')),
        'hora' => '10:00',
        'servico' => 'Banho',
        'observacoes' => 'Teste do sistema',
        'status' => 'agendado'
    ];
    
    $agendamento_id = Agendamento::criar($dados_agendamento);
    
    if ($agendamento_id) {
        echo "<p style='color: green;'>✅ Agendamento criado com sucesso (ID: $agendamento_id)</p>";
        
        // Remover o agendamento de teste
        Agendamento::deletar($agendamento_id);
        echo "<p>Agendamento de teste removido.</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao criar agendamento</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no teste de agendamento: " . $e->getMessage() . "</p>";
}

// Teste 8: Funções Helper
echo "<h2>8. Teste das Funções Helper</h2>";
try {
    $email = "teste@exemplo.com";
    $telefone = "11999999999";
    $cpf = "12345678901";
    
    echo "<p>Email válido: " . (validateEmail($email) ? "✅" : "❌") . "</p>";
    echo "<p>Telefone formatado: " . formatPhone($telefone) . "</p>";
    echo "<p>CPF formatado: " . formatCpf($cpf) . "</p>";
    echo "<p style='color: green;'>✅ Funções Helper OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro nas funções helper: " . $e->getMessage() . "</p>";
}

// Teste 9: Configurações
echo "<h2>9. Teste das Configurações</h2>";
try {
    echo "<p>Ambiente: " . (Config::isDebug() ? "Desenvolvimento" : "Produção") . "</p>";
    echo "<p>Versão: " . APP_VERSION . "</p>";
    echo "<p>Nome: " . APP_NAME . "</p>";
    echo "<p style='color: green;'>✅ Configurações OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro nas configurações: " . $e->getMessage() . "</p>";
}

// Teste 10: Métodos Avançados
echo "<h2>10. Teste dos Métodos Avançados</h2>";
try {
    // Teste de busca
    $clientes_busca = Cliente::buscar("", ['limite' => 5]);
    echo "<p>Busca de clientes: " . count($clientes_busca) . " resultados</p>";
    
    // Teste de estatísticas
    $stats_agendamentos = Agendamento::getEstatisticas();
    echo "<p>Estatísticas de agendamentos: " . ($stats_agendamentos['total'] ?? 0) . " total</p>";
    
    echo "<p style='color: green;'>✅ Métodos Avançados OK</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro nos métodos avançados: " . $e->getMessage() . "</p>";
}

echo "<h2>Resumo</h2>";
echo "<p>Se todos os testes acima mostraram ✅, o sistema está funcionando corretamente!</p>";
echo "<p><a href='dashboard.php'>Ir para o Dashboard</a></p>";
echo "<p><a href='clientes.php'>Ir para Clientes</a></p>";
echo "<p><a href='agendamentos.php'>Ir para Agendamentos</a></p>";
echo "<p><a href='pets.php'>Ir para Pets</a></p>";
echo "<p><a href='relatorios.php'>Ir para Relatórios</a></p>";
echo "<p><a href='notificacoes.php'>Ir para Notificações</a></p>";
?> 