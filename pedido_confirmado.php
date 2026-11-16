<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

exigirLogin();

$pdo      = getConexao();
$pedidoId = (int) ($_GET['id'] ?? 0);

// O cliente só enxerga os PRÓPRIOS pedidos
$stmt = $pdo->prepare(
    'SELECT id, data_pedido, forma_pagamento, total, status
     FROM pedidos WHERE id = :id AND usuario_id = :usuario'
);
$stmt->execute([':id' => $pedidoId, ':usuario' => $_SESSION['usuario_id']]);
$pedido = $stmt->fetch();

$itens = [];
if ($pedido) {
    $stmt = $pdo->prepare(
        'SELECT p.nome, i.quantidade, i.preco_unitario
         FROM itens_pedido i
         INNER JOIN produtos p ON p.id = i.produto_id
         WHERE i.pedido_id = :id'
    );
    $stmt->execute([':id' => $pedidoId]);
    $itens = $stmt->fetchAll();
}

$tituloPagina = 'Pedido confirmado - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">

<?php if (!$pedido): ?>

    <div class="alert alert-warning">
        Pedido não encontrado. <a href="/futzone/index.php">Voltar ao início</a>.
    </div>

<?php else: ?>

    <div class="text-center py-4">
        <i class="bi bi-check-circle-fill text-success display-1"></i>
        <h1 class="titulo-secao mt-3">Compra realizada com sucesso!</h1>
        <p class="text-muted">
            Pedido #<?= (int) $pedido['id'] ?> ·
            <?= htmlspecialchars($pedido['forma_pagamento']) ?> ·
            Status: <strong><?= htmlspecialchars($pedido['status']) ?></strong>
        </p>
    </div>

    <div class="table-responsive">
        <table class="table bg-white shadow-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-end">Preço unit.</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['nome']) ?></td>
                        <td class="text-center"><?= (int) $i['quantidade'] ?></td>
                        <td class="text-end">R$ <?= number_format($i['preco_unitario'], 2, ',', '.') ?></td>
                        <td class="text-end">R$ <?= number_format($i['preco_unitario'] * $i['quantidade'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th class="text-end">R$ <?= number_format($pedido['total'], 2, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="text-center mt-4">
        <a href="/futzone/produtos.php" class="btn btn-verde">Continuar comprando</a>
    </div>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
