<?php
// layout.php - Estrutura base do sistema
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bichos do Bairro - Sistema</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
    <style>
        .sidebar-item:hover { background-color: #f1f5f9; }
        .card { box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06); }
        
        /* Estilos do sub-menu */
        .submenu {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .submenu a {
            transition: all 0.2s ease;
        }
        
        .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        
        /* Hover effects para sub-menu */
        .submenu a:hover {
            background-color: #e2e8f0;
            padding-left: 1.75rem;
        }

        /* Responsividade da sidebar */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
            }
            .sidebar-overlay.open {
                display: block;
            }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="responsive.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Overlay para mobile -->
    <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden" style="display: none;"></div>
    
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white shadow-lg lg:shadow-none">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-paw text-blue-500 mr-2"></i>
                        <span class="hidden sm:inline">Bichos do Bairro</span>
                        <span class="sm:hidden">BDB</span>
                    </h1>
                    <!-- Botão fechar mobile -->
                    <button id="closeSidebar" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            
            <nav class="mt-6">
                <a href="index.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-chart-bar mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="clientes.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-users mr-3"></i>
                    <span>Clientes</span>
                </a>
                <a href="pets.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'pets.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-dog mr-3"></i>
                    <span>Pets</span>
                </a>
                <!-- Agendamentos com sub-menu -->
                <div class="relative">
                    <a href="agendamentos.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= in_array(basename($_SERVER['PHP_SELF']), ['agendamentos.php', 'agendamentos-recorrentes.php', 'agendamentos-recorrentes-form.php']) ? 'bg-blue-50 text-blue-600' : '' ?>">
                        <i class="fas fa-calendar mr-3"></i>
                        <span>Agendamentos</span>
                        <i class="fas fa-chevron-down ml-auto text-xs"></i>
                    </a>
                    <div class="submenu bg-gray-50 border-l-4 border-blue-500" style="display: <?= in_array(basename($_SERVER['PHP_SELF']), ['agendamentos.php', 'agendamentos-recorrentes.php', 'agendamentos-recorrentes-form.php']) ? 'block' : 'none' ?>;">
                        <a href="agendamentos.php" class="sidebar-item flex items-center px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'agendamentos.php' ? 'bg-blue-100 text-blue-700' : '' ?>">
                            <i class="fas fa-calendar-day mr-3"></i>
                            <span>Calendário</span>
                        </a>
                        <a href="agendamentos-recorrentes.php" class="sidebar-item flex items-center px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'agendamentos-recorrentes.php' ? 'bg-blue-100 text-blue-700' : '' ?>">
                            <i class="fas fa-redo mr-3"></i>
                            <span>Recorrentes</span>
                        </a>
                        <a href="agendamentos-recorrentes-form.php" class="sidebar-item flex items-center px-6 py-2 text-sm text-gray-600 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'agendamentos-recorrentes-form.php' ? 'bg-blue-100 text-blue-700' : '' ?>">
                            <i class="fas fa-plus mr-3"></i>
                            <span>Novo Recorrente</span>
                        </a>
                    </div>
                </div>
                <a href="caixa.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'caixa.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-cash-register mr-3"></i>
                    <span>Caixa</span>
                </a>
                <a href="configuracoes.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-cog mr-3"></i>
                    <span>Configurações</span>
                </a>
                <a href="admin.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-tools mr-3"></i>
                    <span>Administração</span>
                </a>
                <a href="admin-permissoes.php" class="sidebar-item flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100 <?= basename($_SERVER['PHP_SELF']) == 'admin-permissoes.php' ? 'bg-blue-50 text-blue-600' : '' ?>">
                    <i class="fas fa-shield-alt mr-3"></i>
                    <span>Permissões</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex items-center justify-between">
                        <!-- Botão menu mobile -->
                        <button id="openSidebar" class="lg:hidden text-gray-500 hover:text-gray-700 mr-4">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">
                            <?php
                            $page = basename($_SERVER['PHP_SELF'], '.php');
                            $titles = [
                                'index' => 'Dashboard',
                                'clientes' => 'Clientes',
                                'pets' => 'Pets',
                                'agendamentos' => 'Calendário de Agendamentos',
                                'agendamentos-recorrentes' => 'Agendamentos Recorrentes',
                                'agendamentos-recorrentes-form' => 'Novo Agendamento Recorrente',
                                'caixa' => 'Caixa',
                                'configuracoes' => 'Configurações',
                                'admin' => 'Administração',
                                'admin-permissoes' => 'Permissões'
                            ];
                            echo $titles[$page] ?? 'Sistema';
                            ?>
                        </h2>
                        <div class="flex items-center space-x-2 sm:space-x-4">
                            <?php if (isset($_SESSION['usuario_nome'])): ?>
                                <span class="text-sm text-gray-500 hidden sm:inline">Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></span>
                                <a href="logout.php" class="text-sm text-red-600 hover:text-red-800">
                                    <i class="fas fa-sign-out-alt sm:hidden"></i>
                                    <span class="hidden sm:inline">Sair</span>
                                </a>
                            <?php else: ?>
                                <span class="text-sm text-gray-500 hidden sm:inline">Bem-vindo ao sistema</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="p-4 sm:p-6">
                <?php if (function_exists('render_content')) render_content(); ?>
            </main>
        </div>
    </div>
    <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/sw.js');
    }
    
    // Controle do menu mobile
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        
        // Abrir sidebar
        openSidebarBtn.addEventListener('click', function() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('open');
        });
        
        // Fechar sidebar
        function closeSidebar() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
        }
        
        closeSidebarBtn.addEventListener('click', closeSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);
        
        // Fechar ao clicar em um link (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 1024) { // lg breakpoint
                    closeSidebar();
                }
            });
        });
        
        // Controle do sub-menu de agendamentos
        const agendamentosLink = document.querySelector('a[href="agendamentos.php"]');
        const submenu = document.querySelector('.submenu');
        
        if (agendamentosLink && submenu) {
            agendamentosLink.addEventListener('click', function(e) {
                e.preventDefault();
                const isVisible = submenu.style.display === 'block';
                submenu.style.display = isVisible ? 'none' : 'block';
                
                // Rotacionar ícone
                const chevron = this.querySelector('.fa-chevron-down');
                if (chevron) {
                    chevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            });
        }
    });
    </script>
    <?php if (basename($_SERVER['PHP_SELF']) === 'agendamentos.php'): ?>
    <script>
    $(document).ready(function() {
        $('#agenda_cliente_id').select2({
            placeholder: 'Selecione o cliente',
            minimumInputLength: 2,
            ajax: {
                url: 'agendamentos.php?action=buscar_clientes',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { q: params.term };
                },
                processResults: function (data) {
                    return data;
                },
                cache: true
            },
            width: '100%'
        });

        $('#agenda_cliente_id').on('change', function() {
            var clienteId = $(this).val();
            $('#agenda_pet_id').html('<option value="">Carregando pets...</option>');
            $.get('agendamentos.php?action=buscar_pets&cliente_id=' + clienteId, function(data) {
                var options = '<option value="">Selecione o pet</option>';
                data.results.forEach(function(pet) {
                    options += '<option value="' + pet.id + '">' + pet.text + '</option>';
                });
                $('#agenda_pet_id').html(options);
            }, 'json');
        });
    });
    </script>
    <?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) === 'agendamentos.php'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
            var agendaForm = document.getElementById('agendaForm');
        if (agendaForm) {
            agendaForm.addEventListener('submit', function(e) {
                e.preventDefault();
                var form = e.target;
                var formData = new FormData(form);
                if (!formData.get('cliente_id') || !formData.get('pet_id')) {
                    return;
                }
                fetch('agendamentos.php?action=salvar', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.text())
                .then(data => {
                    if (data.trim() === 'ok') {
                        closeAgendaModal();
                        if (typeof calendar !== 'undefined') {
                            calendar.refetchEvents();
                        } else {
                            location.reload();
                        }
                    }
                })
                .catch(error => {
                    // Erro silencioso em produção
                });
            });
        }
});
</script>
<?php endif; ?>
    </script>
    <!-- Script para busca de endereços -->
    <script src="js/address-search.js"></script>
</body>
</html>