<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConexao();

// 1) Lê o que veio pela URL (busca e categoria)
$busca       = trim($_GET['busca'] ?? '');
$categoriaId = (int) ($_GET['categoria'] ?? 0);

// 2) Categorias para os botões de filtro (consulta fixa)
$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY id')->fetchAll();

// 3) Monta a consulta de produtos.
//    O texto do SQL é fixo. Os valores digitados pelo usuário
//    NUNCA entram no SQL: vão separados, pelos parâmetros (:busca, :categoria).
//    Isso protege contra SQL Injection.
$sql = "SELECT p.id, p.nome, p.preco, p.estoque, p.imagem, c.nome AS categoria
        FROM produtos p
        INNER JOIN categorias c ON c.id = p.categoria_id
        WHERE p.ativo = 1";
$params = [];

if ($busca !== '') {
    $sql .= " AND p.nome LIKE :busca";
    $params[':busca'] = '%' . $busca . '%';
}

if ($categoriaId > 0) {
    $sql .= " AND p.categoria_id = :categoria";
    $params[':categoria'] = $categoriaId;
}

$sql .= " ORDER BY p.nome";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

$tituloPagina = 'Produtos - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">

    <h1 class="titulo-secao mb-4">Chuteiras</h1>

    <!-- Busca por nome -->
    <form class="row g-2 mb-3" action="/futzone/produtos.php" method="get">
        <div class="col-12 col-md-8">
            <input type="text" name="busca" class="form-control"
                   placeholder="Digite o nome da chuteira..."
                   value="<?= htmlspecialchars($busca) ?>">
        </div>
        <?php if ($categoriaId > 0): ?>
            <!-- Mantém a categoria escolhida ao buscar -->
            <input type="hidden" name="categoria" value="<?= $categoriaId ?>">
        <?php endif; ?>
        <div class="col-12 col-md-4">
            <button type="submit" class="btn btn-verde w-100">
                <i class="bi bi-search"></i> Buscar
            </button>
        </div>
    </form>

    <!-- Filtro por categoria -->
    <div class="mb-4 d-flex flex-wrap gap-2">
        <a href="/futzone/produtos.php<?= $busca !== '' ? '?busca=' . urlencode($busca) : '' ?>"
           class="btn btn-sm <?= $categoriaId === 0 ? 'btn-verde' : 'btn-outline-dark' ?>">
            Todas
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="/futzone/produtos.php?categoria=<?= (int) $cat['id'] ?><?= $busca !== '' ? '&busca=' . urlencode($busca) : '' ?>"
               class="btn btn-sm <?= $categoriaId === (int) $cat['id'] ? 'btn-verde' : 'btn-outline-dark' ?>">
                <?= htmlspecialchars($cat['nome']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Lista de produtos -->
    <?php if (empty($produtos)): ?>
        <div class="alert alert-warning">
            Nenhuma chuteira encontrada. Tente outra busca ou veja
            <a href="/futzone/produtos.php">todos os produtos</a>.
        </div>
    <?php else: ?>
        <p class="text-muted"><?= count($produtos) ?> produto(s) encontrado(s)</p>

        <div class="row g-4">
            <?php foreach ($produtos as $p): ?>
                <?php
                // Só mostra a foto se o arquivo existir em assets/img
                $arquivoImg = __DIR__ . '/assets/img/' . basename($p['imagem']);
                $temImagem  = is_file($arquivoImg);
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card card-produto h-100 shadow-sm">

                        <?php if ($temImagem): ?>
                            <img src="/futzone/assets/img/<?= htmlspecialchars(basename($p['imagem'])) ?>"
                                 class="card-img-top"
                                 alt="<?= htmlspecialchars($p['nome']) ?>">
                        <?php else: ?>
                            <div class="sem-imagem">
                                <i class="bi bi-image"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <span class="badge selo-categoria align-self-start mb-2">
                                <?= htmlspecialchars($p['categoria']) ?>
                            </span>
                            <h5 class="card-title"><?= htmlspecialchars($p['nome']) ?></h5>
                            <p class="preco mb-1">R$ <?= number_format($p['preco'], 2, ',', '.') ?></p>

                            <?php if ($p['estoque'] > 0): ?>
                                <p class="small text-success mb-3">
                                    <i class="bi bi-check-circle"></i> Em estoque (<?= (int) $p['estoque'] ?>)
                                </p>
                            <?php else: ?>
                                <p class="small text-danger mb-3">
                                    <i class="bi bi-x-circle"></i> Esgotado
                                </p>
                            <?php endif; ?>

                            <a href="/futzone/produto.php?id=<?= (int) $p['id'] ?>"
                               class="btn btn-verde mt-auto">Ver produto</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>+