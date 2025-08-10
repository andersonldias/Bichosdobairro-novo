<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/db.php';

echo '<h1>Aplicando Mudanças na Tabela de Clientes</h1>';
echo '<p>E-mail será opcional e telefone será obrigatório</p>';

try {
    $pdo = getDb();
    
    echo '<h2>1. Verificando estrutura atual da tabela...</h2>';
    
    // Verificar estrutura atual
    $stmt = $pdo->query("DESCRIBE clientes");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
    echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
    
    foreach ($colunas as $coluna) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($coluna['Field']) . '</td>';
        echo '<td>' . htmlspecialchars($coluna['Type']) . '</td>';
        echo '<td>' . htmlspecialchars($coluna['Null']) . '</td>';
        echo '<td>' . htmlspecialchars($coluna['Key']) . '</td>';
        echo '<td>' . htmlspecialchars($coluna['Default']) . '</td>';
        echo '<td>' . htmlspecialchars($coluna['Extra']) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    
    echo '<h2>2. Verificando registros existentes...</h2>';
    
    // Verificar registros sem telefone
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE telefone IS NULL OR telefone = ''");
    $semTelefone = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo '<p>Registros sem telefone: <strong>' . $semTelefone . '</strong></p>';
    
    if ($semTelefone > 0) {
        echo '<p style="color: red;">⚠️ ATENÇÃO: Existem registros sem telefone. Eles precisarão ser atualizados antes de tornar o campo obrigatório.</p>';
        echo '<p><a href="?action=show_records_without_phone">Ver registros sem telefone</a></p>';
    }
    
    // Verificar registros com e-mail vazio
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes WHERE email = ''");
    $emailVazio = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo '<p>Registros com e-mail vazio: <strong>' . $emailVazio . '</strong></p>';
    
    // Aplicar mudanças se não houver registros sem telefone
    if ($semTelefone == 0) {
        echo '<h2>3. Aplicando mudanças...</h2>';
        
        // Modificar e-mail para permitir NULL
        echo '<p>Aplicando: E-mail opcional...</p>';
        $pdo->exec("ALTER TABLE clientes MODIFY COLUMN email varchar(100) NULL");
        echo '<p style="color: green;">✅ E-mail agora é opcional</p>';
        
        // Modificar telefone para ser NOT NULL
        echo '<p>Aplicando: Telefone obrigatório...</p>';
        $pdo->exec("ALTER TABLE clientes MODIFY COLUMN telefone varchar(20) NOT NULL");
        echo '<p style="color: green;">✅ Telefone agora é obrigatório</p>';
        
        // Converter e-mails vazios para NULL
        if ($emailVazio > 0) {
            echo '<p>Convertendo e-mails vazios para NULL...</p>';
            $pdo->exec("UPDATE clientes SET email = NULL WHERE email = ''");
            echo '<p style="color: green;">✅ E-mails vazios convertidos para NULL</p>';
        }
        
        // Adicionar índice para telefone
        echo '<p>Adicionando índice para telefone...</p>';
        try {
            $pdo->exec("ALTER TABLE clientes ADD INDEX idx_telefone (telefone)");
            echo '<p style="color: green;">✅ Índice adicionado para telefone</p>';
        } catch (Exception $e) {
            echo '<p style="color: orange;">⚠️ Índice já existe ou erro: ' . $e->getMessage() . '</p>';
        }
        
        echo '<h2>4. Verificando estrutura final...</h2>';
        
        // Verificar estrutura final
        $stmt = $pdo->query("DESCRIBE clientes");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
        echo '<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
        
        foreach ($colunas as $coluna) {
            $style = '';
            if ($coluna['Field'] == 'email' && $coluna['Null'] == 'YES') {
                $style = 'background-color: #d4edda;';
            }
            if ($coluna['Field'] == 'telefone' && $coluna['Null'] == 'NO') {
                $style = 'background-color: #d4edda;';
            }
            
            echo '<tr style="' . $style . '">';
            echo '<td>' . htmlspecialchars($coluna['Field']) . '</td>';
            echo '<td>' . htmlspecialchars($coluna['Type']) . '</td>';
            echo '<td>' . htmlspecialchars($coluna['Null']) . '</td>';
            echo '<td>' . htmlspecialchars($coluna['Key']) . '</td>';
            echo '<td>' . htmlspecialchars($coluna['Default']) . '</td>';
            echo '<td>' . htmlspecialchars($coluna['Extra']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<h2 style="color: green;">✅ Mudanças aplicadas com sucesso!</h2>';
        echo '<p>O sistema agora está configurado com:</p>';
        echo '<ul>';
        echo '<li>✅ E-mail: <strong>OPCIONAL</strong></li>';
        echo '<li>✅ Telefone: <strong>OBRIGATÓRIO</strong></li>';
        echo '</ul>';
        
    } else {
        echo '<h2 style="color: red;">❌ Não é possível aplicar as mudanças</h2>';
        echo '<p>Existem registros sem telefone que precisam ser atualizados primeiro.</p>';
        echo '<p><a href="?action=show_records_without_phone">Ver e atualizar registros sem telefone</a></p>';
    }
    
} catch (Exception $e) {
    echo '<h2 style="color: red;">❌ Erro ao aplicar mudanças</h2>';
    echo '<p>Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// Mostrar registros sem telefone se solicitado
if (isset($_GET['action']) && $_GET['action'] == 'show_records_without_phone') {
    echo '<h2>Registros sem telefone</h2>';
    
    $stmt = $pdo->query("SELECT id, nome, email, telefone FROM clientes WHERE telefone IS NULL OR telefone = ''");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($registros)) {
        echo '<p>Nenhum registro sem telefone encontrado.</p>';
    } else {
        echo '<table border="1" style="border-collapse: collapse; margin: 10px 0;">';
        echo '<tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Telefone</th></tr>';
        
        foreach ($registros as $registro) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($registro['id']) . '</td>';
            echo '<td>' . htmlspecialchars($registro['nome']) . '</td>';
            echo '<td>' . htmlspecialchars($registro['email'] ?? '-') . '</td>';
            echo '<td>' . htmlspecialchars($registro['telefone'] ?? 'VAZIO') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<p><strong>Para continuar, você precisa atualizar esses registros com um telefone válido.</strong></p>';
    }
}

echo '<hr>';
echo '<p><a href="dashboard.php">← Voltar ao Dashboard</a></p>';
echo '<p><a href="clientes.php">← Ir para Clientes</a></p>';
?>




