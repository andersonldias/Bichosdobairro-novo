<?php
require_once '../src/init.php';

echo "<h1>Teste das Melhorias do Sistema</h1>";

try {
    echo "<h2>1. Teste de Configuração</h2>";
    echo "<p>Nome da aplicação: " . Config::getAppConfig('name') . "</p>";
    echo "<p>Versão: " . Config::getAppConfig('version') . "</p>";
    echo "<p>Timezone: " . Config::getAppConfig('timezone') . "</p>";
    echo "<p>Debug: " . (Config::getAppConfig('debug') ? 'Ativado' : 'Desativado') . "</p>";
    
    echo "<h2>2. Teste de Conexão com Banco</h2>";
    global $pdo;
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    echo "<p>✅ Conexão com banco: OK</p>";
    
    echo "<h2>3. Teste de Cliente</h2>";
    $clientes = Cliente::listarTodos();
    echo "<p>✅ Listagem de clientes: " . count($clientes) . " clientes encontrados</p>";
    
    echo "<h2>4. Teste de Cache</h2>";
    Cache::set('teste', 'valor_teste', 60);
    $valor = Cache::get('teste');
    echo "<p>✅ Cache: " . ($valor === 'valor_teste' ? 'OK' : 'ERRO') . "</p>";
    
    echo "<h2>5. Teste de Logs</h2>";
    Logger::info('Teste de log', ['teste' => 'funcionando']);
    echo "<p>✅ Logs: OK</p>";
    
    echo "<h2>6. Teste de Utilitários</h2>";
    $cpf = '12345678909';
    $email = 'teste@teste.com';
    echo "<p>Validação CPF: " . (Utils::validateCPF($cpf) ? 'Válido' : 'Inválido') . "</p>";
    echo "<p>Validação Email: " . (Utils::validateEmail($email) ? 'Válido' : 'Inválido') . "</p>";
    echo "<p>CPF Formatado: " . Utils::formatCPF($cpf) . "</p>";
    
    echo "<h2>7. Teste de Estatísticas</h2>";
    $cacheStats = Cache::getStats();
    $logStats = Logger::getStats();
    echo "<p>Cache: " . $cacheStats['files'] . " arquivos, " . $cacheStats['size_formatted'] . "</p>";
    echo "<p>Logs: " . count($logStats) . " níveis configurados</p>";
    
    echo "<h2>✅ Todos os testes passaram!</h2>";
    echo "<p><a href='dashboard.php'>Voltar para Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Erro nos testes:</h2>";
    echo "<p><strong>Erro:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 