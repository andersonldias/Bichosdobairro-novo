<?php
/**
 * Criar Tabelas de Agendamentos Recorrentes
 * Script de execução direta
 */

require_once 'src/init.php';

echo "<h1>🔧 Criando Tabelas de Agendamentos Recorrentes</h1>";
echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";

try {
    $pdo = getDb();
    
    // 1. Criar tabela principal
    echo "<h2>1. Criando tabela agendamentos_recorrentes...</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS agendamentos_recorrentes (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela agendamentos_recorrentes criada com sucesso!</p>";
    
    // 2. Adicionar colunas na tabela agendamentos
    echo "<h2>2. Adicionando colunas na tabela agendamentos...</h2>";
    
    $colunas = [
        "ADD COLUMN IF NOT EXISTS recorrencia_id INT NULL COMMENT 'Referência ao agendamento recorrente'",
        "ADD COLUMN IF NOT EXISTS data_original DATE NULL COMMENT 'Data original do agendamento recorrente'",
        "ADD COLUMN IF NOT EXISTS status ENUM('confirmado', 'cancelado', 'remarcado') DEFAULT 'confirmado'",
        "ADD COLUMN IF NOT EXISTS observacoes_edicao TEXT COMMENT 'Observações específicas desta ocorrência'"
    ];
    
    foreach ($colunas as $coluna) {
        $sql = "ALTER TABLE agendamentos $coluna";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Coluna adicionada: $coluna</p>";
    }
    
    // 3. Adicionar índices
    echo "<h2>3. Adicionando índices...</h2>";
    
    $indices = [
        "ADD INDEX IF NOT EXISTS idx_recorrencia (recorrencia_id)",
        "ADD INDEX IF NOT EXISTS idx_status (status)",
        "ADD INDEX IF NOT EXISTS idx_data_original (data_original)"
    ];
    
    foreach ($indices as $indice) {
        $sql = "ALTER TABLE agendamentos $indice";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Índice adicionado: $indice</p>";
    }
    
    // 4. Adicionar foreign key
    echo "<h2>4. Adicionando foreign key...</h2>";
    
    try {
        $sql = "ALTER TABLE agendamentos 
                ADD CONSTRAINT fk_agendamento_recorrencia 
                FOREIGN KEY (recorrencia_id) REFERENCES agendamentos_recorrentes(id) ON DELETE SET NULL";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ Foreign key adicionada com sucesso!</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ Foreign key já existe ou erro: " . $e->getMessage() . "</p>";
    }
    
    // 5. Criar tabela de logs
    echo "<h2>5. Criando tabela de logs...</h2>";
    
    $sql = "CREATE TABLE IF NOT EXISTS logs_agendamentos_recorrentes (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Tabela logs_agendamentos_recorrentes criada com sucesso!</p>";
    
    // 6. Verificar se tudo foi criado
    echo "<h2>6. Verificando criação...</h2>";
    
    $tabelas = ['agendamentos_recorrentes', 'logs_agendamentos_recorrentes'];
    
    foreach ($tabelas as $tabela) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Tabela $tabela existe</p>";
        } else {
            echo "<p style='color: red;'>❌ Tabela $tabela NÃO existe</p>";
        }
    }
    
    // Verificar colunas na tabela agendamentos
    $colunas_verificar = ['recorrencia_id', 'data_original', 'status', 'observacoes_edicao'];
    
    foreach ($colunas_verificar as $coluna) {
        $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE '$coluna'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✅ Coluna $coluna existe em agendamentos</p>";
        } else {
            echo "<p style='color: red;'>❌ Coluna $coluna NÃO existe em agendamentos</p>";
        }
    }
    
    echo "<h2 style='color: green;'>🎉 ESTRUTURA CRIADA COM SUCESSO!</h2>";
    echo "<p><a href='agendamentos-recorrentes.php' style='color: blue; text-decoration: underline;'>Clique aqui para acessar os Agendamentos Recorrentes</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}
?> 