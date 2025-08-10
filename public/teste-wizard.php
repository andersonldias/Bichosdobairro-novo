<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../src/Cliente.php';

$msg = '';
$erro = '';

// Cadastro de cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Cliente::criar($_POST['nome'], $_POST['email'], $_POST['telefone'] ?? '', $_POST['endereco'] ?? '');
        $msg = 'Cliente cadastrado com sucesso!';
    } catch (Exception $e) {
        $erro = 'Erro ao cadastrar: ' . $e->getMessage();
    }
}

function render_content() {
    global $msg, $erro;
?>
    <div class="max-w-4xl mx-auto p-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Teste do Wizard</h1>
        
        <!-- Mensagens -->
        <?php if ($msg): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Botão para abrir Wizard -->
        <div class="text-center mb-8">
            <button onclick="openWizard()" class="bg-blue-500 text-white px-8 py-3 rounded-lg hover:bg-blue-600 text-lg font-semibold">
                <i class="fas fa-magic mr-2"></i>Abrir Wizard
            </button>
        </div>

        <!-- Instruções -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-8">
            <h3 class="text-lg font-semibold text-blue-800 mb-2">Como testar:</h3>
            <ul class="text-blue-700 space-y-1">
                <li>• Clique no botão "Abrir Wizard" acima</li>
                <li>• O modal deve aparecer com 4 etapas</li>
                <li>• Navegue entre as etapas usando "Próximo" e "Anterior"</li>
                <li>• Na última etapa, clique em "Salvar Cliente"</li>
            </ul>
        </div>

        <!-- Debug Info -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Debug Info:</h3>
            <div id="debugInfo" class="text-sm text-gray-600">
                Aguardando ação...
            </div>
        </div>
    </div>

    <!-- Modal Wizard Simplificado -->
    <div id="wizardModal" class="fixed inset-0 bg-black bg-opacity-50 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Wizard de Cadastro</h2>
                    <button onclick="closeWizard()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Progress -->
                <div class="p-6 border-b bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div id="step1" class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-sm font-bold">1</div>
                            <span class="ml-2 text-sm">Básico</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step2" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">2</div>
                            <span class="ml-2 text-sm">Contato</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step3" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">3</div>
                            <span class="ml-2 text-sm">Endereço</span>
                        </div>
                        <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
                        <div class="flex items-center">
                            <div id="step4" class="w-8 h-8 rounded-full bg-gray-200 text-gray-500 flex items-center justify-center text-sm font-bold">4</div>
                            <span class="ml-2 text-sm">Final</span>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <form id="wizardForm" method="post">
                        <!-- Step 1 -->
                        <div id="step1Content" class="step-content">
                            <h3 class="text-lg font-semibold mb-4">Informações Básicas</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                                    <input type="text" name="nome" id="wizard_nome" required autofocus
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                                    <input type="email" name="email" id="wizard_email" required 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div id="step2Content" class="step-content hidden">
                            <h3 class="text-lg font-semibold mb-4">Contato</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input type="text" name="telefone" id="wizard_telefone" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div id="step3Content" class="step-content hidden">
                            <h3 class="text-lg font-semibold mb-4">Endereço</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                                <textarea name="endereco" id="wizard_endereco" rows="3"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div id="step4Content" class="step-content hidden">
                            <h3 class="text-lg font-semibold mb-4">Confirmação</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p><strong>Nome:</strong> <span id="confirm_nome"></span></p>
                                <p><strong>E-mail:</strong> <span id="confirm_email"></span></p>
                                <p><strong>Telefone:</strong> <span id="confirm_telefone"></span></p>
                                <p><strong>Endereço:</strong> <span id="confirm_endereco"></span></p>
                            </div>
                        </div>

                        <!-- Navigation -->
                        <div class="flex justify-between mt-6 pt-6 border-t">
                            <button type="button" id="prevBtn" onclick="prevStep()" class="px-6 py-2 border border-gray-300 rounded-md hidden">
                                Anterior
                            </button>
                            <div class="flex gap-2">
                                <button type="button" id="nextBtn" onclick="nextStep()" class="px-6 py-2 bg-blue-500 text-white rounded-md">
                                    Próximo
                                </button>
                                <button type="submit" id="submitBtn" class="px-6 py-2 bg-green-500 text-white rounded-md hidden">
                                    Salvar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 4;

        function openWizard() {
            console.log('Abrindo wizard...');
            document.getElementById('wizardModal').style.display = 'block';
            resetWizard();
            updateDebugInfo('Wizard aberto');
        }

        function closeWizard() {
            document.getElementById('wizardModal').style.display = 'none';
            resetWizard();
            updateDebugInfo('Wizard fechado');
        }

        function resetWizard() {
            currentStep = 1;
            updateStepDisplay();
            document.getElementById('wizardForm').reset();
        }

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    updateStepDisplay();
                    updateDebugInfo(`Indo para etapa ${currentStep}`);
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                updateStepDisplay();
                updateDebugInfo(`Voltando para etapa ${currentStep}`);
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

        function updateDebugInfo(message) {
            document.getElementById('debugInfo').innerHTML = `
                <strong>Status:</strong> ${message}<br>
                <strong>Etapa atual:</strong> ${currentStep}<br>
                <strong>Modal visível:</strong> ${document.getElementById('wizardModal').style.display === 'block' ? 'Sim' : 'Não'}
            `;
        }

        // Fechar modal ao clicar fora
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
                        const currentStepContent = document.getElementById(`step${currentStep}Content`);
                        if (currentStepContent) {
                            const currentStepInputs = currentStepContent.querySelectorAll('input, textarea, select');
                            const isLastInStep = Array.from(currentStepInputs).indexOf(input) === currentStepInputs.length - 1;
                            
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
        
        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            updateDebugInfo('Página carregada');
            setupEnterNavigation();
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