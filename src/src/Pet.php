<?php
require_once __DIR__ . '/db.php';
/**
 * Classe Pet - Sistema Avançado
 * Sistema Bichos do Bairro
 */

class Pet extends BaseModel {
    protected static $table = 'pets';
    
    /**
     * Listar todos os pets (método de compatibilidade)
     */
    public static function listarTodos() {
        $pdo = getDb();
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome FROM pets p JOIN clientes c ON p.cliente_id = c.id ORDER BY p.nome ASC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao listar pets: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo pet
     */
    public static function criar($dados) {
        $pdo = getDb();
        
        try {
            $sql = "INSERT INTO pets (nome, especie, raca, idade, peso, cliente_id, observacoes, created_at) 
                    VALUES (:nome, :especie, :raca, :idade, :peso, :cliente_id, :observacoes, NOW())";
            
            // Tratar idade: converter string vazia para NULL
            $idade = $dados['idade'] ?? '';
            if ($idade === '' || $idade === null) {
                $idade = null;
            } else {
                $idade = intval($idade);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $dados['nome'],
                'especie' => $dados['especie'],
                'raca' => $dados['raca'] ?? '',
                'idade' => $idade,
                'peso' => $dados['peso'] ?? null,
                'cliente_id' => $dados['cliente_id'],
                'observacoes' => $dados['observacoes'] ?? ''
            ]);
            
            $id = $pdo->lastInsertId();
            
            // Criar notificação
            if (class_exists('Notificacao')) {
                Notificacao::notificarNovoPet($id);
            }
            
            return $id;
        } catch (Exception $e) {
            logError('Erro ao criar pet: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Atualizar pet
     */
    public static function atualizar($id, $dados) {
        $pdo = getDb();
        
        try {
            $sql = "UPDATE pets SET 
                        nome = :nome, 
                        especie = :especie, 
                        raca = :raca, 
                        idade = :idade, 
                        peso = :peso, 
                        observacoes = :observacoes, 
                        updated_at = NOW() 
                    WHERE id = :id";
            
            // Tratar idade: converter string vazia para NULL
            $idade = $dados['idade'] ?? '';
            if ($idade === '' || $idade === null) {
                $idade = null;
            } else {
                $idade = intval($idade);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $dados['nome'],
                'especie' => $dados['especie'],
                'raca' => $dados['raca'] ?? '',
                'idade' => $idade,
                'peso' => $dados['peso'] ?? null,
                'observacoes' => $dados['observacoes'] ?? '',
                'id' => $id
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao atualizar pet: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Buscar pet por termo
     */
    public static function buscar($termo, $filtros = []) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome 
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    WHERE p.nome LIKE :termo OR p.raca LIKE :termo OR c.nome LIKE :termo";
            $params = ['termo' => "%$termo%"];
            
            // Aplicar filtros
            if (!empty($filtros['especie'])) {
                $sql .= " AND p.especie = :especie";
                $params['especie'] = $filtros['especie'];
            }
            
            if (!empty($filtros['cliente_id'])) {
                $sql .= " AND p.cliente_id = :cliente_id";
                $params['cliente_id'] = $filtros['cliente_id'];
            }
            
            if (!empty($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params['limite'] = $filtros['limite'];
            }
            
            $sql .= " ORDER BY p.nome ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pet com histórico
     */
    public static function getPetComHistorico($id) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                           COUNT(a.id) as total_agendamentos,
                           MAX(a.data) as ultimo_agendamento
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    LEFT JOIN agendamentos a ON p.id = a.pet_id 
                    WHERE p.id = :id 
                    GROUP BY p.id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar pet com histórico: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter pets por cliente
     */
    public static function getPetsPorCliente($clienteId) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, 
                           COUNT(a.id) as total_agendamentos,
                           MAX(a.data) as ultimo_agendamento
                    FROM pets p 
                    LEFT JOIN agendamentos a ON p.id = a.pet_id 
                    WHERE p.cliente_id = :cliente_id 
                    GROUP BY p.id 
                    ORDER BY p.nome ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['cliente_id' => $clienteId]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets por cliente: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pets mais ativos
     */
    public static function getPetsMaisAtivos($limite = 10) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome, COUNT(a.id) as total_agendamentos
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    LEFT JOIN agendamentos a ON p.id = a.pet_id 
                    GROUP BY p.id 
                    ORDER BY total_agendamentos DESC 
                    LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets mais ativos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pets por espécie
     */
    public static function getPetsPorEspecie($especie) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome 
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    WHERE p.especie = :especie 
                    ORDER BY p.nome ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['especie' => $especie]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets por espécie: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter estatísticas de pets
     */
    public static function getEstatisticas() {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN especie = 'cachorro' THEN 1 ELSE 0 END) as cachorros,
                        SUM(CASE WHEN especie = 'gato' THEN 1 ELSE 0 END) as gatos,
                        SUM(CASE WHEN especie = 'ave' THEN 1 ELSE 0 END) as aves,
                        SUM(CASE WHEN especie = 'roedor' THEN 1 ELSE 0 END) as roedores,
                        SUM(CASE WHEN especie = 'outro' THEN 1 ELSE 0 END) as outros,
                        SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as novos_30_dias,
                        AVG(idade) as idade_media
                    FROM pets";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar estatísticas de pets: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter histórico de agendamentos do pet
     */
    public static function getHistoricoAgendamentos($id, $limite = 20) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    WHERE a.pet_id = :id 
                    ORDER BY a.data DESC, a.hora DESC 
                    LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar histórico de agendamentos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter preferências do pet
     */
    public static function getPreferencias($id) {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        servico,
                        COUNT(*) as total,
                        MAX(data) as ultimo_servico
                    FROM agendamentos 
                    WHERE pet_id = :id AND status = 'concluido'
                    GROUP BY servico 
                    ORDER BY total DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar preferências do pet: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pets com aniversário próximo (próximos 30 dias)
     */
    public static function getPetsComAniversarioProximo($dias = 30) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    WHERE p.data_nascimento IS NOT NULL 
                    AND DATE_FORMAT(p.data_nascimento, '%m-%d') 
                    BETWEEN DATE_FORMAT(CURDATE(), '%m-%d') 
                    AND DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL :dias DAY), '%m-%d')
                    ORDER BY p.data_nascimento ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets com aniversário próximo: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pets que precisam de vacinação (baseado na idade)
     */
    public static function getPetsParaVacinacao() {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome, c.telefone as cliente_telefone
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    WHERE p.idade IS NOT NULL 
                    AND p.idade BETWEEN 2 AND 12 
                    AND p.ultima_vacinacao IS NULL 
                    OR p.ultima_vacinacao < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                    ORDER BY p.idade ASC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets para vacinação: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Atualizar última vacinação
     */
    public static function atualizarUltimaVacinacao($id, $data = null) {
        global $pdo;
        
        try {
            $data = $data ?: date('Y-m-d');
            
            $sql = "UPDATE pets SET ultima_vacinacao = :data, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data' => $data,
                'id' => $id
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao atualizar última vacinação: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter relatório de pets
     */
    public static function getRelatorio($dataInicio, $dataFim) {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        p.*,
                        c.nome as cliente_nome,
                        COUNT(a.id) as total_agendamentos,
                        SUM(CASE WHEN a.status = 'concluido' THEN 1 ELSE 0 END) as agendamentos_concluidos,
                        MAX(a.data) as ultimo_agendamento
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    LEFT JOIN agendamentos a ON p.id = a.pet_id 
                    WHERE p.created_at BETWEEN :data_inicio AND :data_fim 
                    GROUP BY p.id 
                    ORDER BY total_agendamentos DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao gerar relatório de pets: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter pets por faixa etária
     */
    public static function getPetsPorFaixaEtaria() {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        CASE 
                            WHEN idade < 1 THEN 'Filhote (0-1 ano)'
                            WHEN idade BETWEEN 1 AND 7 THEN 'Adulto (1-7 anos)'
                            WHEN idade BETWEEN 8 AND 12 THEN 'Sênior (8-12 anos)'
                            WHEN idade > 12 THEN 'Idoso (12+ anos)'
                            ELSE 'Idade não informada'
                        END as faixa_etaria,
                        COUNT(*) as total
                    FROM pets 
                    GROUP BY faixa_etaria 
                    ORDER BY total DESC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets por faixa etária: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter raças mais populares
     */
    public static function getRacasMaisPopulares($limite = 10) {
        global $pdo;
        
        try {
            $sql = "SELECT raca, COUNT(*) as total 
                    FROM pets 
                    WHERE raca != '' 
                    GROUP BY raca 
                    ORDER BY total DESC 
                    LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar raças mais populares: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar pets por cliente
     */
    public static function buscarPorCliente($clienteId) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM pets WHERE cliente_id = :cliente_id ORDER BY nome ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['cliente_id' => $clienteId]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar pets por cliente: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar pet por ID
     */
    public static function buscarPorId($id) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome FROM pets p JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar pet por ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar pet
     */
    public static function deletar($id) {
        global $pdo;
        
        try {
            $sql = "DELETE FROM pets WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao deletar pet: ' . $e->getMessage());
            return false;
        }
    }

} 