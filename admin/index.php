<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

exigirAdmin();

$pdo = getConexao();

$totalProdutos = (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
$totalPedidos  = (int) $pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
$faturamento   = (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE status <> 'Cancelado'")->fetchColumn();

$tituloPagina = 'Painel Admin - FUTZONE';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <h1 class="titulo-secao mb-4">Painel administrativo</h1>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card shadow-sm p-3 text-center">
                <div class="text-muted">Produtos</div>
                <div class="display-6 fw-bold"><?= $totalProdutos ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm p-3 text-center">
                <div class="text-muted">Pedidos</div>
                <div class="display-6 fw-bold"><?= $totalPedidos ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card shadow-sm p-3 text-center">
                <div class="text-muted">Faturamento</div>
                <div class="display-6 fw-bold">R$ <?= number_format($faturamento, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <a href="/futzone/admin/produtos.php" class="btn btn-verde btn-lg me-2">
        <i class="bi bi-box-seam"></i> Gerenciar produtos
    </a>
    <a href="/futzone/admin/pedidos.php" class="btn btn-outline-dark btn-lg">
        <i class="bi bi-receipt"></i> Ver pedidos
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
