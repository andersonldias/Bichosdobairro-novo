<?php
/**
 * Teste progressivo do init.php
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Teste Progressivo do init.php</h1>";
echo "<p>Início do teste: " . date('H:i:s') . "</p>";

// Verificar se estamos sendo redirecionados
if (isset($_GET['step'])) {
    $step = (int)$_GET['step'];
} else {
    $step = 1;
}

echo "<p><strong>STEP $step</strong></p>";

switch ($step) {
    case 1:
        echo "<p>Testando acesso básico...</p>";
        echo "<p>✅ Arquivo carregado sem redirecionamento</p>";
        echo "<p><a href='?step=2'>Próximo: Testar init.php</a></p>";
        break;
        
    case 2:
        echo "<p>Incluindo init.php...</p>";
        try {
            // Capturar qualquer output
            ob_start();
            require_once '../src/init.php';
            $output = ob_get_contents();
            ob_end_clean();
            
            echo "<p>✅ init.php incluído com sucesso!</p>";
            echo "<p>Output capturado: '" . htmlspecialchars($output) . "'</p>";
            echo "<p>Headers enviados: " . (headers_sent() ? 'SIM' : 'NÃO') . "</p>";
            echo "<p>Sessão ativa: " . (session_status() === PHP_SESSION_ACTIVE ? 'SIM' : 'NÃO') . "</p>";
            
            if (isset($_SESSION['usuario_id'])) {
                echo "<p>👤 Usuário logado: ID " . $_SESSION['usuario_id'] . "</p>";
                echo "<p><strong>PROBLEMA IDENTIFICADO: Usuário está logado, por isso está sendo redirecionado!</strong></p>";
            } else {
                echo "<p>👤 Usuário NÃO logado</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ ERRO: " . $e->getMessage() . "</p>";
        }
        break;
}
?>