<?php
require_once '../src/init.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login-simples.php');
    exit;
}

function render_content() {
    $total_clientes = count(Cliente::listarTodos());
    $total_pets = count(Pet::listarTodos());
    $total_agendamentos = count(Agendamento::listarTodos());
    $lancamentos = $_SESSION['caixa'] ?? [];
    $saldo = 0;
    foreach ($lancamentos as $l) {
        $saldo += ($l['tipo'] === 'entrada' ? $l['valor'] : -$l['valor']);
    }
?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Clientes</p>
                    <p class="text-2xl font-semibold text-gray-900" data-stat="clientes"><?= $total_clientes ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-dog text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pets</p>
                    <p class="text-2xl font-semibold text-gray-900" data-stat="pets"><?= $total_pets ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-calendar text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Agendamentos</p>
                    <p class="text-2xl font-semibold text-gray-900" data-stat="agendamentos"><?= $total_agendamentos ?></p>
                </div>
            </div>
        </div>
        
        <div class="card bg-white rounded-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Saldo</p>
                    <p class="text-2xl font-semibold text-gray-900" data-stat="saldo" style="color:<?= $saldo >= 0 ? '#065f46' : '#b91c1c' ?>">
                        R$ <?= number_format($saldo, 2, ',', '.') ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php
}
include 'layout.php'; 