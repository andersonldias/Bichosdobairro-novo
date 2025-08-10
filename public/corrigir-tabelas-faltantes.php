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
    <title>🔧 Correção de Tabelas Faltantes - Bichos do Bairro</title>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Correção de Tabelas Faltantes - Sistema Bichos do Bairro</h1>
        <p><strong>Data:</strong> <?= date('d/m/Y H:i:s') ?></p>

        <?php
        $results = [];
        $totalSteps = 0;
        $successSteps = 0;

        try {
            $pdo = getDb();
            
            // 1. Verificar e criar tabela logs_login
            $totalSteps++;
            echo "<h2>1. 🔍 Verificando Tabela logs_login</h2>";
            echo "<div class='step'>Verificando se a tabela logs_login existe...</div>";
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'logs_login'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='result warning'>Tabela logs_login não encontrada. Criando...</div>";
                
                $sql = file_get_contents('../sql/create_logs_login_table.sql');
                $pdo->exec($sql);
                
                echo "<div class='result success'>✅ Tabela logs_login criada com sucesso!</div>";
                $results[] = ['logs_login', 'Criada', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result success'>✅ Tabela logs_login já existe!</div>";
                $results[] = ['logs_login', 'Já existe', 'success'];
                $successSteps++;
            }

            // 2. Verificar e criar tabela niveis_acesso
            $totalSteps++;
            echo "<h2>2. 🔍 Verificando Tabela niveis_acesso</h2>";
            echo "<div class='step'>Verificando se a tabela niveis_acesso existe...</div>";
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'niveis_acesso'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='result warning'>Tabela niveis_acesso não encontrada. Criando...</div>";
                
                $sql = file_get_contents('../sql/create_niveis_acesso_table_corrigido.sql');
                $pdo->exec($sql);
                
                echo "<div class='result success'>✅ Tabela niveis_acesso criada com sucesso!</div>";
                $results[] = ['niveis_acesso', 'Criada', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result success'>✅ Tabela niveis_acesso já existe!</div>";
                $results[] = ['niveis_acesso', 'Já existe', 'success'];
                $successSteps++;
            }

            // 3. Verificar e criar tabela permissoes
            $totalSteps++;
            echo "<h2>3. 🔍 Verificando Tabela permissoes</h2>";
            echo "<div class='step'>Verificando se a tabela permissoes existe...</div>";
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'permissoes'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='result warning'>Tabela permissoes não encontrada. Criando...</div>";
                
                // A tabela permissoes é criada junto com niveis_acesso
                echo "<div class='result info'>A tabela permissoes será criada pelo script de niveis_acesso.</div>";
                $results[] = ['permissoes', 'Será criada', 'info'];
            } else {
                echo "<div class='result success'>✅ Tabela permissoes já existe!</div>";
                $results[] = ['permissoes', 'Já existe', 'success'];
                $successSteps++;
            }

            // 4. Verificar e criar tabela nivel_permissoes
            $totalSteps++;
            echo "<h2>4. 🔍 Verificando Tabela nivel_permissoes</h2>";
            echo "<div class='step'>Verificando se a tabela nivel_permissoes existe...</div>";
            
            $stmt = $pdo->query("SHOW TABLES LIKE 'nivel_permissoes'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='result warning'>Tabela nivel_permissoes não encontrada. Criando...</div>";
                
                // A tabela nivel_permissoes é criada junto com niveis_acesso
                echo "<div class='result info'>A tabela nivel_permissoes será criada pelo script de niveis_acesso.</div>";
                $results[] = ['nivel_permissoes', 'Será criada', 'info'];
            } else {
                echo "<div class='result success'>✅ Tabela nivel_permissoes já existe!</div>";
                $results[] = ['nivel_permissoes', 'Já existe', 'success'];
                $successSteps++;
            }

            // 5. Verificar estrutura da tabela usuarios
            $totalSteps++;
            echo "<h2>5. 🔍 Verificando Estrutura da Tabela usuarios</h2>";
            echo "<div class='step'>Verificando se a coluna nivel_acesso_id existe na tabela usuarios...</div>";
            
            $stmt = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'nivel_acesso_id'");
            if ($stmt->rowCount() == 0) {
                echo "<div class='result warning'>Coluna nivel_acesso_id não encontrada. Adicionando...</div>";
                
                $pdo->exec("ALTER TABLE usuarios ADD COLUMN nivel_acesso_id INT NULL AFTER id");
                $pdo->exec("ALTER TABLE usuarios ADD FOREIGN KEY (nivel_acesso_id) REFERENCES niveis_acesso(id) ON DELETE SET NULL");
                
                echo "<div class='result success'>✅ Coluna nivel_acesso_id adicionada com sucesso!</div>";
                $results[] = ['usuarios.nivel_acesso_id', 'Adicionada', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result success'>✅ Coluna nivel_acesso_id já existe!</div>";
                $results[] = ['usuarios.nivel_acesso_id', 'Já existe', 'success'];
                $successSteps++;
            }

            // 6. Atribuir nível padrão aos usuários existentes
            $totalSteps++;
            echo "<h2>6. 🔧 Atribuindo Níveis Padrão</h2>";
            echo "<div class='step'>Atribuindo nível 'admin' aos usuários existentes...</div>";
            
            $stmt = $pdo->query("SELECT id FROM niveis_acesso WHERE nome = 'admin' LIMIT 1");
            if ($stmt->rowCount() > 0) {
                $adminLevel = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
                
                $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso_id = ? WHERE nivel_acesso_id IS NULL");
                $stmt->execute([$adminLevel]);
                
                $affected = $stmt->rowCount();
                echo "<div class='result success'>✅ {$affected} usuário(s) atualizado(s) com nível 'admin'!</div>";
                $results[] = ['usuarios.nivel_padrao', 'Atribuído', 'success'];
                $successSteps++;
            } else {
                echo "<div class='result error'>❌ Nível 'admin' não encontrado!</div>";
                $results[] = ['usuarios.nivel_padrao', 'Erro', 'error'];
            }

        } catch (Exception $e) {
            echo "<div class='result error'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
            $results[] = ['Erro Geral', $e->getMessage(), 'error'];
        }
        ?>

        <div class="summary">
            <h2>📊 Resumo da Correção</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tabela/Coluna</th>
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
            <p><strong>3. Verificar Administração:</strong> <a href="admin-usuarios.php" target="_blank">Gerenciar Usuários</a></p>
            <p><strong>4. Monitorar Logs:</strong> <a href="monitor-conexao.php" target="_blank">Monitor de Conexão</a></p>
        </div>

        <div class="step">
            <h3>🔧 Scripts Relacionados</h3>
            <p><a href="corrigir-sistema-completo.php">Correção Completa do Sistema</a></p>
            <p><a href="corrigir-banco.php">Correção do Banco de Dados</a></p>
            <p><a href="backup-automatico.php">Backup Automático</a></p>
            <p><a href="limpar-logs.php">Limpeza de Logs</a></p>
        </div>
    </div>
</body>
</html> 