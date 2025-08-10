<?php
// Página com credenciais de teste para desenvolvimento
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciais de Teste - Bichos do Bairro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #666;
            margin: 0;
        }
        .info {
            background: #e3f2fd;
            color: #1976d2;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bbdefb;
        }
        .credential {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }
        .credential strong {
            color: #333;
        }
        .credential code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .button {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .button:hover {
            background: #5a6fd8;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Credenciais de Teste</h1>
            <p>Sistema de Agendamento - Bichos do Bairro</p>
        </div>

        <div class="warning">
            ⚠️ <strong>Atenção:</strong> Estas credenciais são apenas para desenvolvimento e testes.
        </div>

        <div class="info">
            <strong>Credenciais de Acesso:</strong>
        </div>

        <div class="credential">
            <strong>Email:</strong><br>
            <code>admin@bichosdobairro.com</code>
        </div>

        <div class="credential">
            <strong>Senha:</strong><br>
            <code>admin123</code>
        </div>

        <div class="info">
            <strong>Links Úteis:</strong>
        </div>

        <div style="text-align: center;">
            <a href="login-simples.php" class="button">🚀 Fazer Login</a>
            <a href="teste-login.php" class="button">🔧 Testar Conexão</a>
            <a href="teste-sessao.php" class="button">🧪 Testar Sessão</a>
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="dashboard.php" style="color: #667eea; text-decoration: none; font-size: 0.9em;">
                📊 Ir para Dashboard
            </a>
        </div>
    </div>
</body>
</html> 