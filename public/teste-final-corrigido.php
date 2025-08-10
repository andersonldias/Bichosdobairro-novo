<?php
/**
 * Teste Final do Sistema - Versão Corrigida
 * Sistema Bichos do Bairro
 * 
 * Este script testa todas as funcionalidades após as correções
 */

// Carregar inicialização completa do sistema
require_once '../src/init.php';

echo "<h1>🧪 Teste Final do Sistema - Bichos do Bairro (CORRIGIDO)</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// ========================================
// 1. TESTE DE INICIALIZAÇÃO
// ========================================

echo "<h2>1. 🔧 Teste de Inicialização</h2>";

$inicializacaoOK = true;

// Verificar constantes
if (defined('APP_ROOT')) {
    echo "<p style='color: green;'>✅ APP_ROOT definida</p>";
} else {
    echo "<p style='color: red;'>❌ APP_ROOT não definida</p>";
    $inicializacaoOK = false;
}

if (defined('APP_VERSION')) {
    echo "<p style='color: green;'>✅ APP_VERSION definida</p>";
} else {
    echo "<p style='color: red;'>❌ APP_VERSION não definida</p>";
    $inicializacaoOK = false;
}

// Verificar configurações
if (class_exists('Config')) {
    echo "<p style='color: green;'>✅ Configurações carregadas</p>";
} else {
    echo "<p style='color: red;'>❌ Configurações não carregadas</p>";
    $inicializacaoOK = false;
}

// ========================================
// 2. TESTE DE CONEXÃO COM BANCO
// ========================================

echo "<h2>2. 🗄️ Teste de Conexão com Banco</h2>";

$conexaoOK = true;

try {
    $pdo = getDb();
    if ($pdo instanceof PDO) {
        echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";
        
        // Testar query simples
        $stmt = getDb()->query('SELECT 1 as test');
        $result = $stmt->fetch();
        if ($result && $result['test'] == 1) {
            echo "<p style='color: green;'>✅ Query de teste executada</p>";
        } else {
            echo "<p style='color: red;'>❌ Query de teste falhou</p>";
            $conexaoOK = false;
        }
        
        // Testar reconexão
        $pdo = null;
        $pdo = getDb();
        if ($pdo instanceof PDO) {
            echo "<p style='color: green;'>✅ Reconexão funcionando</p>";
        } else {
            echo "<p style='color: red;'>❌ Reconexão falhou</p>";
            $conexaoOK = false;
        }
        
    } else {
        echo "<p style='color: red;'>❌ Falha na conexão</p>";
        $conexaoOK = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro na conexão: " . $e->getMessage() . "</p>";
    $conexaoOK = false;
}

// ========================================
// 3. TESTE DE ESTRUTURA DO BANCO
// ========================================

echo "<h2>3. 🏗️ Teste de Estrutura do Banco</h2>";

$estruturaOK = true;

if ($conexaoOK) {
    try {
        // Verificar tabelas essenciais
        $tabelas = ['clientes', 'pets', 'agendamentos', 'telefones'];
        
        foreach ($tabelas as $tabela) {
            if (tableExists($tabela)) {
                echo "<p style='color: green;'>✅ Tabela $tabela existe</p>";
            } else {
                echo "<p style='color: red;'>❌ Tabela $tabela não existe</p>";
                $estruturaOK = false;
            }
        }
        
        // Verificar colunas importantes
        $colunas = [
            'agendamentos' => ['status', 'created_at', 'updated_at'],
            'clientes' => ['created_at', 'updated_at'],
            'pets' => ['created_at', 'updated_at']
        ];
        
        foreach ($colunas as $tabela => $cols) {
            foreach ($cols as $coluna) {
                $sql = "SHOW COLUMNS FROM $tabela LIKE '$coluna'";
                $stmt = getDb()->query($sql);
                if ($stmt->rowCount() > 0) {
                    echo "<p style='color: green;'>✅ Coluna $coluna existe em $tabela</p>";
                } else {
                    echo "<p style='color: red;'>❌ Coluna $coluna não existe em $tabela</p>";
                    $estruturaOK = false;
                }
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro na verificação de estrutura: " . $e->getMessage() . "</p>";
        $estruturaOK = false;
    }
}

// ========================================
// 4. TESTE DE CLASSES
// ========================================

echo "<h2>4. 📦 Teste de Classes</h2>";

$classesOK = true;

try {
    // Verificar se as classes foram carregadas pelo init.php
    if (class_exists('Cliente') && class_exists('Pet') && class_exists('Agendamento')) {
        echo "<p style='color: green;'>✅ Classes carregadas</p>";
    } else {
        echo "<p style='color: red;'>❌ Classes não carregadas</p>";
        $classesOK = false;
    }
    
    // Verificar métodos da classe Cliente
    $metodosCliente = ['listarTodos', 'criar', 'atualizar', 'buscarTelefones', 'buscarPorId', 'deletar'];
    foreach ($metodosCliente as $metodo) {
        if (method_exists('Cliente', $metodo)) {
            echo "<p style='color: green;'>✅ Cliente::$metodo() existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Cliente::$metodo() não existe</p>";
            $classesOK = false;
        }
    }
    
    // Verificar métodos da classe Pet
    $metodosPet = ['listarTodos', 'criar', 'atualizar', 'buscarPorCliente', 'buscarPorId', 'deletar'];
    foreach ($metodosPet as $metodo) {
        if (method_exists('Pet', $metodo)) {
            echo "<p style='color: green;'>✅ Pet::$metodo() existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Pet::$metodo() não existe</p>";
            $classesOK = false;
        }
    }
    
    // Verificar métodos da classe Agendamento
    $metodosAgendamento = ['listarTodos', 'criar', 'atualizar', 'deletar', 'buscarPorId'];
    foreach ($metodosAgendamento as $metodo) {
        if (method_exists('Agendamento', $metodo)) {
            echo "<p style='color: green;'>✅ Agendamento::$metodo() existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Agendamento::$metodo() não existe</p>";
            $classesOK = false;
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar classes: " . $e->getMessage() . "</p>";
    $classesOK = false;
}

// ========================================
// 5. TESTE DE FUNCIONALIDADES
// ========================================

echo "<h2>5. 🧪 Teste de Funcionalidades</h2>";

$funcionalidadesOK = true;

if ($conexaoOK && $classesOK) {
    try {
        // Teste 1: Listar dados
        $clientes = Cliente::listarTodos();
        echo "<p style='color: green;'>✅ Listagem de clientes: " . count($clientes) . " registros</p>";
        
        $pets = Pet::listarTodos();
        echo "<p style='color: green;'>✅ Listagem de pets: " . count($pets) . " registros</p>";
        
        $agendamentos = Agendamento::listarTodos();
        echo "<p style='color: green;'>✅ Listagem de agendamentos: " . count($agendamentos) . " registros</p>";
        
        // Teste 2: Funções helper
        $total = countRecords('clientes');
        echo "<p style='color: green;'>✅ countRecords(): $total clientes</p>";
        
        $existe = tableExists('agendamentos');
        if ($existe) {
            echo "<p style='color: green;'>✅ tableExists(): Tabela agendamentos existe</p>";
        } else {
            echo "<p style='color: red;'>❌ tableExists(): FALHOU</p>";
            $funcionalidadesOK = false;
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Erro nos testes de funcionalidades: " . $e->getMessage() . "</p>";
        $funcionalidadesOK = false;
    }
}

// ========================================
// 6. TESTE DE PERFORMANCE
// ========================================

echo "<h2>6. ⚡ Teste de Performance</h2>";

$performanceOK = true;

try {
    $inicio = microtime(true);
    
    // Executar várias operações
    for ($i = 0; $i < 5; $i++) {
        Cliente::listarTodos();
        Pet::listarTodos();
        Agendamento::listarTodos();
    }
    
    $fim = microtime(true);
    $tempo = round(($fim - $inicio) * 1000, 2);
    
    echo "<p style='color: green;'>✅ 15 operações executadas em {$tempo}ms</p>";
    
    if ($tempo < 1000) {
        echo "<p style='color: green;'>✅ Performance excelente</p>";
    } elseif ($tempo < 3000) {
        echo "<p style='color: orange;'>⚠️ Performance aceitável</p>";
    } else {
        echo "<p style='color: red;'>❌ Performance lenta</p>";
        $performanceOK = false;
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro no teste de performance: " . $e->getMessage() . "</p>";
    $performanceOK = false;
}

// ========================================
// 7. RELATÓRIO FINAL
// ========================================

echo "<h2>7. 📊 Relatório Final</h2>";

$totalTests = 6;
$passedTests = 0;

if ($inicializacaoOK) $passedTests++;
if ($conexaoOK) $passedTests++;
if ($estruturaOK) $passedTests++;
if ($classesOK) $passedTests++;
if ($funcionalidadesOK) $passedTests++;
if ($performanceOK) $passedTests++;

$percentual = round(($passedTests / $totalTests) * 100, 1);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Teste</th><th>Status</th></tr>";

echo "<tr><td>Inicialização</td><td>" . ($inicializacaoOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";
echo "<tr><td>Conexão com Banco</td><td>" . ($conexaoOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";
echo "<tr><td>Estrutura do Banco</td><td>" . ($estruturaOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";
echo "<tr><td>Classes</td><td>" . ($classesOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";
echo "<tr><td>Funcionalidades</td><td>" . ($funcionalidadesOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";
echo "<tr><td>Performance</td><td>" . ($performanceOK ? '✅ OK' : '❌ FALHOU') . "</td></tr>";

echo "</table>";

echo "<h3>Resultado Geral: $passedTests/$totalTests ($percentual%)</h3>";

if ($percentual == 100) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 TODOS OS TESTES PASSARAM! O sistema está funcionando perfeitamente!</p>";
} elseif ($percentual >= 80) {
    echo "<p style='color: orange; font-size: 18px; font-weight: bold;'>⚠️ A maioria dos testes passou. Alguns ajustes podem ser necessários.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ Muitos testes falharam. Execute a correção completa do sistema.</p>";
}

echo "<hr>";
echo "<p><strong>Teste executado em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Versão do sistema:</strong> " . (defined('APP_VERSION') ? APP_VERSION : 'N/A') . "</p>";
echo "<p><a href='teste-final-sistema.php'>Teste Original</a> | <a href='teste-conexao-simples.php'>Teste de Conexão</a> | <a href='dashboard.php'>Dashboard</a></p>";
?> 