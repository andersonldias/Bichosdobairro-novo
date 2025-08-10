<?php
/**
 * Teste Detalhado do Endpoint AJAX - Simulação Completa
 * Sistema Bichos do Bairro
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Teste Detalhado do Endpoint AJAX</h1>";
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
// 1. CARREGAR SISTEMA
// ========================================
echo "<div class='section'>";
echo "<h2>1. 🔌 Carregamento do Sistema</h2>";

try {
    require_once __DIR__ . '/../src/init.php';
    echo "<div class='success'>✅ Sistema carregado</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
echo "</div>";

// ========================================
// 2. SIMULAR EXATAMENTE O ENDPOINT AJAX
// ========================================
echo "<div class='section'>";
echo "<h2>2. 🌐 Simulação Completa do Endpoint AJAX</h2>";

try {
    // Pegar dados válidos
    $stmt = $pdo->query("SELECT id FROM clientes LIMIT 1");
    $cliente = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM pets LIMIT 1");
    $pet = $stmt->fetch();
    
    if (!$cliente || !$pet) {
        echo "<div class='error'>❌ Dados insuficientes para teste</div>";
        exit;
    }
    
    // Simular dados POST exatamente como o formulário envia
    $_POST = [
        'cliente_id' => $cliente['id'],
        'pet_id' => $pet['id'],
        'data' => date('Y-m-d'),
        'hora' => '16:00',
        'servico' => 'Teste Detalhado AJAX',
        'observacoes' => 'Teste via script detalhado',
        'status' => 'Pendente'
    ];
    
    $_GET['action'] = 'salvar';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    echo "<h3>Dados simulados:</h3>";
    echo "<pre>POST: " . print_r($_POST, true) . "</pre>";
    echo "<pre>GET: " . print_r($_GET, true) . "</pre>";
    echo "<pre>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</pre>";
    
    // Verificar se a condição do endpoint é atendida
    $condicao1 = isset($_GET['action']);
    $condicao2 = $_GET['action'] === 'salvar';
    $condicao3 = $_SERVER['REQUEST_METHOD'] === 'POST';
    
    echo "<h3>Verificação das condições do endpoint:</h3>";
    echo "<div class='info'>Condição 1 (isset action): " . ($condicao1 ? '✅' : '❌') . "</div>";
    echo "<div class='info'>Condição 2 (action = salvar): " . ($condicao2 ? '✅' : '❌') . "</div>";
    echo "<div class='info'>Condição 3 (method = POST): " . ($condicao3 ? '✅' : '❌') . "</div>";
    
    if ($condicao1 && $condicao2 && $condicao3) {
        echo "<div class='success'>✅ Todas as condições do endpoint são atendidas</div>";
        
        // Executar o código do endpoint passo a passo
        echo "<h3>Executando código do endpoint:</h3>";
        
        // Passo 1: Log do POST
        echo "<div class='info'>Passo 1: Criando log do POST...</div>";
        $logResult1 = file_put_contents(__DIR__ . '/../logs/debug_post.txt', date('c') . "\n" . print_r($_POST, true));
        echo "<div class='info'>Resultado do log POST: " . ($logResult1 !== false ? '✅' : '❌') . "</div>";
        
        // Passo 2: Log da chamada
        echo "<div class='info'>Passo 2: Criando log da chamada...</div>";
        $logResult2 = file_put_contents(__DIR__ . '/../logs/debug_salvar.txt', date('c') . " - Chamou endpoint salvar\n", FILE_APPEND);
        echo "<div class='info'>Resultado do log chamada: " . ($logResult2 !== false ? '✅' : '❌') . "</div>";
        
        // Passo 3: Extrair dados
        echo "<div class='info'>Passo 3: Extraindo dados do POST...</div>";
        $debug = [];
        $debug['POST'] = $_POST;
        $id = $_POST['id'] ?? '';
        $pet_id = $_POST['pet_id'] ?? '';
        $cliente_id = $_POST['cliente_id'] ?? '';
        $data = $_POST['data'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $servico = $_POST['servico'] ?? '';
        $status = $_POST['status'] ?? 'Pendente';
        $observacoes = $_POST['observacoes'] ?? '';
        $debug['valores'] = compact('id','pet_id','cliente_id','data','hora','servico','status','observacoes');
        
        echo "<div class='success'>✅ Dados extraídos com sucesso</div>";
        echo "<pre>Dados extraídos: " . print_r($debug['valores'], true) . "</pre>";
        
        // Passo 4: Executar criação
        echo "<div class='info'>Passo 4: Executando criação do agendamento...</div>";
        
        try {
            if ($id) {
                echo "<div class='info'>Modo: Atualização (ID: $id)</div>";
                $debug['acao'] = 'atualizar';
                $resultado = Agendamento::atualizar($id, $pet_id, $cliente_id, $data, $hora, $servico, $status, $observacoes);
                $debug['resultado_funcao'] = $resultado;
            } else {
                echo "<div class='info'>Modo: Criação</div>";
                $debug['acao'] = 'criar';
                $dados = [
                    'cliente_id' => $cliente_id,
                    'pet_id' => $pet_id,
                    'data' => $data,
                    'hora' => $hora,
                    'servico' => $servico,
                    'observacoes' => $observacoes,
                    'status' => $status
                ];
                $debug['dados_para_criar'] = $dados;
                echo "<div class='info'>Chamando Agendamento::criar()...</div>";
                $resultado = Agendamento::criar($dados);
                $debug['resultado_funcao'] = $resultado;
                echo "<div class='success'>✅ Agendamento::criar() executado</div>";
            }
            
            $debug['resultado'] = 'ok';
            echo "<div class='success'>✅ Processo concluído com sucesso</div>";
            echo "<div class='info'>Resultado: " . print_r($resultado, true) . "</div>";
            
            // Salvar logs finais
            file_put_contents(__DIR__.'/debug_agendamento.txt', print_r($debug, true));
            file_put_contents(__DIR__ . '/../logs/debug_salvar.txt', date('c') . " - Sucesso ao salvar/atualizar. Resultado: " . print_r($debug['resultado_funcao'], true) . "\n", FILE_APPEND);
            
            echo "<div class='success'>✅ Endpoint AJAX funcionando corretamente!</div>";
            
            // Limpar o agendamento de teste
            if ($resultado && !$id) {
                $stmt = $pdo->prepare("DELETE FROM agendamentos WHERE id = ?");
                $stmt->execute([$resultado]);
                echo "<div class='info'>🧹 Agendamento de teste removido</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Exceção capturada: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            
            $debug['erro'] = $e->getMessage();
            $debug['trace'] = $e->getTraceAsString();
            file_put_contents(__DIR__.'/debug_agendamento.txt', print_r($debug, true));
            file_put_contents(__DIR__ . '/../logs/debug_salvar.txt', date('c') . " - Erro: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
        }
        
    } else {
        echo "<div class='error'>❌ Condições do endpoint não atendidas</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro geral: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
echo "</div>";

// ========================================
// 3. VERIFICAR LOGS CRIADOS
// ========================================
echo "<div class='section'>";
echo "<h2>3. 📝 Verificação de Logs Criados</h2>";

$logFiles = [
    '../logs/debug_post.txt',
    '../logs/debug_salvar.txt',
    'debug_agendamento.txt'
];

foreach ($logFiles as $logFile) {
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $size = filesize($logFile);
        echo "<div class='success'>✅ $logFile (tamanho: " . number_format($size) . " bytes)</div>";
        echo "<h4>Conteúdo de $logFile:</h4>";
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
    } else {
        echo "<div class='warning'>⚠️ $logFile não foi criado</div>";
    }
}
echo "</div>";

echo "<div class='section info'>";
echo "<h2>✅ Teste Detalhado Concluído</h2>";
echo "<p>Este teste executou o endpoint AJAX passo a passo para identificar exatamente onde está falhando.</p>";
echo "</div>";
?> 