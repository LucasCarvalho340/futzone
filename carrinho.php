<?php
session_start();
require_once __DIR__ . '/config/database.php';

$pdo = getConexao();

// Garante que o carrinho existe
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

/**
 * Busca um produto ativo pelo id (prepared statement).
 */
function buscarProdutoAtivo(PDO $pdo, int $id)
{
    $stmt = $pdo->prepare(
        'SELECT id, nome, estoque FROM produtos WHERE id = :id AND ativo = 1'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch();
}

/**
 * Guarda uma mensagem para aparecer após o redirecionamento.
 */
function mensagem(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}

// =====================================================
// PARTE 1: AÇÕES (só acontecem quando o formulário é enviado)
// Tudo é validado aqui no PHP, nunca confiamos só no HTML.
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao       = $_POST['acao'] ?? '';
    $produtoId  = (int) ($_POST['produto_id'] ?? 0);
    $quantidade = (int) ($_POST['quantidade'] ?? 0);

    if ($acao === 'remover') {

        unset($_SESSION['carrinho'][$produtoId]);
        mensagem('success', 'Produto removido do carrinho.');

    } elseif ($acao === 'adicionar' || $acao === 'atualizar') {

        $produto = buscarProdutoAtivo($pdo, $produtoId);

        if (!$produto) {
            mensagem('danger', 'Produto não encontrado.');

        } elseif ($acao === 'adicionar') {

            // Soma com o que já existe no carrinho
            $jaNoCarrinho = $_SESSION['carrinho'][$produtoId] ?? 0;
            $novaQtd      = $jaNoCarrinho + $quantidade;

            if ($quantidade < 1) {
                mensagem('danger', 'Quantidade inválida.');
            } elseif ($novaQtd > $produto['estoque']) {
                mensagem('danger', 'Estoque insuficiente. Só temos ' . $produto['estoque']
                    . ' unidade(s) de ' . $produto['nome'] . '.');
            } else {
                $_SESSION['carrinho'][$produtoId] = $novaQtd;
                mensagem('success', $produto['nome'] . ' adicionado ao carrinho.');
            }

        } else { // atualizar

            if ($quantidade < 1) {
                unset($_SESSION['carrinho'][$produtoId]);
                mensagem('success', 'Produto removido do carrinho.');
            } elseif ($quantidade > $produto['estoque']) {
                $_SESSION['carrinho'][$produtoId] = (int) $produto['estoque'];
                mensagem('warning', 'Só temos ' . $produto['estoque']
                    . ' unidade(s) de ' . $produto['nome'] . '. Ajustamos a quantidade.');
            } else {
                $_SESSION['carrinho'][$produtoId] = $quantidade;
                mensagem('success', 'Quantidade atualizada.');
            }
        }
    }

    // Redireciona para evitar que F5 reenvie o formulário
    header('Location: /futzone/carrinho.php');
    exit;
}

// =====================================================
// PARTE 2: MONTAR OS DADOS PARA MOSTRAR NA TELA
// =====================================================
$itens = [];
$total = 0;

if (!empty($_SESSION['carrinho'])) {

    $ids = array_keys($_SESSION['carrinho']);

    // Cria um "?" para cada id. Só entram "?" no SQL, nunca os valores.
    $marcadores = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare(
        "SELECT id, nome, preco, estoque, imagem
         FROM produtos
         WHERE ativo = 1 AND id IN ($marcadores)"
    );
    $stmt->execute($ids);
    $produtosBanco = $stmt->fetchAll();

    // Remove do carrinho produtos que não existem mais ou foram desativados
    $idsValidos = array_column($produtosBanco, 'id');
    foreach ($ids as $idCarrinho) {
        if (!in_array($idCarrinho, $idsValidos)) {
            unset($_SESSION['carrinho'][$idCarrinho]);
        }
    }

    foreach ($produtosBanco as $p) {
        $qtd      = (int) $_SESSION['carrinho'][$p['id']];
        $subtotal = $p['preco'] * $qtd;
        $total   += $subtotal;

        $itens[] = [
            'id'       => $p['id'],
            'nome'     => $p['nome'],
            'preco'    => $p['preco'],
            'estoque'  => (int) $p['estoque'],
            'imagem'   => $p['imagem'],
            'qtd'      => $qtd,
            'subtotal' => $subtotal,
        ];
    }
}

// Pega a mensagem guardada (e apaga, para aparecer só uma vez)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$tituloPagina = 'Carrinho - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">

    <h1 class="titulo-secao mb-4">Meu carrinho</h1>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= htmlspecialchars($flash['texto']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($itens)): ?>

        <div class="text-center py-5">
            <i class="bi bi-cart-x display-1 text-muted"></i>
            <h4 class="mt-3">Seu carrinho está vazio</h4>
            <a href="/futzone/produtos.php" class="btn btn-verde mt-2">Ver chuteiras</a>
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Produto</th>
                        <th class="text-end">Preço</th>
                        <th class="text-center" style="width: 220px;">Quantidade</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-center">Remover</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itens as $item): ?>
                        <?php
                        $arquivoImg = __DIR__ . '/assets/img/' . basename($item['imagem']);
                        $temImagem  = is_file($arquivoImg);
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($temImagem): ?>
                                        <img src="/futzone/assets/img/<?= htmlspecialchars(basename($item['imagem'])) ?>"
                                             class="mini-img" alt="">
                                    <?php else: ?>
                                        <div class="mini-img mini-sem-imagem"><i class="bi bi-image"></i></div>
                                    <?php endif; ?>
                                    <a href="/futzone/produto.php?id=<?= (int) $item['id'] ?>"
                                       class="text-decoration-none fw-semibold text-dark">
                                        <?= htmlspecialchars($item['nome']) ?>
                                    </a>
                                </div>
                            </td>

                            <td class="text-end">
                                R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                            </td>

                            <td>
                                <!-- Alterar quantidade -->
                                <form action="/futzone/carrinho.php" method="post"
                                      class="d-flex gap-2 justify-content-center">
                                    <input type="hidden" name="acao" value="atualizar">
                                    <input type="hidden" name="produto_id" value="<?= (int) $item['id'] ?>">
                                    <input type="number" name="quantidade" class="form-control form-control-sm"
                                           style="width: 80px;" value="<?= (int) $item['qtd'] ?>"
                                           min="1" max="<?= (int) $item['estoque'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Atualizar</button>
                                </form>
                                <div class="small text-muted text-center mt-1">
                                    Estoque: <?= (int) $item['estoque'] ?>
                                </div>
                            </td>

                            <td class="text-end fw-bold">
                                R$ <?= number_format($item['subtotal'], 2, ',', '.') ?>
                            </td>

                            <td class="text-center">
                                <!-- Remover produto -->
                                <form action="/futzone/carrinho.php" method="post"
                                      onsubmit="return confirm('Remover este produto do carrinho?');">
                                    <input type="hidden" name="acao" value="remover">
                                    <input type="hidden" name="produto_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Total e botões -->
        <div class="row justify-content-between align-items-center mt-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <a href="/futzone/produtos.php" class="btn btn-outline-dark">
                    <i class="bi bi-arrow-left"></i> Continuar comprando
                </a>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1 text-muted">Total geral</p>
                <p class="total-carrinho">R$ <?= number_format($total, 2, ',', '.') ?></p>
                <a href="/futzone/checkout.php" class="btn btn-verde btn-lg">
                    Finalizar compra <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>