<?php

class AuthMiddleware {
    private $auth;
    
    public function __construct() {
        $this->auth = new Auth();
    }
    
    /**
     * Verifica se o usuário está logado
     */
    public function requireLogin() {
        if (!$this->auth->estaLogado()) {
            // Salvar URL atual para redirecionamento após login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            
            header('Location: login.php');
            exit;
        }
    }
    
    /**
     * Verifica se o usuário tem nível de acesso específico
     */
    public function requireAccess($nivel) {
        $this->requireLogin();
        
        if (!$this->auth->temAcesso($nivel)) {
            http_response_code(403);
            echo '<h1>Acesso Negado</h1>';
            echo '<p>Você não tem permissão para acessar esta página.</p>';
            echo '<p><a href="dashboard.php">Voltar ao Dashboard</a></p>';
            exit;
        }
    }
    
    /**
     * Verifica se o usuário é administrador
     */
    public function requireAdmin() {
        $this->requireAccess('admin');
    }
    
    /**
     * Obtém dados do usuário logado
     */
    public function getUsuarioLogado() {
        return $this->auth->getUsuarioLogado();
    }
    
    /**
     * Verifica se a sessão não expirou
     */
    public function checkSessionTimeout($timeout = 3600) { // 1 hora padrão
        if (!$this->auth->estaLogado()) {
            return false;
        }
        
        $loginTime = $_SESSION['login_time'] ?? 0;
        if (time() - $loginTime > $timeout) {
            // Sessão expirada
            $this->auth->logout();
            header('Location: login.php?msg=timeout');
            exit;
        }
        
        // Renovar tempo de login
        $_SESSION['login_time'] = time();
        return true;
    }
    
    /**
     * Verifica se o usuário está ativo
     */
    public function checkUserActive() {
        if (!$this->auth->estaLogado()) {
            return false;
        }
        
        $usuario = $this->auth->getUsuarioLogado();
        if (!$usuario) {
            // Usuário não encontrado no banco (pode ter sido desativado)
            $this->auth->logout();
            header('Location: login.php?msg=inactive');
            exit;
        }
        
        return true;
    }
    
    /**
     * Executa todas as verificações de segurança
     */
    public function securePage($nivel = 'usuario', $timeout = 3600) {
        $this->requireLogin();
        $this->checkSessionTimeout($timeout);
        $this->checkUserActive();
        
        if ($nivel !== 'usuario') {
            $this->requireAccess($nivel);
        }
        
        return $this->getUsuarioLogado();
    }
    
    /**
     * Adiciona headers de segurança
     */
    public function addSecurityHeaders() {
        // Headers de segurança
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; ";
        $csp .= "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
        $csp .= "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; ";
        $csp .= "img-src 'self' data: https:; ";
        $csp .= "font-src 'self' https://cdnjs.cloudflare.com; ";
        $csp .= "connect-src 'self'; ";
        $csp .= "frame-ancestors 'none';";
        
        header("Content-Security-Policy: " . $csp);
    }
    
    /**
     * Registra atividade do usuário
     */
    public function logActivity($acao, $detalhes = '') {
        if (!$this->auth->estaLogado()) {
            return;
        }
        
        $pdo = getDb();
        $stmt = $pdo->prepare('INSERT INTO logs_atividade (usuario_id, acao, detalhes, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');
        
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->execute([
            $_SESSION['usuario_id'],
            $acao,
            $detalhes,
            $ip,
            $userAgent
        ]);
    }
    
    /**
     * Obtém IP real do cliente
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
} 