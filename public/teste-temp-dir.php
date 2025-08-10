<?php
try {
    echo "<h1>Teste Diretório Temporário</h1>";
    echo "<p>Diretório temporário: " . sys_get_temp_dir() . "</p>";
    echo "<p>Arquivo de log: " . sys_get_temp_dir() . '/bichos_db_error.log' . "</p>";
    
    // Verificar se o arquivo de log existe
    $logFile = sys_get_temp_dir() . '/bichos_db_error.log';
    if (file_exists($logFile)) {
        echo "<p style='color: green;'>✅ Arquivo de log existe</p>";
        echo "<p>Tamanho: " . filesize($logFile) . " bytes</p>";
        echo "<p>Última modificação: " . date('Y-m-d H:i:s', filemtime($logFile)) . "</p>";
        
        // Mostrar últimas linhas do log
        $content = file_get_contents($logFile);
        if ($content) {
            echo "<h3>Últimas linhas do log:</h3>";
            echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>" . htmlspecialchars($content) . "</pre>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Arquivo de log não existe ainda</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?>