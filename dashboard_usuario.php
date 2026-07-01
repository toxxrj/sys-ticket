<?php
// 1. Ativa a exibição de erros na tela (Se algo der errado, o PHP vai te dizer o porquê em vez de dar tela branca)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'conexao.php';

// 2. Trava de segurança: Se não houver sessão válida, destrói e manda pro login
if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'usuario') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_nome = $_SESSION['user_nome']; 
$sucesso = '';

// Configuração do link manual do WhatsApp
$telefone_admin = "5521984653856"; 
$texto_mensagem = "Olá, meu nome é {$user_nome}. Estou acessando o Painel de Suporte e preciso falar com o Administrador.";
$link_whatsapp = "https://api.whatsapp.com/send?phone={$telefone_admin}&text=" . urlencode($texto_mensagem);

// 3. Processamento do Formulário de Tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_ticket'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $urgencia = isset($_POST['urgencia']) ? $_POST['urgencia'] : 'Baixa'; 
    
    if (!empty($titulo) && !empty($descricao)) {
        $stmt = $pdo->prepare("INSERT INTO tickets (usuario_id, titulo, descricao, urgencia) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $titulo, $descricao, $urgencia]);
        $sucesso = "Chamado aberto com sucesso!";

        // Se for Alta Urgência, abre o WhatsApp em uma nova aba de forma limpa usando JS
        if ($urgencia === 'Alta') {
            $texto_notificacao = "🚨 *CHAMADO DE ALTA URGÊNCIA* 🚨\n\n👤 *Usuário:* {$user_nome}\n📌 *Título:* {$titulo}\n📝 *Descrição:* {$descricao}";
            $url_whatsapp_alta = "https://api.whatsapp.com/send?phone={$telefone_admin}&text=" . urlencode($texto_notificacao);
            
            echo "<script>
                alert('Aviso enviado ao banco! Redirecionando para o WhatsApp do suporte por ser uma emergência.');
                window.open('{$url_whatsapp_alta}', '_blank');
                window.location.href = 'dashboard_usuario.php';
            </script>";
            exit;
        }
    }
}

// 4. Busca os tickets do usuário
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE usuario_id = ? ORDER BY data_criacao DESC");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Tickets - Suporte</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Área do Cliente — <?= htmlspecialchars($user_nome) ?></h2>
        <a href="logout.php" class="btn-logout">Sair do Sistema</a>
    </div>

    <div class="whatsapp-box">
        <div>
            <strong>Precisa de suporte em tempo real?</strong>
            <p>Fale diretamente com o nosso Administrador pelo chat rápido.</p>
        </div>
        <a href="<?= $link_whatsapp ?>" target="_blank" class="btn-whatsapp">
            💬 Iniciar Chat no WhatsApp
        </a>
    </div>

    <?php if ($sucesso): ?>
        <div style="background: #c6f6d5; color: #22543d; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-weight: bold;">
            <?= $sucesso ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h3>Abrir Novo Chamado</h3>
        <form method="POST">
            <label>Título do problema:</label>
            <input type="text" name="titulo" placeholder="Ex: Erro ao carregar relatório de vendas" required>
            
            <label>Nível de Urgência:</label>
            <select name="urgencia" required>
                <option value="Baixa">Baixa (Dúvidas gerais, pequenos ajustes)</option>
                <option value="Média" selected>Média (Problema impede algumas funções)</option>
                <option value="Alta">Alta (Sistema fora do ar, interrupção total)</option>
            </select>

            <label>Descrição detalhada:</label>
            <textarea name="descricao" rows="5" placeholder="Forneça o máximo de detalhes possível sobre o ocorrido..." required></textarea>
            
            <button type="submit" name="criar_ticket" class="btn-success">Enviar Ticket</button>
        </form>
    </div>

    <div class="card">
        <h3>Histórico de Chamados</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Urgência</th>
                    <th>Descrição</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($tickets) === 0): ?>
                    <tr><td colspan="5" style="text-align: center; color: #718096;">Nenhum ticket aberto até o momento.</td></tr>
                <?php else: ?>
                    <?php foreach ($tickets as $t): 
                        $statusClass = str_replace(' ', '-', $t['status']); 
                    ?>
                    <tr>
                        <td data-label="ID">#<?= $t['id'] ?></td>
                        <td data-label="Título"><strong><?= htmlspecialchars($t['titulo']) ?></strong></td>
                        <td data-label="Urgência">
                            <span class="urgencia-badge urgencia-<?= $t['urgencia'] ?>">
                                <?= $t['urgencia'] ?>
                            </span>
                        </td>
                        <td data-label="Descrição"><?= nl2br(htmlspecialchars($t['descricao'])) ?></td>
                        <td data-label="Status">
                            <span class="status-badge status-<?= $statusClass ?>">
                                <?= $t['status'] ?>
                            </span>
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
