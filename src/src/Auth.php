<?php

class Auth {
    private $pdo;
    private $maxTentativas = 5;
    private $tempoBloqueio = 900; // 15 minutos
    
    public function __construct() {
        $this->pdo = getDb();
    }
    
    /**
     * Tenta fazer login do usuário
     */
    public function login($email, $senha) {
        // Sanitizar entrada
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'erro' => 'E-mail inválido'];
        }
        
        // Verificar se o usuário está bloqueado
        $usuario = $this->buscarUsuario($email);
        if ($usuario && $this->estaBloqueado($usuario)) {
            $this->logarTentativa($email, false);
            return ['sucesso' => false, 'erro' => 'Conta temporariamente bloqueada. Tente novamente em alguns minutos.'];
        }
        
        // Verificar credenciais
        if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
            // Login bem-sucedido
            $this->resetarTentativas($usuario['id']);
            $this->atualizarUltimoLogin($usuario['id']);
            $this->logarTentativa($email, true, $usuario['id']);
            
            // Iniciar sessão
            $this->iniciarSessao($usuario);
            
            return ['sucesso' => true, 'usuario' => $usuario];
        } else {
            // Login falhou
            if ($usuario) {
                $this->incrementarTentativas($usuario['id']);
            }
            $this->logarTentativa($email, false);
            
            return ['sucesso' => false, 'erro' => 'E-mail ou senha inválidos'];
        }
    }
    
    /**
     * Verifica se o usuário está logado
     */
    public function estaLogado() {
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
    }
    
    /**
     * Verifica se o usuário tem nível de acesso específico
     */
    public function temAcesso($nivel) {
        if (!$this->estaLogado()) {
            return false;
        }
        
        $niveis = ['usuario' => 1, 'admin' => 2];
        $nivelUsuario = $_SESSION['usuario_nivel'] ?? 'usuario';
        
        return $niveis[$nivelUsuario] >= $niveis[$nivel];
    }
    
    /**
     * Faz logout do usuário
     */
    public function logout() {
        // Limpar sessão
        session_unset();
        session_destroy();
        
        // Iniciar nova sessão limpa
        session_start();
        
        // Regenerar ID da sessão
        session_regenerate_id(true);
    }
    
    /**
     * Busca usuário por email
     */
    public function buscarUsuario($email) {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1 LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verifica se o usuário está bloqueado
     */
    private function estaBloqueado($usuario) {
        if ($usuario['tentativas_login'] >= $this->maxTentativas && $usuario['bloqueado_ate']) {
            $bloqueadoAte = strtotime($usuario['bloqueado_ate']);
            if (time() < $bloqueadoAte) {
                return true;
            } else {
                // Desbloquear automaticamente
                $this->resetarTentativas($usuario['id']);
            }
        }
        return false;
    }
    
    /**
     * Incrementa tentativas de login
     */
    private function incrementarTentativas($usuarioId) {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET tentativas_login = tentativas_login + 1 WHERE id = ?');
        $stmt->execute([$usuarioId]);
        
        // Verificar se deve bloquear
        $stmt = $this->pdo->prepare('SELECT tentativas_login FROM usuarios WHERE id = ?');
        $stmt->execute([$usuarioId]);
        $tentativas = $stmt->fetchColumn();
        
        if ($tentativas >= $this->maxTentativas) {
            $bloqueadoAte = date('Y-m-d H:i:s', time() + $this->tempoBloqueio);
            $stmt = $this->pdo->prepare('UPDATE usuarios SET bloqueado_ate = ? WHERE id = ?');
            $stmt->execute([$bloqueadoAte, $usuarioId]);
        }
    }
    
    /**
     * Reseta tentativas de login
     */
    private function resetarTentativas($usuarioId) {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET tentativas_login = 0, bloqueado_ate = NULL WHERE id = ?');
        $stmt->execute([$usuarioId]);
    }
    
    /**
     * Atualiza último login
     */
    private function atualizarUltimoLogin($usuarioId) {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?');
        $stmt->execute([$usuarioId]);
    }
    
    /**
     * Inicia sessão do usuário
     */
    private function iniciarSessao($usuario) {
        // Regenerar ID da sessão para segurança
        session_regenerate_id(true);
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_email'] = $usuario['email'];
        $_SESSION['usuario_nivel'] = $usuario['nivel_acesso'];
        $_SESSION['login_time'] = time();
    }
    
    /**
     * Registra tentativa de login
     */
    private function logarTentativa($email, $sucesso, $usuarioId = null) {
        $ip = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Converter booleano para inteiro
        $sucessoInt = $sucesso ? 1 : 0;
        
        $stmt = $this->pdo->prepare('INSERT INTO logs_login (usuario_id, email, ip_address, user_agent, sucesso) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$usuarioId, $email, $ip, $userAgent, $sucessoInt]);
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
    
    /**
     * Cria novo usuário
     */
    public function criarUsuario($nome, $email, $senha, $nivel = 'usuario') {
        // Validar dados
        if (empty($nome) || empty($email) || empty($senha)) {
            return ['sucesso' => false, 'erro' => 'Todos os campos são obrigatórios'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'erro' => 'E-mail inválido'];
        }
        
        if (strlen($senha) < 6) {
            return ['sucesso' => false, 'erro' => 'Senha deve ter pelo menos 6 caracteres'];
        }
        
        // Verificar se email já existe
        $stmt = $this->pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['sucesso' => false, 'erro' => 'E-mail já cadastrado'];
        }
        
        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir usuário
        $stmt = $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash, nivel_acesso) VALUES (?, ?, ?, ?)');
        if ($stmt->execute([$nome, $email, $senhaHash, $nivel])) {
            return ['sucesso' => true, 'id' => $this->pdo->lastInsertId()];
        } else {
            return ['sucesso' => false, 'erro' => 'Erro ao criar usuário'];
        }
    }
    
    /**
     * Altera senha do usuário
     */
    public function alterarSenha($usuarioId, $senhaAtual, $novaSenha) {
        // Verificar senha atual
        $stmt = $this->pdo->prepare('SELECT senha_hash FROM usuarios WHERE id = ?');
        $stmt->execute([$usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario || !password_verify($senhaAtual, $usuario['senha_hash'])) {
            return ['sucesso' => false, 'erro' => 'Senha atual incorreta'];
        }
        
        // Validar nova senha
        if (strlen($novaSenha) < 6) {
            return ['sucesso' => false, 'erro' => 'Nova senha deve ter pelo menos 6 caracteres'];
        }
        
        // Hash da nova senha
        $novaSenhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        
        // Atualizar senha
        $stmt = $this->pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
        if ($stmt->execute([$novaSenhaHash, $usuarioId])) {
            return ['sucesso' => true];
        } else {
            return ['sucesso' => false, 'erro' => 'Erro ao alterar senha'];
        }
    }
    
    /**
     * Obtém dados do usuário logado
     */
    public function getUsuarioLogado() {
        if (!$this->estaLogado()) {
            return null;
        }
        
        $stmt = $this->pdo->prepare('SELECT id, nome, email, nivel_acesso, ultimo_login FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['usuario_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPdo() {
        return $this->pdo;
    }
} 