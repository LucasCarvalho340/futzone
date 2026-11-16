<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tituloPagina = $tituloPagina ?? 'FUTZONE';
$logado       = isset($_SESSION['usuario_id']);
$ehAdmin      = ($_SESSION['usuario_tipo'] ?? '') === 'admin';
$qtdCarrinho  = array_sum($_SESSION['carrinho'] ?? []);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/futzone/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark barra-topo">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/futzone/index.php">
            <i class="bi bi-lightning-charge-fill"></i> FUTZONE
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#menuPrincipal" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/futzone/index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="/futzone/produtos.php">Chuteiras</a></li>
                <?php if ($ehAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="/futzone/admin/index.php">
                            <i class="bi bi-gear"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <form class="d-flex me-lg-3 mb-2 mb-lg-0" action="/futzone/produtos.php" method="get">
                <input class="form-control form-control-sm me-2" type="search" name="busca"
                       placeholder="Buscar chuteira...">
                <button class="btn btn-sm btn-outline-light" type="submit" aria-label="Buscar">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <ul class="navbar-nav align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link" href="/futzone/carrinho.php">
                        <i class="bi bi-cart3"></i> Carrinho
                        <?php if ($qtdCarrinho > 0): ?>
                            <span class="badge rounded-pill bg-success"><?= (int) $qtdCarrinho ?></span>
                        <?php endif; ?>
                    </a>
                </li>

                <?php if ($logado): ?>
                    <li class="nav-item">
                        <span class="nav-link disabled text-light">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/futzone/logout.php">Sair</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/futzone/login.php">Entrar</a></li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-verde ms-lg-2" href="/futzone/cadastro.php">Cadastrar</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
