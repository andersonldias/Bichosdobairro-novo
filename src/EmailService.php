<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Carregar PHPMailer manualmente
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configurarMailer();
    }
    
    private function configurarMailer() {
        try {
            // Configurações do servidor SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USERNAME'] ?? '';
            $this->mailer->Password = $_ENV['SMTP_PASSWORD'] ?? '';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $_ENV['SMTP_PORT'] ?? 587;
            $this->mailer->CharSet = 'UTF-8';
            // Configurações do remetente
            $this->mailer->setFrom(
                $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@bichosdobairro.com',                $_ENV['SMTP_FROM_NAME'] ?? 'Sistema Bichos do Bairro'
            );
            
        } catch (Exception $e) {
            error_log('Erro na configuração do PHPMailer: ' . $e->getMessage());
        }
    }
    
    /**
     * Envia e-mail de recuperação de senha
     */
    public function enviarRecuperacaoSenha($email, $nome, $senhaTemporaria) {
        try {
            $this->mailer->addAddress($email, $nome);
            $this->mailer->Subject = 'Recuperação de Senha - Bichos do Bairro';
            
            $corpo = $this->getTemplateRecuperacaoSenha($nome, $senhaTemporaria);
            $this->mailer->isHTML(true);
            $this->mailer->Body = $corpo;
            $this->mailer->AltBody = $this->getTextoPlanoRecuperacaoSenha($nome, $senhaTemporaria);
            
            $resultado = $this->mailer->send();
            
            if ($resultado) {
                return ['sucesso' => true, 'mensagem' => 'E-mail enviado com sucesso'];
            } else {
                return ['sucesso' => false, 'erro' => 'Falha ao enviar e-mail'];
            }
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
            return ['sucesso' => false, 'erro' => 'Erro ao enviar e-mail: ' . $e->getMessage()];
        }
    }
    
    /**
     * Template HTML para recuperação de senha
     */
    private function getTemplateRecuperacaoSenha($nome, $senhaTemporaria) {
        return "
        <!DOCTYPE html>
        <html lang=pt-BR><head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Recuperação de Senha</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height:1.6; color: #333; max-width: 600px; margin:0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667ea 0ba2100%); padding: 30px; border-radius:10px; text-align: center; margin-bottom: 30px;'>
                <h1 style='color: white; margin: 0; font-size:24px;'>🔐 Recuperação de Senha</h1>
                <p style='color: white; margin: 10px 0 00; opacity: 0.9>Sistema Bichos do Bairro</p>
            </div>
            
            <div style='background: #f89fa; padding: 25px; border-radius: 8px; border-left: 4px solid #667eea;'>
                <h2 style='color: #333; margin-top: 0;'>Olá, {$nome}!</h2>
                
                <p>Recebemos uma solicitação para recuperar sua senha no sistema <strong>Bichos do Bairro</strong>.</p>
                
                <div style='background: white; padding: 20px; border-radius: 6px; margin:20px; border: 2px dashed #667eea;'>
                    <h3 style='color: #667eea; margin-top: 0;'>🔄 Sua Nova Senha Temporária</h3>
                    <div style='background: #f34f6; padding: 15px; border-radius:4px; font-family: monospace; font-size: 18px; font-weight: bold; color: #1f2937; letter-spacing: 2px;'>
                       {$senhaTemporaria}
                    </div>
                </div>
                
                <div style='background: #fef3c7; padding: 15px; border-radius: 6px; border-left: 4px solid #f59e0b; margin: 20px 0;'>
                    <h4 style='color: #92400; margin-top:0;'>⚠️ Importante</h4>
                    <ul style='color: #92400e; margin: 10px 0; padding-left: 20px;'>
                        <li>Esta é uma senha temporária</li>
                        <li>Altere sua senha após fazer login</li>
                        <li>Mantenha sua senha segura</li>
                    </ul>
                </div>
                
                <p style='margin-bottom: 0;'><strong>Não solicitou esta recuperação?</strong> Entre em contato conosco imediatamente.</p>
            </div>
            
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>
                <p>Este e-mail foi enviado automaticamente pelo sistema Bichos do Bairro.</p>
                <p>© " . date('Y') . " Sistema Bichos do Bairro. Todos os direitos reservados.</p>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Versão texto plano para recuperação de senha
     */
    private function getTextoPlanoRecuperacaoSenha($nome, $senhaTemporaria) {
        return "
        Recuperação de Senha - Sistema Bichos do Bairro
        
        Olá, {$nome}!
        
        Recebemos uma solicitação para recuperar sua senha no sistema Bichos do Bairro.
        
        SUA NOVA SENHA TEMPORÁRIA:{$senhaTemporaria}
        
        IMPORTANTE:
        - Esta é uma senha temporária
        - Altere sua senha após fazer login
        - Mantenha sua senha segura
        
        Não solicitou esta recuperação? Entre em contato conosco imediatamente.
        
        © " . date('Y') . " Sistema Bichos do Bairro. Todos os direitos reservados.";
    }
    
    /**
     * Testa a configuração de e-mail
     */
    public function testarConfiguracao() {
        try {
            // Testa conexão SMTP
            $this->mailer->SMTPDebug = 0;
            $this->mailer->smtpConnect();
            return ['sucesso' => true, 'mensagem' => 'Configuração de e-mail OK'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erro' => 'Erro na configuração: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verifica se as configurações de e-mail estão definidas
     */
    public static function configuracaoValida() {
        $host = $_ENV['SMTP_HOST'] ?? '';
        $username = $_ENV['SMTP_USERNAME'] ?? '';
        $password = $_ENV['SMTP_PASSWORD'] ?? '';
        
        return !empty($host) && !empty($username) && !empty($password);
    }
} 