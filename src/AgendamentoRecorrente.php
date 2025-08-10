<?php
/**
 * Classe AgendamentoRecorrente
 * Sistema Bichos do Bairro
 * 
 * Gerencia agendamentos recorrentes e gera ocorrências para o calendário
 */

class AgendamentoRecorrente {
    
    /**
     * Listar todos os agendamentos recorrentes
     */
    public static function listarTodos() {
        $pdo = getDb();
        
        try {
            $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos_recorrentes ar
                    JOIN clientes c ON ar.cliente_id = c.id
                    JOIN pets p ON ar.pet_id = p.id
                    WHERE ar.ativo = TRUE
                    ORDER BY ar.data_inicio DESC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao listar agendamentos recorrentes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo agendamento recorrente
     */
    public static function criar($dados) {
        $pdo = getDb();
        
        try {
            $sql = "INSERT INTO agendamentos_recorrentes 
                    (cliente_id, pet_id, tipo_recorrencia, dia_semana, semana_mes, 
                     hora_inicio, duracao, data_inicio, data_fim, observacoes) 
                    VALUES 
                    (:cliente_id, :pet_id, :tipo_recorrencia, :dia_semana, :semana_mes,
                     :hora_inicio, :duracao, :data_inicio, :data_fim, :observacoes)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cliente_id' => $dados['cliente_id'],
                'pet_id' => $dados['pet_id'],
                'tipo_recorrencia' => $dados['tipo_recorrencia'],
                'dia_semana' => $dados['dia_semana'],
                'semana_mes' => $dados['semana_mes'] ?? null,
                'hora_inicio' => $dados['hora_inicio'],
                'duracao' => $dados['duracao'] ?? 60,
                'data_inicio' => $dados['data_inicio'],
                'data_fim' => $dados['data_fim'] ?? null,
                'observacoes' => $dados['observacoes'] ?? ''
            ]);
            
            $id = $pdo->lastInsertId();
            
            // Log da criação
            self::logAcao($id, 'criado', null, $dados);
            
            return $id;
        } catch (Exception $e) {
            logError('Erro ao criar agendamento recorrente: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Gerar ocorrências para um período específico
     */
    public static function gerarOcorrencias($dataInicio, $dataFim) {
        $pdo = getDb();
        
        try {
            // Buscar agendamentos recorrentes ativos
            $sql = "SELECT * FROM agendamentos_recorrentes 
                    WHERE ativo = TRUE 
                    AND data_inicio <= :data_fim 
                    AND (data_fim IS NULL OR data_fim >= :data_inicio)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            $recorrentes = $stmt->fetchAll();
            $ocorrencias = [];
            
            foreach ($recorrentes as $recorrente) {
                $datas = self::calcularDatasOcorrencias($recorrente, $dataInicio, $dataFim);
                
                foreach ($datas as $data) {
                    // Verificar se já existe agendamento para esta data
                    $existe = self::verificarAgendamentoExistente($recorrente['id'], $data);
                    
                    if (!$existe) {
                        $ocorrencias[] = [
                            'recorrencia_id' => $recorrente['id'],
                            'cliente_id' => $recorrente['cliente_id'],
                            'pet_id' => $recorrente['pet_id'],
                            'data' => $data,
                            'hora' => $recorrente['hora_inicio'],
                            'servico' => 'Agendamento Recorrente',
                            'status' => 'confirmado',
                            'observacoes' => $recorrente['observacoes'],
                            'data_original' => $data
                        ];
                    }
                }
            }
            
            return $ocorrencias;
        } catch (Exception $e) {
            logError('Erro ao gerar ocorrências: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcular datas de ocorrências para um agendamento recorrente
     */
    private static function calcularDatasOcorrencias($recorrente, $dataInicio, $dataFim) {
        $datas = [];
        $dataAtual = max($recorrente['data_inicio'], $dataInicio);
        $dataLimite = min($recorrente['data_fim'] ?? $dataFim, $dataFim);
        
        // Converter dia da semana para formato correto (0=Domingo, 1=Segunda, etc.)
        $diaSemanaRecorrente = $recorrente['dia_semana'];
        
        // Encontrar a primeira data que corresponde ao dia da semana
        while ($dataAtual <= $dataLimite) {
            $diaSemanaAtual = date('w', strtotime($dataAtual)); // 0=Domingo, 1=Segunda, ..., 6=Sábado
            
            if ($diaSemanaAtual == $diaSemanaRecorrente) {
                // Verificar se é a semana correta para recorrência mensal
                if ($recorrente['tipo_recorrencia'] === 'mensal' && $recorrente['semana_mes']) {
                    $semanaMes = ceil(date('j', strtotime($dataAtual)) / 7);
                    if ($semanaMes == $recorrente['semana_mes']) {
                        $datas[] = $dataAtual;
                    }
                } else {
                    $datas[] = $dataAtual;
                }
                
                // Avançar para próxima data baseada no tipo de recorrência
                switch ($recorrente['tipo_recorrencia']) {
                    case 'semanal':
                        $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 week'));
                        break;
                    case 'quinzenal':
                        $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +2 weeks'));
                        break;
                    case 'mensal':
                        $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 month'));
                        break;
                }
            } else {
                // Avançar um dia até encontrar o dia correto
                $dataAtual = date('Y-m-d', strtotime($dataAtual . ' +1 day'));
            }
        }
        
        return $datas;
    }
    
    /**
     * Verificar se já existe agendamento para uma data específica
     */
    public static function verificarAgendamentoExistente($recorrenciaId, $data) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT COUNT(*) as total FROM agendamentos 
                    WHERE recorrencia_id = :recorrencia_id 
                    AND data = :data";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'recorrencia_id' => $recorrenciaId,
                'data' => $data
            ]);
            
            $resultado = $stmt->fetch();
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            logError('Erro ao verificar agendamento existente: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Criar agendamento individual a partir de ocorrência
     */
    public static function criarAgendamentoOcorrencia($ocorrencia) {
        $pdo = getDb();
        
        try {
            $sql = "INSERT INTO agendamentos 
                    (cliente_id, pet_id, data, hora, servico, status, observacoes, 
                     recorrencia_id, data_original) 
                    VALUES 
                    (:cliente_id, :pet_id, :data, :hora, :servico, :status, :observacoes,
                     :recorrencia_id, :data_original)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'cliente_id' => $ocorrencia['cliente_id'],
                'pet_id' => $ocorrencia['pet_id'],
                'data' => $ocorrencia['data'],
                'hora' => $ocorrencia['hora'],
                'servico' => $ocorrencia['servico'],
                'status' => $ocorrencia['status'],
                'observacoes' => $ocorrencia['observacoes'],
                'recorrencia_id' => $ocorrencia['recorrencia_id'],
                'data_original' => $ocorrencia['data_original']
            ]);
            
            $agendamentoId = $pdo->lastInsertId();
            
            // Log da criação da ocorrência
            self::logAcao($ocorrencia['recorrencia_id'], 'criado', $agendamentoId, $ocorrencia);
            
            return $agendamentoId;
        } catch (Exception $e) {
            logError('Erro ao criar agendamento ocorrência: ' . $e->getMessage(), $ocorrencia);
            return false;
        }
    }
    
    /**
     * Buscar agendamentos recorrentes com ocorrências para o calendário
     */
    public static function buscarParaCalendario($dataInicio, $dataFim) {
        $pdo = getDb();
        
        try {
            // Gerar ocorrências para o período
            $ocorrencias = self::gerarOcorrencias($dataInicio, $dataFim);
            
            // Criar agendamentos para as ocorrências
            foreach ($ocorrencias as $ocorrencia) {
                self::criarAgendamentoOcorrencia($ocorrencia);
            }
            
            // Buscar agendamentos (incluindo os recorrentes) para o período
            $sql = "SELECT a.*, c.nome as cliente_nome, p.nome as pet_nome,
                           ar.tipo_recorrencia, ar.dia_semana, ar.semana_mes
                    FROM agendamentos a
                    JOIN clientes c ON a.cliente_id = c.id
                    JOIN pets p ON a.pet_id = p.id
                    LEFT JOIN agendamentos_recorrentes ar ON a.recorrencia_id = ar.id
                    WHERE a.data BETWEEN :data_inicio AND :data_fim
                    ORDER BY a.data, a.hora";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamentos para calendário: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Log de ações
     */
    private static function logAcao($recorrenciaId, $acao, $agendamentoId = null, $dados = null) {
        $pdo = getDb();
        
        try {
            $sql = "INSERT INTO logs_agendamentos_recorrentes 
                    (recorrencia_id, agendamento_id, acao, dados_novos) 
                    VALUES (:recorrencia_id, :agendamento_id, :acao, :dados)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'recorrencia_id' => $recorrenciaId,
                'agendamento_id' => $agendamentoId,
                'acao' => $acao,
                'dados' => $dados ? json_encode($dados) : null
            ]);
        } catch (Exception $e) {
            logError('Erro ao logar ação de agendamento recorrente: ' . $e->getMessage());
        }
    }
    
    /**
     * Buscar por ID
     */
    public static function buscarPorId($id) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT ar.*, c.nome as cliente_nome, p.nome as pet_nome 
                    FROM agendamentos_recorrentes ar
                    JOIN clientes c ON ar.cliente_id = c.id
                    JOIN pets p ON ar.pet_id = p.id
                    WHERE ar.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar agendamento recorrente: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Atualizar agendamento recorrente
     */
    public static function atualizar($id, $dados) {
        $pdo = getDb();
        
        try {
            // Buscar dados anteriores para log
            $anterior = self::buscarPorId($id);
            
            $sql = "UPDATE agendamentos_recorrentes SET 
                    cliente_id = :cliente_id,
                    pet_id = :pet_id,
                    tipo_recorrencia = :tipo_recorrencia,
                    dia_semana = :dia_semana,
                    semana_mes = :semana_mes,
                    hora_inicio = :hora_inicio,
                    duracao = :duracao,
                    data_inicio = :data_inicio,
                    data_fim = :data_fim,
                    observacoes = :observacoes,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute([
                'cliente_id' => $dados['cliente_id'],
                'pet_id' => $dados['pet_id'],
                'tipo_recorrencia' => $dados['tipo_recorrencia'],
                'dia_semana' => $dados['dia_semana'],
                'semana_mes' => $dados['semana_mes'] ?? null,
                'hora_inicio' => $dados['hora_inicio'],
                'duracao' => $dados['duracao'] ?? 60,
                'data_inicio' => $dados['data_inicio'],
                'data_fim' => $dados['data_fim'] ?? null,
                'observacoes' => $dados['observacoes'] ?? '',
                'id' => $id
            ]);
            
            if ($resultado) {
                // Log da atualização
                self::logAcao($id, 'editado', null, [
                    'anteriores' => $anterior,
                    'novos' => $dados
                ]);
            }
            
            return $resultado;
        } catch (Exception $e) {
            logError('Erro ao atualizar agendamento recorrente: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Deletar agendamento recorrente
     */
    public static function deletar($id) {
        $pdo = getDb();
        
        try {
            // Buscar dados para log
            $anterior = self::buscarPorId($id);
            
            $sql = "UPDATE agendamentos_recorrentes SET ativo = FALSE WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $resultado = $stmt->execute(['id' => $id]);
            
            if ($resultado) {
                // Log da exclusão
                self::logAcao($id, 'cancelado', null, $anterior);
            }
            
            return $resultado;
        } catch (Exception $e) {
            logError('Erro ao deletar agendamento recorrente: ' . $e->getMessage());
            return false;
        }
    }
} 