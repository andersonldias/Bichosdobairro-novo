<?php

class NivelAcesso {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getDb();
    }
    
    /**
     * Lista todos os níveis de acesso
     */
    public function listarTodos() {
        $stmt = $this->pdo->prepare('SELECT * FROM niveis_acesso WHERE ativo = 1 ORDER BY nome');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca um nível por ID
     */
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare('SELECT * FROM niveis_acesso WHERE id = ? AND ativo = 1');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca um nível por nome
     */
    public function buscarPorNome($nome) {
        $stmt = $this->pdo->prepare('SELECT * FROM niveis_acesso WHERE nome = ? AND ativo = 1');
        $stmt->execute([$nome]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Cria um novo nível de acesso
     */
    public function criar($nome, $descricao, $cor = '#667ea') {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO niveis_acesso (nome, descricao, cor) VALUES (?, ?, ?)');
            $stmt->execute([$nome, $descricao, $cor]);
            return ['sucesso' => true, 'id' => $this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['sucesso' => false, 'erro' => 'Erro ao criar nível de acesso: ' . $e->getMessage()];
        }
    }
    
    /**
     * Atualiza um nível de acesso
     */
    public function atualizar($id, $nome, $descricao, $cor) {
        try {
            $stmt = $this->pdo->prepare('UPDATE niveis_acesso SET nome = ?, descricao = ?, cor = ? WHERE id = ?');
            $stmt->execute([$nome, $descricao, $cor, $id]);
            return ['sucesso' => true];
        } catch (PDOException $e) {
            return ['sucesso' => false, 'erro' => 'Erro ao atualizar nível de acesso: ' . $e->getMessage()];
        }
    }
    
    /**
     * Exclui um nível de acesso
     */
    public function excluir($id) {
        try {
            // Verificar se há usuários usando este nível
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE nivel_acesso = ?');
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                return ['sucesso' => false, 'erro' => 'Não é possível excluir este nível pois há usuários associados a ele.'];
            }
            
            $stmt = $this->pdo->prepare('DELETE FROM niveis_acesso WHERE id = ?');
            $stmt->execute([$id]);
            return ['sucesso' => true];
        } catch (PDOException $e) {
            return ['sucesso' => false, 'erro' => 'Erro ao excluir nível de acesso: ' . $e->getMessage()];
        }
    }
    
    /**
     * Lista todas as permissões
     */
    public function listarPermissoes() {
        $stmt = $this->pdo->prepare('SELECT * FROM permissoes WHERE ativo = 1 ORDER BY area, nome');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lista permissões agrupadas por área
     */
    public function listarPermissoesPorArea() {
        $permissoes = $this->listarPermissoes();
        $agrupadas = [];
        
        foreach ($permissoes as $permissao) {
            $area = $permissao['area'];
            if (!isset($agrupadas[$area])) {
                $agrupadas[$area] = [];
            }
            $agrupadas[$area][] = $permissao;
        }
        
        return $agrupadas;
    }
    
    /**
     * Busca permissões de um nível específico
     */
    public function buscarPermissoesNivel($nivelId) {
        $stmt = $this->pdo->prepare('
            SELECT p.* FROM permissoes p
            INNER JOIN nivel_permissoes np ON p.id = np.permissao_id
            WHERE np.nivel_id = ? AND p.ativo = 1
            ORDER BY p.area, p.nome
       ');
        $stmt->execute([$nivelId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Atualiza permissões de um nível
     */
    public function atualizarPermissoesNivel($nivelId, $permissoesIds) {
        try {
            $this->pdo->beginTransaction();
            
            // Remover todas as permissões atuais
            $stmt = $this->pdo->prepare('DELETE FROM nivel_permissoes WHERE nivel_id = ?');
            $stmt->execute([$nivelId]);
            
            // Adicionar novas permissões
            if (!empty($permissoesIds)) {
                $stmt = $this->pdo->prepare('INSERT INTO nivel_permissoes (nivel_id, permissao_id) VALUES (?, ?)');
                foreach ($permissoesIds as $permissaoId) {
                    $stmt->execute([$nivelId, $permissaoId]);
                }
            }
            
            $this->pdo->commit();
            return ['sucesso' => true];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['sucesso' => false, 'erro' => 'Erro ao atualizar permissões: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verifica se um usuário tem uma permissão específica
     */
    public function usuarioTemPermissao($usuarioId, $permissaoNome) {
        $stmt = $this->pdo->prepare('
            SELECT COUNT(*) FROM nivel_permissoes np
            INNER JOIN permissoes p ON np.permissao_id = p.id
            INNER JOIN usuarios u ON u.nivel_acesso = np.nivel_id
            WHERE u.id = ? AND p.nome = ? AND p.ativo = 1
       ');
        $stmt->execute([$usuarioId, $permissaoNome]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Verifica se o usuário logado tem uma permissão específica
     */
    public function logadoTemPermissao($permissaoNome) {
        if (!isset($_SESSION['usuario_id'])) {
            return false;
        }
        return $this->usuarioTemPermissao($_SESSION['usuario_id'], $permissaoNome);
    }
    
    /**
     * Lista áreas disponíveis
     */
    public function listarAreas() {
        $stmt = $this->pdo->prepare('SELECT DISTINCT area FROM permissoes WHERE ativo = 1 ORDER BY area');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Obtém estatísticas dos níveis
     */
    public function getEstatisticas() {
        $stats = [];
        
        // Total de níveis
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM niveis_acesso WHERE ativo = 1');
        $stmt->execute();
        $stats['total_niveis'] = $stmt->fetchColumn();
        
        // Total de permissões
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM permissoes WHERE ativo = 1');
        $stmt->execute();
        $stats['total_permissoes'] = $stmt->fetchColumn();
        
        // Usuários por nível
        $stmt = $this->pdo->prepare('
            SELECT na.nome, COUNT(u.id) as total
            FROM niveis_acesso na
            LEFT JOIN usuarios u ON na.id = u.nivel_acesso
            WHERE na.ativo = 1
            GROUP BY na.id, na.nome
            ORDER BY na.nome
       ');
        $stmt->execute();
        $stats['usuarios_por_nivel'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }
} 