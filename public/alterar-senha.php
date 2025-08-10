<?php
require_once '../src/init.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login-simples.php');
    exit;
}

$auth = new Auth();
$mensagem = '';
$tipo_mensagem = '';

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $mensagem = 'Todos os campos são obrigatórios.';
        $tipo_mensagem = 'erro';
    } elseif ($nova_senha !== $confirmar_senha) {
        $mensagem = 'A nova senha e a confirmação não coincidem.';
        $tipo_mensagem = 'erro';
    } elseif (strlen($nova_senha) < 6) {
        $mensagem = 'A nova senha deve ter pelo menos 6 caracteres.';
        $tipo_mensagem = 'erro';
    } else {
        try {
            $resultado = $auth->alterarSenha($_SESSION['usuario_id'], $senha_atual, $nova_senha);
            if ($resultado['sucesso']) {
                $mensagem = 'Senha alterada com sucesso!';
                $tipo_mensagem = 'sucesso';
            } else {
                $mensagem = $resultado['erro'];
                $tipo_mensagem = 'erro';
            }
        } catch (Exception $e) {
            $mensagem = 'Erro ao alterar senha: ' . $e->getMessage();
            $tipo_mensagem = 'erro';
        }
    }
}

function render_content() {
    global $mensagem, $tipo_mensagem;
?>
    <div class="max-w-md mx-auto">
        <div class="card bg-white rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">
                <i class="fas fa-key text-blue-500 mr-2"></i>
                Alterar Senha
            </h3>

            <!-- Mensagens -->
            <?php if (!empty($mensagem)): ?>
                <div class="mb-6 p-4 rounded-lg <?= $tipo_mensagem === 'sucesso' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Senha Atual</label>
                    <input 
                        type="password" 
                        name="senha_atual" 
                        required 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Digite sua senha atual"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                    <input 
                        type="password" 
                        name="nova_senha" 
                        required 
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Digite a nova senha"
                    >
                    <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                    <input 
                        type="password" 
                        name="confirmar_senha" 
                        required 
                        minlength="6"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Confirme a nova senha"
                    >
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <i class="fas fa-save mr-2"></i>
                    Alterar Senha
                </button>
            </form>

            <!-- Informações de Segurança -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-blue-800 mb-2">
                    <i class="fas fa-shield-alt mr-1"></i>
                    Dicas de Segurança
                </h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Use pelo menos 6 caracteres</li>
                    <li>• Combine letras, números e símbolos</li>
                    <li>• Evite senhas óbvias (123456, senha, etc.)</li>
                    <li>• Não compartilhe sua senha com ninguém</li>
                </ul>
            </div>

            <!-- Links -->
            <div class="mt-6 text-center">
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>
<?php
}
include 'layout.php';
?> 