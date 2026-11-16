<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

exigirAdmin();

$pdo = getConexao();

// Todos os pedidos, com o nome do cliente
$pedidos = $pdo->query(
    'SELECT p.id, p.data_pedido, p.forma_pagamento, p.total, p.status, u.nome AS cliente
     FROM pedidos p
     INNER JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.id DESC'
)->fetchAll();

// Todos os itens, agrupados por pedido
$itensPorPedido = [];
$linhas = $pdo->query(
    'SELECT i.pedido_id, pr.nome, i.quantidade, i.preco_unitario
     FROM itens_pedido i
     INNER JOIN produtos pr ON pr.id = i.produto_id
     ORDER BY i.id'
)->fetchAll();

foreach ($linhas as $l) {
    $itensPorPedido[$l['pedido_id']][] = $l;
}

$tituloPagina = 'Pedidos - Admin FUTZONE';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4 mb-5">

    <h1 class="titulo-secao mb-4">Pedidos realizados</h1>

    <?php if (empty($pedidos)): ?>
        <div class="alert alert-info">Nenhum pedido realizado ainda.</div>
    <?php endif; ?>

    <?php foreach ($pedidos as $p): ?>
        <div class="card shadow-sm mb-3">
            <div class="card-header d-flex flex-wrap justify-content-between gap-2">
                <strong>Pedido #<?= (int) $p['id'] ?></strong>
                <span><?= htmlspecialchars($p['cliente']) ?></span>
                <span><?= date('d/m/Y H:i', strtotime($p['data_pedido'])) ?></span>
                <span><?= htmlspecialchars($p['forma_pagamento']) ?></span>
                <span class="badge bg-success"><?= htmlspecialchars($p['status']) ?></span>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-2">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-end">Preço unit.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itensPorPedido[$p['id']] ?? [] as $i): ?>
                            <tr>
                                <td><?= htmlspecialchars($i['nome']) ?></td>
                                <td class="text-center"><?= (int) $i['quantidade'] ?></td>
                                <td class="text-end">R$ <?= number_format($i['preco_unitario'], 2, ',', '.') ?></td>
                                <td class="text-end">R$ <?= number_format($i['preco_unitario'] * $i['quantidade'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-end fw-bold">
                    Total: R$ <?= number_format($p['total'], 2, ',', '.') ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <a href="/futzone/admin/index.php" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Voltar ao painel
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
