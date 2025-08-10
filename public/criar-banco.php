<?php
echo '<h2>Criando Banco de Dados SQLite</h2>';

$db_path = __DIR__ . '/../database.sqlite';

try {
    // Criar conexão SQLite
    $pdo = new PDO("sqlite:$db_path");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '<p style="color:green;">✅ Banco SQLite criado com sucesso!</p>';
    echo '<p>Caminho: ' . $db_path . '</p>';
    
    // Criar tabelas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            email TEXT NOT NULL,
            telefone TEXT,
            endereco TEXT,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT NOT NULL,
            especie TEXT,
            raca TEXT,
            idade INTEGER,
            cliente_id INTEGER NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS agendamentos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pet_id INTEGER NOT NULL,
            data DATETIME NOT NULL,
            servico TEXT NOT NULL,
            observacoes TEXT,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (pet_id) REFERENCES pets(id)
        )
    ");
    
    echo '<p style="color:green;">✅ Tabelas criadas com sucesso!</p>';
    echo '<p><a href="index.php">Ir para o Dashboard</a></p>';
    
} catch (PDOException $e) {
    echo '<p style="color:red;">❌ Erro: ' . $e->getMessage() . '</p>';
    echo '<p>Verifique se o diretório tem permissões de escrita.</p>';
} 