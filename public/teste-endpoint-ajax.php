<?php
/**
 * Teste Específico do Endpoint AJAX de Agendamento
 * Sistema Bichos do Bairro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Teste Específico do Endpoint AJAX</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>";

// ========================================
// 1. VERIFICAR SE O SISTEMA CARREGA
// ========================================
echo "<div class='section'>";
echo "<h2>1. 🔌 Verificação de Carregamento do Sistema</h2>";

try {
    require_once __DIR__ . '/../src/init.php';
    echo "<div class='success'>✅ Sistema carregado com sucesso</div>";
    
    // Verificar se as classes estão disponíveis
    if (class_exists('Agendamento')) {
        echo "<div class='success'>✅ Classe Agendamento carregada</div>";
    } else {
        echo "<div class='error'>❌ Classe Agendamento não encontrada</div>";
    }
    
    if (class_exists('Cliente')) {
        echo "<div class='success'>✅ Classe Cliente carregada</div>";
    } else {
        echo "<div class='error'>❌ Classe Cliente não encontrada</div>";
    }
    
    if (class_exists('Pet')) {
        echo "<div class='success'>✅ Classe Pet carregada</div>";
    } else {
        echo "<div class='error'>❌ Classe Pet não encontrada</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao carregar sistema: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 2. VERIFICAR DADOS DISPONÍVEIS
// ========================================
echo "<div class='section'>";
echo "<h2>2. 📊 Verificação de Dados Disponíveis</h2>";

try {
    if (isset($pdo)) {
        // Verificar clientes
        $stmt = $pdo->query("SELECT id, nome FROM clientes LIMIT 3");
        $clientes = $stmt->fetchAll();
        echo "<div class='info'>📊 Clientes disponíveis: " . count($clientes) . "</div>";
        if (count($clientes) > 0) {
            echo "<pre>Primeiros clientes: " . print_r($clientes, true) . "</pre>";
        }
        
        // Verificar pets
        $stmt = $pdo->query("SELECT id, nome, cliente_id FROM pets LIMIT 3");
        $pets = $stmt->fetchAll();
        echo "<div class='info'>📊 Pets disponíveis: " . count($pets) . "</div>";
        if (count($pets) > 0) {
            echo "<pre>Primeiros pets: " . print_r($pets, true) . "</pre>";
        }
        
        // Verificar agendamentos existentes
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
        $count = $stmt->fetch();
        echo "<div class='info'>📊 Agendamentos existentes: " . $count['total'] . "</div>";
        
    } else {
        echo "<div class='error'>❌ Conexão PDO não disponível</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao verificar dados: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 3. TESTE DIRETO DO MÉTODO CRIAR
// ========================================
echo "<div class='section'>";
echo "<h2>3. ➕ Teste Direto do Método Agendamento::criar()</h2>";

try {
    if (isset($pdo) && count($clientes) > 0 && count($pets) > 0) {
        $cliente = $clientes[0];
        $pet = $pets[0];
        
        $dados = [
            'cliente_id' => $cliente['id'],
            'pet_id' => $pet['id'],
            'data' => date('Y-m-d'),
            'hora' => '14:00',
            'servico' => 'Teste Direto',
            'observacoes' => 'Teste via script de diagnóstico',
            'status' => 'Pendente'
        ];
        
        echo "<h3>Dados para teste:</h3>";
        echo "<pre>" . print_r($dados, true) . "</pre>";
        
        $resultado = Agendamento::criar($dados);
        
        if ($resultado) {
            echo "<div class='success'>✅ Agendamento criado com sucesso! ID: $resultado</div>";
            
            // Verificar se foi realmente inserido
            $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ?");
            $stmt->execute([$resultado]);
            $agendamento = $stmt->fetch();
            
            if ($agendamento) {
                echo "<div class='success'>✅ Agendamento encontrado no banco:</div>";
                echo "<pre>" . print_r($agendamento, true) . "</pre>";
                
                // Limpar o teste
                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
                $stmt->execute([$resultado]);
                echo "<div class='info'>🧹 Agendamento de teste removido</div>";
            } else {
                echo "<div class='error'>❌ Agendamento não encontrado no banco</div>";
            }
        } else {
            echo "<div class='error'>❌ Falha ao criar agendamento</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Dados insuficientes para teste</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro no teste direto: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 4. SIMULAÇÃO DO ENDPOINT AJAX
// ========================================
echo "<div class='section'>";
echo "<h2>4. 🌐 Simulação do Endpoint AJAX</h2>";

try {
    if (isset($pdo) && count($clientes) > 0 && count($pets) > 0) {
        $cliente = $clientes[0];
        $pet = $pets[0];
        
        // Simular dados POST
        $_POST = [
            'cliente_id' => $cliente['id'],
            'pet_id' => $pet['id'],
            'data' => date('Y-m-d'),
            'hora' => '15:00',
            'servico' => 'Teste AJAX Simulado',
            'observacoes' => 'Teste via script de diagnóstico',
            'status' => 'Pendente'
        ];
        
        $_GET['action'] = 'salvar';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        echo "<h3>Dados POST simulados:</h3>";
        echo "<pre>" . print_r($_POST, true) . "</pre>";
        
        // Capturar saída
        ob_start();
        
        // Incluir o arquivo de agendamentos
        include 'agendamentos.php';
        
        $output = ob_get_clean();
        
        echo "<h3>Resposta do endpoint:</h3>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
        
        if (strpos($output, 'ok') !== false) {
            echo "<div class='success'>✅ Endpoint AJAX funcionando corretamente</div>";
        } else {
            echo "<div class='error'>❌ Endpoint AJAX retornou erro</div>";
            
            // Verificar logs criados
            echo "<h3>Verificando logs criados:</h3>";
            
            $logFiles = [
                '../logs/debug_post.txt',
                '../logs/debug_salvar.txt',
                'debug_agendamento.txt'
            ];
            
            foreach ($logFiles as $logFile) {
                if (file_exists($logFile)) {
                    $content = file_get_contents($logFile);
                    echo "<h4>Conteúdo de $logFile:</h4>";
                    echo "<pre>" . htmlspecialchars($content) . "</pre>";
                } else {
                    echo "<div class='warning'>⚠️ Arquivo $logFile não foi criado</div>";
                }
            }
        }
        
    } else {
        echo "<div class='warning'>⚠️ Dados insuficientes para simulação</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro na simulação do endpoint: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 5. VERIFICAÇÃO DE PERMISSÕES DE LOG
// ========================================
echo "<div class='section'>";
echo "<h2>5. 📝 Verificação de Permissões de Log</h2>";

$logDir = __DIR__ . '/../logs';
$testFile = $logDir . '/teste_permissao.txt';

echo "<div class='info'>📁 Diretório de logs: $logDir</div>";

if (is_dir($logDir)) {
    echo "<div class='success'>✅ Diretório de logs existe</div>";
    
    if (is_writable($logDir)) {
        echo "<div class='success'>✅ Diretório de logs tem permissão de escrita</div>";
        
        // Testar criação de arquivo
        $testContent = "Teste de permissão - " . date('Y-m-d H:i:s');
        if (file_put_contents($testFile, $testContent) !== false) {
            echo "<div class='success'>✅ Arquivo de teste criado com sucesso</div>";
            
            // Ler o arquivo criado
            $readContent = file_get_contents($testFile);
            if ($readContent === $testContent) {
                echo "<div class='success'>✅ Arquivo pode ser lido corretamente</div>";
            } else {
                echo "<div class='error'>❌ Problema na leitura do arquivo</div>";
            }
            
            // Limpar arquivo de teste
            unlink($testFile);
            echo "<div class='info'>🧹 Arquivo de teste removido</div>";
        } else {
            echo "<div class='error'>❌ Não foi possível criar arquivo de teste</div>";
        }
    } else {
        echo "<div class='error'>❌ Diretório de logs não tem permissão de escrita</div>";
    }
} else {
    echo "<div class='error'>❌ Diretório de logs não existe</div>";
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>✅ Teste do Endpoint AJAX Concluído</h2>";
echo "<p>Este teste identificou os problemas específicos do endpoint AJAX. Verifique os resultados acima para entender onde está falhando.</p>";
echo "</div>";
?> 