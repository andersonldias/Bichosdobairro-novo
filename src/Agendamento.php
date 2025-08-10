<?php
require_once __DIR__ . '/db.php';
/**
 * Classe Agendamento - Sistema Avançado
 * Sistema Bichos do Bairro
 */

class Agendamento extends BaseModel {
    protected static $table = 'agendamentos';
    
    /**
     * Listar todos os agendamentos (método de compatibilidade)
     */
    public static function listarTodos() {
        $pdo = getDb();
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    LEFT JOIN clientes c ON a.cliente_id = c.id 
                    LEFT JOIN pets p ON a.pet_id = p.id 
                    ORDER BY a.data DESC, a.hora DESC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao listar agendamentos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo agendamento
     */
    public static function criar($dados) {
        $pdo = getDb();
        
        try {
            $sql = "INSERT INTO agendamentos (cliente_id, pet_id, data, hora, servico, observacoes, status) 
                    VALUES (:cliente_id, :pet_id, :data, :hora, :servico, :observacoes, :status)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cliente_id' => $dados['cliente_id'],
                'pet_id' => $dados['pet_id'],
                'data' => $dados['data'],
                'hora' => $dados['hora'],
                'servico' => $dados['servico'],
                'observacoes' => $dados['observacoes'],
                'status' => $dados['status']
            ]);
            
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            logError('Erro ao criar agendamento: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Obter agendamentos por data
     */
    public static function getAgendamentosPorData($data) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.data = :data 
                    ORDER BY a.hora ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['data' => $data]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos por data: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter agendamentos por status
     */
    public static function getAgendamentosPorStatus($status) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.status = :status 
                    ORDER BY a.data ASC, a.hora ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['status' => $status]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos por status: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter agendamentos próximos (próximos 7 dias)
     */
    public static function getAgendamentosProximos($limite = 10) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.data >= CURDATE() 
                    AND a.status IN ('agendado', 'em_andamento')
                    ORDER BY a.data ASC, a.hora ASC 
                    LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos próximos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar disponibilidade de horário
     */
    public static function verificarDisponibilidade($data, $hora, $excluirId = null) {
        global $pdo;
        
        try {
            $sql = "SELECT COUNT(*) as total FROM agendamentos 
                    WHERE data = :data AND hora = :hora 
                    AND status NOT IN ('cancelado')";
            
            $params = ['data' => $data, 'hora' => $hora];
            
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
                $params['excluir_id'] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] == 0;
        } catch (Exception $e) {
            logError('Erro ao verificar disponibilidade: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter horários disponíveis para uma data
     */
    public static function getHorariosDisponiveis($data) {
        // Horários de funcionamento (8h às 18h, intervalos de 1 hora)
        $horarios = [
            '08:00', '09:00', '10:00', '11:00', '12:00',
            '13:00', '14:00', '15:00', '16:00', '17:00'
        ];
        
        // Obter horários ocupados
        $ocupados = self::getHorariosOcupados($data);
        
        // Retornar horários disponíveis
        return array_diff($horarios, $ocupados);
    }
    
    /**
     * Obter horários ocupados para uma data
     */
    private static function getHorariosOcupados($data) {
        global $pdo;
        
        try {
            $sql = "SELECT hora FROM agendamentos 
                    WHERE data = :data AND status NOT IN ('cancelado')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['data' => $data]);
            
            return array_column($stmt->fetchAll(), 'hora');
        } catch (Exception $e) {
            logError('Erro ao buscar horários ocupados: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Atualizar status do agendamento
     */
    public static function atualizarStatus($id, $status) {
        global $pdo;
        
        try {
            $sql = "UPDATE agendamentos SET status = :status, updated_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'id' => $id
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao atualizar status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter estatísticas de agendamentos
     */
    public static function getEstatisticas() {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'agendado' THEN 1 ELSE 0 END) as agendados,
                        SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                        SUM(CASE WHEN status = 'em_andamento' THEN 1 ELSE 0 END) as em_andamento,
                        SUM(CASE WHEN data = CURDATE() THEN 1 ELSE 0 END) as hoje
                    FROM agendamentos";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar estatísticas: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter agendamentos por período
     */
    public static function getAgendamentosPorPeriodo($dataInicio, $dataFim) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                           p.nome as pet_nome, p.especie as pet_especie
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.data BETWEEN :data_inicio AND :data_fim 
                    ORDER BY a.data ASC, a.hora ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos por período: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar agendamentos
     */
    public static function buscar($termo, $filtros = []) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE (c.nome LIKE :termo OR p.nome LIKE :termo OR a.servico LIKE :termo)";
            
            $params = ['termo' => "%$termo%"];
            
            // Aplicar filtros
            if (!empty($filtros['status'])) {
                $sql .= " AND a.status = :status";
                $params['status'] = $filtros['status'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND a.data >= :data_inicio";
                $params['data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND a.data <= :data_fim";
                $params['data_fim'] = $filtros['data_fim'];
            }
            
            $sql .= " ORDER BY a.data DESC, a.hora DESC";
            
            if (isset($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params['limite'] = $filtros['limite'];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter relatório de serviços
     */
    public static function getRelatorioServicos($dataInicio, $dataFim) {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        servico,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos,
                        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
                    FROM agendamentos 
                    WHERE data BETWEEN :data_inicio AND :data_fim 
                    GROUP BY servico 
                    ORDER BY total DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao gerar relatório de serviços: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter agendamentos vencidos (não concluídos de datas passadas)
     */
    public static function getAgendamentosVencidos() {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                           p.nome as pet_nome
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.data < CURDATE() 
                    AND a.status IN ('agendado', 'em_andamento')
                    ORDER BY a.data DESC, a.hora DESC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos vencidos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Enviar lembretes de agendamentos
     */
    public static function enviarLembretes() {
        $amanha = date('Y-m-d', strtotime('+1 day'));
        $agendamentos = self::getAgendamentosPorData($amanha);
        
        foreach ($agendamentos as $agendamento) {
            // Aqui você pode implementar o envio de SMS/email
            logInfo('Lembrete enviado para agendamento', [
                'cliente' => $agendamento['cliente_nome'],
                'data' => $agendamento['data'],
                'hora' => $agendamento['hora']
            ]);
        }
        
        return count($agendamentos);
    }
    
    /**
     * Limpar agendamentos antigos (mais de 1 ano)
     */
    public static function limparAgendamentosAntigos() {
        global $pdo;
        
        try {
            $sql = "DELETE FROM agendamentos 
                    WHERE data < DATE_SUB(CURDATE(), INTERVAL 1 YEAR) 
                    AND status IN ('concluido', 'cancelado')";
            
            $stmt = $pdo->query($sql);
            $removidos = $stmt->rowCount();
            
            logInfo('Agendamentos antigos removidos', ['quantidade' => $removidos]);
            
            return $removidos;
        } catch (Exception $e) {
            logError('Erro ao limpar agendamentos antigos: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Atualizar agendamento
     */
    public static function atualizar($id, $pet_id, $cliente_id, $data, $hora, $servico, $status, $observacoes) {
        $pdo = getDb();
        
        try {
            $sql = "UPDATE agendamentos SET 
                        pet_id = :pet_id,
                        cliente_id = :cliente_id,
                        data = :data,
                        hora = :hora,
                        servico = :servico,
                        status = :status,
                        observacoes = :observacoes,
                        updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pet_id' => $pet_id,
                'cliente_id' => $cliente_id,
                'data' => $data,
                'hora' => $hora,
                'servico' => $servico,
                'status' => $status,
                'observacoes' => $observacoes,
                'id' => $id
            ]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao atualizar agendamento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar agendamento (método alternativo)
     */
    public static function criarSimples($pet_id, $cliente_id, $data, $hora, $servico, $status, $observacoes) {
        global $pdo;
        
        try {
            $sql = "INSERT INTO agendamentos (pet_id, cliente_id, data, hora, servico, status, observacoes, created_at) 
                    VALUES (:pet_id, :cliente_id, :data, :hora, :servico, :status, :observacoes, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'pet_id' => $pet_id,
                'cliente_id' => $cliente_id,
                'data' => $data,
                'hora' => $hora,
                'servico' => $servico,
                'status' => $status,
                'observacoes' => $observacoes
            ]);
            
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            logError('Erro ao criar agendamento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar agendamento
     */
    public static function deletar($id) {
        global $pdo;
        
        try {
            $sql = "DELETE FROM agendamentos WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao deletar agendamento: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar agendamento por ID
     */
    public static function buscarPorId($id) {
        global $pdo;
        
        try {
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamento por ID: ' . $e->getMessage());
            return false;
        }
    }
} 