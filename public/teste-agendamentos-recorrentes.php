<?php
/**
 * Teste de Agendamentos Recorrentes
 * Sistema Bichos do Bairro
 */

require_once 'src/init.php';

echo "<h1>🧪 Teste de Agendamentos Recorrentes</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = getDb();
    
    // 1. Verificar se as tabelas existem
    echo "<h2>1. Verificando tabelas...</h2>";
    
    $tabelas = ['agendamentos_recorrentes', 'logs_agendamentos_recorrentes'];
    
    foreach ($tabelas as $tabela) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Tabela $tabela existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabela $tabela NÃO existe</p>";
        }
    }
    
    // 2. Verificar estrutura da tabela agendamentos_recorrentes
    echo "<h2>2. Verificando estrutura da tabela...</h2>";
    
    $stmt = $pdo->query("DESCRIBE agendamentos_recorrentes");
    $colunas = $stmt->fetchAll();
    
    echo "<p>Colunas encontradas: " . count($colunas) . "</p>";
    echo "<ul>";
    foreach ($colunas as $coluna) {
        echo "<li><strong>{$coluna['Field']}</strong> - {$coluna['Type']}</li>";
    }
    echo "</ul>";
    
    // 3. Verificar se há dados
    echo "<h2>3. Verificando dados...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM agendamentos_recorrentes");
    $resultado = $stmt->fetch();
    echo "<p>Total de agendamentos recorrentes: " . $resultado['total'] . "</p>";
    
    // 4. Testar query principal
    echo "<h2>4. Testando query principal...</h2>";
    
    $sql = "SELECT 
                ar.*,
                c.nome as cliente_nome,
                p.nome as pet_nome,
                CASE 
                    WHEN ar.tipo_recorrencia = 'semanal' THEN 'Semanal'
                    WHEN ar.tipo_recorrencia = 'quinzenal' THEN 'Quinzenal'
                    WHEN ar.tipo_recorrencia = 'mensal' THEN 'Mensal'
                END as tipo_nome,
                CASE 
                    WHEN ar.dia_semana = 1 THEN 'Segunda-feira'
                    WHEN ar.dia_semana = 2 THEN 'Terça-feira'
                    WHEN ar.dia_semana = 3 THEN 'Quarta-feira'
                    WHEN ar.dia_semana = 4 THEN 'Quinta-feira'
                    WHEN ar.dia_semana = 5 THEN 'Sexta-feira'
                    WHEN ar.dia_semana = 6 THEN 'Sábado'
                    WHEN ar.dia_semana = 7 THEN 'Domingo'
                END as dia_nome,
                COUNT(a.id) as total_agendamentos,
                COUNT(CASE WHEN a.status = 'confirmado' THEN 1 END) as agendamentos_confirmados,
                COUNT(CASE WHEN a.status = 'cancelado' THEN 1 END) as agendamentos_cancelados
            FROM agendamentos_recorrentes ar
            JOIN clientes c ON ar.cliente_id = c.id
            JOIN pets p ON ar.pet_id = p.id
            LEFT JOIN agendamentos a ON ar.id = a.recorrencia_id
            GROUP BY ar.id
            ORDER BY ar.created_at DESC";
    
    $stmt = $pdo->query($sql);
    $agendamentos = $stmt->fetchAll();
    
    echo "<p style='color: green;'>✅ Query executada com sucesso!</p>";
    echo "<p>Agendamentos encontrados: " . count($agendamentos) . "</p>";
    
    // 5. Verificar se há clientes e pets
    echo "<h2>5. Verificando dados de referência...</h2>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
    $resultado = $stmt->fetch();
    echo "<p>Total de clientes: " . $resultado['total'] . "</p>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pets");
    $resultado = $stmt->fetch();
    echo "<p>Total de pets: " . $resultado['total'] . "</p>";
    
    // 6. Testar inserção de dados de exemplo
    echo "<h2>6. Testando inserção de dados...</h2>";
    
    // Verificar se há pelo menos um cliente e um pet
    $stmt = $pdo->query("SELECT id FROM clientes LIMIT 1");
    $cliente = $stmt->fetch();
    
    $stmt = $pdo->query("SELECT id FROM pets LIMIT 1");
    $pet = $stmt->fetch();
    
    if ($cliente && $pet) {
        // Inserir agendamento de teste
        $sql = "INSERT INTO agendamentos_recorrentes 
                (cliente_id, pet_id, tipo_recorrencia, dia_semana, hora_inicio, duracao, data_inicio, observacoes) 
                VALUES (?, ?, 'semanal', 2, '09:00:00', 60, CURDATE(), 'Teste de agendamento recorrente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente['id'], $pet['id']]);
        
        echo "<p style='color: green;'>✅ Agendamento de teste inserido com sucesso!</p>";
        
        // Remover o teste
        $pdo->exec("DELETE FROM agendamentos_recorrentes WHERE observacoes = 'Teste de agendamento recorrente'");
        echo "<p style='color: blue;'>🗑️ Agendamento de teste removido</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Não há clientes ou pets cadastrados para teste</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 TESTE CONCLUÍDO COM SUCESSO!</h2>";
    echo "<p><a href='agendamentos-recorrentes.php' style='color: blue; text-decoration: underline;'>Acessar Agendamentos Recorrentes</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 