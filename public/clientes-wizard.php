<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/Cliente.php';

// Mensagens de feedback
$msg = '';
$erro = '';

// Edição: buscar cliente se id_edit estiver presente
$editando = false;
$cliente_edit = [
    'id' => '', 'nome' => '', 'email' => '', 'telefone' => '', 'endereco' => ''
];
if (isset($_GET['id_edit'])) {
    $id_edit = $_GET['id_edit'];
    $todos = Cliente::listarTodos();
    foreach ($todos as $c) {
        if ($c['id'] == $id_edit) {
            $cliente_edit = $c;
            $editando = true;
            break;
        }
    }
}

// Cadastro ou edição de cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'], $_POST['email'])) {
    try {
        if (isset($_POST['id']) && $_POST['id']) {
            // Atualizar
            Cliente::atualizar($_POST['id'], $_POST['nome'], $_POST['email'], $_POST['telefone'] ?? '', $_POST['endereco'] ?? '');
            $msg = 'Cliente atualizado com sucesso!';
        } else {
            // Novo
            Cliente::criar($_POST['nome'], $_POST['email'], $_POST['telefone'] ?? '', $_POST['endereco'] ?? '');
            $msg = 'Cliente cadastrado com sucesso!';
        }
    } catch (Exception $e) {
        $erro = 'Erro ao salvar: ' . $e->getMessage();
    }
}

// Exclusão de cliente
if (isset($_GET['excluir'])) {
    try {
        Cliente::deletar($_GET['excluir']);
        $msg = 'Cliente excluído com sucesso!';
    } catch (Exception $e) {
        $erro = 'Erro ao excluir: ' . $e->getMessage();
    }
}

// Busca e filtros avançados
$busca = $_GET['busca'] ?? '';
$filtro_campo = $_GET['filtro_campo'] ?? 'todos';
$ordenacao = $_GET['ordenacao'] ?? 'nome';
$direcao = $_GET['direcao'] ?? 'asc';

$clientes = Cliente::listarTodos();

// Aplicar filtros
if ($busca) {
    $clientes = array_filter($clientes, function($c) use ($busca, $filtro_campo) {
        if ($filtro_campo === 'todos') {
            return stripos($c['nome'], $busca) !== false || 
                   stripos($c['email'], $busca) !== false ||
                   stripos($c['telefone'], $busca) !== false ||
                   stripos($c['endereco'], $busca) !== false;
        } else {
            return stripos($c[$filtro_campo], $busca) !== false;
        }
    });
}

// Aplicar ordenação
usort($clientes, function($a, $b) use ($ordenacao, $direcao) {
    $valor_a = $a[$ordenacao] ?? '';
    $valor_b = $b[$ordenacao] ?? '';
    
    if ($direcao === 'asc') {
        return strcasecmp($valor_a, $valor_b);
    } else {
        return strcasecmp($valor_b, $valor_a);
    }
});

// Estatísticas
$total_clientes = count($clientes);
$clientes_com_telefone = count(array_filter($clientes, function($c) { return !empty($c['telefone']); }));
$clientes_com_endereco = count(array_filter($clientes, function($c) { return !empty($c['endereco']); }));

function render_content() {
    global $msg, $erro, $clientes, $busca, $filtro_campo, $ordenacao, $direcao, $editando, $cliente_edit;
    global $total_clientes, $clientes_com_telefone, $clientes_com_endereco;
?>
    <div class="space-y-6">
        <!-- Mensagens -->
        <?php if ($msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Total de Clientes</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $total_clientes ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-green-100 text-green-600">
                        <i class="fas fa-phone text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Com Telefone</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $clientes_com_telefone ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="p-2 rounded-full bg-purple-100 text-purple-600">
                        <i class="fas fa-map-marker-alt text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-600">Com Endereço</p>
                        <p class="text-2xl font-bold text-gray-900"><?= $clientes_com_endereco ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Busca Avançada -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Busca Avançada</h3>
            <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Termo de Busca</label>
                    <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" 
                           placeholder="Digite para buscar..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Campo</label>
                    <select name="filtro_campo" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="todos" <?= $filtro_campo === 'todos' ? 'selected' : '' ?>>Todos os campos</option>
                        <option value="nome" <?= $filtro_campo === 'nome' ? 'selected' : '' ?>>Nome</option>
                        <option value="email" <?= $filtro_campo === 'email' ? 'selected' : '' ?>>E-mail</option>
                        <option value="telefone" <?= $filtro_campo === 'telefone' ? 'selected' : '' ?>>Telefone</option>
                        <option value="endereco" <?= $filtro_campo === 'endereco' ? 'selected' : '' ?>>Endereço</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordenar por</label>
                    <select name="ordenacao" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="nome" <?= $ordenacao === 'nome' ? 'selected' : '' ?>>Nome</option>
                        <option value="email" <?= $ordenacao === 'email' ? 'selected' : '' ?>>E-mail</option>
                        <option value="telefone" <?= $ordenacao === 'telefone' ? 'selected' : '' ?>>Telefone</option>
                        <option value="endereco" <?= $ordenacao === 'endereco' ? 'selected' : '' ?>>Endereço</option>
                        <option value="id" <?= $ordenacao === 'id' ? 'selected' : '' ?>>ID</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Direção</label>
                    <select name="direcao" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="asc" <?= $direcao === 'asc' ? 'selected' : '' ?>>Crescente</option>
                        <option value="desc" <?= $direcao === 'desc' ? 'selected' : '' ?>>Decrescente</option>
                    </select>
                </div>
                
                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                        <i class="fas fa-search mr-2"></i>Buscar
                    </button>
                    <a href="clientes-wizard.php" class="text-gray-500 hover:text-gray-700 px-6 py-2 border border-gray-300 rounded-md">
                        <i class="fas fa-times mr-2"></i>Limpar
                    </a>
                </div>
            </form>
        </div>

        <!-- Wizard de Cadastro -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">
                    <?= $editando ? 'Editar Cliente' : 'Novo Cliente' ?>
                </h3>
                <div class="flex gap-2">
                    <button onclick="openWizard()" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 flex items-center">
                        <i class="fas fa-plus mr-2"></i>Novo Cliente
                    </button>
                    <button onclick="testModal()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 flex items-center">
                        <i class="fas fa-test mr-2"></i>Teste Modal
                    </button>
                </div>
            </div>
        </div>

        <!-- Lista de Clientes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Lista de Clientes (<?= $total_clientes ?>)</h3>
                <div class="text-sm text-gray-500">
                    <?php if ($busca): ?>
                        Resultados para: "<?= htmlspecialchars($busca) ?>"
                    <?php endif; ?>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">E-mail</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Endereço</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($clientes)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    <?php if ($busca): ?>
                                        Nenhum cliente encontrado para "<?= htmlspecialchars($busca) ?>"
                                    <?php else: ?>
                                        Nenhum cliente cadastrado
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clientes as $c): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($c['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($c['nome']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($c['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (!empty($c['telefone'])): ?>
                                        <i class="fas fa-phone text-green-500 mr-1"></i><?= htmlspecialchars($c['telefone']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if (!empty($c['endereco'])): ?>
                                        <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i><?= htmlspecialchars($c['endereco']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="editCliente(<?= htmlspecialchars(json_encode($c)) ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </button>
                                    <a href="clientes-wizard.php?excluir=<?= $c['id'] ?>" onclick="return confirm('Excluir este cliente?')" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash mr-1"></i>Excluir
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Wizard -->
    <div id="wizardModal" class="fixed inset-0 bg-black bg-opacity-50 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="mt-3">
                <!-- Header do Wizard -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-800">Cadastro de Cliente</h3>
                    <button onclick="closeWizard()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="step1" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                            <div class="ml-2 text-sm font-medium text-blue-500">Informações Básicas</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step2" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Contato</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step3" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Endereço</div>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step4" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">4</div>
                            <div class="ml-2 text-sm font-medium text-gray-500">Confirmação</div>
                        </div>
                    </div>
                </div>

                <!-- Formulário Wizard -->
                <form id="wizardForm" method="post" class="space-y-6">
                    <input type="hidden" name="id" id="wizard_id">
                    
                    <!-- Step 1: Informações Básicas -->
                    <div id="step1Content" class="step-content">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Informações Básicas</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
                                <input type="text" name="nome" id="wizard_nome" placeholder="Digite o nome completo" required autofocus
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                <input type="email" name="email" id="wizard_email" placeholder="email@exemplo.com" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                                <input type="text" name="cpf" id="wizard_cpf" placeholder="000.000.000-00" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Contato -->
                    <div id="step2Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Informações de Contato</h4>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone *</label>
                                    <input type="text" name="telefone" id="wizard_telefone" placeholder="(11) 99999-9999" required
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                                    <input type="text" name="whatsapp" id="wizard_whatsapp" placeholder="(11) 99999-9999" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                                <textarea name="observacoes" id="wizard_observacoes" placeholder="Informações adicionais..." rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Endereço -->
                    <div id="step3Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Endereço</h4>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                    <input type="text" name="cep" id="wizard_cep" placeholder="00000-000" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                                    <input type="text" name="logradouro" id="wizard_logradouro" placeholder="Rua, Avenida, etc." 
                                           data-address-search="true"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                                    <input type="text" name="numero" id="wizard_numero" placeholder="123" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                                    <input type="text" name="complemento" id="wizard_complemento" placeholder="Apto, Casa, etc." 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                                    <input type="text" name="bairro" id="wizard_bairro" placeholder="Bairro" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                                    <input type="text" name="cidade" id="wizard_cidade" placeholder="São Paulo" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                                    <select name="estado" id="wizard_estado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Selecione...</option>
                                        <option value="SP">São Paulo</option>
                                        <option value="RJ">Rio de Janeiro</option>
                                        <option value="MG">Minas Gerais</option>
                                        <option value="RS">Rio Grande do Sul</option>
                                        <option value="PR">Paraná</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço Completo</label>
                                <textarea name="endereco" id="wizard_endereco" placeholder="Endereço completo (opcional)" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Confirmação -->
                    <div id="step4Content" class="step-content hidden">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Confirmação dos Dados</h4>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome:</label>
                                    <p id="confirm_nome" class="text-gray-900 font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">E-mail:</label>
                                    <p id="confirm_email" class="text-gray-900 font-medium"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Telefone:</label>
                                    <p id="confirm_telefone" class="text-gray-900 font-medium"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Endereço:</label>
                                    <p id="confirm_endereco" class="text-gray-900 font-medium"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Navegação -->
                    <div class="flex justify-between pt-6 border-t border-gray-200">
                        <button type="button" id="prevBtn" onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 hidden">
                            <i class="fas fa-arrow-left mr-2"></i>Anterior
                        </button>
                        <div class="flex gap-2">
                            <button type="button" id="nextBtn" onclick="nextStep()" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                                Próximo<i class="fas fa-arrow-right ml-2"></i>
                            </button>
                            <button type="submit" id="submitBtn" class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 hidden">
                                <i class="fas fa-save mr-2"></i>Salvar Cliente
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function testModal() {
            alert('Teste do modal funcionando!');
            const modal = document.getElementById('wizardModal');
            if (modal) {
                modal.style.display = 'block';
                console.log('Modal encontrado e aberto!');
            } else {
                console.error('Modal não encontrado!');
            }
        }

        function openWizard() {
            console.log('Abrindo wizard...');
            const modal = document.getElementById('wizardModal');
            if (modal) {
                modal.style.display = 'block';
                modal.classList.remove('hidden');
                resetWizard();
            } else {
                console.error('Modal não encontrado!');
            }
        }

        function closeWizard() {
            const modal = document.getElementById('wizardModal');
            if (modal) {
                modal.style.display = 'none';
                modal.classList.add('hidden');
                resetWizard();
            }
        }

        function resetWizard() {
            currentStep = 1;
            updateStepDisplay();
            document.getElementById('wizardForm').reset();
            document.getElementById('wizard_id').value = '';
        }

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepDisplay();
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
            }
        }

        function updateStepDisplay() {
            // Esconder todos os conteúdos
            for (let i = 1; i <= totalSteps; i++) {
                document.getElementById(`step${i}Content`).classList.add('hidden');
                document.getElementById(`step${i}`).classList.remove('bg-blue-500', 'text-white');
                document.getElementById(`step${i}`).classList.add('bg-gray-200', 'text-gray-500');
            }

            // Mostrar conteúdo atual
            document.getElementById(`step${currentStep}Content`).classList.remove('hidden');
            document.getElementById(`step${currentStep}`).classList.remove('bg-gray-200', 'text-gray-500');
            document.getElementById(`step${currentStep}`).classList.add('bg-blue-500', 'text-white');

            // Atualizar botões
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');
            const submitBtn = document.getElementById('submitBtn');

            if (currentStep === 1) {
                prevBtn.classList.add('hidden');
            } else {
                prevBtn.classList.remove('hidden');
            }

            if (currentStep === totalSteps) {
                nextBtn.classList.add('hidden');
                submitBtn.classList.remove('hidden');
                updateConfirmation();
            } else {
                nextBtn.classList.remove('hidden');
                submitBtn.classList.add('hidden');
            }
        }

        function validateCurrentStep() {
            if (currentStep === 1) {
                const nome = document.getElementById('wizard_nome').value.trim();
                
                if (!nome) {
                    alert('Por favor, preencha o nome obrigatório.');
                    return false;
                }
            }
            return true;
        }

        function updateConfirmation() {
            document.getElementById('confirm_nome').textContent = document.getElementById('wizard_nome').value || '-';
            document.getElementById('confirm_email').textContent = document.getElementById('wizard_email').value || '-';
            document.getElementById('confirm_telefone').textContent = document.getElementById('wizard_telefone').value || '-';
            document.getElementById('confirm_endereco').textContent = document.getElementById('wizard_endereco').value || '-';
        }

        function editCliente(cliente) {
            openWizard();
            document.getElementById('wizard_id').value = cliente.id;
            document.getElementById('wizard_nome').value = cliente.nome;
            document.getElementById('wizard_email').value = cliente.email;
            document.getElementById('wizard_telefone').value = cliente.telefone || '';
            document.getElementById('wizard_endereco').value = cliente.endereco || '';
        }

        // Fechar modal ao clicar fora dele
        document.getElementById('wizardModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeWizard();
            }
        });

        // Fechar modal com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeWizard();
            }
        });

        // Função para configurar navegação com Enter
        function setupEnterNavigation() {
            const wizardForm = document.getElementById('wizardForm');
            if (!wizardForm) return;
            
            const inputs = wizardForm.querySelectorAll('input, textarea, select');
            
            inputs.forEach((input, index) => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        
                        // Se for o último campo do step atual, avançar para o próximo step
                        const currentStepInputs = document.getElementById(`step${currentStep}Content`).querySelectorAll('input, textarea, select');
                        const isLastInStep = index === currentStepInputs.length - 1;
                        
                        if (isLastInStep && currentStep < totalSteps) {
                            nextStep();
                        } else if (isLastInStep && currentStep === totalSteps) {
                            // Se for o último campo do último step, submeter o formulário
                            wizardForm.dispatchEvent(new Event('submit'));
                        } else {
                            // Avançar para o próximo campo
                            const nextInput = inputs[index + 1];
                            if (nextInput) {
                                nextInput.focus();
                            }
                        }
                    }
                });
            });
        }
        
        // Função para focar no primeiro campo do step atual
        function focusFirstField() {
            const currentStepContent = document.getElementById(`step${currentStep}Content`);
            if (currentStepContent) {
                const firstInput = currentStepContent.querySelector('input, textarea, select');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        }

        // Inicializar wizard quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página carregada, verificando modal...');
            const modal = document.getElementById('wizardModal');
            if (modal) {
                console.log('Modal encontrado!');
                setupEnterNavigation();
            } else {
                console.error('Modal não encontrado na inicialização!');
            }
        });
        
        // Atualizar a função updateStepDisplay para focar no primeiro campo
        const originalUpdateStepDisplay = updateStepDisplay;
        updateStepDisplay = function() {
            originalUpdateStepDisplay();
            // Focar no primeiro campo após mudar de step
            setTimeout(focusFirstField, 100);
        };
    </script>
<?php
}
include 'layout.php';