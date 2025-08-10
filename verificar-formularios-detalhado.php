<?php
/**
 * Verificação Detalhada de Formulários e Validações
 * Sistema Bichos do Bairro
 * 
 * Este script faz uma análise profunda dos formulários para identificar:
 * - Problemas de validação
 * - Falhas de segurança
 * - Bugs de interface
 * - Problemas de usabilidade
 */

// Configurar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Verificação Detalhada de Formulários - Sistema Bichos do Bairro</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .error { color: #dc2626; background: #fef2f2; padding: 10px; margin: 5px 0; border-left: 4px solid #dc2626; }
    .warning { color: #d97706; background: #fffbeb; padding: 10px; margin: 5px 0; border-left: 4px solid #d97706; }
    .success { color: #059669; background: #f0fdf4; padding: 10px; margin: 5px 0; border-left: 4px solid #059669; }
    .info { color: #2563eb; background: #eff6ff; padding: 10px; margin: 5px 0; border-left: 4px solid #2563eb; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #e5e7eb; border-radius: 8px; }
    pre { background: #f3f4f6; padding: 10px; border-radius: 4px; overflow-x: auto; }
    .code { background: #f3f4f6; padding: 5px; border-radius: 3px; font-family: monospace; }
</style>";

$problemas = [];
$melhorias = [];
$sucessos = [];

// 1. Verificar formulário de login
echo "<div class='section'>";
echo "<h2>🔐 Verificação do Formulário de Login</h2>";

$login_file = 'public/login.php';
if (file_exists($login_file)) {
    $conteudo = file_get_contents($login_file);
    
    // Verificar proteção contra força bruta
    if (str_contains($conteudo, 'tentativas_login')) {
        $sucessos[] = "Proteção contra força bruta implementada";
        echo "<div class='success'>✅ Proteção contra força bruta implementada</div>";
    } else {
        $problemas[] = "Falta proteção contra força bruta no login";
        echo "<div class='error'>❌ Falta proteção contra força bruta no login</div>";
    }
    
    // Verificar validação de email
    if (str_contains($conteudo, 'filter_var') && str_contains($conteudo, 'FILTER_VALIDATE_EMAIL')) {
        $sucessos[] = "Validação de email implementada";
        echo "<div class='success'>✅ Validação de email implementada</div>";
    } else {
        $problemas[] = "Falta validação de email no login";
        echo "<div class='error'>❌ Falta validação de email no login</div>";
    }
    
    // Verificar sanitização de entrada
    if (str_contains($conteudo, 'sanitize') || str_contains($conteudo, 'htmlspecialchars')) {
        $sucessos[] = "Sanitização de entrada implementada";
        echo "<div class='success'>✅ Sanitização de entrada implementada</div>";
    } else {
        $problemas[] = "Falta sanitização de entrada no login";
        echo "<div class='error'>❌ Falta sanitização de entrada no login</div>";
    }
    
    // Verificar autocomplete
    if (str_contains($conteudo, 'autocomplete')) {
        $sucessos[] = "Autocomplete configurado";
        echo "<div class='success'>✅ Autocomplete configurado</div>";
    } else {
        $melhorias[] = "Adicionar autocomplete nos campos de login";
        echo "<div class='warning'>⚠️ Adicionar autocomplete nos campos de login</div>";
    }
}
echo "</div>";

// 2. Verificar formulário de clientes
echo "<div class='section'>";
echo "<h2>👥 Verificação do Formulário de Clientes</h2>";

$clientes_file = 'public/clientes.php';
if (file_exists($clientes_file)) {
    $conteudo = file_get_contents($clientes_file);
    
    // Verificar validação de CPF
    if (str_contains($conteudo, 'validarCPF')) {
        $sucessos[] = "Validação de CPF implementada";
        echo "<div class='success'>✅ Validação de CPF implementada</div>";
    } else {
        $problemas[] = "Falta validação de CPF";
        echo "<div class='error'>❌ Falta validação de CPF</div>";
    }
    
    // Verificar validação de telefone
    if (str_contains($conteudo, 'telefone') && str_contains($conteudo, 'empty')) {
        $sucessos[] = "Validação de telefone implementada";
        echo "<div class='success'>✅ Validação de telefone implementada</div>";
    } else {
        $problemas[] = "Falta validação de telefone";
        echo "<div class='error'>❌ Falta validação de telefone</div>";
    }
    
    // Verificar validação de email
    if (str_contains($conteudo, 'email') && (str_contains($conteudo, 'filter_var') || str_contains($conteudo, 'validateEmail'))) {
        $sucessos[] = "Validação de email implementada";
        echo "<div class='success'>✅ Validação de email implementada</div>";
    } else {
        $problemas[] = "Falta validação de email";
        echo "<div class='error'>❌ Falta validação de email</div>";
    }
    
    // Verificar wizard de cadastro
    if (str_contains($conteudo, 'wizardForm')) {
        $sucessos[] = "Wizard de cadastro implementado";
        echo "<div class='success'>✅ Wizard de cadastro implementado</div>";
    } else {
        $melhorias[] = "Considerar implementar wizard para cadastro de clientes";
        echo "<div class='warning'>⚠️ Considerar implementar wizard para cadastro de clientes</div>";
    }
}
echo "</div>";

// 3. Verificar formulário de agendamentos
echo "<div class='section'>";
echo "<h2>📅 Verificação do Formulário de Agendamentos</h2>";

$agendamentos_file = 'public/agendamentos.php';
if (file_exists($agendamentos_file)) {
    $conteudo = file_get_contents($agendamentos_file);
    
    // Verificar validação de data
    if (str_contains($conteudo, 'data') && (str_contains($conteudo, 'strtotime') || str_contains($conteudo, 'date'))) {
        $sucessos[] = "Validação de data implementada";
        echo "<div class='success'>✅ Validação de data implementada</div>";
    } else {
        $problemas[] = "Falta validação de data";
        echo "<div class='error'>❌ Falta validação de data</div>";
    }
    
    // Verificar validação de horário
    if (str_contains($conteudo, 'hora') && str_contains($conteudo, 'time')) {
        $sucessos[] = "Validação de horário implementada";
        echo "<div class='success'>✅ Validação de horário implementada</div>";
    } else {
        $problemas[] = "Falta validação de horário";
        echo "<div class='error'>❌ Falta validação de horário</div>";
    }
    
    // Verificar conflitos de horário
    if (str_contains($conteudo, 'conflito') || str_contains($conteudo, 'disponivel')) {
        $sucessos[] = "Verificação de conflitos implementada";
        echo "<div class='success'>✅ Verificação de conflitos implementada</div>";
    } else {
        $melhorias[] = "Considerar implementar verificação de conflitos de horário";
        echo "<div class='warning'>⚠️ Considerar implementar verificação de conflitos de horário</div>";
    }
    
    // Verificar seleção de cliente
    if (str_contains($conteudo, 'cliente_id') && str_contains($conteudo, 'required')) {
        $sucessos[] = "Seleção de cliente obrigatória";
        echo "<div class='success'>✅ Seleção de cliente obrigatória</div>";
    } else {
        $problemas[] = "Falta tornar seleção de cliente obrigatória";
        echo "<div class='error'>❌ Falta tornar seleção de cliente obrigatória</div>";
    }
}
echo "</div>";

// 4. Verificar formulário de pets
echo "<div class='section'>";
echo "<h2>🐕 Verificação do Formulário de Pets</h2>";

$pets_file = 'public/pets.php';
if (file_exists($pets_file)) {
    $conteudo = file_get_contents($pets_file);
    
    // Verificar validação de nome
    if (str_contains($conteudo, 'nome') && str_contains($conteudo, 'empty')) {
        $sucessos[] = "Validação de nome do pet implementada";
        echo "<div class='success'>✅ Validação de nome do pet implementada</div>";
    } else {
        $problemas[] = "Falta validação de nome do pet";
        echo "<div class='error'>❌ Falta validação de nome do pet</div>";
    }
    
    // Verificar seleção de espécie
    if (str_contains($conteudo, 'especie') && str_contains($conteudo, 'required')) {
        $sucessos[] = "Seleção de espécie obrigatória";
        echo "<div class='success'>✅ Seleção de espécie obrigatória</div>";
    } else {
        $problemas[] = "Falta tornar seleção de espécie obrigatória";
        echo "<div class='error'>❌ Falta tornar seleção de espécie obrigatória</div>";
    }
    
    // Verificar seleção de cliente
    if (str_contains($conteudo, 'cliente_id') && str_contains($conteudo, 'required')) {
        $sucessos[] = "Seleção de cliente obrigatória";
        echo "<div class='success'>✅ Seleção de cliente obrigatória</div>";
    } else {
        $problemas[] = "Falta tornar seleção de cliente obrigatória";
        echo "<div class='error'>❌ Falta tornar seleção de cliente obrigatória</div>";
    }
}
echo "</div>";

// 5. Verificar problemas gerais de formulários
echo "<div class='section'>";
echo "<h2>🔧 Verificação Geral de Formulários</h2>";

// Verificar arquivos com formulários
$arquivos_formularios = [
    'public/clientes.php',
    'public/agendamentos.php',
    'public/pets.php',
    'public/login.php',
    'public/agendamentos-recorrentes-form.php',
    'public/configuracoes.php'
];

foreach ($arquivos_formularios as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar CSRF
        if (str_contains($conteudo, 'csrf') || str_contains($conteudo, 'token')) {
            $sucessos[] = "Proteção CSRF em $arquivo";
            echo "<div class='success'>✅ Proteção CSRF em $arquivo</div>";
        } else {
            $problemas[] = "Falta proteção CSRF em $arquivo";
            echo "<div class='error'>❌ Falta proteção CSRF em $arquivo</div>";
        }
        
        // Verificar validação de entrada
        if (str_contains($conteudo, 'empty(') || str_contains($conteudo, 'isset(') || str_contains($conteudo, 'filter_var')) {
            $sucessos[] = "Validação de entrada em $arquivo";
            echo "<div class='success'>✅ Validação de entrada em $arquivo</div>";
        } else {
            $problemas[] = "Falta validação de entrada em $arquivo";
            echo "<div class='error'>❌ Falta validação de entrada em $arquivo</div>";
        }
        
        // Verificar sanitização
        if (str_contains($conteudo, 'htmlspecialchars') || str_contains($conteudo, 'sanitize')) {
            $sucessos[] = "Sanitização em $arquivo";
            echo "<div class='success'>✅ Sanitização em $arquivo</div>";
        } else {
            $problemas[] = "Falta sanitização em $arquivo";
            echo "<div class='error'>❌ Falta sanitização em $arquivo</div>";
        }
    }
}
echo "</div>";

// 6. Verificar problemas de usabilidade
echo "<div class='section'>";
echo "<h2>🎨 Verificação de Usabilidade</h2>";

// Verificar feedback visual
$arquivos_interface = [
    'public/clientes.php',
    'public/agendamentos.php',
    'public/pets.php'
];

foreach ($arquivos_interface as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar mensagens de erro
        if (str_contains($conteudo, 'erro') || str_contains($conteudo, 'error')) {
            $sucessos[] = "Mensagens de erro em $arquivo";
            echo "<div class='success'>✅ Mensagens de erro em $arquivo</div>";
        } else {
            $melhorias[] = "Adicionar mensagens de erro em $arquivo";
            echo "<div class='warning'>⚠️ Adicionar mensagens de erro em $arquivo</div>";
        }
        
        // Verificar mensagens de sucesso
        if (str_contains($conteudo, 'sucesso') || str_contains($conteudo, 'success')) {
            $sucessos[] = "Mensagens de sucesso em $arquivo";
            echo "<div class='success'>✅ Mensagens de sucesso em $arquivo</div>";
        } else {
            $melhorias[] = "Adicionar mensagens de sucesso em $arquivo";
            echo "<div class='warning'>⚠️ Adicionar mensagens de sucesso em $arquivo</div>";
        }
        
        // Verificar loading states
        if (str_contains($conteudo, 'loading') || str_contains($conteudo, 'disabled')) {
            $sucessos[] = "Estados de loading em $arquivo";
            echo "<div class='success'>✅ Estados de loading em $arquivo</div>";
        } else {
            $melhorias[] = "Adicionar estados de loading em $arquivo";
            echo "<div class='warning'>⚠️ Adicionar estados de loading em $arquivo</div>";
        }
    }
}
echo "</div>";

// 7. Verificar problemas de acessibilidade
echo "<div class='section'>";
echo "<h2>♿ Verificação de Acessibilidade</h2>";

foreach ($arquivos_interface as $arquivo) {
    if (file_exists($arquivo)) {
        $conteudo = file_get_contents($arquivo);
        
        // Verificar labels
        if (str_contains($conteudo, '<label')) {
            $sucessos[] = "Labels em $arquivo";
            echo "<div class='success'>✅ Labels em $arquivo</div>";
        } else {
            $melhorias[] = "Adicionar labels em $arquivo";
            echo "<div class='warning'>⚠️ Adicionar labels em $arquivo</div>";
        }
        
        // Verificar placeholders
        if (str_contains($conteudo, 'placeholder')) {
            $sucessos[] = "Placeholders em $arquivo";
            echo "<div class='success'>✅ Placeholders em $arquivo</div>";
        } else {
            $melhorias[] = "Adicionar placeholders em $arquivo";
            echo "<div class='warning'>⚠️ Adicionar placeholders em $arquivo</div>";
        }
        
        // Verificar required
        if (str_contains($conteudo, 'required')) {
            $sucessos[] = "Campos obrigatórios marcados em $arquivo";
            echo "<div class='success'>✅ Campos obrigatórios marcados em $arquivo</div>";
        } else {
            $melhorias[] = "Marcar campos obrigatórios em $arquivo";
            echo "<div class='warning'>⚠️ Marcar campos obrigatórios em $arquivo</div>";
        }
    }
}
echo "</div>";

// 8. Resumo final
echo "<div class='section'>";
echo "<h2>📊 Resumo da Verificação de Formulários</h2>";

echo "<div class='info'>";
echo "<h3>Estatísticas:</h3>";
echo "<ul>";
echo "<li>Problemas críticos encontrados: " . count($problemas) . "</li>";
echo "<li>Melhorias sugeridas: " . count($melhorias) . "</li>";
echo "<li>Verificações bem-sucedidas: " . count($sucessos) . "</li>";
echo "</ul>";
echo "</div>";

if (!empty($problemas)) {
    echo "<div class='error'>";
    echo "<h3>❌ Problemas Críticos:</h3>";
    echo "<ul>";
    foreach ($problemas as $problema) {
        echo "<li>$problema</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($melhorias)) {
    echo "<div class='warning'>";
    echo "<h3>⚠️ Melhorias Sugeridas:</h3>";
    echo "<ul>";
    foreach ($melhorias as $melhoria) {
        echo "<li>$melhoria</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($sucessos)) {
    echo "<div class='success'>";
    echo "<h3>✅ Verificações Bem-sucedidas:</h3>";
    echo "<ul>";
    foreach ($sucessos as $sucesso) {
        echo "<li>$sucesso</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Recomendações específicas
echo "<div class='info'>";
echo "<h3>💡 Recomendações Específicas:</h3>";
echo "<ul>";
if (!empty($problemas)) {
    echo "<li>Priorize a correção dos problemas críticos</li>";
}
if (!empty($melhorias)) {
    echo "<li>Implemente as melhorias de usabilidade sugeridas</li>";
}
echo "<li>Teste todos os formulários em diferentes navegadores</li>";
echo "<li>Valide a acessibilidade com leitores de tela</li>";
echo "<li>Implemente testes automatizados para validações</li>";
echo "<li>Documente as regras de validação para cada formulário</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

echo "<p><strong>Verificação de formulários concluída em: " . date('d/m/Y H:i:s') . "</strong></p>";
?> 