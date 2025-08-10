<?php
require_once 'src/init.php';

echo "🧪 Teste de Validação de Campo - Telefone Duplicado\n";
echo "===================================================\n\n";

try {
    // Simular POST request para validar-campo.php
    $_POST['campo'] = 'telefone';
    $_POST['valor'] = '(41) 99813-5428'; // Telefone que você testou
    $_POST['cliente_id'] = ''; // Novo cliente
    
    // Capturar output
    ob_start();
    
    // Incluir o arquivo de validação
    include 'public/validar-campo.php';
    
    $output = ob_get_clean();
    
    echo "📤 Dados enviados:\n";
    echo "   Campo: " . $_POST['campo'] . "\n";
    echo "   Valor: " . $_POST['valor'] . "\n";
    echo "   Cliente ID: " . ($_POST['cliente_id'] ?: 'Novo') . "\n\n";
    
    echo "📥 Resposta do sistema:\n";
    echo "   $output\n\n";
    
    // Decodificar JSON para verificar
    $response = json_decode($output, true);
    if ($response) {
        echo "✅ JSON válido decodificado:\n";
        echo "   Válido: " . ($response['valido'] ? 'SIM' : 'NÃO') . "\n";
        echo "   Mensagem: " . ($response['mensagem'] ?? 'N/A') . "\n";
        
        if (!$response['valido']) {
            echo "\n🎯 SUCESSO: A validação está funcionando!\n";
            echo "   O telefone duplicado foi detectado e bloqueado.\n";
        } else {
            echo "\n❌ PROBLEMA: A validação não está funcionando!\n";
            echo "   O telefone duplicado foi aceito.\n";
        }
    } else {
        echo "❌ ERRO: Não foi possível decodificar a resposta JSON\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
