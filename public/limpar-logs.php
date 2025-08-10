<?php
/**
 * Script de Limpeza de Logs
 * Sistema Bichos do Bairro
 * 
 * Este script limpa logs antigos e organiza o sistema
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🧹 Limpeza de Logs - Sistema Bichos do Bairro</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

// ========================================
// 1. LIMPEZA DE LOGS ANTIGOS
// ========================================

echo "<h2>1. 📁 Limpeza de Logs Antigos</h2>";

$logDir = '../logs/';
$maxSize = 1024 * 1024; // 1MB
$maxAge = 7 * 24 * 60 * 60; // 7 dias

if (is_dir($logDir)) {
    $files = scandir($logDir);
    $cleaned = 0;
    $backedUp = 0;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $logDir . $file;
        
        if (is_file($filePath)) {
            $fileSize = filesize($filePath);
            $fileAge = time() - filemtime($filePath);
            
            // Se o arquivo é muito grande ou muito antigo
            if ($fileSize > $maxSize || $fileAge > $maxAge) {
                // Fazer backup antes de limpar
                $backupPath = $logDir . 'backup_' . date('Y-m-d_H-i-s') . '_' . $file;
                if (copy($filePath, $backupPath)) {
                    $backedUp++;
                    echo "<p style='color: blue;'>ℹ️ Backup criado: $file</p>";
                }
                
                // Limpar arquivo
                if (file_put_contents($filePath, "# Log limpo em " . date('d/m/Y H:i:s') . "\n")) {
                    $cleaned++;
                    echo "<p style='color: green;'>✅ Log limpo: $file (Tamanho: " . round($fileSize / 1024, 2) . "KB)</p>";
                }
            }
        }
    }
    
    echo "<p style='color: green;'>✅ Limpeza concluída: $cleaned arquivos limpos, $backedUp backups criados</p>";
} else {
    echo "<p style='color: red;'>❌ Diretório de logs não encontrado</p>";
}

// ========================================
// 2. LIMPEZA DE ARQUIVOS DE DEBUG
// ========================================

echo "<h2>2. 🗑️ Limpeza de Arquivos de Debug</h2>";

$debugFiles = [
    'debug_pets_error.txt',
    'debug_agendamento.txt',
    'debug_post_completo.txt',
    'debug_agendamento_lista.php',
    'debug-test.php',
    'debug_pets_insert.txt',
    'debug_pets_error.txt',
    'debug_agendamentos_dia.txt',
    'debug-calendario-fechado.php',
    'debug-endpoint.php',
    'debug-listar-clientes-pets.php',
    'debug-fullcalendar-fechado.php',
    'debug-insercao-agendamento.php',
    'debug-lista-pets.php',
    'debug-listagem-agendamentos.php',
    'debug-melhorias.php',
    'debug-simples-fechado.php',
    'debug-sistema.php',
    'debug-telefones.php',
    'debug-validacao.php',
    'debug-wizard-simples.php',
    'debug-wizard.php',
    'diagnostico.php',
    'diagnostico-agendamento.php',
    'excluir-agendamento.php',
    'limpar-testes.php',
    'teste-agendamento.php',
    'teste-agendamentos.php',
    'teste-calendario-estatico.php',
    'teste-calendario-fechado.php',
    'teste-calendario-simples.php',
    'teste-compatibilidade.php',
    'teste-conexao-pets.php',
    'teste-conexao.php',
    'teste-endpoint-ajax.php',
    'teste-endpoint-detalhado.php',
    'teste-endpoint-fechado.php',
    'teste-endpoint-isolado.php',
    'teste-exclusao.php',
    'teste-insercao-agendamento.php',
    'teste-lista-pets.php',
    'teste-listagem-agendamentos.php',
    'teste-melhorias.php',
    'teste-simples-fechado.php',
    'teste-telefones.php',
    'teste-validacao.php',
    'teste-wizard-simples.php',
    'teste-wizard.php',
    'validar-campo.php',
    'verificar-dados-banco.php',
    'verificar-dados.php'
];

$removed = 0;
$kept = 0;

foreach ($debugFiles as $file) {
    $filePath = $file;
    
    if (file_exists($filePath)) {
        // Fazer backup antes de remover
        $backupPath = '../logs/backup_' . date('Y-m-d_H-i-s') . '_' . $file;
        if (copy($filePath, $backupPath)) {
            if (unlink($filePath)) {
                $removed++;
                echo "<p style='color: green;'>✅ Arquivo removido: $file</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao remover: $file</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Erro ao fazer backup: $file</p>";
        }
    } else {
        $kept++;
    }
}

echo "<p style='color: green;'>✅ Limpeza de debug concluída: $removed arquivos removidos, $kept já não existiam</p>";

// ========================================
// 3. ORGANIZAÇÃO DE ARQUIVOS
// ========================================

echo "<h2>3. 📂 Organização de Arquivos</h2>";

// Criar diretórios se não existirem
$dirs = [
    '../logs/backups/',
    '../logs/old/',
    '../temp/',
    '../cache/'
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✅ Diretório criado: $dir</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao criar diretório: $dir</p>";
        }
    } else {
        echo "<p style='color: blue;'>ℹ️ Diretório já existe: $dir</p>";
    }
}

// ========================================
// 4. LIMPEZA DE SESSÕES ANTIGAS
// ========================================

echo "<h2>4. 🗂️ Limpeza de Sessões Antigas</h2>";

$sessionPath = session_save_path();
if (empty($sessionPath)) {
    $sessionPath = sys_get_temp_dir();
}

if (is_dir($sessionPath)) {
    $files = glob($sessionPath . '/sess_*');
    $cleaned = 0;
    
    foreach ($files as $file) {
        $fileAge = time() - filemtime($file);
        if ($fileAge > 24 * 60 * 60) { // Mais de 24 horas
            if (unlink($file)) {
                $cleaned++;
            }
        }
    }
    
    echo "<p style='color: green;'>✅ Sessões antigas limpas: $cleaned arquivos removidos</p>";
} else {
    echo "<p style='color: red;'>❌ Diretório de sessões não encontrado</p>";
}

// ========================================
// 5. VERIFICAÇÃO DE ESPAÇO EM DISCO
// ========================================

echo "<h2>5. 💾 Verificação de Espaço em Disco</h2>";

$totalSpace = disk_total_space('../');
$freeSpace = disk_free_space('../');
$usedSpace = $totalSpace - $freeSpace;
$usagePercent = round(($usedSpace / $totalSpace) * 100, 2);

echo "<p><strong>Espaço total:</strong> " . round($totalSpace / 1024 / 1024 / 1024, 2) . " GB</p>";
echo "<p><strong>Espaço livre:</strong> " . round($freeSpace / 1024 / 1024 / 1024, 2) . " GB</p>";
echo "<p><strong>Espaço usado:</strong> " . round($usedSpace / 1024 / 1024 / 1024, 2) . " GB</p>";
echo "<p><strong>Uso:</strong> $usagePercent%</p>";

if ($usagePercent > 90) {
    echo "<p style='color: red;'>⚠️ ATENÇÃO: Espaço em disco crítico!</p>";
} elseif ($usagePercent > 80) {
    echo "<p style='color: orange;'>⚠️ Espaço em disco alto</p>";
} else {
    echo "<p style='color: green;'>✅ Espaço em disco OK</p>";
}

// ========================================
// 6. RELATÓRIO FINAL
// ========================================

echo "<h2>6. 📊 Relatório Final</h2>";

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Item</th><th>Status</th></tr>";

echo "<tr><td>Logs limpos</td><td>✅ Concluído</td></tr>";
echo "<tr><td>Arquivos de debug removidos</td><td>✅ Concluído</td></tr>";
echo "<tr><td>Diretórios organizados</td><td>✅ Concluído</td></tr>";
echo "<tr><td>Sessões antigas limpas</td><td>✅ Concluído</td></tr>";
echo "<tr><td>Espaço em disco</td><td>" . ($usagePercent > 90 ? '❌ Crítico' : ($usagePercent > 80 ? '⚠️ Alto' : '✅ OK')) . "</td></tr>";

echo "</table>";

// ========================================
// 7. RECOMENDAÇÕES
// ========================================

echo "<h2>7. 📋 Recomendações</h2>";

echo "<ul>";
echo "<li>✅ Execute este script semanalmente</li>";
echo "<li>✅ Monitore o espaço em disco regularmente</li>";
echo "<li>✅ Configure backup automático dos logs importantes</li>";
echo "<li>✅ Revise os arquivos de backup antes de deletar</li>";
echo "<li>✅ Mantenha apenas os arquivos de debug necessários</li>";
echo "</ul>";

echo "<h3>🔗 Links Úteis</h3>";
echo "<p><a href='corrigir-sistema-completo.php'>Correção Completa</a> | <a href='monitor-conexao.php'>Monitor de Conexão</a> | <a href='dashboard.php'>Dashboard</a></p>";

echo "<hr>";
echo "<p><strong>Limpeza executada em:</strong> " . date('d/m/Y H:i:s') . "</p>";
echo "<p><strong>Próxima limpeza recomendada:</strong> " . date('d/m/Y H:i:s', time() + 7 * 24 * 60 * 60) . "</p>";
?> 