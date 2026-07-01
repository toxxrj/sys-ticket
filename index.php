<?php
session_start();
require 'conexao.php';

$erro = '';

// Se já estiver logado, redireciona para o painel correto
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_tipo'] === 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_usuario.php");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);

    if (!empty($email) && !empty($senha)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && ($senha === $usuario['senha'] || password_verify($senha, $usuario['senha']))) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_tipo'] = $usuario['tipo'];

            if ($usuario['tipo'] === 'admin') {
                header("Location: dashboard_admin.php");
            } else {
                header("Location: dashboard_usuario.php");
            }
            exit;
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Suporte Milward 235</title>
    <!-- Fontes com pegada mecânica e geométrica limpa -->
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Share+Tech+Mono&family=Space+Grotesk:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Estilo Era Industrial */
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #1e222b; /* Tom cinza grafite muito escuro */
            /* Textura quadriculada sutil simulando malha de ferro ou planta técnica */
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 20px 20px;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            padding: 24px;
            box-sizing: border-box;
        }

        .welcome-text {
            text-transform: lowercase;
            text-align: center;
            color: #d1d5db; /* Cinza claro metalizado */
            font-family: 'Oswald', sans-serif;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: 1px;
            margin-bottom: 25px;
            border-bottom: 1px solid #374151;
            padding-bottom: 12px;
        }

        .card-industrial {
            background: #282c34; /* Textura de metal fosco escuro / cimento escuro */
            padding: 35px 25px;
            border-radius: 4px; /* Cantos retos mais severos, característicos do estilo */
            border-left: 5px solid #4b5563; /* Borda robusta simulando viga de aço */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .card-industrial h3 {
            margin-top: 0;
            margin-bottom: 30px;
            text-align: center;
            color: #f3f4f6;
            font-family: 'Oswald', sans-serif;
            font-size: 20px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #9ca3af;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 25px;
            border: 2px solid #374151; /* Borda estilo chapa metálica */
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            background-color: #1f232a; /* Caixa interna escura */
            color: #ffffff;
            font-family: 'Space Grotesk', sans-serif;
            transition: all 0.3s;
        }

        input[type="email"]:focus, input[type="password"]:focus {
            border-color: #6b7280; /* Brilho de aço polido */
            outline: none;
            background-color: #242932;
            box-shadow: 0 0 0 3px rgba(107, 114, 128, 0.2);
        }

        /* Botão simulando metal escovado pesado / ferro */
        .btn-industrial {
            background: #4b5563; /* Cor de ferro fundido */
            color: #ffffff;
            padding: 14px;
            border: 1px solid #6b7280;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            font-family: 'Oswald', sans-serif;
            transition: all 0.2s;
        }

        .btn-industrial:hover {
            background: #374151;
            border-color: #4b5563;
        }

        .signature {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #6b7280;
            font-family: 'Share Tech Tech', 'Share Tech Mono', monospace; /* Fonte estilo terminal/maquinário */
            text-transform: uppercase;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    
    <!-- Texto obrigatório mantido estritamente em letras minúsculas -->
    <div class="welcome-text">
        bemvindos ao suporte milward 235
    </div>

    <div class="card-industrial">
        <h3>Acessar o Painel</h3>
        
        <?php if ($erro): ?>
            <div style="background: #7f1d1d; color: #fca5a5; border: 1px solid #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 25px; font-size: 13px; text-align: center; font-weight: bold;">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label>Seu E-mail:</label>
            <input type="email" name="email" placeholder="EXEMPLO@EMAIL.COM" required>

            <label>Sua Senha:</label>
            <input type="password" name="senha" placeholder="••••••••" required>

            <button type="submit" class="btn-industrial">Entrar no Sistema</button>
        </form>
    </div>

    <!-- Assinatura com cara de número de série / código técnico -->
    <div class="signature">
        // by equipetoxx \\
    </div>
</div>

</body>
</html>
