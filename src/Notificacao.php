<?php
/**
 * Classe Notificacao - Sistema de Notificações
 * Sistema Bichos do Bairro
 */

class Notificacao extends BaseModel {
    protected static $table = 'notificacoes';
    
    /**
     * Criar nova notificação
     */
    public static function criar($dados) {
        global $pdo;
        
        try {
            $sql = "INSERT INTO notificacoes (titulo, mensagem, tipo, dados_extra, lida, created_at) 
                    VALUES (:titulo, :mensagem, :tipo, :dados_extra, :lida, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo' => $dados['titulo'],
                'mensagem' => $dados['mensagem'],
                'tipo' => $dados['tipo'],
                'dados_extra' => json_encode($dados['dados_extra'] ?? []),
                'lida' => 0
            ]);
            
            return $pdo->lastInsertId();
        } catch (Exception $e) {
            logError('Erro ao criar notificação: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Obter notificações não lidas
     */
    public static function getNotificacoesNaoLidas() {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM notificacoes WHERE lida = 0 ORDER BY created_at DESC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar notificações não lidas: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter notificações recentes
     */
    public static function getNotificacoesRecentes($limite = 10) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM notificacoes ORDER BY created_at DESC LIMIT :limite";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar notificações recentes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marcar notificação como lida
     */
    public static function marcarComoLida($id) {
        global $pdo;
        
        try {
            $sql = "UPDATE notificacoes SET lida = 1, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao marcar notificação como lida: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Marcar todas as notificações como lidas
     */
    public static function marcarTodasComoLidas() {
        global $pdo;
        
        try {
            $sql = "UPDATE notificacoes SET lida = 1, updated_at = NOW() WHERE lida = 0";
            $stmt = $pdo->query($sql);
            return $stmt->rowCount();
        } catch (Exception $e) {
            logError('Erro ao marcar todas as notificações como lidas: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Enviar lembrete para agendamento específico
     */
    public static function enviarLembreteAgendamento($agendamentoId) {
        global $pdo;
        
        try {
            // Buscar dados do agendamento
            $sql = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                           p.nome as pet_nome, p.especie as pet_especie
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $agendamentoId]);
            $agendamento = $stmt->fetch();
            
            if (!$agendamento) {
                return false;
            }
            
            // Criar notificação
            $dados = [
                'titulo' => 'Lembrete de Agendamento',
                'mensagem' => "Lembrete: {$agendamento['cliente_nome']} tem agendamento para {$agendamento['pet_nome']} em " . formatDate($agendamento['data']) . " às {$agendamento['hora']}",
                'tipo' => 'lembrete',
                'dados_extra' => [
                    'agendamento_id' => $agendamentoId,
                    'cliente_nome' => $agendamento['cliente_nome'],
                    'cliente_telefone' => $agendamento['cliente_telefone'],
                    'pet_nome' => $agendamento['pet_nome'],
                    'data' => $agendamento['data'],
                    'hora' => $agendamento['hora'],
                    'servico' => $agendamento['servico']
                ]
            ];
            
            $notificacaoId = self::criar($dados);
            
            if ($notificacaoId) {
                // Aqui você pode implementar o envio de SMS/email
                self::enviarSMS($agendamento['cliente_telefone'], $dados['mensagem']);
                self::enviarEmail($agendamento['cliente_nome'], $dados['mensagem']);
                
                logInfo('Lembrete enviado para agendamento', [
                    'agendamento_id' => $agendamentoId,
                    'cliente' => $agendamento['cliente_nome'],
                    'data' => $agendamento['data'],
                    'hora' => $agendamento['hora']
                ]);
                
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            logError('Erro ao enviar lembrete: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar lembretes automáticos
     */
    public static function enviarLembretesAutomaticos() {
        $amanha = date('Y-m-d', strtotime('+1 day'));
        $agendamentos = Agendamento::getAgendamentosPorData($amanha);
        $enviados = 0;
        
        foreach ($agendamentos as $agendamento) {
            if (self::enviarLembreteAgendamento($agendamento['id'])) {
                $enviados++;
            }
        }
        
        return $enviados;
    }
    
    /**
     * Enviar notificação de agendamento vencido
     */
    public static function notificarAgendamentoVencido($agendamentoId) {
        global $pdo;
        
        try {
            // Buscar dados do agendamento
            $sql = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                           p.nome as pet_nome
                    FROM agendamentos a 
                    JOIN clientes c ON a.cliente_id = c.id 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $agendamentoId]);
            $agendamento = $stmt->fetch();
            
            if (!$agendamento) {
                return false;
            }
            
            // Criar notificação
            $dados = [
                'titulo' => 'Agendamento Vencido',
                'mensagem' => "ATENÇÃO: Agendamento de {$agendamento['cliente_nome']} para {$agendamento['pet_nome']} vencido em " . formatDate($agendamento['data']),
                'tipo' => 'vencido',
                'dados_extra' => [
                    'agendamento_id' => $agendamentoId,
                    'cliente_nome' => $agendamento['cliente_nome'],
                    'cliente_telefone' => $agendamento['cliente_telefone'],
                    'pet_nome' => $agendamento['pet_nome'],
                    'data' => $agendamento['data'],
                    'hora' => $agendamento['hora']
                ]
            ];
            
            return self::criar($dados);
        } catch (Exception $e) {
            logError('Erro ao notificar agendamento vencido: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificação de novo cliente
     */
    public static function notificarNovoCliente($clienteId) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $clienteId]);
            $cliente = $stmt->fetch();
            
            if (!$cliente) {
                return false;
            }
            
            $dados = [
                'titulo' => 'Novo Cliente Cadastrado',
                'mensagem' => "Novo cliente cadastrado: {$cliente['nome']} - {$cliente['telefone']}",
                'tipo' => 'novo_cliente',
                'dados_extra' => [
                    'cliente_id' => $clienteId,
                    'cliente_nome' => $cliente['nome'],
                    'cliente_telefone' => $cliente['telefone']
                ]
            ];
            
            return self::criar($dados);
        } catch (Exception $e) {
            logError('Erro ao notificar novo cliente: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar notificação de novo pet
     */
    public static function notificarNovoPet($petId) {
        global $pdo;
        
        try {
            $sql = "SELECT p.*, c.nome as cliente_nome 
                    FROM pets p 
                    JOIN clientes c ON p.cliente_id = c.id 
                    WHERE p.id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $petId]);
            $pet = $stmt->fetch();
            
            if (!$pet) {
                return false;
            }
            
            $dados = [
                'titulo' => 'Novo Pet Cadastrado',
                'mensagem' => "Novo pet cadastrado: {$pet['nome']} ({$pet['especie']}) - Cliente: {$pet['cliente_nome']}",
                'tipo' => 'novo_pet',
                'dados_extra' => [
                    'pet_id' => $petId,
                    'pet_nome' => $pet['nome'],
                    'pet_especie' => $pet['especie'],
                    'cliente_nome' => $pet['cliente_nome']
                ]
            ];
            
            return self::criar($dados);
        } catch (Exception $e) {
            logError('Erro ao notificar novo pet: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enviar SMS (simulado)
     */
    private static function enviarSMS($telefone, $mensagem) {
        // Aqui você implementaria a integração com serviço de SMS
        // Por exemplo: Twilio, Zenvia, etc.
        
        logInfo('SMS enviado', [
            'telefone' => $telefone,
            'mensagem' => $mensagem
        ]);
        
        return true;
    }
    
    /**
     * Enviar Email (simulado)
     */
    private static function enviarEmail($nome, $mensagem) {
        // Aqui você implementaria o envio de email
        // Por exemplo: PHPMailer, SendGrid, etc.
        
        logInfo('Email enviado', [
            'nome' => $nome,
            'mensagem' => $mensagem
        ]);
        
        return true;
    }
    
    /**
     * Limpar notificações antigas (mais de 30 dias)
     */
    public static function limparAntigas() {
        global $pdo;
        
        try {
            $sql = "DELETE FROM notificacoes 
                    WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) 
                    AND lida = 1";
            
            $stmt = $pdo->query($sql);
            $removidas = $stmt->rowCount();
            
            logInfo('Notificações antigas removidas', ['quantidade' => $removidas]);
            
            return $removidas;
        } catch (Exception $e) {
            logError('Erro ao limpar notificações antigas: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obter estatísticas de notificações
     */
    public static function getEstatisticas() {
        global $pdo;
        
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN lida = 0 THEN 1 ELSE 0 END) as nao_lidas,
                        SUM(CASE WHEN lida = 1 THEN 1 ELSE 0 END) as lidas,
                        SUM(CASE WHEN tipo = 'lembrete' THEN 1 ELSE 0 END) as lembretes,
                        SUM(CASE WHEN tipo = 'vencido' THEN 1 ELSE 0 END) as vencidos,
                        SUM(CASE WHEN tipo = 'novo_cliente' THEN 1 ELSE 0 END) as novos_clientes,
                        SUM(CASE WHEN tipo = 'novo_pet' THEN 1 ELSE 0 END) as novos_pets
                    FROM notificacoes";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar estatísticas de notificações: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar notificações
     */
    public static function buscar($termo, $filtros = []) {
        global $pdo;
        
        try {
            $sql = "SELECT * FROM notificacoes WHERE titulo LIKE :termo OR mensagem LIKE :termo";
            $params = ['termo' => "%$termo%"];
            
            // Aplicar filtros
            if (!empty($filtros['tipo'])) {
                $sql .= " AND tipo = :tipo";
                $params['tipo'] = $filtros['tipo'];
            }
            
            if (isset($filtros['lida'])) {
                $sql .= " AND lida = :lida";
                $params['lida'] = $filtros['lida'];
            }
            
            if (!empty($filtros['data_inicio'])) {
                $sql .= " AND created_at >= :data_inicio";
                $params['data_inicio'] = $filtros['data_inicio'];
            }
            
            if (!empty($filtros['data_fim'])) {
                $sql .= " AND created_at <= :data_fim";
                $params['data_fim'] = $filtros['data_fim'];
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            if (isset($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params['limite'] = $filtros['limite'];
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar notificações: ' . $e->getMessage());
            return [];
        }
    }
} 