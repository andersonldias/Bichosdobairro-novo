<?php
/**
 * Script para verificar o status do repositório GitHub
 */

echo "<h1>Verificação do Repositório GitHub</h1>\n";

$repo_url = "https://github.com/andersonldias/bichosdobairrophp";
$api_url = "https://api.github.com/repos/andersonldias/bichosdobairrophp";

echo "<h2>Informações do Repositório</h2>\n";
echo "<p><strong>URL:</strong> <a href='{$repo_url}' target='_blank'>{$repo_url}</a></p>\n";

// Verificar se o repositório existe
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: PHP Script',
            'Accept: application/vnd.github.v3+json'
        ]
    ]
]);

echo "<h2>Verificando API do GitHub...</h2>\n";

$response = @file_get_contents($api_url, false, $context);
if ($response === false) {
    echo "<p style='color: red;'>❌ Erro ao acessar a API do GitHub</p>\n";
    echo "<p>Possíveis causas:</p>\n";
    echo "<ul>\n";
    echo "<li>Repositório privado</li>\n";
    echo "<li>Repositório não existe</li>\n";
    echo "<li>Problema de conectividade</li>\n";
    echo "</ul>\n";
} else {
    $data = json_decode($response, true);
    
    if ($data) {
        echo "<p style='color: green;'>✅ Repositório encontrado!</p>\n";
        echo "<h3>Detalhes do Repositório:</h3>\n";
        echo "<ul>\n";
        echo "<li><strong>Nome:</strong> " . htmlspecialchars($data['name']) . "</li>\n";
        echo "<li><strong>Descrição:</strong> " . htmlspecialchars($data['description'] ?? 'N/A') . "</li>\n";
        echo "<li><strong>Linguagem:</strong> " . htmlspecialchars($data['language'] ?? 'N/A') . "</li>\n";
        echo "<li><strong>Stars:</strong> " . $data['stargazers_count'] . "</li>\n";
        echo "<li><strong>Forks:</strong> " . $data['forks_count'] . "</li>\n";
        echo "<li><strong>Última atualização:</strong> " . $data['updated_at'] . "</li>\n";
        echo "<li><strong>Visibilidade:</strong> " . ($data['private'] ? 'Privado' : 'Público') . "</li>\n";
        echo "</ul>\n";
        
        // Verificar commits recentes
        $commits_url = $api_url . "/commits";
        $commits_response = @file_get_contents($commits_url, false, $context);
        
        if ($commits_response) {
            $commits = json_decode($commits_response, true);
            if ($commits && count($commits) > 0) {
                echo "<h3>Commits Recentes:</h3>\n";
                echo "<ul>\n";
                foreach (array_slice($commits, 0, 5) as $commit) {
                    $sha = substr($commit['sha'], 0, 7);
                    $message = htmlspecialchars($commit['commit']['message']);
                    $date = $commit['commit']['author']['date'];
                    echo "<li><strong>{$sha}</strong> - {$message} <em>({$date})</em></li>\n";
                }
                echo "</ul>\n";
            }
        }
    } else {
        echo "<p style='color: red;'>❌ Erro ao decodificar resposta da API</p>\n";
    }
}

echo "<h2>Verificação Manual</h2>\n";
echo "<p>Para verificar manualmente, acesse:</p>\n";
echo "<ul>\n";
echo "<li><a href='{$repo_url}' target='_blank'>Repositório no GitHub</a></li>\n";
echo "<li><a href='{$repo_url}/commits' target='_blank'>Histórico de Commits</a></li>\n";
echo "<li><a href='{$repo_url}/tree/main' target='_blank'>Arquivos do Projeto</a></li>\n";
echo "</ul>\n";

echo "<h2>Status do Commit</h2>\n";
echo "<p>Se você vê as seguintes mudanças no GitHub, o commit foi realizado com sucesso:</p>\n";
echo "<ul>\n";
echo "<li>✅ Arquivo <code>sql/update_clientes_email_opcional.sql</code></li>\n";
echo "<li>✅ Arquivo <code>public/aplicar-mudancas-clientes.php</code></li>\n";
echo "<li>✅ Arquivo <code>MUDANCAS_CADASTRO_CLIENTES.md</code></li>\n";
echo "<li>✅ Arquivo <code>MELHORIAS_UX_FORMULARIOS.md</code></li>\n";
echo "<li>✅ Modificações em <code>src/Cliente.php</code></li>\n";
echo "<li>✅ Modificações em <code>public/validar-campo.php</code></li>\n";
echo "<li>✅ Modificações nos formulários de clientes</li>\n";
echo "</ul>\n";

echo "<h2>Próximos Passos</h2>\n";
echo "<p>Se o commit foi realizado com sucesso:</p>\n";
echo "<ol>\n";
echo "<li>✅ Verifique as mudanças no GitHub</li>\n";
echo "<li>✅ Teste os formulários de clientes</li>\n";
echo "<li>✅ Confirme que o e-mail é opcional</li>\n";
echo "<li>✅ Confirme que o telefone é obrigatório</li>\n";
echo "<li>✅ Teste o autofoco nos formulários</li>\n";
echo "<li>✅ Teste a navegação com Enter</li>\n";
echo "</ol>\n";

echo "<hr>\n";
echo "<p><em>Script executado em: " . date('Y-m-d H:i:s') . "</em></p>\n";
?>



