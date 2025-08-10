<?php
require_once 'src/init.php';

echo "🔍 Investigação de Telefone - Validação\n";
echo "=======================================\n\n";

try {
    $telefoneTeste = '(41) 99813-5428';
    
    echo "📱 Telefone de teste: $telefoneTeste\n\n";
    
    $pdo = getDb();
    
    // 1. Verificar como está armazenado no banco
    echo "1️⃣ Verificando telefone no banco...\n";
    $stmt = $pdo->prepare("SELECT id, nome, telefone FROM clientes WHERE telefone = ?");
    $stmt->execute([$telefoneTeste]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        echo "   ✅ Cliente encontrado:\n";
        echo "      ID: " . $cliente['id'] . "\n";
        echo "      Nome: " . $cliente['nome'] . "\n";
        echo "      Telefone: '" . $cliente['telefone'] . "'\n";
        echo "      Comprimento: " . strlen($cliente['telefone']) . " caracteres\n";
    } else {
        echo "   ❌ Cliente NÃO encontrado com telefone exato\n";
    }
    
    // 2. Verificar com telefone limpo
    echo "\n2️⃣ Verificando com telefone limpo...\n";
    $telefone_limpo = preg_replace('/[^0-9]/', '', $telefoneTeste);
    echo "   Telefone limpo: '$telefone_limpo'\n";
    echo "   Comprimento: " . strlen($telefone_limpo) . " dígitos\n";
    
    $stmt = $pdo->prepare("SELECT id, nome, telefone FROM clientes WHERE telefone = ?");
    $stmt->execute([$telefone_limpo]);
    $cliente_limpo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente_limpo) {
        echo "   ✅ Cliente encontrado com telefone limpo\n";
    } else {
        echo "   ❌ Cliente NÃO encontrado com telefone limpo\n";
    }
    
    // 3. Verificar com LIKE para encontrar variações
    echo "\n3️⃣ Buscando variações do telefone...\n";
    $stmt = $pdo->prepare("SELECT id, nome, telefone FROM clientes WHERE telefone LIKE ?");
    $stmt->execute(['%99813%']);
    $clientes_like = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($clientes_like) {
        echo "   ✅ Clientes encontrados com padrão '99813':\n";
        foreach ($clientes_like as $c) {
            echo "      ID: " . $c['id'] . " | Nome: " . $c['nome'] . " | Telefone: '" . $c['telefone'] . "'\n";
        }
    } else {
        echo "   ❌ Nenhum cliente encontrado com padrão '99813'\n";
    }
    
    // 4. Testar o método verificarDuplicidadeTelefone
    echo "\n4️⃣ Testando método verificarDuplicidadeTelefone...\n";
    
    // Com telefone original
    $existe_original = Cliente::verificarDuplicidadeTelefone($telefoneTeste);
    echo "   Telefone original '$telefoneTeste': " . ($existe_original ? 'EXISTE' : 'NÃO EXISTE') . "\n";
    
    // Com telefone limpo
    $existe_limpo = Cliente::verificarDuplicidadeTelefone($telefone_limpo);
    echo "   Telefone limpo '$telefone_limpo': " . ($existe_limpo ? 'EXISTE' : 'NÃO EXISTE') . "\n";
    
    // 5. Verificar se há diferença na tabela
    echo "\n5️⃣ Verificando estrutura da tabela...\n";
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($colunas as $coluna) {
        if ($coluna['Field'] === 'telefone') {
            echo "   Campo 'telefone':\n";
            echo "      Tipo: " . $coluna['Type'] . "\n";
            echo "      Null: " . $coluna['Null'] . "\n";
            echo "      Default: " . ($coluna['Default'] ?? 'N/A') . "\n";
            break;
        }
    }
    
    echo "\n🎯 Investigação concluída!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
