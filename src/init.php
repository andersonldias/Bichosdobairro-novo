<?php
/**
 * ARQUIVO: init.php
 * SISTEMA: Bichos do Bairro - Inicialização Principal
 * 
 * ⚠️  ATENÇÃO - FUNCIONALIDADES CRÍTICAS EM FUNCIONAMENTO:
 * ✅ Autoload de classes funcionando
 * ✅ Configuração de sessão ativa
 * ✅ Conexão com banco de dados estável
 * ✅ Funções utilitárias carregadas
 * 
 * 🚨 NÃO ALTERE SEM BACKUP:
 * - Configurações de sessão
 * - Autoload de classes
 * - Inicialização do banco de dados
 * - Funções globais
 * 
 * 📝 REGRA DE OURO:
 * "Ao fazer upgrade ou alteração, NÃO MUDE NADA que já está funcionando"
 */

// Carregar autoloader do sistema (garante Config, Utils, BaseModel, etc.)
require_once __DIR__ . '/autoload.php';

// Garantir que a conexão com o banco e a função getDb() estejam disponíveis
if (!function_exists('getDb')) {
	require_once __DIR__ . '/db.php';
}

// Helpers mínimos usados em páginas públicas
if (!function_exists('isAjax')) {
	function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}
}

if (!function_exists('jsonResponse')) {
	function jsonResponse($data, $status = 200) {
		http_response_code($status);
		header('Content-Type: application/json');
		echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit;
	}
}

if (!function_exists('sanitize')) {
	function sanitize($input) {
		if (is_array($input)) {
			return array_map('sanitize', $input);
		}
		return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
	}
}