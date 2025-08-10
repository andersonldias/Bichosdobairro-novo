<?php
/**
 * Sistema de cache simples baseado em arquivos
 */
class Cache {
    private static $cacheDir;
    private static $defaultTTL = 3600; // 1 hora
    
    /**
     * Inicializa o sistema de cache
     */
    public static function init() {
        self::$cacheDir = LOGS_PATH . '/cache';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Gera chave de cache
     */
    private static function generateKey($key) {
        return md5($key) . '.cache';
    }
    
    /**
     * Obtém caminho do arquivo de cache
     */
    private static function getCachePath($key) {
        return self::$cacheDir . '/' . self::generateKey($key);
    }
    
    /**
     * Armazena valor no cache
     */
    public static function set($key, $value, $ttl = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $ttl = $ttl ?? self::$defaultTTL;
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        $cachePath = self::getCachePath($key);
        return file_put_contents($cachePath, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Obtém valor do cache
     */
    public static function get($key, $default = null) {
        if (!self::$cacheDir) {
            self::init();
        }
        
        $cachePath = self::getCachePath($key);
        
        if (!file_exists($cachePath)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($cachePath));
        
        if (!$data || !isset($data['expires']) || time() > $data['expires']) {
            self::delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Verifica se chave existe no cache
     */
    public static function has($key) {
        return self::get($key) !== null;
    }
    
    /**
     * Remove item do cache
     */
    public static function delete($key) {
        $cachePath = self::getCachePath($key);
        if (file_exists($cachePath)) {
            return unlink($cachePath);
        }
        return true;
    }
    
    /**
     * Limpa todo o cache
     */
    public static function clear() {
        if (!self::$cacheDir) {
            return true;
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * Obtém ou armazena valor no cache
     */
    public static function remember($key, $callback, $ttl = null) {
        $value = self::get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        self::set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Incrementa valor no cache
     */
    public static function increment($key, $value = 1) {
        $current = self::get($key, 0);
        $newValue = $current + $value;
        self::set($key, $newValue);
        return $newValue;
    }
    
    /**
     * Decrementa valor no cache
     */
    public static function decrement($key, $value = 1) {
        return self::increment($key, -$value);
    }
    
    /**
     * Obtém estatísticas do cache
     */
    public static function getStats() {
        if (!self::$cacheDir) {
            return ['files' => 0, 'size' => 0];
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        $size = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
        }
        
        return [
            'files' => count($files),
            'size' => $size,
            'size_formatted' => self::formatBytes($size)
        ];
    }
    
    /**
     * Formata bytes para leitura humana
     */
    private static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Limpa cache expirado
     */
    public static function gc() {
        if (!self::$cacheDir) {
            return;
        }
        
        $files = glob(self::$cacheDir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if (!$data || !isset($data['expires']) || time() > $data['expires']) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
} 