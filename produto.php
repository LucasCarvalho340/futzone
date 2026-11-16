<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConexao();

// 1) Lê o id da URL e converte para número inteiro.
//    Se alguém digitar texto (ex.: ?id=abc), vira 0 e não acha nada.
$id = (int) ($_GET['id'] ?? 0);

// 2) Busca o produto. O valor entra separado do SQL (prepared statement).
$stmt = $pdo->prepare(
    "SELECT p.id, p.nome, p.descricao, p.preco, p.estoque, p.imagem, c.nome AS categoria
     FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id
     WHERE p.id = :id AND p.ativo = 1"
);
$stmt->execute([':id' => $id]);
$produto = $stmt->fetch();

// 3) Define o título da aba
$tituloPagina = $produto ? $produto['nome'] . ' - FUTZONE' : 'Produto não encontrado - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">

<?php if (!$produto): ?>

    <!-- Produto não existe ou está desativado -->
    <div class="alert alert-warning">
        Produto não encontrado.
        <a href="/futzone/produtos.php">Voltar para os produtos</a>.
    </div>

<?php else: ?>

    <?php
    // Só mostra a foto se o arquivo existir em assets/img
    $arquivoImg = __DIR__ . '/assets/img/' . basename($produto['imagem']);
    $temImagem  = is_file($arquivoImg);
    ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/futzone/index.php">Início</a></li>
            <li class="breadcrumb-item"><a href="/futzone/produtos.php">Produtos</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($produto['nome']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">

        <!-- Imagem -->
        <div class="col-12 col-md-6">
            <?php if ($temImagem): ?>
                <img src="/futzone/assets/img/<?= htmlspecialchars(basename($produto['imagem'])) ?>"
                     class="img-fluid rounded shadow-sm imagem-produto"
                     alt="<?= htmlspecialchars($produto['nome']) ?>">
            <?php else: ?>
                <div class="sem-imagem sem-imagem-grande rounded shadow-sm">
                    <i class="bi bi-image"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Informações -->
        <div class="col-12 col-md-6">
            <span class="badge selo-categoria mb-2">
                <?= htmlspecialchars($produto['categoria']) ?>
            </span>

            <h1 class="fw-bold"><?= htmlspecialchars($produto['nome']) ?></h1>

            <p class="preco preco-grande">
                R$ <?= number_format($produto['preco'], 2, ',', '.') ?>
            </p>

            <p class="text-muted"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>

            <?php if ($produto['estoque'] > 0): ?>

                <p class="text-success">
                    <i class="bi bi-check-circle"></i>
                    Disponível: <strong><?= (int) $produto['estoque'] ?></strong> unidade(s) em estoque
                </p>

                <!-- Envia os dados para o carrinho (criaremos o carrinho no próximo passo) -->
                <form action="/futzone/carrinho.php" method="post" class="row g-2 align-items-end">
                    <input type="hidden" name="acao" value="adicionar">
                    <input type="hidden" name="produto_id" value="<?= (int) $produto['id'] ?>">

                    <div class="col-5 col-sm-4">
                        <label for="quantidade" class="form-label">Quantidade</label>
                        <input type="number" id="quantidade" name="quantidade"
                               class="form-control" value="1"
                               min="1" max="<?= (int) $produto['estoque'] ?>" required>
                    </div>

                    <div class="col-7 col-sm-8">
                        <button type="submit" class="btn btn-verde btn-lg w-100">
                            <i class="bi bi-cart-plus"></i> Adicionar ao carrinho
                        </button>
                    </div>
                </form>

            <?php else: ?>

                <p class="text-danger fw-bold">
                    <i class="bi bi-x-circle"></i> Produto esgotado
                </p>
                <button class="btn btn-secondary btn-lg" disabled>Indisponível</button>

            <?php endif; ?>

            <div class="mt-4">
                <a href="/futzone/produtos.php" class="text-decoration-none">
                    <i class="bi bi-arrow-left"></i> Voltar para os produtos
                </a>
            </div>
        </div>

    </div>

<?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>