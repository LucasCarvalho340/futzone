<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

exigirAdmin();

$pdo = getConexao();

// =====================================================
// AÇÕES (ativar/desativar e excluir) - só via POST
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $acao = $_POST['acao'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    if ($acao === 'alternar') {

        $stmt = $pdo->prepare('UPDATE produtos SET ativo = 1 - ativo WHERE id = :id');
        $stmt->execute([':id' => $id]);
        mensagemFlash('success', 'Situação do produto atualizada.');

    } elseif ($acao === 'excluir') {

        try {
            $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
            $stmt->execute([':id' => $id]);
            mensagemFlash('success', 'Produto excluído.');
        } catch (PDOException $e) {
            // Produto já vendido não pode ser apagado (chave estrangeira)
            mensagemFlash('warning', 'Este produto já foi vendido e não pode ser excluído. Desative-o.');
        }
    }

    header('Location: /futzone/admin/produtos.php');
    exit;
}

$produtos = $pdo->query(
    'SELECT p.id, p.nome, p.preco, p.estoque, p.ativo, c.nome AS categoria
     FROM produtos p
     INNER JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.id'
)->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$tituloPagina = 'Produtos - Admin FUTZONE';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="titulo-secao mb-0">Produtos</h1>
        <a href="/futzone/admin/produto_form.php" class="btn btn-verde">
            <i class="bi bi-plus-lg"></i> Novo produto
        </a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>">
            <?= htmlspecialchars($flash['texto']) ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th class="text-end">Preço</th>
                    <th class="text-center">Estoque</th>
                    <th class="text-center">Situação</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><?= (int) $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['categoria']) ?></td>
                        <td class="text-end">R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                        <td class="text-center"><?= (int) $p['estoque'] ?></td>
                        <td class="text-center">
                            <?php if ($p['ativo']): ?>
                                <span class="badge bg-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end text-nowrap">
                            <a href="/futzone/admin/produto_form.php?id=<?= (int) $p['id'] ?>"
                               class="btn btn-sm btn-outline-dark">Editar</a>

                            <form action="/futzone/admin/produtos.php" method="post" class="d-inline">
                                <input type="hidden" name="acao" value="alternar">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <?= $p['ativo'] ? 'Desativar' : 'Ativar' ?>
                                </button>
                            </form>

                            <form action="/futzone/admin/produtos.php" method="post" class="d-inline"
                                  onsubmit="return confirm('Excluir este produto?');">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
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

    <a href="/futzone/admin/index.php" class="text-decoration-none">
        <i class="bi bi-arrow-left"></i> Voltar ao painel
    </a>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
