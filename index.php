<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConexao();

// Busca as categorias no banco (consulta fixa, sem dados do usuário)
$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY id')->fetchAll();

// Ícone de cada categoria (Bootstrap Icons)
$icones = [
    'Campo'    => 'bi-trophy',
    'Society'  => 'bi-lightning-charge',
    'Futsal'   => 'bi-bullseye',
    'Infantil' => 'bi-emoji-smile',
];

$tituloPagina = 'FUTZONE - Chuteiras para quem vive o jogo';
require_once __DIR__ . '/includes/header.php';
?>

<!-- BANNER PRINCIPAL -->
<section class="hero">
    <div class="container">
        <h1>Chuteiras para<br>quem <span>vive o jogo.</span></h1>
        <p class="mt-3 mb-4">
            Campo, society, futsal e infantil. Encontre a chuteira perfeita para o seu estilo de jogo.
        </p>
        <a href="/futzone/produtos.php" class="btn btn-verde btn-lg me-2">Ver chuteiras</a>
        <a href="#categorias" class="btn btn-outline-claro btn-lg">Categorias</a>
    </div>
</section>

<!-- CATEGORIAS -->
<section class="container mt-5" id="categorias">
    <h2 class="titulo-secao mb-4">Categorias</h2>

    <div class="row g-4">
        <?php foreach ($categorias as $cat): ?>
            <div class="col-6 col-md-3">
                <a href="/futzone/produtos.php?categoria=<?= (int) $cat['id'] ?>"
                   class="card card-categoria shadow-sm text-center p-4 h-100">
                    <div class="icone mb-2">
                        <i class="bi <?= $icones[$cat['nome']] ?? 'bi-star' ?>"></i>
                    </div>
                    <h5 class="mb-0"><?= htmlspecialchars($cat['nome']) ?></h5>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- CHAMADA PARA A LOJA -->
<section class="container mt-5">
    <div class="chamada-loja">
        <h2>Conheça a loja FUTZONE</h2>
        <p class="mb-4">Modelos para todas as posições e todos os tipos de piso.</p>
        <a href="/futzone/produtos.php" class="btn btn-verde btn-lg">Explorar todos os produtos</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>