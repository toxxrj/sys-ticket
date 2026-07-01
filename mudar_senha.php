<?php
session_start();
require 'conexao.php';

// Bloqueia se o usuário não veio pelo fluxo correto de login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['troca_forcada'])) {
    header("Location: index.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = trim($_POST['nova_senha']);
    $confirma_senha = trim($_POST['confirma_senha']);

    if (strlen($nova_senha) < 5) {
        $erro = "A senha deve ter pelo menos 5 caracteres.";
    } elseif ($nova_senha !== $confirma_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        // Criptografa a nova senha escolhida
        $senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
        
        // Atualiza a senha e desmarca o flag de primeiro acesso
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 0 WHERE id = ?");
        $stmt->execute([$senha_hash, $_SESSION['user_id']]);

        // Remove a trava de segurança e joga para o painel
        unset($_SESSION['troca_forcada']);
        header("Location: dashboard_admin.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Primeiro Acesso - Mudar Senha</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { border: 2px solid #ffc107; padding: 30px; border-radius: 8px; width: 100%; max-width: 360px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #ffc107; color: #000; border: 0; font-size: 16px; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .erro { color: red; }
    </style>
</head>
<body>
<div class="box">
    <h3 style="margin-top:0; color: #b58100;">Primeiro Acesso Detectado</h3>
    <p style="font-size: 14px; color: #666;">Por motivos de segurança, você precisa alterar a senha padrão (<b>admin123</b>) antes de acessar o painel administrativo.</p>
    
    <?php if ($erro): ?><p class="erro"><?= $erro ?></p><?php endif; ?>
    
    <form method="POST">
        <label>Nova Senha:</label>
        <input type="password" name="nova_senha" required minlength="5">
        
        <label>Confirme a Nova Senha:</label>
        <input type="password" name="confirma_senha" required minlength="5">
        
        <button type="submit">Salvar Nova Senha & Entrar</button>
    </form>
</div>
</body>
</html>
