<?php
session_start();
if (!isset($_SESSION['caixa'])) {
    $_SESSION['caixa'] = [];
}
$msg = '';
$erro = '';

// Cadastro de lançamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['descricao'], $_POST['valor'], $_POST['tipo'])) {
    $valor = floatval($_POST['valor']);
    if ($valor <= 0) {
        $erro = 'O valor deve ser maior que zero.';
    } else {
        $_SESSION['caixa'][] = [
            'descricao' => $_POST['descricao'],
            'valor' => $valor,
            'tipo' => $_POST['tipo'],
            'data' => date('Y-m-d H:i:s')
        ];
        $msg = 'Lançamento adicionado!';
    }
}

function render_content() {
    global $msg, $erro;
    $lancamentos = $_SESSION['caixa'] ?? [];
    $saldo = 0;
    foreach ($lancamentos as $l) {
        $saldo += ($l['tipo'] === 'entrada' ? $l['valor'] : -$l['valor']);
    }
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

        <!-- Saldo Atual -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">Saldo Atual</h3>
                <div class="text-2xl font-bold" style="color:<?= $saldo >= 0 ? '#065f46' : '#b91c1c' ?>">
                    R$ <?= number_format($saldo, 2, ',', '.') ?>
                </div>
            </div>
        </div>

        <!-- Formulário de Lançamento -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Novo Lançamento</h3>
            <form method="post" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input type="text" name="descricao" placeholder="Descrição do lançamento" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor *</label>
                    <input type="number" name="valor" placeholder="0.00" step="0.01" min="0.01" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select name="tipo" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-md hover:bg-blue-600">
                        Adicionar
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Lançamentos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Histórico de Lançamentos</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($lancamentos as $l): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($l['data']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($l['descricao']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $l['tipo'] === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $l['tipo'] === 'entrada' ? 'Entrada' : 'Saída' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="color:<?= $l['tipo'] === 'entrada' ? '#065f46' : '#b91c1c' ?>">
                                R$ <?= number_format($l['valor'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php
}
include 'layout.php'; 