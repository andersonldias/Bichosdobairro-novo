<?php
require_once __DIR__ . '/db.php';
require_once 'BaseModel.php';
require_once 'Utils.php';

/**
 * Classe Cliente - Sistema Avançado
 * Sistema Bichos do Bairro
 */

class Cliente extends BaseModel {
    protected static $table = 'clientes';
    
    /**
     * Listar todos os clientes (método de compatibilidade)
     */
    public static function listarTodos() {
        $pdo = getDb();
        
        try {
            $sql = "SELECT * FROM clientes ORDER BY nome ASC";
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao listar clientes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Criar novo cliente
     * E-mail é opcional, telefone é obrigatório, CPF é obrigatório
     */
    public static function criar($dados) {
        $pdo = getDb();
        
        // Validação: telefone é obrigatório
        if (empty($dados['telefone'])) {
            throw new Exception('Telefone é obrigatório');
        }
        
        // Validação: telefone não pode ser duplicado
        if (self::verificarDuplicidadeTelefone($dados['telefone'])) {
            throw new Exception('Telefone já está cadastrado para outro cliente');
        }
        
        // Validação: CPF é obrigatório
        if (empty($dados['cpf'])) {
            throw new Exception('CPF é obrigatório');
        }
        
        try {
            $sql = "INSERT INTO clientes (nome, email, telefone, cpf, cep, logradouro, numero, complemento, bairro, cidade, estado, observacoes, criado_em) 
                    VALUES (:nome, :email, :telefone, :cpf, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :observacoes, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $dados['nome'],
                'email' => !empty($dados['email']) ? $dados['email'] : null,
                'telefone' => preg_replace('/[^0-9]/', '', $dados['telefone']),
                'cpf' => $dados['cpf'],
                'cep' => $dados['cep'] ?? null,
                'logradouro' => $dados['logradouro'] ?? null,
                'numero' => $dados['numero'] ?? null,
                'complemento' => $dados['complemento'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'estado' => $dados['estado'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null
            ]);
            
            $id = $pdo->lastInsertId();
            
            // Salvar telefones se fornecidos
            if (!empty($dados['telefones']) && is_array($dados['telefones'])) {
                self::salvarTelefones($id, $dados['telefones']);
            }
            
            // Salvar pets se fornecidos
            if (!empty($dados['pets']) && is_array($dados['pets'])) {
                self::salvarPets($id, $dados['pets']);
            }
            
            // Criar notificação
            if (class_exists('Notificacao')) {
                Notificacao::notificarNovoCliente($id);
            }
            
            return $id;
        } catch (Exception $e) {
            logError('Erro ao criar cliente: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Atualizar cliente
     * E-mail é opcional, telefone é obrigatório, CPF é obrigatório
     */
    public static function atualizar($id, $dados) {
        $pdo = getDb();
        
        // Validação: telefone é obrigatório
        if (empty($dados['telefone'])) {
            throw new Exception('Telefone é obrigatório');
        }
        
        // Validação: telefone não pode ser duplicado (excluindo o cliente atual)
        if (self::verificarDuplicidadeTelefone($dados['telefone'], $id)) {
            throw new Exception('Telefone já está cadastrado para outro cliente');
        }
        
        // Validação: CPF é obrigatório
        if (empty($dados['cpf'])) {
            throw new Exception('CPF é obrigatório');
        }
        
        try {
            $sql = "UPDATE clientes SET 
                        nome = :nome, 
                        email = :email, 
                        telefone = :telefone, 
                        cpf = :cpf, 
                        cep = :cep,
                        logradouro = :logradouro,
                        numero = :numero,
                        complemento = :complemento,
                        bairro = :bairro,
                        cidade = :cidade,
                        estado = :estado,
                        observacoes = :observacoes, 
                        updated_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'nome' => $dados['nome'],
                'email' => !empty($dados['email']) ? $dados['email'] : null,
                'telefone' => preg_replace('/[^0-9]/', '', $dados['telefone']),
                'cpf' => $dados['cpf'],
                'cep' => $dados['cep'] ?? null,
                'logradouro' => $dados['logradouro'] ?? null,
                'numero' => $dados['numero'] ?? null,
                'complemento' => $dados['complemento'] ?? null,
                'bairro' => $dados['bairro'] ?? null,
                'cidade' => $dados['cidade'] ?? null,
                'estado' => $dados['estado'] ?? null,
                'observacoes' => $dados['observacoes'] ?? null,
                'id' => $id
            ]);
            
            // Salvar telefones se fornecidos
            if (!empty($dados['telefones']) && is_array($dados['telefones'])) {
                self::salvarTelefones($id, $dados['telefones']);
            }
            
            // Salvar pets se fornecidos
            if (!empty($dados['pets']) && is_array($dados['pets'])) {
                self::salvarPets($id, $dados['pets']);
            }
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao atualizar cliente: ' . $e->getMessage(), $dados);
            return false;
        }
    }
    
    /**
     * Buscar cliente por termo
     */
    public static function buscar($termo, $filtros = []) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT * FROM clientes WHERE nome LIKE :termo OR email LIKE :termo OR telefone LIKE :termo OR cpf LIKE :termo";
            $params = ['termo' => "%$termo%"];
            
            // Aplicar filtros
            if (!empty($filtros['limite'])) {
                $sql .= " LIMIT :limite";
                $params['limite'] = $filtros['limite'];
            }
            
            $sql .= " ORDER BY nome ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar clientes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter cliente com pets
     */
    public static function getClienteComPets($id) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT c.*, 
                           COUNT(p.id) as total_pets,
                           GROUP_CONCAT(p.nome SEPARATOR ', ') as nomes_pets
                    FROM clientes c 
                    LEFT JOIN pets p ON c.id = p.cliente_id 
                    WHERE c.id = :id 
                    GROUP BY c.id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar cliente com pets: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter clientes por período
     */
    public static function getClientesPorPeriodo($dataInicio, $dataFim) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT * FROM clientes 
                    WHERE created_at BETWEEN :data_inicio AND :data_fim 
                    ORDER BY created_at DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar clientes por período: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter clientes mais ativos
     */
    public static function getClientesMaisAtivos($limite = 10) {
        $pdo = getDb();
        
        try {
            $sql = "SELECT c.*, COUNT(a.id) as total_agendamentos
                    FROM clientes c 
                    LEFT JOIN agendamentos a ON c.id = a.cliente_id 
                    GROUP BY c.id 
                    ORDER BY total_agendamentos DESC 
                    LIMIT :limite";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar clientes mais ativos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter clientes inativos (sem agendamentos nos últimos 6 meses)
     */
    public static function getClientesInativos() {
        try {
            $pdo = getDb();
            $sql = "SELECT c.*, MAX(a.data) as ultimo_agendamento
                    FROM clientes c 
                    LEFT JOIN agendamentos a ON c.id = a.cliente_id 
                    GROUP BY c.id 
                    HAVING ultimo_agendamento IS NULL 
                       OR ultimo_agendamento < DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    ORDER BY ultimo_agendamento ASC";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar clientes inativos: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obter estatísticas de clientes
     */
    public static function getEstatisticas() {
        try {
            $pdo = getDb();
            $sql = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as novos_30_dias,
                        SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as novos_7_dias,
                        SUM(CASE WHEN email != '' THEN 1 ELSE 0 END) as com_email,
                        SUM(CASE WHEN cpf != '' THEN 1 ELSE 0 END) as com_cpf
                    FROM clientes";
            
            $stmt = $pdo->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar estatísticas de clientes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar se CPF já existe
     */
    public static function cpfExiste($cpf, $excluirId = null) {
        try {
            $pdo = getDb();
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE cpf = :cpf";
            $params = ['cpf' => $cpf];
            
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
                $params['excluir_id'] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            logError('Erro ao verificar CPF: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se email já existe (e-mail é opcional)
     */
    public static function emailExiste($email, $excluirId = null) {
        // Se o e-mail estiver vazio, não verificar duplicidade
        if (empty($email)) {
            return false;
        }
        
        try {
            $pdo = getDb();
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE email = :email AND email IS NOT NULL";
            $params = ['email' => $email];
            
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
                $params['excluir_id'] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            logError('Erro ao verificar email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter histórico de agendamentos do cliente
     */
    public static function getHistoricoAgendamentos($id, $limite = 20) {
        try {
            $pdo = getDb();
            $sql = "SELECT a.*, p.nome as pet_nome 
                    FROM agendamentos a 
                    JOIN pets p ON a.pet_id = p.id 
                    WHERE a.cliente_id = :id 
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
     * Obter preferências do cliente
     */
    public static function getPreferencias($id) {
        try {
            $pdo = getDb();
            $sql = "SELECT 
                        servico,
                        COUNT(*) as total,
                        MAX(data) as ultimo_servico
                    FROM agendamentos 
                    WHERE cliente_id = :id AND status = 'concluido'
                    GROUP BY servico 
                    ORDER BY total DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar preferências do cliente: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Enviar notificação para cliente
     */
    public static function enviarNotificacao($id, $titulo, $mensagem) {
        $cliente = self::buscarPorId($id);
        
        if (!$cliente) {
            return false;
        }
        
        $dados = [
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'tipo' => 'cliente',
            'dados_extra' => [
                'cliente_id' => $id,
                'cliente_nome' => $cliente['nome'],
                'cliente_telefone' => $cliente['telefone'],
                'cliente_email' => $cliente['email']
            ]
        ];
        
        if (class_exists('Notificacao')) {
            return Notificacao::criar($dados);
        }
        
        return false;
    }
    
    /**
     * Obter relatório de clientes
     */
    public static function getRelatorio($dataInicio, $dataFim) {
        try {
            $pdo = getDb();
            $sql = "SELECT 
                        c.*,
                        COUNT(a.id) as total_agendamentos,
                        SUM(CASE WHEN a.status = 'concluido' THEN 1 ELSE 0 END) as agendamentos_concluidos,
                        COUNT(DISTINCT p.id) as total_pets,
                        MAX(a.data) as ultimo_agendamento
                    FROM clientes c 
                    LEFT JOIN agendamentos a ON c.id = a.cliente_id 
                    LEFT JOIN pets p ON c.id = p.cliente_id 
                    WHERE c.created_at BETWEEN :data_inicio AND :data_fim 
                    GROUP BY c.id 
                    ORDER BY total_agendamentos DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim
            ]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao gerar relatório de clientes: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Buscar telefones do cliente
     */
    public static function buscarTelefones($clienteId) {
        try {
            $pdo = getDb();
            $sql = "SELECT * FROM telefones WHERE cliente_id = :cliente_id ORDER BY id ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['cliente_id' => $clienteId]);
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro ao buscar telefones do cliente: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Verificar duplicidade de telefone
     */
    public static function verificarDuplicidadeTelefone($telefone, $excluirId = null) {
        try {
            $pdo = getDb();
            
            // Limpar telefone (remover caracteres especiais) para comparação
            $telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);
            
            // Buscar todos os telefones e comparar os limpos no PHP
            $sql = "SELECT telefone FROM clientes";
            $params = [];
            
            if ($excluirId) {
                $sql .= " WHERE id != :excluir_id";
                $params['excluir_id'] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $telefones = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Verificar se algum telefone limpo é igual ao telefone de teste
            foreach ($telefones as $telefone_banco) {
                $telefone_banco_limpo = preg_replace('/[^0-9]/', '', $telefone_banco);
                if ($telefone_banco_limpo === $telefone_limpo) {
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            logError('Erro ao verificar duplicidade de telefone: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar duplicidade de campo
     */
    public static function verificarDuplicidade($campo, $valor, $excluirId = null) {
        try {
            $pdo = getDb();
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE $campo = :valor";
            $params = ['valor' => $valor];
            
            if ($excluirId) {
                $sql .= " AND id != :excluir_id";
                $params['excluir_id'] = $excluirId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            logError('Erro ao verificar duplicidade: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validar CPF
     */
    public static function validarCPF($cpf) {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // Verifica se tem 11 dígitos
        if (strlen($cpf) != 11) {
            return false;
        }
        
        // Verifica se todos os dígitos são iguais
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        
        // Calcula os dígitos verificadores
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
     * Verificar se existe cliente duplicado
     * E-mail é opcional, só verifica se não estiver vazio
     */
    public static function existeDuplicado($nome, $email, $cpf, $ignorarId = null) {
        try {
            $pdo = getDb();
            $sql = "SELECT COUNT(*) as total FROM clientes WHERE (nome = :nome OR cpf = :cpf)";
            $params = [
                'nome' => $nome,
                'cpf' => $cpf
            ];
            
            // Só verificar e-mail se não estiver vazio
            if (!empty($email)) {
                $sql .= " OR email = :email";
                $params['email'] = $email;
            }
            
            if ($ignorarId) {
                $sql .= " AND id != :ignorar_id";
                $params['ignorar_id'] = $ignorarId;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $resultado = $stmt->fetch();
            
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            logError('Erro ao verificar duplicado: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar cliente por ID
     */
    public static function buscarPorId($id) {
        try {
            $pdo = getDb();
            $sql = "SELECT * FROM clientes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->fetch();
        } catch (Exception $e) {
            logError('Erro ao buscar cliente por ID: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Deletar cliente
     */
    public static function deletar($id) {
        try {
            $pdo = getDb();
            // Primeiro deletar telefones relacionados
            $pdo->exec("DELETE FROM telefones WHERE cliente_id = $id");
            
            // Depois deletar o cliente
            $sql = "DELETE FROM clientes WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            logError('Erro ao deletar cliente: ' . $e->getMessage());
            return false;
        }
    }

    public static function buscarPorNome($termo) {
        try {
            $pdo = getDb();
            $sql = "SELECT * FROM clientes WHERE nome LIKE :termo ORDER BY nome LIMIT 20";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['termo' => "%$termo%"]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            logError('Erro no método buscarPorNome: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Salvar telefones do cliente
     */
    public static function salvarTelefones($clienteId, $telefones) {
        if (empty($telefones) || !is_array($telefones)) {
            return false;
        }
        
        try {
            $pdo = getDb();
            
            // Primeiro deletar telefones existentes
            $stmt = $pdo->prepare("DELETE FROM telefones WHERE cliente_id = :cliente_id");
            $stmt->execute(['cliente_id' => $clienteId]);
            
            // Inserir novos telefones
            $stmt = $pdo->prepare("INSERT INTO telefones (cliente_id, nome, numero, criado_em) VALUES (:cliente_id, :nome, :numero, NOW())");
            
            foreach ($telefones as $telefone) {
                if (!empty($telefone['numero'])) {
                    $numeroLimpo = preg_replace('/[^0-9]/', '', $telefone['numero']);
                    $stmt->execute([
                        'cliente_id' => $clienteId,
                        'nome' => $telefone['nome'] ?: 'Principal',
                        'numero' => $numeroLimpo
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            logError('Erro ao salvar telefones: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salvar pets do cliente
     */
    public static function salvarPets($clienteId, $pets) {
        if (empty($pets) || !is_array($pets)) {
            return false;
        }
        
        try {
            $pdo = getDb();
            
            // Primeiro deletar pets existentes
            $stmt = $pdo->prepare("DELETE FROM pets WHERE cliente_id = :cliente_id");
            $stmt->execute(['cliente_id' => $clienteId]);
            
            // Inserir novos pets
            $stmt = $pdo->prepare("INSERT INTO pets (cliente_id, nome, especie, raca, idade, criado_em) VALUES (:cliente_id, :nome, :especie, :raca, :idade, NOW())");
            
            foreach ($pets as $pet) {
                if (!empty($pet['nome'])) {
                    $stmt->execute([
                        'cliente_id' => $clienteId,
                        'nome' => $pet['nome'],
                        'especie' => $pet['especie'] ?: null,
                        'raca' => $pet['raca'] ?: null,
                        'idade' => $pet['idade'] ?: null
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            logError('Erro ao salvar pets: ' . $e->getMessage());
            return false;
        }
    }
}