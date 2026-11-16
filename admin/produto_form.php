<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

exigirAdmin();

$pdo = getConexao();

// Com ?id=  -> editar.  Sem id -> cadastrar.
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY id')->fetchAll();

// Valores iniciais do formulário
$dados = [
    'categoria_id' => $categorias[0]['id'] ?? 0,
    'nome'         => '',
    'descricao'    => '',
    'preco'        => '',
    'estoque'      => 0,
    'imagem'       => '',
    'ativo'        => 1,
];

if ($id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $existente = $stmt->fetch();

    if (!$existente) {
        mensagemFlash('danger', 'Produto não encontrado.');
        header('Location: /futzone/admin/produtos.php');
        exit;
    }
    $dados = $existente;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $dados['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $dados['nome']         = trim($_POST['nome'] ?? '');
    $dados['descricao']    = trim($_POST['descricao'] ?? '');
    $dados['preco']        = trim($_POST['preco'] ?? '');
    $dados['estoque']      = trim($_POST['estoque'] ?? '');
    $dados['imagem']       = basename(trim($_POST['imagem'] ?? ''));
    $dados['ativo']        = isset($_POST['ativo']) ? 1 : 0;

    // ---------- Validações ----------
    if ($dados['nome'] === '') {
        $erros[] = 'Informe o nome do produto.';
    }
    if ($dados['descricao'] === '') {
        $erros[] = 'Informe a descrição.';
    }

    $precoNum = str_replace(',', '.', $dados['preco']);
    if (!is_numeric($precoNum) || (float) $precoNum < 0) {
        $erros[] = 'Preço inválido.';
    }

    if (!ctype_digit((string) $dados['estoque'])) {
        $erros[] = 'O estoque precisa ser um número inteiro (0 ou mais).';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categorias WHERE id = :id');
    $stmt->execute([':id' => $dados['categoria_id']]);
    if ((int) $stmt->fetchColumn() === 0) {
        $erros[] = 'Escolha uma categoria válida.';
    }

    if ($dados['imagem'] === '') {
        $dados['imagem'] = 'sem-imagem.jpg';
    }

    // ---------- Salvar ----------
    if (empty($erros)) {

        $params = [
            ':categoria' => $dados['categoria_id'],
            ':nome'      => $dados['nome'],
            ':descricao' => $dados['descricao'],
            ':preco'     => number_format((float) $precoNum, 2, '.', ''),
            ':estoque'   => (int) $dados['estoque'],
            ':imagem'    => $dados['imagem'],
            ':ativo'     => $dados['ativo'],
        ];

        if ($id > 0) {
            $params[':id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE produtos
                 SET categoria_id = :categoria, nome = :nome, descricao = :descricao,
                     preco = :preco, estoque = :estoque, imagem = :imagem, ativo = :ativo
                 WHERE id = :id'
            );
            $stmt->execute($params);
            mensagemFlash('success', 'Produto atualizado.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem, ativo)
                 VALUES (:categoria, :nome, :descricao, :preco, :estoque, :imagem, :ativo)'
            );
            $stmt->execute($params);
            mensagemFlash('success', 'Produto cadastrado.');
        }

        header('Location: /futzone/admin/produtos.php');
        exit;
    }
}

$tituloPagina = ($id > 0 ? 'Editar' : 'Novo') . ' produto - Admin FUTZONE';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <h1 class="titulo-secao mb-4"><?= $id > 0 ? 'Editar produto' : 'Novo produto' ?></h1>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/futzone/admin/produto_form.php" method="post" class="card shadow-sm p-4">
                <input type="hidden" name="id" value="<?= $id ?>">

                <div class="mb-3">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" id="nome" name="nome" class="form-control" maxlength="120"
                           value="<?= htmlspecialchars($dados['nome']) ?>" required>
                </div>

                <div class="mb-3">
                    <label for="categoria_id" class="form-label">Categoria</label>
                    <select id="categoria_id" name="categoria_id" class="form-select">
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= (int) $c['id'] ?>"
                                <?= (int) $dados['categoria_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="4" class="form-control"
                              required><?= htmlspecialchars($dados['descricao']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="preco" class="form-label">Preço (R$)</label>
                        <input type="text" id="preco" name="preco" class="form-control"
                               value="<?= htmlspecialchars((string) $dados['preco']) ?>" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="estoque" class="form-label">Estoque</label>
                        <input type="number" id="estoque" name="estoque" class="form-control" min="0"
                               value="<?= htmlspecialchars((string) $dados['estoque']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="imagem" class="form-label">Nome do arquivo da imagem</label>
                    <input type="text" id="imagem" name="imagem" class="form-control"
                           placeholder="ex.: nike-mercurial-vapor.jpg"
                           value="<?= htmlspecialchars($dados['imagem']) ?>">
                    <div class="form-text">A imagem precisa estar na pasta assets/img.</div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo"
                           <?= $dados['ativo'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="ativo">Produto ativo (aparece na loja)</label>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-verde">Salvar</button>
                    <a href="/futzone/admin/produtos.php" class="btn btn-outline-dark">Cancelar</a>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
