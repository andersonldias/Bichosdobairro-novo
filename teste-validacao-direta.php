<?php
require_once 'src/init.php';

echo "🧪 Teste Direto de Validação - Telefone Duplicado\n";
echo "=================================================\n\n";

try {
    $telefoneTeste = '(41) 99813-5428'; // Telefone que você testou
    
    echo "📱 Testando telefone: $telefoneTeste\n\n";
    
    // 1. Verificar se o telefone já existe no banco
    echo "1️⃣ Verificando se telefone já existe...\n";
    $existe = Cliente::verificarDuplicidadeTelefone($telefoneTeste);
    echo "   Resultado: " . ($existe ? 'EXISTE' : 'NÃO EXISTE') . "\n\n";
    
    if ($existe) {
        echo "✅ SUCESSO: O telefone foi detectado como duplicado!\n";
        echo "   A validação está funcionando corretamente.\n\n";
        
        // 2. Simular o que o validar-campo.php faria
        echo "2️⃣ Simulando validação do formulário...\n";
        
        // Limpar telefone (remover caracteres especiais)
        $telefone_limpo = preg_replace('/[^0-9]/', '', $telefoneTeste);
        echo "   Telefone limpo: $telefone_limpo\n";
        
        // Validar formato (10 ou 11 dígitos)
        if (strlen($telefone_limpo) < 10 || strlen($telefone_limpo) > 11) {
            echo "   ❌ Telefone com formato inválido\n";
        } else {
            echo "   ✅ Telefone com formato válido\n";
            
            // Verificar duplicidade
            $duplicado = Cliente::verificarDuplicidadeTelefone($telefone_limpo, null);
            if ($duplicado) {
                echo "   ✅ Telefone duplicado detectado!\n";
                echo "   📝 Mensagem: Este telefone já está cadastrado\n";
                echo "   🚫 Resultado: Cliente NÃO pode ser criado\n";
            } else {
                echo "   ❌ ERRO: Telefone duplicado NÃO foi detectado!\n";
            }
        }
        
    } else {
        echo "❌ PROBLEMA: O telefone NÃO foi detectado como duplicado!\n";
        echo "   A validação não está funcionando.\n";
        
        // Verificar se realmente existe no banco
        echo "\n🔍 Verificando diretamente no banco...\n";
        $pdo = getDb();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM clientes WHERE telefone = ?");
        $stmt->execute([$telefoneTeste]);
        $resultado = $stmt->fetch();
        
        echo "   Telefone '$telefoneTeste' no banco: " . ($resultado['total'] > 0 ? 'EXISTE' : 'NÃO EXISTE') . "\n";
        
        if ($resultado['total'] > 0) {
            echo "   🚨 PROBLEMA: Telefone existe no banco mas validação falhou!\n";
        }
    }
    
    echo "\n🎯 Teste concluído!\n";
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
