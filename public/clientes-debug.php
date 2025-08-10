<?php
/**
 * Versão de debug do clientes.php
 */

// Forçar exibição de erros e flush de buffer
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start();

echo "<!-- DEBUG: Iniciando clientes-debug.php -->\n";
flush();

try {
    echo "<!-- DEBUG: Carregando init.php -->\n";
    require_once '../src/init.php';
    echo "<!-- DEBUG: init.php carregado com sucesso -->\n";
    flush();
    
    // Verificar se é uma requisição AJAX
    echo "<!-- DEBUG: Verificando se é AJAX -->\n";
    if (isAjax()) {
        echo "<!-- DEBUG: É uma requisição AJAX -->\n";
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        echo "<!-- DEBUG: Action = $action -->\n";
        
        switch ($action) {
            case 'buscar':
                echo "<!-- DEBUG: Executando busca -->\n";
                $termo = $_GET['termo'] ?? '';
                echo "<!-- DEBUG: Termo = $termo -->\n";
                
                $clientes = Cliente::buscar($termo, ['limite' => 10]);
                echo "<!-- DEBUG: Busca executada, " . count($clientes) . " resultados -->\n";
                
                // Limpar buffer antes de enviar JSON
                ob_clean();
                jsonResponse($clientes);
                break;
                
            case 'criar':
                echo "<!-- DEBUG: Criando cliente -->\n";
                $dados = [
                    'nome' => sanitize($_POST['nome'] ?? ''),
                    'email' => sanitize($_POST['email'] ?? ''),
                    'telefone' => sanitize($_POST['telefone'] ?? ''),
                    'cpf' => sanitize($_POST['cpf'] ?? ''),
                    'endereco' => sanitize($_POST['endereco'] ?? ''),
                    'observacoes' => sanitize($_POST['observacoes'] ?? '')
                ];
                
                if (empty($dados['nome']) || empty($dados['telefone'])) {
                    ob_clean();
                    jsonResponse(['success' => false, 'message' => 'Nome e telefone são obrigatórios'], 400);
                }
                
                $id = Cliente::criar($dados);
                if ($id) {
                    ob_clean();
                    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Cliente criado com sucesso']);
                } else {
                    ob_clean();
                    jsonResponse(['success' => false, 'message' => 'Erro ao criar cliente'], 500);
                }
                break;
                
            default:
                echo "<!-- DEBUG: Action não reconhecida -->\n";
                ob_clean();
                jsonResponse(['success' => false, 'message' => 'Ação não reconhecida'], 400);
        }
        exit;
    }
    
    echo "<!-- DEBUG: Não é AJAX, carregando página normal -->\n";
    flush();
    
} catch (Exception $e) {
    echo "<!-- DEBUG: ERRO CAPTURADO: " . $e->getMessage() . " -->\n";
    echo "<!-- DEBUG: ARQUIVO: " . $e->getFile() . " LINHA: " . $e->getLine() . " -->\n";
    
    if (isAjax()) {
        ob_clean();
        jsonResponse(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
    } else {
        echo "<h1>Erro Detectado</h1>";
        echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>Arquivo:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
        echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    exit;
}

// Obter todos os clientes
echo "<!-- DEBUG: Buscando todos os clientes -->\n";
$clientes = Cliente::listarTodos();
echo "<!-- DEBUG: Encontrados " . count($clientes) . " clientes -->\n";

// Mensagens de feedback
$mensagem = $_SESSION['mensagem'] ?? null;
$tipo_mensagem = $_SESSION['tipo_mensagem'] ?? 'info';
unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);

// Dados para edição
$cliente_editando = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $cliente_editando = Cliente::buscarPorId($id);
}

echo "<!-- DEBUG: Iniciando HTML -->\n";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - DEBUG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">Clientes - Versão Debug</h1>
        
        <?php if ($mensagem): ?>
            <div class="mb-4 p-4 rounded <?= $tipo_mensagem === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4"><?= $cliente_editando ? 'Editar Cliente' : 'Novo Cliente' ?></h2>
            
            <form id="form-cliente" class="space-y-4">
                <?php if ($cliente_editando): ?>
                    <input type="hidden" name="id" value="<?= $cliente_editando['id'] ?>">
                <?php endif; ?>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="nome" required autofocus
                               value="<?= htmlspecialchars($cliente_editando['nome'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                        <input type="tel" name="telefone" required 
                               value="<?= htmlspecialchars($cliente_editando['telefone'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" 
                               value="<?= htmlspecialchars($cliente_editando['email'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                        <input type="text" name="cpf" 
                               value="<?= htmlspecialchars($cliente_editando['cpf'] ?? '') ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="endereco" 
                           value="<?= htmlspecialchars($cliente_editando['endereco'] ?? '') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                    <textarea name="observacoes" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($cliente_editando['observacoes'] ?? '') ?></textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        <?= $cliente_editando ? 'Atualizar' : 'Criar' ?> Cliente
                    </button>
                    
                    <?php if ($cliente_editando): ?>
                        <a href="clientes-debug.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancelar
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b">
                <h2 class="text-xl font-semibold">Lista de Clientes (<?= count($clientes) ?>)</h2>
            </div>
            
            <div class="p-6">
                <?php if (empty($clientes)): ?>
                    <p class="text-gray-500 text-center py-8">Nenhum cliente cadastrado ainda.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2 text-left">Nome</th>
                                    <th class="px-4 py-2 text-left">Telefone</th>
                                    <th class="px-4 py-2 text-left">Email</th>
                                    <th class="px-4 py-2 text-left">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?= htmlspecialchars($cliente['nome']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($cliente['telefone']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($cliente['email'] ?? '-') ?></td>
                                        <td class="px-4 py-2">
                                            <a href="?editar=<?= $cliente['id'] ?>" 
                                               class="text-blue-600 hover:text-blue-800 mr-2">Editar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-6">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">← Voltar ao Dashboard</a>
        </div>
    </div>
    
    <script>
    console.log('DEBUG: Script carregado');
    
    // Função para avançar para o próximo campo com Enter
    function setupEnterNavigation() {
        const inputs = document.querySelectorAll('#form-cliente input, #form-cliente textarea');
        
        inputs.forEach((input, index) => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    // Se for o último campo, submeter o formulário
                    if (index === inputs.length - 1) {
                        document.getElementById('form-cliente').dispatchEvent(new Event('submit'));
                    } else {
                        // Avançar para o próximo campo
                        inputs[index + 1].focus();
                    }
                }
            });
        });
    }
    
    // Configurar navegação com Enter quando a página carregar
    document.addEventListener('DOMContentLoaded', function() {
        setupEnterNavigation();
        
        // Focar no primeiro campo automaticamente
        const firstInput = document.querySelector('#form-cliente input[autofocus]');
        if (firstInput) {
            firstInput.focus();
        }
    });
    
    document.getElementById('form-cliente').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('DEBUG: Form submetido');
        
        const formData = new FormData(this);
        formData.append('action', 'criar');
        
        fetch('clientes-debug.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('DEBUG: Resposta recebida', response);
            return response.json();
        })
        .then(data => {
            console.log('DEBUG: Dados JSON', data);
            if (data.success) {
                alert('Cliente criado com sucesso!');
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('DEBUG: Erro na requisição', error);
            alert('Erro na requisição: ' + error.message);
        });
    });
    </script>
</body>
</html>
<?php
echo "<!-- DEBUG: HTML finalizado -->\n";
ob_end_flush();
?>