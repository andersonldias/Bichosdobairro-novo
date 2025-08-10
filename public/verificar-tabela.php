<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once '../src/init.php';
    $pdo = getDb();
    
    echo "=== ESTRUTURA ATUAL DA TABELA usuarios ===\n";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'usuarios'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Tabela 'usuarios' não existe!\n";
        exit;
    }
    
    // Mostrar estrutura atual
    $stmt = $pdo->query("DESCRIBE usuarios");
    echo "\nColunas atuais:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']} ({$row['Type']})\n";
    }
    
    // Verificar se as colunas necessárias existem
    $colunas_necessarias = ['nivel_acesso', 'ativo', 'ultimo_login', 'tentativas_login', 'bloqueado_ate'];
    $colunas_faltando = [];
    
    $stmt = $pdo->query("DESCRIBE usuarios");
    $colunas_existentes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $colunas_existentes[] = $row['Field'];
    }
    
    foreach ($colunas_necessarias as $coluna) {
        if (!in_array($coluna, $colunas_existentes)) {
            $colunas_faltando[] = $coluna;
        }
    }
    
    if (empty($colunas_faltando)) {
        echo "\n✅ Todas as colunas necessárias existem!\n";
    } else {
        echo "\n❌ Colunas faltando: " . implode(', ', $colunas_faltando) . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
?> 