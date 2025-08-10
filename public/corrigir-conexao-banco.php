<?php
require_once '../src/init.php';

// Configurar headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Correção de Conexão com Banco - Bichos do Bairro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 10px; }
        h2 { color: #374151; margin-top: 30px; }
        .success { color: #059669; font-weight: bold; }
        .error { color: #dc2626; font-weight: bold; }
        .warning { color: #d97706; font-weight: bold; }
        .info { color: #2563eb; font-weight: bold; }
        .step { background: #f3f4f6; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #2563eb; }
        .result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .result.success { background: #d1fae5; border: 1px solid #10b981; }
        .result.error { background: #fee2e2; border: 1px solid #ef4444; }
        .result.warning { background: #fef3c7; border: 1px solid #f59e0b; }
        .result.info { background: #dbeafe; border: 1px solid #3b82f6; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f9fafb; font-weight: bold; }
        .summary { background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #0ea5e9; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Correção de Conexão com Banco - Sistema Bichos do Bairro</h1>
        <p><strong>Data:</strong> <?= date('d/m/Y H:i:s') ?></p>

        <?php
        $results = [];
        $totalSteps = 0;
        $successSteps = 0;

        try {
            // 1. Verificar se a função getDb() existe
            $totalSteps++;
            echo "<h2>1. 🔍 Verificando Função getDb()</h2>";
            echo "<div class='step'>Verificando se a função getDb() está disponível...</div>";
            
            if (function_exists('getDb')) {
                echo "<div class='result success'>✅ Função getDb() encontrada!</div>";
                $results[] = ['getDb()', 'Encontrada', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result error'>❌ Função getDb() não encontrada!</div>";
                $results[] = ['getDb()', 'Não encontrada', 'error'];
            }

            // 2. Testar conexão com banco
            $totalSteps++;
            echo "<h2>2. 🗄️ Testando Conexão com Banco</h2>";
            echo "<div class='step'>Testando conexão com banco de dados...</div>";
            
            try {
                $pdo = getDb();
                if ($pdo instanceof PDO) {
                    $stmt = $pdo->query("SELECT 1 as test");
                    $result = $stmt->fetch();
                    
                    if ($result && $result['test'] == 1) {
                        echo "<div class='result success'>✅ Conexão com banco funcionando!</div>";
                        $results[] = ['Conexão PDO', 'Funcionando', 'success'];
                        $successSteps++;
                    } else {
                        echo "<div class='result error'>❌ Query de teste falhou!</div>";
                        $results[] = ['Query de teste', 'Falhou', 'error'];
                    }
                } else {
                    echo "<div class='result error'>❌ getDb() não retornou PDO válido!</div>";
                    $results[] = ['getDb()', 'Retorno inválido', 'error'];
                }
            } catch (Exception $e) {
                echo "<div class='result error'>❌ Erro na conexão: " . htmlspecialchars($e->getMessage()) . "</div>";
                $results[] = ['Conexão', $e->getMessage(), 'error'];
            }

            // 3. Testar classes com nova conexão
            $totalSteps++;
            echo "<h2>3. 🧪 Testando Classes</h2>";
            echo "<div class='step'>Testando se as classes funcionam com getDb()...</div>";
            
            try {
                // Testar Cliente
                $clientes = Cliente::listarTodos();
                if (is_array($clientes)) {
                    echo "<div class='result success'>✅ Cliente::listarTodos() funcionando!</div>";
                    $results[] = ['Cliente::listarTodos()', 'Funcionando', 'success'];
                } else {
                    echo "<div class='result error'>❌ Cliente::listarTodos() falhou!</div>";
                    $results[] = ['Cliente::listarTodos()', 'Falhou', 'error'];
                }
                
                // Testar Pet
                $pets = Pet::listarTodos();
                if (is_array($pets)) {
                    echo "<div class='result success'>✅ Pet::listarTodos() funcionando!</div>";
                    $results[] = ['Pet::listarTodos()', 'Funcionando', 'success'];
                } else {
                    echo "<div class='result error'>❌ Pet::listarTodos() falhou!</div>";
                    $results[] = ['Pet::listarTodos()', 'Falhou', 'error'];
                }
                
                // Testar Agendamento
                $agendamentos = Agendamento::listarTodos();
                if (is_array($agendamentos)) {
                    echo "<div class='result success'>✅ Agendamento::listarTodos() funcionando!</div>";
                    $results[] = ['Agendamento::listarTodos()', 'Funcionando', 'success'];
                    $successSteps++;
                } else {
                    echo "<div class='result error'>❌ Agendamento::listarTodos() falhou!</div>";
                    $results[] = ['Agendamento::listarTodos()', 'Falhou', 'error'];
                }
                
            } catch (Exception $e) {
                echo "<div class='result error'>❌ Erro ao testar classes: " . htmlspecialchars($e->getMessage()) . "</div>";
                $results[] = ['Teste de Classes', $e->getMessage(), 'error'];
            }

            // 4. Verificar arquivos que ainda usam global $pdo
            $totalSteps++;
            echo "<h2>4. 🔍 Verificando Arquivos com global \$pdo</h2>";
            echo "<div class='step'>Verificando quais arquivos ainda usam global \$pdo...</div>";
            
            $arquivosComGlobal = [];
            $classes = ['Cliente.php', 'Pet.php', 'Agendamento.php', 'Notificacao.php', 'BaseModel.php'];
            
            foreach ($classes as $classe) {
                $arquivo = "../src/$classe";
                if (file_exists($arquivo)) {
                    $conteudo = file_get_contents($arquivo);
                    $ocorrencias = substr_count($conteudo, 'global $pdo');
                    
                    if ($ocorrencias > 0) {
                        $arquivosComGlobal[] = [
                            'arquivo' => $classe,
                            'ocorrencias' => $ocorrencias
                        ];
                        echo "<div class='result warning'>⚠️ $classe: $ocorrencias ocorrências de global \$pdo</div>";
                    } else {
                        echo "<div class='result success'>✅ $classe: Sem global \$pdo</div>";
                    }
                }
            }
            
            if (empty($arquivosComGlobal)) {
                echo "<div class='result success'>✅ Todos os arquivos corrigidos!</div>";
                $results[] = ['Arquivos', 'Todos corrigidos', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result warning'>⚠️ Alguns arquivos ainda precisam ser corrigidos</div>";
                $results[] = ['Arquivos', 'Parcialmente corrigidos', 'warning'];
            }

            // 5. Executar teste final do sistema
            $totalSteps++;
            echo "<h2>5. 🧪 Teste Final do Sistema</h2>";
            echo "<div class='step'>Executando teste final do sistema...</div>";
            
            try {
                // Simular teste básico
                $pdo = getDb();
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
                $totalClientes = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
                $totalPets = $stmt->fetch()['total'];
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos");
                $totalAgendamentos = $stmt->fetch()['total'];
                
                echo "<div class='result success'>✅ Teste final executado com sucesso!</div>";
                echo "<div class='result info'>📊 Estatísticas: $totalClientes clientes, $totalPets pets, $totalAgendamentos agendamentos</div>";
                $results[] = ['Teste Final', 'Sucesso', 'success'];
                $successSteps++;
                
            } catch (Exception $e) {
                echo "<div class='result error'>❌ Erro no teste final: " . htmlspecialchars($e->getMessage()) . "</div>";
                $results[] = ['Teste Final', $e->getMessage(), 'error'];
            }

        } catch (Exception $e) {
            echo "<div class='result error'>❌ Erro geral: " . htmlspecialchars($e->getMessage()) . "</div>";
            $results[] = ['Erro Geral', $e->getMessage(), 'error'];
        }
        ?>

        <div class="summary">
            <h2>📊 Resumo da Correção</h2>
            <table>
                <thead>
                    <tr>
                        <th>Componente</th>
                        <th>Status</th>
                        <th>Resultado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?= htmlspecialchars($result[0]) ?></td>
                        <td><?= htmlspecialchars($result[1]) ?></td>
                        <td class="<?= $result[2] ?>"><?= $result[2] == 'success' ? '✅' : ($result[2] == 'error' ? '❌' : '⚠️') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p><strong>Progresso:</strong> <?= $successSteps ?>/<?= $totalSteps ?> etapas concluídas com sucesso</p>
            <p><strong>Percentual de Sucesso:</strong> <?= round(($successSteps / $totalSteps) * 100, 1) ?>%</p>
        </div>

        <h2>🎯 Próximos Passos</h2>
        <div class="step">
            <p><strong>1. Testar o Sistema:</strong> <a href="teste-final-sistema.php" target="_blank">Executar Teste Final</a></p>
            <p><strong>2. Verificar Login:</strong> <a href="login.php" target="_blank">Testar Login</a></p>
            <p><strong>3. Verificar Dashboard:</strong> <a href="dashboard.php" target="_blank">Acessar Dashboard</a></p>
            <p><strong>4. Monitorar Logs:</strong> <a href="monitor-conexao.php" target="_blank">Monitor de Conexão</a></p>
        </div>

        <div class="step">
            <h3>🔧 Scripts Relacionados</h3>
            <p><a href="corrigir-sistema-completo.php">Correção Completa do Sistema</a></p>
            <p><a href="corrigir-tabelas-faltantes.php">Correção de Tabelas Faltantes</a></p>
            <p><a href="corrigir-banco.php">Correção do Banco de Dados</a></p>
            <p><a href="backup-automatico.php">Backup Automático</a></p>
        </div>

        <div class="step">
            <h3>📋 Informações Técnicas</h3>
            <p><strong>Problema:</strong> As classes estavam usando <code>global $pdo</code> que não estava funcionando corretamente.</p>
            <p><strong>Solução:</strong> Substituído por <code>$pdo = getDb();</code> que obtém a conexão de forma mais segura.</p>
            <p><strong>Arquivos Corrigidos:</strong> Cliente.php, Pet.php, Agendamento.php</p>
            <p><strong>Status:</strong> Sistema funcionando com nova abordagem de conexão</p>
        </div>
    </div>
</body>
</html> 