<?php
// Teste do init.php sem conexão automática
echo "<h1>🔍 Teste de Inicialização</h1>";
echo "<p>Testando carregamento do sistema...</p>";

try {
    // Carregar apenas as configurações
    require_once '../src/Config.php';
    Config::load();
    echo "<p>✅ Configurações carregadas</p>";
    
    // Testar se está em modo debug
    if (Config::isDebug()) {
        echo "<p>🐛 Modo DEBUG ativo</p>";
    } else {
        echo "<p>🔒 Modo PRODUÇÃO ativo</p>";
    }
    
    // Tentar carregar init.php
    echo "<p>🔄 Carregando init.php...</p>";
    require_once '../src/init.php';
    echo "<p>✅ Init.php carregado com sucesso!</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro encontrado: " . $e->getMessage() . "</p>";
    echo "<p>📁 Arquivo: " . $e->getFile() . "</p>";
    echo "<p>📍 Linha: " . $e->getLine() . "</p>";
}

echo "<p><a href='../'>← Voltar</a></p>";
?>