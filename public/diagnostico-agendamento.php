<?php
/**
 * Diagnóstico Completo do Sistema de Agendamento
 * Sistema Bichos do Bairro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico Completo do Sistema de Agendamento</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .warning { background-color: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>";

// ========================================
// 1. VERIFICAÇÃO DE CONEXÃO COM BANCO
// ========================================
echo "<div class='section'>";
echo "<h2>1. 🔌 Verificação de Conexão com Banco de Dados</h2>";

try {
    require_once __DIR__ . '/../src/init.php';
    echo "<div class='success'>✅ Sistema inicializado com sucesso</div>";
    
    // Verificar se $pdo está disponível
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "<div class='success'>✅ Conexão PDO estabelecida</div>";
        
        // Testar conexão
        $stmt = $pdo->query("SELECT 1");
        if ($stmt) {
            echo "<div class='success'>✅ Query de teste executada com sucesso</div>";
        }
    } else {
        echo "<div class='error'>❌ Variável \$pdo não está disponível</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro na inicialização: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 2. VERIFICAÇÃO DE TABELAS
// ========================================
echo "<div class='section'>";
echo "<h2>2. 📋 Verificação de Tabelas</h2>";

try {
    if (isset($pdo)) {
        // Verificar tabela agendamentos
        $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ Tabela 'agendamentos' existe</div>";
            
            // Verificar estrutura da tabela
            $stmt = $pdo->query("DESCRIBE agendamentos");
            $columns = $stmt->fetchAll();
            
            echo "<h3>Estrutura da tabela agendamentos:</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($columns as $column) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Verificar se há registros
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
            $count = $stmt->fetch();
            echo "<div class='info'>📊 Total de agendamentos: " . $count['total'] . "</div>";
            
        } else {
            echo "<div class='error'>❌ Tabela 'agendamentos' não existe</div>";
        }
        
        // Verificar outras tabelas necessárias
        $tables = ['clientes', 'pets'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✅ Tabela '$table' existe</div>";
            } else {
                echo "<div class='error'>❌ Tabela '$table' não existe</div>";
            }
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao verificar tabelas: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 3. TESTE DE INSERÇÃO DE AGENDAMENTO
// ========================================
echo "<div class='section'>";
echo "<h2>3. ➕ Teste de Inserção de Agendamento</h2>";

try {
    if (isset($pdo)) {
        // Verificar se há clientes e pets para teste
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
        $clientesCount = $stmt->fetch()['total'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
        $petsCount = $stmt->fetch()['total'];
        
        echo "<div class='info'>📊 Clientes disponíveis: $clientesCount</div>";
        echo "<div class='info'>📊 Pets disponíveis: $petsCount</div>";
        
        if ($clientesCount > 0 && $petsCount > 0) {
            // Pegar primeiro cliente e pet
            $stmt = $pdo->query("SELECT id FROM clientes LIMIT 1");
            $cliente = $stmt->fetch();
            
            $stmt = $pdo->query("SELECT id FROM pets LIMIT 1");
            $pet = $stmt->fetch();
            
            if ($cliente && $pet) {
                // Testar inserção via classe Agendamento
                $dados = [
                    'cliente_id' => $cliente['id'],
                    'pet_id' => $pet['id'],
                    'data' => date('Y-m-d'),
                    'hora' => '10:00',
                    'servico' => 'Teste de Diagnóstico',
                    'observacoes' => 'Agendamento criado pelo script de diagnóstico',
                    'status' => 'Pendente'
                ];
                
                echo "<h3>Testando inserção com dados:</h3>";
                echo "<pre>" . print_r($dados, true) . "</pre>";
                
                $resultado = Agendamento::criar($dados);
                
                if ($resultado) {
                    echo "<div class='success'>✅ Agendamento criado com sucesso! ID: $resultado</div>";
                    
                    // Verificar se o agendamento foi realmente inserido
                    $stmt = $pdo->prepare("SELECT * FROM agendamentos WHERE id = ?");
                    $stmt->execute([$resultado]);
                    $agendamento = $stmt->fetch();
                    
                    if ($agendamento) {
                        echo "<div class='success'>✅ Agendamento encontrado no banco:</div>";
                        echo "<pre>" . print_r($agendamento, true) . "</pre>";
                        
                        // Limpar o agendamento de teste
                        $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
                        $stmt->execute([$resultado]);
                        echo "<div class='info'>🧹 Agendamento de teste removido</div>";
                    } else {
                        echo "<div class='error'>❌ Agendamento não encontrado no banco após inserção</div>";
                    }
                } else {
                    echo "<div class='error'>❌ Falha ao criar agendamento</div>";
                }
            } else {
                echo "<div class='warning'>⚠️ Não foi possível obter cliente ou pet para teste</div>";
            }
        } else {
            echo "<div class='warning'>⚠️ Não há clientes ou pets suficientes para teste</div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro no teste de inserção: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 4. TESTE DE LISTAGEM DE AGENDAMENTOS
// ========================================
echo "<div class='section'>";
echo "<h2>4. 📋 Teste de Listagem de Agendamentos</h2>";

try {
    if (isset($pdo)) {
        // Testar método listarTodos
        $agendamentos = Agendamento::listarTodos();
        
        if (is_array($agendamentos)) {
            echo "<div class='success'>✅ Método listarTodos() executado com sucesso</div>";
            echo "<div class='info'>📊 Agendamentos encontrados: " . count($agendamentos) . "</div>";
            
            if (count($agendamentos) > 0) {
                echo "<h3>Primeiros 3 agendamentos:</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Cliente</th><th>Pet</th><th>Data</th><th>Hora</th><th>Serviço</th><th>Status</th></tr>";
                
                $count = 0;
                foreach ($agendamentos as $agendamento) {
                    if ($count >= 3) break;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($agendamento['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['cliente_nome'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['pet_nome'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['data']) . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['hora']) . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['servico']) . "</td>";
                    echo "<td>" . htmlspecialchars($agendamento['status']) . "</td>";
                    echo "</tr>";
                    $count++;
                }
                echo "</table>";
            } else {
                echo "<div class='info'>📝 Nenhum agendamento encontrado</div>";
            }
        } else {
            echo "<div class='error'>❌ Método listarTodos() não retornou array</div>";
        }
        
        // Testar método getAgendamentosPorData
        $hoje = date('Y-m-d');
        $agendamentosHoje = Agendamento::getAgendamentosPorData($hoje);
        
        echo "<div class='info'>📅 Agendamentos para hoje ($hoje): " . count($agendamentosHoje) . "</div>";
        
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro no teste de listagem: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 5. VERIFICAÇÃO DE ARQUIVOS DE LOG
// ========================================
echo "<div class='section'>";
echo "<h2>5. 📝 Verificação de Arquivos de Log</h2>";

$logFiles = [
    '../logs/error.log',
    '../logs/debug_post.txt',
    '../logs/debug_salvar.txt',
    'debug_agendamento.txt'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $size = filesize($logFile);
        $modified = date('Y-m-d H:i:s', filemtime($logFile));
        echo "<div class='info'>📄 $logFile - Tamanho: " . number_format($size) . " bytes - Modificado: $modified</div>";
        
        if ($size > 0) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);
            $lastLines = array_slice($lines, -5); // Últimas 5 linhas
            
            echo "<h4>Últimas linhas de $logFile:</h4>";
            echo "<pre>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
        }
    } else {
        echo "<div class='warning'>⚠️ Arquivo $logFile não existe</div>";
    }
}
echo "</div>";

// ========================================
// 6. VERIFICAÇÃO DE CONFIGURAÇÕES
// ========================================
echo "<div class='section'>";
echo "<h2>6. ⚙️ Verificação de Configurações</h2>";

try {
    if (class_exists('Config')) {
        $dbConfig = Config::getDbConfig();
        echo "<h3>Configurações do Banco:</h3>";
        echo "<pre>" . print_r($dbConfig, true) . "</pre>";
        
        echo "<h3>Configurações da Aplicação:</h3>";
        $appConfig = [
            'APP_NAME' => Config::get('APP_NAME'),
            'APP_ENV' => Config::get('APP_ENV'),
            'APP_DEBUG' => Config::get('APP_DEBUG'),
            'APP_TIMEZONE' => Config::get('APP_TIMEZONE')
        ];
        echo "<pre>" . print_r($appConfig, true) . "</pre>";
    } else {
        echo "<div class='error'>❌ Classe Config não encontrada</div>";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao verificar configurações: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 7. TESTE DE ENDPOINT AJAX
// ========================================
echo "<div class='section'>";
echo "<h2>7. 🌐 Teste de Endpoint AJAX</h2>";

try {
    if (isset($pdo)) {
        // Simular dados POST
        $_POST = [
            'cliente_id' => '1',
            'pet_id' => '1',
            'data' => date('Y-m-d'),
            'hora' => '11:00',
            'servico' => 'Teste AJAX',
            'observacoes' => 'Teste via diagnóstico',
            'status' => 'Pendente'
        ];
        
        $_GET['action'] = 'salvar';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        echo "<h3>Simulando requisição AJAX:</h3>";
        echo "<pre>POST: " . print_r($_POST, true) . "</pre>";
        
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
        }
        
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro no teste do endpoint AJAX: " . htmlspecialchars($e->getMessage()) . "</div>";
}
echo "</div>";

// ========================================
// 8. RESUMO E RECOMENDAÇÕES
// ========================================
echo "<div class='section'>";
echo "<h2>8. 📋 Resumo e Recomendações</h2>";

echo "<h3>Problemas Identificados:</h3>";
echo "<ul>";
echo "<li>Verificar se a conexão com o banco está estável</li>";
echo "<li>Confirmar se as tabelas têm a estrutura correta</li>";
echo "<li>Verificar se os métodos da classe Agendamento estão funcionando</li>";
echo "<li>Testar se o endpoint AJAX está respondendo corretamente</li>";
echo "</ul>";

echo "<h3>Próximos Passos:</h3>";
echo "<ol>";
echo "<li>Verificar se o banco de dados está acessível</li>";
echo "<li>Confirmar se as tabelas foram criadas corretamente</li>";
echo "<li>Testar inserção manual de agendamento</li>";
echo "<li>Verificar logs de erro para detalhes específicos</li>";
echo "<li>Testar a interface web de agendamento</li>";
echo "</ol>";

echo "<h3>Scripts Úteis:</h3>";
echo "<ul>";
echo "<li><a href='criar-banco.php'>criar-banco.php</a> - Criar banco SQLite</li>";
echo "<li><a href='criar-tabelas.php'>criar-tabelas.php</a> - Criar tabelas MySQL</li>";
echo "<li><a href='teste-insercao-agendamento.php'>teste-insercao-agendamento.php</a> - Teste de inserção</li>";
echo "<li><a href='verificar-dados.php'>verificar-dados.php</a> - Verificar dados</li>";
echo "</ul>";
echo "</div>";

echo "<div class='section info'>";
echo "<h2>✅ Diagnóstico Concluído</h2>";
echo "<p>Este diagnóstico identificou os principais pontos do sistema de agendamento. Verifique os resultados acima e siga as recomendações para resolver os problemas encontrados.</p>";
echo "</div>";
?> 