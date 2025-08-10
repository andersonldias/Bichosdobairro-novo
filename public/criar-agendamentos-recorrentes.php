<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../src/init.php';

$erro = '';
$sucesso = '';
$resultados = [];

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Executar criação das tabelas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_tabelas'])) {
    try {
        $pdo = Database::getConnection();
        
        // Array com todos os comandos SQL
        $comandos = [
            // 1. Criar tabela principal
            "CREATE TABLE IF NOT EXISTS agendamentos_recorrentes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cliente_id INT NOT NULL,
                pet_id INT NOT NULL,
                tipo_recorrencia ENUM('semanal', 'quinzenal', 'mensal') NOT NULL,
                dia_semana INT NOT NULL COMMENT '1=Segunda, 2=Terça, 3=Quarta, 4=Quinta, 5=Sexta, 6=Sábado, 7=Domingo',
                semana_mes INT NULL COMMENT 'Para mensal: 1=1ª semana, 2=2ª semana, 3=3ª semana, 4=4ª semana, 5=última semana',
                hora_inicio TIME NOT NULL,
                duracao INT NOT NULL DEFAULT 60 COMMENT 'duração em minutos',
                data_inicio DATE NOT NULL,
                data_fim DATE NULL COMMENT 'NULL = indefinido',
                ativo BOOLEAN DEFAULT TRUE,
                observacoes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_cliente (cliente_id),
                INDEX idx_pet (pet_id),
                INDEX idx_tipo_recorrencia (tipo_recorrencia),
                INDEX idx_data_inicio (data_inicio),
                INDEX idx_ativo (ativo),
                
                FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
                FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Tabela agendamentos_recorrentes criada/verificada",
            
            // 2. Adicionar colunas na tabela agendamentos
            "ALTER TABLE agendamentos 
             ADD COLUMN IF NOT EXISTS recorrencia_id INT NULL COMMENT 'Referência ao agendamento recorrente'" => "Coluna recorrencia_id adicionada",
            
            "ALTER TABLE agendamentos 
             ADD COLUMN IF NOT EXISTS data_original DATE NULL COMMENT 'Data original do agendamento recorrente'" => "Coluna data_original adicionada",
            
            "ALTER TABLE agendamentos 
             ADD COLUMN IF NOT EXISTS status ENUM('confirmado', 'cancelado', 'remarcado') DEFAULT 'confirmado'" => "Coluna status adicionada",
            
            "ALTER TABLE agendamentos 
             ADD COLUMN IF NOT EXISTS observacoes_edicao TEXT COMMENT 'Observações específicas desta ocorrência'" => "Coluna observacoes_edicao adicionada",
            
            // 3. Adicionar índices
            "ALTER TABLE agendamentos ADD INDEX IF NOT EXISTS idx_recorrencia (recorrencia_id)" => "Índice idx_recorrencia adicionado",
            "ALTER TABLE agendamentos ADD INDEX IF NOT EXISTS idx_status (status)" => "Índice idx_status adicionado",
            "ALTER TABLE agendamentos ADD INDEX IF NOT EXISTS idx_data_original (data_original)" => "Índice idx_data_original adicionado",
            
            // 4. Adicionar foreign key
            "ALTER TABLE agendamentos 
             ADD CONSTRAINT IF NOT EXISTS fk_agendamento_recorrencia 
             FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE SET NULL" => "Foreign key fk_agendamento_recorrencia adicionada",
            
            // 5. Criar tabela de logs
            "CREATE TABLE IF NOT EXISTS logs_agendamentos_recorrentes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                recorrencia_id INT NOT NULL,
                agendamento_id INT NULL,
                acao ENUM('criado', 'editado', 'cancelado', 'remarcado', 'pausado', 'reativado') NOT NULL,
                data_acao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                dados_anteriores JSON NULL,
                dados_novos JSON NULL,
                usuario_id INT NULL,
                observacoes TEXT,
                
                INDEX idx_recorrencia (recorrencia_id),
                INDEX idx_agendamento (agendamento_id),
                INDEX idx_acao (acao),
                INDEX idx_data_acao (data_acao),
                
                FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE CASCADE,
                FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci" => "Tabela logs_agendamentos_recorrentes criada/verificada",
            
            // 6. Criar função para nome do dia da semana
            "CREATE FUNCTION IF NOT EXISTS get_dia_semana_nome(dia INT) 
             RETURNS VARCHAR(20)
             DETERMINISTIC
             BEGIN
                 DECLARE nome VARCHAR(20);
                 CASE dia
                     WHEN 1 THEN SET nome = 'Segunda-feira';
                     WHEN 2 THEN SET nome = 'Terça-feira';
                     WHEN 3 THEN SET nome = 'Quarta-feira';
                     WHEN 4 THEN SET nome = 'Quinta-feira';
                     WHEN 5 THEN SET nome = 'Sexta-feira';
                     WHEN 6 THEN SET nome = 'Sábado';
                     WHEN 7 THEN SET nome = 'Domingo';
                     ELSE SET nome = 'Desconhecido';
                 END CASE;
                 RETURN nome;
             END" => "Função get_dia_semana_nome criada",
            
            // 7. Criar função para nome da semana do mês
            "CREATE FUNCTION IF NOT EXISTS get_semana_mes_nome(semana INT) 
             RETURNS VARCHAR(20)
             DETERMINISTIC
             BEGIN
                 DECLARE nome VARCHAR(20);
                 CASE semana
                     WHEN 1 THEN SET nome = '1ª semana';
                     WHEN 2 THEN SET nome = '2ª semana';
                     WHEN 3 THEN SET nome = '3ª semana';
                     WHEN 4 THEN SET nome = '4ª semana';
                     WHEN 5 THEN SET nome = 'última semana';
                     ELSE SET nome = 'Desconhecida';
                 END CASE;
                 RETURN nome;
             END" => "Função get_semana_mes_nome criada",
            
            // 8. Criar view para agendamentos recorrentes
            "CREATE OR REPLACE VIEW v_agendamentos_recorrentes AS
             SELECT 
                 ar.id,
                 ar.cliente_id,
                 c.nome as cliente_nome,
                 ar.pet_id,
                 p.nome as pet_nome,
                 ar.tipo_recorrencia,
                 ar.dia_semana,
                 get_dia_semana_nome(ar.dia_semana) as dia_semana_nome,
                 ar.semana_mes,
                 get_semana_mes_nome(ar.semana_mes) as semana_mes_nome,
                 ar.hora_inicio,
                 ar.duracao,
                 ar.data_inicio,
                 ar.data_fim,
                 ar.ativo,
                 ar.observacoes,
                 ar.created_at,
                 ar.updated_at
             FROM agendamentos_recorrentes ar
             JOIN clientes c ON ar.cliente_id = c.id
             JOIN pets p ON ar.pet_id = p.id
             WHERE ar.ativo = TRUE" => "View v_agendamentos_recorrentes criada",
            
            // 9. Criar view para próximos agendamentos
            "CREATE OR REPLACE VIEW v_proximos_recorrentes AS
             SELECT 
                 ar.id,
                 ar.cliente_id,
                 c.nome as cliente_nome,
                 ar.pet_id,
                 p.nome as pet_nome,
                 ar.tipo_recorrencia,
                 ar.dia_semana,
                 ar.semana_mes,
                 ar.hora_inicio,
                 ar.duracao,
                 ar.data_inicio,
                 ar.data_fim,
                 ar.observacoes,
                 CASE 
                     WHEN ar.tipo_recorrencia = 'semanal' THEN
                         DATE_ADD(CURDATE(), INTERVAL (ar.dia_semana - WEEKDAY(CURDATE()) + 7) % 7 DAY)
                     WHEN ar.tipo_recorrencia = 'quinzenal' THEN
                         DATE_ADD(CURDATE(), INTERVAL (ar.dia_semana - WEEKDAY(CURDATE()) + 14) % 14 DAY)
                     WHEN ar.tipo_recorrencia = 'mensal' THEN
                         DATE_ADD(CURDATE(), INTERVAL 1 MONTH)
                 END as proxima_data
             FROM agendamentos_recorrentes ar
             JOIN clientes c ON ar.cliente_id = c.id
             JOIN pets p ON ar.pet_id = p.id
             WHERE ar.ativo = TRUE 
             AND (ar.data_fim IS NULL OR ar.data_fim >= CURDATE())" => "View v_proximos_recorrentes criada"
        ];
        
        // Executar cada comando
        foreach ($comandos as $sql => $descricao) {
            try {
                $pdo->exec($sql);
                $resultados[] = "✅ " . $descricao;
            } catch (PDOException $e) {
                $resultados[] = "⚠️ " . $descricao . " - " . $e->getMessage();
            }
        }
        
        $sucesso = "Estrutura de agendamentos recorrentes criada com sucesso!";
        
    } catch (Exception $e) {
        $erro = "Erro ao criar estrutura: " . $e->getMessage();
    }
}

// Verificar se as tabelas existem
$tabelas_existem = false;
try {
    $pdo = Database::getConnection();
    $stmt = $pdo->query("SHOW TABLES LIKE 'agendamentos_recorrentes'");
    $tabelas_existem = $stmt->rowCount() > 0;
} catch (Exception $e) {
    // Ignorar erro
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Agendamentos Recorrentes - Bichos do Bairro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                <i class="fas fa-calendar-repeat text-blue-500 mr-3"></i>
                Sistema de Agendamentos Recorrentes
            </h1>
            <p class="text-gray-600">Versão 1.1 - Criando estrutura do banco de dados</p>
        </div>

        <!-- Status das Tabelas -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-database text-green-500 mr-2"></i>
                Status do Banco de Dados
            </h2>
            
            <?php if ($tabelas_existem): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-check-circle mr-2"></i>
                    <strong>Tabelas já existem!</strong> O sistema de agendamentos recorrentes já está configurado.
                </div>
            <?php else: ?>
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Tabelas não encontradas.</strong> Clique no botão abaixo para criar a estrutura.
                </div>
            <?php endif; ?>
        </div>

        <!-- Botão de Criação -->
        <?php if (!$tabelas_existem): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form method="post" action="">
                <button type="submit" name="criar_tabelas" class="w-full bg-blue-500 text-white py-3 px-6 rounded-lg hover:bg-blue-600 font-semibold transition-colors">
                    <i class="fas fa-magic mr-2"></i>
                    Criar Estrutura de Agendamentos Recorrentes
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Resultados -->
        <?php if (!empty($resultados)): ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-list-check text-blue-500 mr-2"></i>
                Resultados da Criação
            </h2>
            <div class="space-y-2">
                <?php foreach ($resultados as $resultado): ?>
                    <div class="text-sm"><?= htmlspecialchars($resultado) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Próximos Passos -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-arrow-right text-purple-500 mr-2"></i>
                Próximos Passos
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                        1. Interface de Criação
                    </h3>
                    <p class="text-sm text-gray-600">Criar formulário para adicionar agendamentos recorrentes</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-list text-blue-500 mr-2"></i>
                        2. Lista de Recorrentes
                    </h3>
                    <p class="text-sm text-gray-600">Interface para gerenciar agendamentos recorrentes</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-calendar text-yellow-500 mr-2"></i>
                        3. Integração com Calendário
                    </h3>
                    <p class="text-sm text-gray-600">Mostrar agendamentos recorrentes no calendário</p>
                </div>
                
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-800 mb-2">
                        <i class="fas fa-edit text-purple-500 mr-2"></i>
                        4. Edição Individual
                    </h3>
                    <p class="text-sm text-gray-600">Permitir editar agendamentos específicos</p>
                </div>
            </div>
        </div>

        <!-- Links de Navegação -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-link text-gray-500 mr-2"></i>
                Navegação
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="dashboard.php" class="bg-gray-100 hover:bg-gray-200 text-gray-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
                
                <a href="agendamentos.php" class="bg-blue-100 hover:bg-blue-200 text-blue-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-calendar mr-2"></i>
                    Agendamentos
                </a>
                
                <a href="admin.php" class="bg-purple-100 hover:bg-purple-200 text-purple-800 py-3 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-cog mr-2"></i>
                    Administração
                </a>
            </div>
        </div>

        <!-- Mensagens -->
        <?php if (!empty($erro)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mt-6">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sucesso)): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mt-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?= htmlspecialchars($sucesso) ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Auto-refresh após criação bem-sucedida
    <?php if (!empty($sucesso)): ?>
    setTimeout(function() {
        location.reload();
    }, 3000);
    <?php endif; ?>
    </script>
</body>
</html> 