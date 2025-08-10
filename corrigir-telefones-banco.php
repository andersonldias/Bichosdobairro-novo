<?php
require_once 'src/init.php';

echo "🔧 Corrigindo telefones no banco de dados...\n";
echo "==========================================\n\n";

try {
    $pdo = getDb();
    
    // 1. Corrigir tabela clientes
    echo "1. Corrigindo telefones na tabela clientes...\n";
    
    $stmt = $pdo->query("SELECT id, telefone FROM clientes WHERE telefone IS NOT NULL");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $clientesCorrigidos = 0;
    $stmtUpdate = $pdo->prepare("UPDATE clientes SET telefone = :telefone_limpo WHERE id = :id");
    
    foreach ($clientes as $cliente) {
        $telefoneOriginal = $cliente['telefone'];
        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefoneOriginal);
        
        if ($telefoneOriginal !== $telefoneLimpo) {
            $stmtUpdate->execute([
                'telefone_limpo' => $telefoneLimpo,
                'id' => $cliente['id']
            ]);
            $clientesCorrigidos++;
            echo "   Cliente ID {$cliente['id']}: '{$telefoneOriginal}' → '{$telefoneLimpo}'\n";
        }
    }
    
    echo "   ✅ {$clientesCorrigidos} telefones corrigidos na tabela clientes\n\n";
    
    // 2. Corrigir tabela telefones
    echo "2. Corrigindo telefones na tabela telefones...\n";
    
    $stmt = $pdo->query("SELECT id, numero FROM telefones WHERE numero IS NOT NULL");
    $telefones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $telefonesCorrigidos = 0;
    $stmtUpdateTel = $pdo->prepare("UPDATE telefones SET numero = :numero_limpo WHERE id = :id");
    
    foreach ($telefones as $telefone) {
        $numeroOriginal = $telefone['numero'];
        $numeroLimpo = preg_replace('/[^0-9]/', '', $numeroOriginal);
        
        if ($numeroOriginal !== $numeroLimpo) {
            $stmtUpdateTel->execute([
                'numero_limpo' => $numeroLimpo,
                'id' => $telefone['id']
            ]);
            $telefonesCorrigidos++;
            echo "   Telefone ID {$telefone['id']}: '{$numeroOriginal}' → '{$numeroLimpo}'\n";
        }
    }
    
    echo "   ✅ {$telefonesCorrigidos} telefones corrigidos na tabela telefones\n\n";
    
    // 3. Verificar duplicatas após limpeza
    echo "3. Verificando duplicatas após limpeza...\n";
    
    $stmt = $pdo->query("
        SELECT telefone, COUNT(*) as total 
        FROM clientes 
        WHERE telefone IS NOT NULL 
        GROUP BY telefone 
        HAVING COUNT(*) > 1
    ");
    $duplicatas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($duplicatas)) {
        echo "   ✅ Nenhuma duplicata encontrada\n";
    } else {
        echo "   ⚠️ Duplicatas encontradas:\n";
        foreach ($duplicatas as $dup) {
            echo "      Telefone '{$dup['telefone']}': {$dup['total']} registros\n";
        }
    }
    
    echo "\n🎉 Correção concluída com sucesso!\n";
    echo "Total de registros corrigidos: " . ($clientesCorrigidos + $telefonesCorrigidos) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>