<?php
/**
 * Classe de utilitários para validação, sanitização e outras funções auxiliares
 */
class Utils {
    
    /**
     * Sanitiza uma string removendo caracteres perigosos
     */
    public static function sanitize($string) {
        return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Valida e formata CPF
     */
    public static function validateCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Formata CPF para exibição
     */
    public static function formatCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
    }
    
    /**
     * Valida e formata telefone
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 11;
    }
    
    /**
     * Formata telefone para exibição
     */
    public static function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        } elseif (strlen($phone) == 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        return $phone;
    }
    
    /**
     * Formatar telefone apenas para exibição (não altera o banco)
     */
    public static function formatPhoneDisplay($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        } elseif (strlen($phone) == 10) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
        }
        return $phone;
    }
    
    /**
     * Limpar telefone para salvamento no banco
     */
    public static function cleanPhoneForDatabase($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }
    
    /**
     * Valida email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Valida CEP
     */
    public static function validateCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return strlen($cep) == 8;
    }
    
    /**
     * Formata CEP para exibição
     */
    public static function formatCEP($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
    
    /**
     * Converte string vazia para null
     */
    public static function emptyToNull($value) {
        return ($value === '' || $value === null) ? null : $value;
    }
    
    /**
     * Converte string para inteiro seguro
     */
    public static function toInt($value) {
        $value = self::emptyToNull($value);
        return $value === null ? null : intval($value);
    }
    
    /**
     * Gera token CSRF
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida token CSRF
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Redireciona com mensagem
     */
    public static function redirect($url, $message = '', $type = 'msg') {
        $param = $type === 'error' ? 'erro' : 'msg';
        $separator = strpos($url, '?') !== false ? '&' : '?';
        header("Location: $url{$separator}{$param}=" . urlencode($message));
        exit;
    }
    
    /**
     * Formata data para exibição
     */
    public static function formatDate($date, $format = 'd/m/Y') {
        if (!$date) return '';
        return date($format, strtotime($date));
    }
    
    /**
     * Formata data e hora para exibição
     */
    public static function formatDateTime($datetime, $format = 'd/m/Y H:i') {
        if (!$datetime) return '';
        return date($format, strtotime($datetime));
    }
    
    /**
     * Formata valor monetário
     */
    public static function formatMoney($value) {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }
    
    /**
     * Converte valor monetário para float
     */
    public static function parseMoney($value) {
        return floatval(str_replace(['R$', ' ', '.'], '', str_replace(',', '.', $value)));
    }
    
    /**
     * Gera slug para URLs
     */
    public static function generateSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[áàâãä]/u', 'a', $string);
        $string = preg_replace('/[éèêë]/u', 'e', $string);
        $string = preg_replace('/[íìîï]/u', 'i', $string);
        $string = preg_replace('/[óòôõö]/u', 'o', $string);
        $string = preg_replace('/[úùûü]/u', 'u', $string);
        $string = preg_replace('/[ç]/u', 'c', $string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
    
    /**
     * Trunca texto com reticências
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }
    
    /**
     * Verifica se é uma requisição AJAX
     */
    public static function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Retorna resposta JSON
     */
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Log de erros personalizado
     */
    public static function logError($message, $context = []) {
        $logFile = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | Context: ' . json_encode($context) : '';
        $logEntry = "[$timestamp] $message$contextStr\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}