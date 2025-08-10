<?php
require_once __DIR__ . '/../src/init.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/NivelAcesso.php';

// Verificar se está logado e é admin
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login-simples.php');
    exit;
}

if ($_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

$auth = new Auth();
$nivelAcesso = new NivelAcesso();
$niveis = $nivelAcesso->listarTodos();
$permissoesPorArea = $nivelAcesso->listarPermissoesPorArea();
$mensagem = '';
$tipo_mensagem = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';
    
    switch ($acao) {
        case 'criar_usuario':
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $nivel = $_POST['nivel'] ?? 'usuario';
            
            if (empty($nome) || empty($email) || empty($senha)) {
                $mensagem = 'Todos os campos são obrigatórios.';
                $tipo_mensagem = 'erro';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensagem = 'E-mail inválido.';
                $tipo_mensagem = 'erro';
            } else {
                try {
                    $resultado = $auth->criarUsuario($nome, $email, $senha, $nivel);
                    if ($resultado['sucesso']) {
                        $mensagem = 'Usuário criado com sucesso!';
                        $tipo_mensagem = 'sucesso';
                    } else {
                        $mensagem = $resultado['erro'];
                        $tipo_mensagem = 'erro';
                    }
                } catch (Exception $e) {
                    $mensagem = 'Erro ao criar usuário: ' . $e->getMessage();
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'criar_nivel':
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '#667eea';
            if ($nome === '') {
                $mensagem = 'O nome do nível é obrigatório.';
                $tipo_mensagem = 'erro';
            } else {
                $res = $nivelAcesso->criar($nome, $descricao, $cor);
                if ($res['sucesso']) {
                    $mensagem = 'Nível criado com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = $res['erro'];
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'editar_usuario':
            $usuario_id = $_POST['usuario_id'] ?? '';
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $nivel = $_POST['nivel'] ?? 'usuario';
            $ativo = isset($_POST['ativo']) ? 1 : 0;
            
            if (empty($usuario_id) || empty($nome) || empty($email)) {
                $mensagem = 'Todos os campos são obrigatórios.';
                $tipo_mensagem = 'erro';
            } else {
                try {
                    $stmt = $auth->getPdo()->prepare('UPDATE usuarios SET nome = ?, email = ?, nivel_acesso = ?, ativo = ? WHERE id = ?');
                    $resultado = $stmt->execute([$nome, $email, $nivel, $ativo, $usuario_id]);
                    
                    if ($resultado) {
                        $mensagem = 'Usuário atualizado com sucesso!';
                        $tipo_mensagem = 'sucesso';
                    } else {
                        $mensagem = 'Erro ao atualizar usuário.';
                        $tipo_mensagem = 'erro';
                    }
                } catch (Exception $e) {
                    $mensagem = 'Erro ao atualizar usuário: ' . $e->getMessage();
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'editar_nivel':
            $id = intval($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '#667eea';
            if ($id <= 0 || $nome === '') {
                $mensagem = 'ID e nome obrigatórios.';
                $tipo_mensagem = 'erro';
            } else {
                $res = $nivelAcesso->atualizar($id, $nome, $descricao, $cor);
                if ($res['sucesso']) {
                    $mensagem = 'Nível atualizado!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = $res['erro'];
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'excluir_usuario':
            $usuario_id = $_POST['usuario_id'] ?? '';
            
            if (empty($usuario_id)) {
                $mensagem = 'ID do usuário é obrigatório.';
                $tipo_mensagem = 'erro';
            } elseif ($usuario_id == $_SESSION['usuario_id']) {
                $mensagem = 'Você não pode excluir sua própria conta.';
                $tipo_mensagem = 'erro';
            } else {
                try {
                    $stmt = $auth->getPdo()->prepare('DELETE FROM usuarios WHERE id = ?');
                    $resultado = $stmt->execute([$usuario_id]);
                    
                    if ($resultado) {
                        $mensagem = 'Usuário excluído com sucesso!';
                        $tipo_mensagem = 'sucesso';
                    } else {
                        $mensagem = 'Erro ao excluir usuário.';
                        $tipo_mensagem = 'erro';
                    }
                } catch (Exception $e) {
                    $mensagem = 'Erro ao excluir usuário: ' . $e->getMessage();
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'excluir_nivel':
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                $mensagem = 'ID inválido.';
                $tipo_mensagem = 'erro';
            } else {
                $res = $nivelAcesso->excluir($id);
                if ($res['sucesso']) {
                    $mensagem = 'Nível excluído!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = $res['erro'];
                    $tipo_mensagem = 'erro';
                }
            }
            break;
            
        case 'atualizar_permissoes':
            $nivel_id = intval($_POST['nivel_id'] ?? 0);
            $permissoes = $_POST['permissoes'] ?? [];
            if ($nivel_id <= 0) {
                $mensagem = 'ID do nível inválido.';
                $tipo_mensagem = 'erro';
            } else {
                $res = $nivelAcesso->atualizarPermissoesNivel($nivel_id, $permissoes);
                if ($res['sucesso']) {
                    $mensagem = 'Permissões atualizadas!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = $res['erro'];
                    $tipo_mensagem = 'erro';
                }
            }
            break;
    }
}

// AJAX para buscar permissões de um nível
if (isset($_GET['action']) && $_GET['action'] === 'buscar_permissoes') {
    $nivelId = intval($_GET['nivel_id'] ?? 0);
    $permissoes = $nivelAcesso->buscarPermissoesNivel($nivelId);
    header('Content-Type: application/json');
    echo json_encode(['permissoes' => $permissoes]);
    exit;
}

// Buscar usuários
try {
    $stmt = $auth->getPdo()->prepare("SELECT id, nome, email, nivel_acesso, ativo, ultimo_login FROM usuarios ORDER BY nome");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
    $mensagem = 'Erro ao carregar usuários: ' . $e->getMessage();
    $tipo_mensagem = 'erro';
}

function render_content() {
    global $usuarios, $niveis, $permissoesPorArea, $mensagem, $tipo_mensagem;
?>
    <div class="space-y-6">
        <!-- Mensagens -->
        <?php if (!empty($mensagem)): ?>
            <div class="p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <!-- Header Principal -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">EMPRESA</h1>
                    <h2 class="text-xl font-semibold text-gray-700">PERMISSÕES</h2>
                </div>
                <div class="flex space-x-3">
                    <button onclick="abrirModalNivel()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-all flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        NOVO GRUPO
                    </button>
                </div>
            </div>

            <!-- Seletor de Nível -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Selecionar Nível de Acesso</label>
                <select id="seletorNivel" onchange="selecionarNivel()" class="w-full max-w-md px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">Selecione um nível...</option>
                    <?php foreach ($niveis as $nivel): ?>
                        <option value="<?= $nivel['id'] ?>" data-cor="<?= htmlspecialchars($nivel['cor'] ?? '#667eea') ?>">
                            <?= htmlspecialchars(ucfirst($nivel['nome'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Ações do Nível Selecionado -->
            <div id="acoesNivel" class="hidden mb-6">
                <div class="flex space-x-3">
                    <button onclick="editarNivelSelecionado()" class="bg-orange-100 text-orange-700 px-4 py-2 rounded-md hover:bg-orange-200 transition-all">
                        <i class="fas fa-edit mr-1"></i>
                        ALTERAR NOME
                    </button>
                    <button onclick="inativarNivel()" class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-md hover:bg-yellow-200 transition-all">
                        <i class="fas fa-ban mr-1"></i>
                        INATIVAR GRUPO
                    </button>
                    <button onclick="excluirNivel()" class="bg-red-100 text-red-700 px-4 py-2 rounded-md hover:bg-red-200 transition-all">
                        <i class="fas fa-trash mr-1"></i>
                        EXCLUIR GRUPO
                    </button>
                    <button onclick="migrarUsuarios()" class="bg-blue-100 text-blue-700 px-4 py-2 rounded-md hover:bg-blue-200 transition-all">
                        <i class="fas fa-users mr-1"></i>
                        MIGRAR USUÁRIOS
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid de Permissões -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    FUNCIONALIDADES
                    <i class="fas fa-chevron-down ml-2"></i>
                </h3>
                <span class="text-sm text-gray-600">USUÁRIOS</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-blue-50">
                    <thead>
                        <tr class="bg-blue-100">
                            <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Área</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">ACESSAR</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">ADICIONAR</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">ALTERAR</th>
                            <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">EXCLUIR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $mapAcoes = [
                            'Acessar' => ['visualizar','acessar'],
                            'Adicionar' => ['criar','adicionar','novo'],
                            'Alterar' => ['editar','alterar','atualizar'],
                            'Excluir' => ['excluir','remover','deletar']
                        ];
                        foreach ($permissoesPorArea as $area => $permissoes): ?>
                            <tr class="border-b border-blue-200 hover:bg-blue-100 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    <i class="fas fa-chevron-down mr-2 text-gray-500"></i>
                                    <?= htmlspecialchars($area) ?>
                                </td>
                                <?php foreach ($mapAcoes as $acao => $palavras):
                                    $permissao = null;
                                    foreach ($permissoes as $p) {
                                        foreach ($palavras as $palavra) {
                                            if (stripos($p['nome'],$palavra)!==false) {
                                                $permissao = $p;
                                                break 2;
                                            }
                                        }
                                    }
                                ?>
                                <td class="px-4 py-3 text-center">
                                    <?php if ($permissao): ?>
                                        <input type="checkbox" 
                                               name="permissoes[]" 
                                               value="<?= $permissao['id'] ?>" 
                                               class="permissao-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                               data-area="<?= htmlspecialchars($area) ?>"
                                               data-acao="<?= htmlspecialchars($acao) ?>">
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lista de Usuários -->
        <div class="bg-white rounded-lg p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-users mr-2"></i>
                    Lista de Usuários
                </h3>
                <button onclick="abrirModalUsuario()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-all">
                    <i class="fas fa-plus mr-2"></i>
                    Novo Usuário
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-600"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($usuario['nome']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($usuario['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= htmlspecialchars(ucfirst($usuario['nivel_acesso'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($usuario['ativo']): ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            <i class="fas fa-check mr-1"></i>
                                            Ativo
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            <i class="fas fa-times mr-1"></i>
                                            Inativo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Nunca' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button onclick="editarUsuario(<?= htmlspecialchars(json_encode($usuario)) ?>)" class="text-blue-600 hover:text-blue-900 transition-all p-1 rounded hover:bg-blue-50" title="Editar usuário">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="alterarSenha(<?= $usuario['id'] ?>)" class="text-orange-600 hover:text-orange-900 transition-all p-1 rounded hover:bg-orange-50" title="Alterar senha">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                            <button onclick="excluirUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nome']) ?>')" class="text-red-600 hover:text-red-900 transition-all p-1 rounded hover:bg-red-50" title="Excluir usuário">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Usuário -->
    <div id="modalUsuario" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-user-plus text-blue-500 mr-2"></i>
                    <span id="modalUsuarioTitulo">Novo Usuário</span>
                </h3>
                
                <form method="post" id="formUsuario">
                    <input type="hidden" name="acao" id="acaoUsuario" value="criar_usuario">
                    <input type="hidden" name="usuario_id" id="edit_usuario_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                            <input type="text" name="nome" id="edit_nome" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                            <input type="email" name="email" id="edit_email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div id="campoSenha">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                            <input type="password" name="senha" id="edit_senha" minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nível de Acesso</label>
                            <select name="nivel" id="edit_nivel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <?php foreach ($niveis as $nivel): ?>
                                    <option value="<?= htmlspecialchars($nivel['nome']) ?>"><?= htmlspecialchars(ucfirst($nivel['nome'])) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="flex items-center" id="campoAtivo">
                            <input type="checkbox" name="ativo" id="edit_ativo" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edit_ativo" class="ml-2 block text-sm text-gray-900">Usuário Ativo</label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="fecharModalUsuario()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-all">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Criar/Editar Nível -->
    <div id="modalNivel" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-layer-group text-green-500 mr-2"></i>
                    <span id="modalNivelTitulo">Novo Nível</span>
                </h3>
                
                <form method="post" id="formNivel">
                    <input type="hidden" name="acao" id="acaoNivel" value="criar_nivel">
                    <input type="hidden" name="id" id="edit_nivel_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Nível</label>
                            <input type="text" name="nome" id="edit_nivel_nome" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                            <input type="color" name="cor" id="edit_nivel_cor" value="#667eea" class="w-full h-10 border border-gray-300 rounded-md">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                            <textarea name="descricao" id="edit_nivel_descricao" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="fecharModalNivel()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-all">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Alterar Senha -->
    <div id="modalSenha" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-key text-orange-500 mr-2"></i>
                    Alterar Senha
                </h3>
                
                <form method="post" id="formSenha">
                    <input type="hidden" name="acao" value="alterar_senha">
                    <input type="hidden" name="usuario_id" id="senha_usuario_id">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                            <input type="password" name="nova_senha" required minlength="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="fecharModalSenha()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-all">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition-all">
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let nivelSelecionado = null;

        function selecionarNivel() {
            const select = document.getElementById('seletorNivel');
            const nivelId = select.value;
            const acoesDiv = document.getElementById('acoesNivel');
            
            if (nivelId) {
                nivelSelecionado = nivelId;
                acoesDiv.classList.remove('hidden');
                
                // Carregar permissões do nível
                fetch('admin-permissoes.php?action=buscar_permissoes&nivel_id=' + nivelId)
                    .then(r => r.json())
                    .then(data => {
                        // Limpar checkboxes
                        document.querySelectorAll('.permissao-checkbox').forEach(cb => cb.checked = false);
                        
                        // Marcar permissões do nível
                        if (data.permissoes) {
                            data.permissoes.forEach(p => {
                                let cb = document.querySelector('.permissao-checkbox[value="'+p.id+'"]');
                                if (cb) cb.checked = true;
                            });
                        }
                    });
            } else {
                nivelSelecionado = null;
                acoesDiv.classList.add('hidden');
                document.querySelectorAll('.permissao-checkbox').forEach(cb => cb.checked = false);
            }
        }

        function abrirModalUsuario() {
            document.getElementById('modalUsuarioTitulo').textContent = 'Novo Usuário';
            document.getElementById('acaoUsuario').value = 'criar_usuario';
            document.getElementById('edit_usuario_id').value = '';
            document.getElementById('edit_nome').value = '';
            document.getElementById('edit_email').value = '';
            document.getElementById('edit_senha').value = '';
            document.getElementById('edit_senha').required = true;
            document.getElementById('campoSenha').style.display = 'block';
            document.getElementById('campoAtivo').style.display = 'none';
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

        function editarUsuario(usuario) {
            document.getElementById('modalUsuarioTitulo').textContent = 'Editar Usuário';
            document.getElementById('acaoUsuario').value = 'editar_usuario';
            document.getElementById('edit_usuario_id').value = usuario.id;
            document.getElementById('edit_nome').value = usuario.nome;
            document.getElementById('edit_email').value = usuario.email;
            document.getElementById('edit_senha').required = false;
            document.getElementById('campoSenha').style.display = 'none';
            document.getElementById('campoAtivo').style.display = 'flex';
            document.getElementById('edit_nivel').value = usuario.nivel_acesso;
            document.getElementById('edit_ativo').checked = usuario.ativo == 1;
            document.getElementById('modalUsuario').classList.remove('hidden');
        }

        function abrirModalNivel() {
            document.getElementById('modalNivelTitulo').textContent = 'Novo Nível';
            document.getElementById('acaoNivel').value = 'criar_nivel';
            document.getElementById('edit_nivel_id').value = '';
            document.getElementById('edit_nivel_nome').value = '';
            document.getElementById('edit_nivel_cor').value = '#667eea';
            document.getElementById('edit_nivel_descricao').value = '';
            document.getElementById('modalNivel').classList.remove('hidden');
        }

        function editarNivelSelecionado() {
            if (!nivelSelecionado) return;
            
            const select = document.getElementById('seletorNivel');
            const option = select.options[select.selectedIndex];
            const nome = option.text;
            const cor = option.getAttribute('data-cor');
            
            document.getElementById('modalNivelTitulo').textContent = 'Editar Nível';
            document.getElementById('acaoNivel').value = 'editar_nivel';
            document.getElementById('edit_nivel_id').value = nivelSelecionado;
            document.getElementById('edit_nivel_nome').value = nome;
            document.getElementById('edit_nivel_cor').value = cor;
            document.getElementById('modalNivel').classList.remove('hidden');
        }

        function alterarSenha(usuarioId) {
            document.getElementById('senha_usuario_id').value = usuarioId;
            document.getElementById('modalSenha').classList.remove('hidden');
        }

        function excluirUsuario(usuarioId, nome) {
            if (confirm(`Tem certeza que deseja excluir o usuário "${nome}"? Esta ação não pode ser desfeita.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir_usuario">
                    <input type="hidden" name="usuario_id" value="${usuarioId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function excluirNivel() {
            if (!nivelSelecionado) return;
            
            const select = document.getElementById('seletorNivel');
            const nome = select.options[select.selectedIndex].text;
            
            if (confirm(`Tem certeza que deseja excluir o nível "${nome}"? Esta ação não pode ser desfeita.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir_nivel">
                    <input type="hidden" name="id" value="${nivelSelecionado}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function inativarNivel() {
            // Funcionalidade será implementada em breve
        }

        function migrarUsuarios() {
            // Funcionalidade será implementada em breve
        }

        function fecharModalUsuario() {
            document.getElementById('modalUsuario').classList.add('hidden');
        }

        function fecharModalNivel() {
            document.getElementById('modalNivel').classList.add('hidden');
        }

        function fecharModalSenha() {
            document.getElementById('modalSenha').classList.add('hidden');
        }

        // Fechar modais ao clicar fora
        document.getElementById('modalUsuario').addEventListener('click', function(e) {
            if (e.target === this) fecharModalUsuario();
        });

        document.getElementById('modalNivel').addEventListener('click', function(e) {
            if (e.target === this) fecharModalNivel();
        });

        document.getElementById('modalSenha').addEventListener('click', function(e) {
            if (e.target === this) fecharModalSenha();
        });

        // Salvar permissões quando checkbox for alterado
        document.querySelectorAll('.permissao-checkbox').forEach(cb => {
            cb.addEventListener('change', function() {
                if (nivelSelecionado) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="acao" value="atualizar_permissoes">
                        <input type="hidden" name="nivel_id" value="${nivelSelecionado}">
                    `;
                    
                    document.querySelectorAll('.permissao-checkbox:checked').forEach(checkedCb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'permissoes[]';
                        input.value = checkedCb.value;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    </script>
<?php
}
include __DIR__ . '/layout.php';
?> 