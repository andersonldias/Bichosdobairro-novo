<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';

echo "<h1>Correção Completa do Banco de Dados</h1>";

try {
    // 1. Verificar se a coluna status existe na tabela agendamentos
    echo "<h2>1. Verificando coluna 'status' na tabela agendamentos...</h2>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'status'");
    $coluna_existe = $stmt->fetch();
    
    if (!$coluna_existe) {
        echo "<p>Coluna 'status' não encontrada. Adicionando...</p>";
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN status VARCHAR(30) DEFAULT 'Pendente' AFTER servico");
        echo "<p style='color: green;'>✓ Coluna 'status' adicionada com sucesso!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'status' já existe.</p>";
    }
    
    // 2. Verificar se as colunas created_at e updated_at existem
    echo "<h2>2. Verificando colunas de timestamp...</h2>";
    
    // Verificar created_at em agendamentos
    $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'created_at'");
    $created_at_existe = $stmt->fetch();
    
    if (!$created_at_existe) {
        echo "<p>Coluna 'created_at' não encontrada em agendamentos. Adicionando...</p>";
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'created_at' adicionada em agendamentos!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'created_at' já existe em agendamentos.</p>";
    }
    
    // Verificar updated_at em agendamentos
    $stmt = $pdo->query("SHOW COLUMNS FROM agendamentos LIKE 'updated_at'");
    $updated_at_existe = $stmt->fetch();
    
    if (!$updated_at_existe) {
        echo "<p>Coluna 'updated_at' não encontrada em agendamentos. Adicionando...</p>";
        $pdo->exec("ALTER TABLE agendamentos ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'updated_at' adicionada em agendamentos!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'updated_at' já existe em agendamentos.</p>";
    }
    
    // Verificar created_at em clientes
    $stmt = $pdo->query("SHOW COLUMNS FROM clientes LIKE 'created_at'");
    $created_at_clientes_existe = $stmt->fetch();
    
    if (!$created_at_clientes_existe) {
        echo "<p>Coluna 'created_at' não encontrada em clientes. Adicionando...</p>";
        $pdo->exec("ALTER TABLE clientes ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'created_at' adicionada em clientes!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'created_at' já existe em clientes.</p>";
    }
    
    // Verificar updated_at em clientes
    $stmt = $pdo->query("SHOW COLUMNS FROM clientes LIKE 'updated_at'");
    $updated_at_clientes_existe = $stmt->fetch();
    
    if (!$updated_at_clientes_existe) {
        echo "<p>Coluna 'updated_at' não encontrada em clientes. Adicionando...</p>";
        $pdo->exec("ALTER TABLE clientes ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'updated_at' adicionada em clientes!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'updated_at' já existe em clientes.</p>";
    }
    
    // Verificar created_at em pets
    $stmt = $pdo->query("SHOW COLUMNS FROM pets LIKE 'created_at'");
    $created_at_pets_existe = $stmt->fetch();
    
    if (!$created_at_pets_existe) {
        echo "<p>Coluna 'created_at' não encontrada em pets. Adicionando...</p>";
        $pdo->exec("ALTER TABLE pets ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'created_at' adicionada em pets!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'created_at' já existe em pets.</p>";
    }
    
    // Verificar updated_at em pets
    $stmt = $pdo->query("SHOW COLUMNS FROM pets LIKE 'updated_at'");
    $updated_at_pets_existe = $stmt->fetch();
    
    if (!$updated_at_pets_existe) {
        echo "<p>Coluna 'updated_at' não encontrada em pets. Adicionando...</p>";
        $pdo->exec("ALTER TABLE pets ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "<p style='color: green;'>✓ Coluna 'updated_at' adicionada em pets!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Coluna 'updated_at' já existe em pets.</p>";
    }
    
    // 3. Verificar estrutura da tabela pets
    echo "<h2>3. Verificando estrutura da tabela pets...</h2>";
    
    $stmt = $pdo->query("DESCRIBE pets");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Colunas da tabela pets:</p>";
    echo "<ul>";
    foreach ($colunas as $coluna) {
        echo "<li><strong>{$coluna['Field']}</strong> - {$coluna['Type']} - Null: {$coluna['Null']} - Default: {$coluna['Default']}</li>";
    }
    echo "</ul>";
    
    // 4. Testar inserção de pet com idade NULL
    echo "<h2>4. Testando inserção de pet com idade NULL...</h2>";
    
    try {
        $stmt = $pdo->prepare("INSERT INTO pets (nome, especie, raca, idade, cliente_id) VALUES (?, ?, ?, ?, ?)");
        $resultado = $stmt->execute(['Pet Teste', 'Canina', 'SRD', null, 1]);
        
        if ($resultado) {
            $pet_id = $pdo->lastInsertId();
            echo "<p style='color: green;'>✓ Pet inserido com sucesso (ID: $pet_id)</p>";
            
            // Remover o pet de teste
            $pdo->exec("DELETE FROM pets WHERE id = $pet_id");
            echo "<p>Pet de teste removido.</p>";
        } else {
            echo "<p style='color: red;'>✗ Erro ao inserir pet</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    }
    
    // 5. Verificar estrutura da tabela agendamentos
    echo "<h2>5. Verificando estrutura da tabela agendamentos...</h2>";
    
    $stmt = $pdo->query("DESCRIBE agendamentos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Colunas da tabela agendamentos:</p>";
    echo "<ul>";
    foreach ($colunas as $coluna) {
        echo "<li><strong>{$coluna['Field']}</strong> - {$coluna['Type']} - Null: {$coluna['Null']} - Default: {$coluna['Default']}</li>";
    }
    echo "</ul>";
    
    // 6. Verificar estrutura da tabela clientes
    echo "<h2>6. Verificando estrutura da tabela clientes...</h2>";
    
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Colunas da tabela clientes:</p>";
    echo "<ul>";
    foreach ($colunas as $coluna) {
        echo "<li><strong>{$coluna['Field']}</strong> - {$coluna['Type']} - Null: {$coluna['Null']} - Default: {$coluna['Default']}</li>";
    }
    echo "</ul>";
    
    // 7. Verificar se a tabela telefones existe
    echo "<h2>7. Verificando tabela telefones...</h2>";
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'telefones'");
    $tabela_telefones_existe = $stmt->fetch();
    
    if (!$tabela_telefones_existe) {
        echo "<p>Tabela 'telefones' não encontrada. Criando...</p>";
        $pdo->exec("CREATE TABLE telefones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            nome VARCHAR(50),
            numero VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        )");
        echo "<p style='color: green;'>✓ Tabela 'telefones' criada com sucesso!</p>";
    } else {
        echo "<p style='color: blue;'>✓ Tabela 'telefones' já existe.</p>";
    }
    
    echo "<h2 style='color: green;'>✓ Correções concluídas com sucesso!</h2>";
    echo "<p><a href='clientes.php'>Voltar para Clientes</a> | <a href='agendamentos.php'>Voltar para Agendamentos</a> | <a href='teste-sistema.php'>Testar Sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Erro durante as correções:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 