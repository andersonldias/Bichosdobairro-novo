<?php
/**
 * Teste Endpoint JSON
 * Sistema Bichos do Bairro
 * 
 * Este script testa se o endpoint action=listar está retornando JSON puro
 */

// Simular a requisição
$_GET['action'] = 'listar';

// Incluir o arquivo principal
ob_start();
include 'agendamentos.php';
$output = ob_get_clean();

// Verificar se é JSON válido
$json_start = strpos($output, '[');
if ($json_start !== false) {
    $json_content = substr($output, $json_start);
    $decoded = json_decode($json_content, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ SUCCESSO: Endpoint retornando JSON válido!\n";
        echo "📊 Total de agendamentos: " . count($decoded) . "\n";
        echo "🔍 Primeiros 100 caracteres do JSON:\n";
        echo substr($json_content, 0, 100) . "...\n";
    } else {
        echo "❌ ERRO: JSON inválido!\n";
        echo "Erro: " . json_last_error_msg() . "\n";
        echo "🔍 Primeiros 200 caracteres da saída:\n";
        echo substr($output, 0, 200) . "\n";
    }
} else {
    echo "❌ ERRO: Não foi encontrado JSON na saída!\n";
    echo "🔍 Primeiros 200 caracteres da saída:\n";
    echo substr($output, 0, 200) . "\n";
}
?> 