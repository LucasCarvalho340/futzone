<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// 1) Só usuário logado finaliza compra
exigirLogin('checkout');

// 2) Carrinho vazio? Volta para o carrinho
if (empty($_SESSION['carrinho'])) {
    mensagemFlash('warning', 'Seu carrinho está vazio.');
    header('Location: /futzone/carrinho.php');
    exit;
}

$pdo = getConexao();
$formasValidas = ['PIX', 'Boleto', 'Cartão'];

// =====================================================
// FINALIZAR PEDIDO (quando o formulário é enviado)
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $forma = $_POST['forma_pagamento'] ?? '';

    if (!in_array($forma, $formasValidas, true)) {
        mensagemFlash('danger', 'Escolha uma forma de pagamento.');
        header('Location: /futzone/checkout.php');
        exit;
    }

    // Transação: ou grava TUDO, ou não grava nada
    $pdo->beginTransaction();

    try {
        $total  = 0;
        $linhas = [];

        foreach ($_SESSION['carrinho'] as $produtoId => $qtd) {
            $produtoId = (int) $produtoId;
            $qtd       = (int) $qtd;

            // FOR UPDATE trava a linha para ninguém comprar o mesmo estoque ao mesmo tempo
            $stmt = $pdo->prepare(
                'SELECT id, nome, preco, estoque FROM produtos
                 WHERE id = :id AND ativo = 1 FOR UPDATE'
            );
            $stmt->execute([':id' => $produtoId]);
            $p = $stmt->fetch();

            if (!$p) {
                throw new RuntimeException('Um dos produtos não está mais disponível.');
            }
            if ($qtd < 1 || $qtd > (int) $p['estoque']) {
                throw new RuntimeException('Estoque insuficiente para ' . $p['nome']
                    . ' (restam ' . $p['estoque'] . ').');
            }

            $total   += $p['preco'] * $qtd;
            $linhas[] = ['id' => $p['id'], 'qtd' => $qtd, 'preco' => $p['preco']];
        }

        // 1. Cria o pedido (pagamento simulado: confirmado na hora)
        $stmt = $pdo->prepare(
            'INSERT INTO pedidos (usuario_id, forma_pagamento, total, status)
             VALUES (:usuario, :forma, :total, :status)'
        );
        $stmt->execute([
            ':usuario' => $_SESSION['usuario_id'],
            ':forma'   => $forma,
            ':total'   => $total,
            ':status'  => 'Pago',
        ]);
        $pedidoId = (int) $pdo->lastInsertId();

        $stmtItem = $pdo->prepare(
            'INSERT INTO itens_pedido (pedido_id, produto_id, quantidade, preco_unitario)
             VALUES (:pedido, :produto, :qtd, :preco)'
        );
        $stmtEstoque = $pdo->prepare(
            'UPDATE produtos SET estoque = estoque - :qtd WHERE id = :id'
        );

        foreach ($linhas as $l) {
            // 2. Registra cada item (com o preço congelado do momento)
            $stmtItem->execute([
                ':pedido'  => $pedidoId,
                ':produto' => $l['id'],
                ':qtd'     => $l['qtd'],
                ':preco'   => $l['preco'],
            ]);
            // 3. Baixa o estoque
            $stmtEstoque->execute([':qtd' => $l['qtd'], ':id' => $l['id']]);
        }

        $pdo->commit();

        // 4. Limpa o carrinho
        $_SESSION['carrinho'] = [];

        header('Location: /futzone/pedido_confirmado.php?id=' . $pedidoId);
        exit;

    } catch (RuntimeException $e) {
        $pdo->rollBack();
        mensagemFlash('danger', $e->getMessage());
        header('Location: /futzone/carrinho.php');
        exit;
    } catch (Throwable $e) {
        $pdo->rollBack();
        mensagemFlash('danger', 'Não foi possível finalizar o pedido. Tente novamente.');
        header('Location: /futzone/carrinho.php');
        exit;
    }
}

// =====================================================
// MONTAR O RESUMO PARA MOSTRAR
// =====================================================
$ids        = array_keys($_SESSION['carrinho']);
$marcadores = implode(',', array_fill(0, count($ids), '?'));

$stmt = $pdo->prepare(
    "SELECT id, nome, preco FROM produtos WHERE ativo = 1 AND id IN ($marcadores)"
);
$stmt->execute($ids);

$itens = [];
$total = 0;
foreach ($stmt->fetchAll() as $p) {
    $qtd       = (int) $_SESSION['carrinho'][$p['id']];
    $subtotal  = $p['preco'] * $qtd;
    $total    += $subtotal;
    $itens[]   = ['nome' => $p['nome'], 'preco' => $p['preco'], 'qtd' => $qtd, 'subtotal' => $subtotal];
}

if (empty($itens)) {
    $_SESSION['carrinho'] = [];
    header('Location: /futzone/carrinho.php');
    exit;
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$tituloPagina = 'Finalizar compra - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">

    <h1 class="titulo-secao mb-4">Finalizar compra</h1>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= htmlspecialchars($flash['texto']) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Resumo -->
        <div class="col-12 col-lg-7">
            <h5>Resumo do pedido</h5>
            <div class="table-responsive">
                <table class="table bg-white shadow-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Produto</th>
                            <th class="text-center">Qtd</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nome']) ?></td>
                                <td class="text-center"><?= (int) $item['qtd'] ?></td>
                                <td class="text-end">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-end">Total</th>
                            <th class="text-end">R$ <?= number_format($total, 2, ',', '.') ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Pagamento -->
        <div class="col-12 col-lg-5">
            <h5>Forma de pagamento</h5>
            <form action="/futzone/checkout.php" method="post" class="card shadow-sm p-3">

                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="forma_pagamento"
                           id="pix" value="PIX" required>
                    <label class="form-check-label" for="pix"><i class="bi bi-qr-code"></i> PIX</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="forma_pagamento"
                           id="boleto" value="Boleto">
                    <label class="form-check-label" for="boleto"><i class="bi bi-upc"></i> Boleto</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="forma_pagamento"
                           id="cartao" value="Cartão">
                    <label class="form-check-label" for="cartao"><i class="bi bi-credit-card"></i> Cartão</label>
                </div>

                <p class="small text-muted">Pagamento simulado: será confirmado imediatamente.</p>

                <button type="submit" class="btn btn-verde btn-lg w-100">Confirmar pagamento</button>
                <a href="/futzone/carrinho.php" class="btn btn-link w-100 mt-2">Voltar ao carrinho</a>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
