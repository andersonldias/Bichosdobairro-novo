<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Verificação dos Dados da Tabela Cliente</h1>";

try {
    // Configurações padrão do banco
    $host = 'xmysql.bichosdobairro.com.br';
    $db   = 'bichosdobairro5';
    $user = 'bichosdobairro5';
    $pass = '!BdoB.1179!';
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green;'>✅ Conectado ao banco de dados com sucesso!</p>";
    
    // Verificar se a tabela existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'clientes'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color:red;'>❌ Tabela 'clientes' não encontrada!</p>";
        exit;
    }
    
    echo "<p style='color:green;'>✅ Tabela 'clientes' encontrada!</p>";
    
    // Consultar estrutura da tabela
    echo "<h2>Estrutura da Tabela</h2>";
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th>";
    echo "</tr>";
    
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($coluna['Field'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Type'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Null'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Key'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($coluna['Extra'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Consultar dados
    echo "<h2>Dados Cadastrados</h2>";
    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($clientes)) {
        echo "<p style='color:orange;'>⚠️ Nenhum cliente cadastrado na tabela.</p>";
    } else {
        echo "<p style='color:green;'>✅ Encontrados " . count($clientes) . " cliente(s) cadastrado(s).</p>";
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f0f0f0;'>";
        echo "<th>ID</th>";
        echo "<th>Nome</th>";
        echo "<th>E-mail</th>";
        echo "<th>CPF</th>";
        echo "<th>Telefone</th>";
        echo "<th>Endereço</th>";
        echo "<th>Criado em</th>";
        echo "</tr>";
        
        foreach ($clientes as $cliente) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($cliente['id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['nome'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['email'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['cpf'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['telefone'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['endereco'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($cliente['criado_em'] ?? '') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Estatísticas
        echo "<h2>Estatísticas</h2>";
        echo "<ul>";
        echo "<li>Total de clientes: " . count($clientes) . "</li>";
        
        $comTelefone = array_filter($clientes, function($c) { return !empty($c['telefone']); });
        echo "<li>Com telefone: " . count($comTelefone) . "</li>";
        
        $comEndereco = array_filter($clientes, function($c) { return !empty($c['endereco']); });
        echo "<li>Com endereço: " . count($comEndereco) . "</li>";
        
        $comCPF = array_filter($clientes, function($c) { return !empty($c['cpf']); });
        echo "<li>Com CPF: " . count($comCPF) . "</li>";
        echo "</ul>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>❌ Erro ao conectar com o banco de dados: " . $e->getMessage() . "</p>";
    echo "<p>Verifique se:</p>";
    echo "<ul>";
    echo "<li>O servidor MySQL está rodando</li>";
    echo "<li>As credenciais estão corretas</li>";
    echo "<li>A conexão com o banco está disponível</li>";
    echo "</ul>";
}
?> 