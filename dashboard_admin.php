<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'admin' || isset($_SESSION['troca_forcada'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['ticket_id']]);
}

$query = "SELECT t.*, u.nome as usuario_nome FROM tickets t JOIN usuarios u ON t.usuario_id = u.id ORDER BY t.data_criacao DESC";
$tickets = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Admin - Suporte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Painel Administrativo — Central de Suporte</h2>
        <a href="logout.php" class="btn-logout">Sair do Painel</a>
    </div>
    
    <div class="card">
        <h3>Todos os Chamados do Sistema</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th style="width: 120px;">Usuário</th>
                    <th style="width: 200px;">Título</th>
                    <th>Descrição</th>
                    <th style="width: 130px;">Status Atual</th>
                    <th style="width: 240px;">Gerenciar Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tickets) === 0): ?>
            <tr><td colspan="7" style="text-align: center; color: #718096;">Nenhum chamado gerado no sistema.</td></tr>
        <?php else: ?>
            <?php foreach ($tickets as $t): 
                $statusClass = str_replace(' ', '-', $t['status']);
            ?>
            <tr>
                <td data-label="ID">#<?= $t['id'] ?></td>
                <td data-label="Usuário"><b><?= htmlspecialchars($t['usuario_nome']) ?></b></td>
                <td data-label="Título"><?= htmlspecialchars($t['titulo']) ?></td>
                
                <!-- EXIBIÇÃO DA URGÊNCIA NO PAINEL DO ADMIN -->
                <td data-label="Urgência">
                    <span class="urgencia-badge urgencia-<?= $t['urgencia'] ?>">
                        <?= $t['urgencia'] ?>
                    </span>
                </td>
                
                <td data-label="Descrição"><?= nl2br(htmlspecialchars($t['descricao'])) ?></td>
                <td data-label="Status Atual">
                    <span class="status-badge status-<?= $statusClass ?>">
                        <?= $t['status'] ?>
                    </span>
                </td>
                <td data-label="Ação">
                    <form method="POST" class="admin-actions">
                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                        <select name="status">
                            <option value="Aberto" <?= $t['status'] == 'Aberto'?'selected':'' ?>>Aberto</option>
                            <option value="Em Andamento" <?= $t['status'] == 'Em Andamento'?'selected':'' ?>>Em Andamento</option>
                            <option value="Fechado" <?= $t['status'] == 'Fechado'?'selected':'' ?>>Fechado</option>
                        </select>
                        <button type="submit" name="atualizar_status" class="btn-update">Atualizar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</div>

</body>
</html>
