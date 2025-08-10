<?php
/**
 * Monitor de Conexão com Banco de Dados
 * Sistema Bichos do Bairro
 * 
 * Este script monitora a estabilidade da conexão e executa testes
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Monitor de Conexão - Sistema Bichos do Bairro</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// Carregar configurações
require_once '../src/Config.php';
Config::load();

// Carregar conexão
require_once '../src/db.php';

// ========================================
// 1. TESTE DE CONEXÃO BÁSICA
// ========================================

echo "<h2>1. 🔗 Teste de Conexão Básica</h2>";

try {
    $pdo = getDb();
    if ($pdo) {
        echo "<p style='color: green;'>✅ Conexão estabelecida com sucesso</p>";
        
        // Testar query simples
        $stmt = $pdo->query('SELECT 1 as test');
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "<p style='color: green;'>✅ Query de teste executada com sucesso</p>";
        } else {
            echo "<p style='color: red;'>❌ Query de teste falhou</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
}

// ========================================
// 2. TESTE DE RECONEXÃO
// ========================================

echo "<h2>2. 🔄 Teste de Reconexão</h2>";

try {
    // Forçar nova conexão
    global $pdo;
    $pdo = null;
    
    $pdo = getDb();
    if ($pdo) {
        echo "<p style='color: green;'>✅ Reconexão bem-sucedida</p>";
        
        // Testar novamente
        $stmt = $pdo->query('SELECT NOW() as hora_atual');
        $result = $stmt->fetch();
        echo "<p style='color: blue;'>ℹ️ Hora atual do servidor: " . $result['hora_atual'] . "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ Falha na reconexão</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na reconexão: " . $e->getMessage() . "</p>";
}

// ========================================
// 3. TESTE DE FUNÇÕES HELPER
// ========================================

echo "<h2>3. 🛠️ Teste de Funções Helper</h2>";

try {
    // Testar fetchOne
    $result = fetchOne('SELECT COUNT(*) as total FROM clientes');
    if ($result) {
        echo "<p style='color: green;'>✅ fetchOne() funcionando - Total de clientes: " . $result['total'] . "</p>";
    } else {
        echo "<p style='color: red;'>❌ fetchOne() falhou</p>";
    }
    
    // Testar fetchAll
    $result = fetchAll('SELECT id, nome FROM clientes LIMIT 5');
    if ($result) {
        echo "<p style='color: green;'>✅ fetchAll() funcionando - " . count($result) . " clientes retornados</p>";
    } else {
        echo "<p style='color: red;'>❌ fetchAll() falhou</p>";
    }
    
    // Testar countRecords
    $total = countRecords('pets');
    echo "<p style='color: green;'>✅ countRecords() funcionando - Total de pets: $total</p>";
    
    // Testar tableExists
    $existe = tableExists('agendamentos');
    if ($existe) {
        echo "<p style='color: green;'>✅ tableExists() funcionando - Tabela agendamentos existe</p>";
    } else {
        echo "<p style='color: red;'>❌ tableExists() falhou ou tabela não existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro nos testes de funções helper: " . $e->getMessage() . "</p>";
}

// ========================================
// 4. TESTE DE CLASSES
// ========================================

echo "<h2>4. 📦 Teste de Classes</h2>";

try {
    // Carregar classes
    require_once '../src/BaseModel.php';
    require_once '../src/Cliente.php';
    require_once '../src/Pet.php';
    require_once '../src/Agendamento.php';
    
    // Testar Cliente
    $clientes = Cliente::listarTodos();
    echo "<p style='color: green;'>✅ Cliente::listarTodos() - " . count($clientes) . " registros</p>";
    
    // Testar Pet
    $pets = Pet::listarTodos();
    echo "<p style='color: green;'>✅ Pet::listarTodos() - " . count($pets) . " registros</p>";
    
    // Testar Agendamento
    $agendamentos = Agendamento::listarTodos();
    echo "<p style='color: green;'>✅ Agendamento::listarTodos() - " . count($agendamentos) . " registros</p>";
    
    // Testar métodos específicos
    if (method_exists('Cliente', 'buscarTelefones')) {
        echo "<p style='color: green;'>✅ Cliente::buscarTelefones() existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Cliente::buscarTelefones() não existe</p>";
    }
    
    if (method_exists('Pet', 'buscarPorCliente')) {
        echo "<p style='color: green;'>✅ Pet::buscarPorCliente() existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Pet::buscarPorCliente() não existe</p>";
    }
    
    if (method_exists('Agendamento', 'deletar')) {
        echo "<p style='color: green;'>✅ Agendamento::deletar() existe</p>";
    } else {
        echo "<p style='color: red;'>❌ Agendamento::deletar() não existe</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro nos testes de classes: " . $e->getMessage() . "</p>";
}

// ========================================
// 5. TESTE DE PERFORMANCE
// ========================================

echo "<h2>5. ⚡ Teste de Performance</h2>";

try {
    $inicio = microtime(true);
    
    // Executar várias queries
    for ($i = 0; $i < 10; $i++) {
        fetchOne('SELECT COUNT(*) as total FROM clientes');
        fetchOne('SELECT COUNT(*) as total FROM pets');
        fetchOne('SELECT COUNT(*) as total FROM agendamentos');
    }
    
    $fim = microtime(true);
    $tempo = round(($fim - $inicio) * 1000, 2);
    
    echo "<p style='color: green;'>✅ 30 queries executadas em {$tempo}ms</p>";
    
    if ($tempo < 1000) {
        echo "<p style='color: green;'>✅ Performance excelente</p>";
    } elseif ($tempo < 3000) {
        echo "<p style='color: orange;'>⚠️ Performance aceitável</p>";
    } else {
        echo "<p style='color: red;'>❌ Performance lenta</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no teste de performance: " . $e->getMessage() . "</p>";
}

// ========================================
// 6. INFORMAÇÕES DO SISTEMA
// ========================================

echo "<h2>6. ℹ️ Informações do Sistema</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Item</th><th>Valor</th></tr>";

// Informações do PHP
echo "<tr><td>Versão do PHP</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>Extensão PDO</td><td>" . (extension_loaded('pdo') ? '✅ Carregada' : '❌ Não carregada') . "</td></tr>";
echo "<tr><td>Extensão PDO MySQL</td><td>" . (extension_loaded('pdo_mysql') ? '✅ Carregada' : '❌ Não carregada') . "</td></tr>";

// Informações do banco
try {
    $pdo = getDb();
    if ($pdo) {
        $stmt = $pdo->query('SELECT VERSION() as version');
        $result = $stmt->fetch();
        echo "<tr><td>Versão do MySQL</td><td>" . $result['version'] . "</td></tr>";
        
        $stmt = $pdo->query('SELECT @@max_connections as max_conn');
        $result = $stmt->fetch();
        echo "<tr><td>Máximo de conexões</td><td>" . $result['max_conn'] . "</td></tr>";
        
        $stmt = $pdo->query('SHOW VARIABLES LIKE "wait_timeout"');
        $result = $stmt->fetch();
        echo "<tr><td>Timeout de conexão</td><td>" . $result['Value'] . " segundos</td></tr>";
    }
} catch (Exception $e) {
    echo "<tr><td>Informações do banco</td><td>❌ Erro: " . $e->getMessage() . "</td></tr>";
}

// Informações da aplicação
echo "<tr><td>Ambiente</td><td>" . Config::get('APP_ENV') . "</td></tr>";
echo "<tr><td>Debug</td><td>" . (Config::isDebug() ? 'Ativo' : 'Inativo') . "</td></tr>";
echo "<tr><td>Timezone</td><td>" . Config::get('APP_TIMEZONE') . "</td></tr>";

echo "</table>";

// ========================================
// 7. RECOMENDAÇÕES
// ========================================

echo "<h2>7. 📋 Recomendações</h2>";

echo "<ul>";
echo "<li>✅ Execute este monitor regularmente para verificar a saúde da conexão</li>";
echo "<li>✅ Monitore os logs em logs/error.log</li>";
echo "<li>✅ Configure alertas se a performance ficar lenta</li>";
echo "<li>✅ Faça backup regular do banco de dados</li>";
echo "<li>✅ Mantenha o sistema atualizado</li>";
echo "</ul>";

echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='corrigir-sistema-completo.php'>Correção Completa</a> | <a href='dashboard.php'>Dashboard</a> | <a href='teste-sistema.php'>Teste do Sistema</a></p>";

echo "<hr>";
echo "<p><strong>Monitor executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Versão do sistema:</strong> " . (defined('APP_VERSION') ? APP_VERSION : 'N/A') . "</p>";
?> 