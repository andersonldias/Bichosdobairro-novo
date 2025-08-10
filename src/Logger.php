<?php
/**
 * Sistema de logs melhorado
 */
class Logger {
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;
    
    private static $logLevels = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT     => 'ALERT',
        self::CRITICAL  => 'CRITICAL',
        self::ERROR     => 'ERROR',
        self::WARNING   => 'WARNING',
        self::NOTICE    => 'NOTICE',
        self::INFO      => 'INFO',
        self::DEBUG     => 'DEBUG'
    ];
    
    private static $logDir;
    private static $maxLogSize = 10485760; // 10MB
    private static $maxLogFiles = 5;
    
    /**
     * Inicializa o sistema de logs
     */
    public static function init() {
        self::$logDir = LOGS_PATH;
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }
    }
    
    /**
     * Registra log de emergência
     */
    public static function emergency($message, array $context = []) {
        self::log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Registra log de alerta
     */
    public static function alert($message, array $context = []) {
        self::log(self::ALERT, $message, $context);
    }
    
    /**
     * Registra log crítico
     */
    public static function critical($message, array $context = []) {
        self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Registra log de erro
     */
    public static function error($message, array $context = []) {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Registra log de aviso
     */
    public static function warning($message, array $context = []) {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * Registra log de notificação
     */
    public static function notice($message, array $context = []) {
        self::log(self::NOTICE, $message, $context);
    }
    
    /**
     * Registra log de informação
     */
    public static function info($message, array $context = []) {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Registra log de debug
     */
    public static function debug($message, array $context = []) {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Registra log com nível específico
     */
    public static function log($level, $message, array $context = []) {
        if (!self::$logDir) {
            self::init();
        }
        
        $logFile = self::getLogFile($level);
        $logEntry = self::formatLogEntry($level, $message, $context);
        
        // Verificar tamanho do arquivo e fazer rotação se necessário
        if (file_exists($logFile) && filesize($logFile) > self::$maxLogSize) {
            self::rotateLogFile($logFile);
        }
        
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtém arquivo de log baseado no nível
     */
    private static function getLogFile($level) {
        $levelName = strtolower(self::$logLevels[$level]);
        return self::$logDir . "/{$levelName}.log";
    }
    
    /**
     * Formata entrada de log
     */
    private static function formatLogEntry($level, $message, array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = self::$logLevels[$level];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        
        $entry = "[$timestamp] [$levelName] [$ip] $message";
        
        if (!empty($context)) {
            $entry .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        $entry .= " | URI: $requestUri | User-Agent: $userAgent";
        
        return $entry;
    }
    
    /**
     * Rotaciona arquivo de log
     */
    private static function rotateLogFile($logFile) {
        for ($i = self::$maxLogFiles - 1; $i >= 1; $i--) {
            $oldFile = $logFile . ".$i";
            $newFile = $logFile . "." . ($i + 1);
            
            if (file_exists($oldFile)) {
                if ($i == self::$maxLogFiles - 1) {
                    unlink($oldFile);
                } else {
                    rename($oldFile, $newFile);
                }
            }
        }
        
        if (file_exists($logFile)) {
            rename($logFile, $logFile . '.1');
        }
    }
    
    /**
     * Obtém logs recentes
     */
    public static function getRecentLogs($level = null, $limit = 100) {
        if (!self::$logDir) {
            self::init();
        }
        
        $logs = [];
        
        if ($level !== null) {
            $logFile = self::getLogFile($level);
            if (file_exists($logFile)) {
                $logs = self::readLogFile($logFile, $limit);
            }
        } else {
            // Ler todos os arquivos de log
            foreach (self::$logLevels as $levelCode => $levelName) {
                $logFile = self::getLogFile($levelCode);
                if (file_exists($logFile)) {
                    $logs = array_merge($logs, self::readLogFile($logFile, $limit));
                }
            }
            
            // Ordenar por timestamp
            usort($logs, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });
            
            $logs = array_slice($logs, 0, $limit);
        }
        
        return $logs;
    }
    
    /**
     * Lê arquivo de log
     */
    private static function readLogFile($logFile, $limit) {
        $logs = [];
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (!$lines) {
            return $logs;
        }
        
        // Pegar as últimas linhas
        $lines = array_slice($lines, -$limit);
        
        foreach ($lines as $line) {
            $logs[] = self::parseLogLine($line);
        }
        
        return $logs;
    }
    
    /**
     * Faz parse de uma linha de log
     */
    private static function parseLogLine($line) {
        // Formato: [timestamp] [level] [ip] message | Context: {...} | URI: ... | User-Agent: ...
        preg_match('/^\[([^\]]+)\] \[([^\]]+)\] \[([^\]]+)\] (.+?)(?:\s+\|\s+Context:\s+(.+?))?(?:\s+\|\s+URI:\s+(.+?))?(?:\s+\|\s+User-Agent:\s+(.+?))?$/', $line, $matches);
        
        if (count($matches) >= 5) {
            return [
                'timestamp' => $matches[1],
                'level' => $matches[2],
                'ip' => $matches[3],
                'message' => $matches[4],
                'context' => isset($matches[5]) ? json_decode($matches[5], true) : null,
                'uri' => isset($matches[6]) ? $matches[6] : null,
                'user_agent' => isset($matches[7]) ? $matches[7] : null
            ];
        }
        
        return ['raw' => $line];
    }
    
    /**
     * Limpa logs antigos
     */
    public static function cleanOldLogs($days = 30) {
        if (!self::$logDir) {
            self::init();
        }
        
        $cutoff = time() - ($days * 24 * 60 * 60);
        $cleaned = 0;
        
        foreach (self::$logLevels as $levelCode => $levelName) {
            $logFile = self::getLogFile($levelCode);
            
            for ($i = 1; $i <= self::$maxLogFiles; $i++) {
                $oldFile = $logFile . ".$i";
                if (file_exists($oldFile) && filemtime($oldFile) < $cutoff) {
                    unlink($oldFile);
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtém estatísticas dos logs
     */
    public static function getStats() {
        if (!self::$logDir) {
            self::init();
        }
        
        $stats = [];
        
        foreach (self::$logLevels as $levelCode => $levelName) {
            $logFile = self::getLogFile($levelCode);
            $levelNameLower = strtolower($levelName);
            
            $stats[$levelNameLower] = [
                'exists' => file_exists($logFile),
                'size' => file_exists($logFile) ? filesize($logFile) : 0,
                'lines' => file_exists($logFile) ? count(file($logFile)) : 0
            ];
        }
        
        return $stats;
    }
} 