<?php
/**
 * Diagnóstico Completo da Agenda - Sistema Bichos do Bairro
 * Verifica todos os aspectos do sistema de agendamentos
 */

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico da Agenda - Bichos do Bairro</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background: #f8f9fa; }
        h1, h2, h3 { color: #333; }
        .status { font-weight: bold; }
        .status.ok { color: #28a745; }
        .status.error { color: #dc3545; }
        .status.warning { color: #ffc107; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Diagnóstico Completo da Agenda</h1>
        <p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>
        <p><strong>Versão do PHP:</strong> " . PHP_VERSION . "</p>
        <p><strong>Diretório atual:</strong> " . __DIR__ . "</p>
        <hr>";

// 1. VERIFICAÇÃO DE ARQUIVOS ESSENCIAIS
echo "<div class='section'>
    <h2>📁 Verificação de Arquivos Essenciais</h2>";

$arquivos_essenciais = [
    __DIR__ . '/../src/init.php' => 'Arquivo de inicialização',
    __DIR__ . '/../src/db.php' => 'Conexão com banco de dados',
    __DIR__ . '/../src/Config.php' => 'Configurações do sistema',
    __DIR__ . '/../src/Agendamento.php' => 'Classe de agendamentos',
    __DIR__ . '/../src/Cliente.php' => 'Classe de clientes',
    __DIR__ . '/../src/Pet.php' => 'Classe de pets',
    __DIR__ . '/../config_agenda.json' => 'Configuração da agenda',
    __DIR__ . '/../.env' => 'Variáveis de ambiente'
];

foreach ($arquivos_essenciais as $arquivo => $descricao) {
    if (file_exists($arquivo)) {
        echo "<p class='status ok'>✅ $descricao - <strong>OK</strong></p>";
    } else {
        echo "<p class='status error'>❌ $descricao - <strong>NÃO ENCONTRADO</strong> ($arquivo)</p>";
    }
}

echo "</div>";

// 2. VERIFICAÇÃO DE CONFIGURAÇÕES
echo "<div class='section'>
    <h2>⚙️ Verificação de Configurações</h2>";

// Verificar config_agenda.json
$config_file = __DIR__ . '/../config_agenda.json';
if (file_exists($config_file)) {
    $config_agenda = json_decode(file_get_contents($config_file), true);
    if ($config_agenda) {
        echo "<p class='status ok'>✅ config_agenda.json - <strong>VÁLIDO</strong></p>";
        echo "<div class='code'>";
        echo "<strong>Configuração atual:</strong><br>";
        echo "Início: " . ($config_agenda['inicio'] ?? 'N/A') . "<br>";
        echo "Fim: " . ($config_agenda['fim'] ?? 'N/A') . "<br>";
        echo "Intervalo: " . ($config_agenda['intervalo'] ?? 'N/A') . " minutos<br>";
        echo "Dias abertos: " . implode(', ', $config_agenda['abertos'] ?? []) . "<br>";
        echo "</div>";
    } else {
        echo "<p class='status error'>❌ config_agenda.json - <strong>JSON INVÁLIDO</strong></p>";
    }
} else {
    echo "<p class='status error'>❌ config_agenda.json - <strong>NÃO ENCONTRADO</strong></p>";
}

echo "</div>";

// 3. VERIFICAÇÃO DE CONEXÃO COM BANCO
echo "<div class='section'>
    <h2>🗄️ Verificação de Conexão com Banco de Dados</h2>";

try {
    // Tentar carregar configurações
    $config_file = __DIR__ . '/../src/Config.php';
    if (file_exists($config_file)) {
        require_once $config_file;
        Config::load();
        
        $dbConfig = Config::getDbConfig();
        echo "<p class='status ok'>✅ Configurações carregadas</p>";
        echo "<div class='code'>";
        echo "<strong>Configuração do banco:</strong><br>";
        echo "Host: " . $dbConfig['host'] . "<br>";
        echo "Banco: " . $dbConfig['name'] . "<br>";
        echo "Usuário: " . $dbConfig['user'] . "<br>";
        echo "Charset: " . $dbConfig['charset'] . "<br>";
        echo "</div>";
        
        // Tentar conexão
        $db_file = __DIR__ . '/../src/db.php';
        if (file_exists($db_file)) {
            require_once $db_file;
            
            // Verificar se $pdo foi criado
            if (isset($pdo) && $pdo instanceof PDO) {
                echo "<p class='status ok'>✅ Conexão PDO estabelecida</p>";
                
                // Testar query simples
                try {
                    $stmt = $pdo->query('SELECT 1 as test');
                    $result = $stmt->fetch();
                    if ($result) {
                        echo "<p class='status ok'>✅ Query de teste executada com sucesso</p>";
                    }
                } catch (Exception $e) {
                    echo "<p class='status error'>❌ Erro na query de teste: " . $e->getMessage() . "</p>";
                }
            } else {
                echo "<p class='status error'>❌ Variável \$pdo não encontrada ou inválida</p>";
            }
        } else {
            echo "<p class='status error'>❌ Arquivo db.php não encontrado</p>";
        }
    } else {
        echo "<p class='status error'>❌ Arquivo Config.php não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p class='status error'>❌ Erro ao carregar configurações: " . $e->getMessage() . "</p>";
}

echo "</div>";

// 4. VERIFICAÇÃO DE TABELAS
echo "<div class='section'>
    <h2>📊 Verificação de Tabelas do Banco</h2>";

if (isset($pdo) && $pdo instanceof PDO) {
    $tabelas_essenciais = ['clientes', 'pets', 'agendamentos'];
    
    foreach ($tabelas_essenciais as $tabela) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$tabela'");
            if ($stmt->rowCount() > 0) {
                echo "<p class='status ok'>✅ Tabela '$tabela' - <strong>EXISTE</strong></p>";
                
                // Contar registros
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
                $result = $stmt->fetch();
                echo "<p>📈 Registros em '$tabela': " . $result['total'] . "</p>";
            } else {
                echo "<p class='status error'>❌ Tabela '$tabela' - <strong>NÃO EXISTE</strong></p>";
            }
        } catch (Exception $e) {
            echo "<p class='status error'>❌ Erro ao verificar tabela '$tabela': " . $e->getMessage() . "</p>";
        }
    }
} else {
    echo "<p class='status error'>❌ Conexão com banco não disponível</p>";
}

echo "</div>";

// 5. VERIFICAÇÃO DE CLASSES
echo "<div class='section'>
    <h2>🔧 Verificação de Classes</h2>";

$classes_essenciais = ['Agendamento', 'Cliente', 'Pet'];

foreach ($classes_essenciais as $classe) {
    if (class_exists($classe)) {
        echo "<p class='status ok'>✅ Classe '$classe' - <strong>CARREGADA</strong></p>";
        
        // Verificar métodos essenciais
        $metodos_essenciais = [
            'Agendamento' => ['listarTodos', 'criar', 'atualizar', 'deletar'],
            'Cliente' => ['listarTodos', 'buscarPorNome'],
            'Pet' => ['listarTodos', 'buscarPorCliente']
        ];
        
        if (isset($metodos_essenciais[$classe])) {
            foreach ($metodos_essenciais[$classe] as $metodo) {
                if (method_exists($classe, $metodo)) {
                    echo "<p class='status ok'>  ✅ Método '$metodo' - <strong>EXISTE</strong></p>";
                } else {
                    echo "<p class='status warning'>  ⚠️ Método '$metodo' - <strong>NÃO ENCONTRADO</strong></p>";
                }
            }
        }
    } else {
        echo "<p class='status error'>❌ Classe '$classe' - <strong>NÃO CARREGADA</strong></p>";
    }
}

echo "</div>";

// 6. VERIFICAÇÃO DE AGENDAMENTOS

echo "<div class='section'>
    <h2>📅 Verificação de Agendamentos</h2>";

// Carregar dependências antes das classes
if (file_exists(__DIR__ . '/../src/Utils.php')) {
    require_once __DIR__ . '/../src/Utils.php';
}
if (file_exists(__DIR__ . '/../src/BaseModel.php')) {
    require_once __DIR__ . '/../src/BaseModel.php';
}
if (file_exists(__DIR__ . '/../src/init.php')) {
    require_once __DIR__ . '/../src/init.php';
}
if (file_exists(__DIR__ . '/../src/Agendamento.php')) {
    require_once __DIR__ . '/../src/Agendamento.php';
}
if (file_exists(__DIR__ . '/../src/Cliente.php')) {
    require_once __DIR__ . '/../src/Cliente.php';
}
if (file_exists(__DIR__ . '/../src/Pet.php')) {
    require_once __DIR__ . '/../src/Pet.php';
}

if (class_exists('Agendamento') && isset($pdo)) {
    try {
        // Listar agendamentos
        $agendamentos = Agendamento::listarTodos();
        echo "<p class='status ok'>✅ Agendamentos carregados: " . count($agendamentos) . " registros</p>";
        
        // Resumo por status
        $status_resumo = [];
        foreach ($agendamentos as $a) {
            $status = $a['status'] ?? 'Indefinido';
            if (!isset($status_resumo[$status])) $status_resumo[$status] = 0;
            $status_resumo[$status]++;
        }
        echo "<h3>Resumo por Status:</h3><ul>";
        foreach ($status_resumo as $status => $qtd) {
            echo "<li><strong>$status:</strong> $qtd</li>";
        }
        echo "</ul>";

        // Checagem de conflitos de horário
        $conflitos = [];
        $ag_por_data_hora = [];
        foreach ($agendamentos as $a) {
            $chave = $a['data'] . ' ' . substr($a['hora'],0,5);
            if (!isset($ag_por_data_hora[$chave])) $ag_por_data_hora[$chave] = [];
            $ag_por_data_hora[$chave][] = $a;
        }
        foreach ($ag_por_data_hora as $chave => $lista) {
            if (count($lista) > 1) {
                $conflitos[] = $lista;
            }
        }
        if (count($conflitos) > 0) {
            echo "<h3 class='status error'>⚠️ Conflitos de Horário Encontrados:</h3>";
            foreach ($conflitos as $conf) {
                echo "<div class='code'>";
                foreach ($conf as $a) {
                    echo "ID: {$a['id']} | Data: {$a['data']} {$a['hora']} | Cliente: {$a['cliente_nome']} | Pet: {$a['pet_nome']} | Serviço: {$a['servico']} | Status: {$a['status']}<br>";
                }
                echo "</div>";
            }
        } else {
            echo "<p class='status ok'>✅ Nenhum conflito de horário encontrado</p>";
        }

        // Checagem de horários fora do expediente
        $config_file = __DIR__ . '/../config_agenda.json';
        $config_agenda = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];
        $inicio = $config_agenda['inicio'] ?? '08:00';
        $fim = $config_agenda['fim'] ?? '18:00';
        $fora_expediente = [];
        foreach ($agendamentos as $a) {
            if ($a['hora'] < $inicio || $a['hora'] >= $fim) {
                $fora_expediente[] = $a;
            }
        }
        if (count($fora_expediente) > 0) {
            echo "<h3 class='status warning'>⚠️ Agendamentos fora do expediente:</h3>";
            echo "<div class='code'>";
            foreach ($fora_expediente as $a) {
                echo "ID: {$a['id']} | Data: {$a['data']} {$a['hora']} | Cliente: {$a['cliente_nome']} | Pet: {$a['pet_nome']} | Serviço: {$a['servico']} | Status: {$a['status']}<br>";
            }
            echo "</div>";
        } else {
            echo "<p class='status ok'>✅ Nenhum agendamento fora do expediente</p>";
        }

        // Listagem de agendamentos futuros e passados
        $hoje = date('Y-m-d');
        $futuros = array_filter($agendamentos, function($a) use ($hoje) { return $a['data'] >= $hoje; });
        $passados = array_filter($agendamentos, function($a) use ($hoje) { return $a['data'] < $hoje; });
        echo "<h3>Agendamentos Futuros: ".count($futuros)."</h3>";
        if (count($futuros) > 0) {
            echo "<ul>";
            foreach (array_slice($futuros, 0, 5) as $a) {
                echo "<li>{$a['data']} {$a['hora']} - {$a['cliente_nome']} ({$a['pet_nome']}) - {$a['servico']} - <strong>{$a['status']}</strong></li>";
            }
            if (count($futuros) > 5) echo "<li>... e mais ".(count($futuros)-5)." futuros</li>";
            echo "</ul>";
        }
        echo "<h3>Agendamentos Passados: ".count($passados)."</h3>";
        if (count($passados) > 0) {
            echo "<ul>";
            foreach (array_slice($passados, -5) as $a) {
                echo "<li>{$a['data']} {$a['hora']} - {$a['cliente_nome']} ({$a['pet_nome']}) - {$a['servico']} - <strong>{$a['status']}</strong></li>";
            }
            if (count($passados) > 5) echo "<li>... e mais ".(count($passados)-5)." passados</li>";
            echo "</ul>";
        }

        // Checagem de horários disponíveis para os próximos 3 dias
        echo "<h3>Horários Disponíveis nos Próximos 3 Dias:</h3>";
        for ($i=0; $i<3; $i++) {
            $data = date('Y-m-d', strtotime("+$i day"));
            $dia_semana = date('w', strtotime($data));
            $aberto = isset($config_agenda['abertos'][$dia_semana]) ? $config_agenda['abertos'][$dia_semana] : 1;
            if (!$aberto) {
                echo "<p><strong>$data:</strong> Fechado</p>";
                continue;
            }
            $intervalo = isset($config_agenda['intervalos'][$dia_semana]) ? $config_agenda['intervalos'][$dia_semana] : ($config_agenda['intervalo'] ?? 20);
            $horaAtual = $inicio;
            $horarios = [];
            while ($horaAtual < $fim) {
                $horarios[] = $horaAtual;
                list($h, $m) = explode(':', $horaAtual);
                $m += $intervalo;
                while ($m >= 60) { $h++; $m -= 60; }
                $horaAtual = (strlen($h)<2?'0':'').$h . ':' . (strlen($m)<2?'0':'').$m;
            }
            $ocupados = array_map(function($a) use ($data) { return $a['hora']; }, array_filter($agendamentos, function($a) use ($data) { return $a['data'] == $data; }));
            $livres = array_diff($horarios, $ocupados);
            echo "<p><strong>$data:</strong> Livres: ".implode(', ', $livres)." | Ocupados: ".implode(', ', $ocupados)."</p>";
        }

    } catch (Exception $e) {
        echo "<p class='status error'>❌ Erro ao carregar agendamentos: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p class='status error'>❌ Classe Agendamento ou conexão não disponível</p>";
}

echo "</div>";

// 7. VERIFICAÇÃO DE LOGS
echo "<div class='section'>
    <h2>📝 Verificação de Logs</h2>";

$log_dir = __DIR__ . '/../logs';
if (is_dir($log_dir)) {
    echo "<p class='status ok'>✅ Diretório de logs existe</p>";
    
    $logs = scandir($log_dir);
    foreach ($logs as $log) {
        if ($log !== '.' && $log !== '..') {
            $size = filesize($log_dir . '/' . $log);
            $modified = date('d/m/Y H:i:s', filemtime($log_dir . '/' . $log));
            echo "<p>📄 $log - " . number_format($size) . " bytes - Modificado: $modified</p>";
        }
    }
} else {
    echo "<p class='status warning'>⚠️ Diretório de logs não encontrado</p>";
}

echo "</div>";

// 8. VERIFICAÇÃO DE PERMISSÕES
echo "<div class='section'>
    <h2>🔐 Verificação de Permissões</h2>";

$diretorios = [__DIR__ . '/../logs', __DIR__ . '/../src', __DIR__];
foreach ($diretorios as $dir) {
    if (is_dir($dir)) {
        if (is_readable($dir)) {
            echo "<p class='status ok'>✅ $dir - <strong>LEITURA OK</strong></p>";
        } else {
            echo "<p class='status error'>❌ $dir - <strong>SEM PERMISSÃO DE LEITURA</strong></p>";
        }
        
        if (is_writable($dir)) {
            echo "<p class='status ok'>✅ $dir - <strong>ESCRITA OK</strong></p>";
        } else {
            echo "<p class='status warning'>⚠️ $dir - <strong>SEM PERMISSÃO DE ESCRITA</strong></p>";
        }
    } else {
        echo "<p class='status error'>❌ $dir - <strong>NÃO EXISTE</strong></p>";
    }
}

echo "</div>";

// 9. RESUMO E RECOMENDAÇÕES
echo "<div class='section'>
    <h2>📋 Resumo e Recomendações</h2>";

echo "<h3>Status Geral:</h3>";

// Verificar problemas críticos
$problemas_criticos = [];
$problemas_menores = [];

if (!file_exists(__DIR__ . '/../src/init.php')) {
    $problemas_criticos[] = "Arquivo init.php não encontrado";
}

if (!file_exists(__DIR__ . '/../src/db.php')) {
    $problemas_criticos[] = "Arquivo db.php não encontrado";
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    $problemas_criticos[] = "Conexão com banco de dados não estabelecida";
}

if (!class_exists('Agendamento')) {
    $problemas_criticos[] = "Classe Agendamento não carregada";
}

if (!file_exists(__DIR__ . '/../config_agenda.json')) {
    $problemas_menores[] = "Arquivo config_agenda.json não encontrado";
}

if (empty($problemas_criticos) && empty($problemas_menores)) {
    echo "<p class='status ok'>✅ <strong>SISTEMA FUNCIONANDO CORRETAMENTE</strong></p>";
    echo "<p>Todos os componentes essenciais estão operacionais.</p>";
} else {
    if (!empty($problemas_criticos)) {
        echo "<p class='status error'>❌ <strong>PROBLEMAS CRÍTICOS ENCONTRADOS:</strong></p>";
        echo "<ul>";
        foreach ($problemas_criticos as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
    }
    
    if (!empty($problemas_menores)) {
        echo "<p class='status warning'>⚠️ <strong>PROBLEMAS MENORES:</strong></p>";
        echo "<ul>";
        foreach ($problemas_menores as $problema) {
            echo "<li>$problema</li>";
        }
        echo "</ul>";
    }
}

echo "<h3>Recomendações:</h3>";
echo "<ul>";
echo "<li>Verifique se o arquivo .env existe e está configurado corretamente</li>";
echo "<li>Certifique-se de que o banco de dados está acessível</li>";
echo "<li>Verifique as permissões dos diretórios logs/ e public/</li>";
echo "<li>Monitore os logs de erro para identificar problemas</li>";
echo "</ul>";

echo "</div>";

echo "<hr>
<p><strong>Diagnóstico concluído em:</strong> " . date('d/m/Y H:i:s') . "</p>
<p><a href='agendamentos.php'>← Voltar para a Agenda</a></p>
</div>
</body>
</html>";
?> 