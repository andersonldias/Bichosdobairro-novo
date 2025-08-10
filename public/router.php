<?php
/**
 * Router personalizado para servidor PHP embutido
 * Corrige problema com URLs contendo @vite
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Corrigir URLs com @vite (problema de extensão do navegador)
if (strpos($path, '/@vite/') === 0) {
    $newPath = substr($path, 6); // Remove '/@vite'
    
    // Verificar se não vai criar loop
    if (strpos($newPath, '@vite/') === false) {
        header('Location: ' . $newPath, true, 301);
        exit;
    } else {
        // Se ainda contém @vite, remover completamente
        $newPath = str_replace('@vite/', '', $newPath);
        header('Location: /' . $newPath, true, 301);
        exit;
    }
}

// Se o arquivo existe, servir diretamente
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false; // Deixa o servidor PHP embutido servir o arquivo
}

// Se não tem extensão, tentar adicionar .php
if (!pathinfo($path, PATHINFO_EXTENSION)) {
    $phpFile = __DIR__ . $path . '.php';
    if (file_exists($phpFile)) {
        require $phpFile;
        return true;
    }
}

// Log de URLs não encontradas para debug
error_log("URL não encontrada: " . $path);

// Página não encontrada
http_response_code(404);
if (file_exists(__DIR__ . '/404.php')) {
    require __DIR__ . '/404.php';
} else {
    echo '404 - Página não encontrada: ' . htmlspecialchars($path);
}
return true;
?>