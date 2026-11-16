<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Se já está logado, não precisa cadastrar
if (isset($_SESSION['usuario_id'])) {
    header('Location: /futzone/index.php');
    exit;
}

$erros = [];
$nome  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $senha     = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar_senha'] ?? '';

    // ---------- Validações (feitas no servidor) ----------
    if ($nome === '') {
        $erros[] = 'Informe seu nome.';
    }

    if ($email === '') {
        $erros[] = 'Informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'O e-mail informado não é válido.';
    }

    if ($senha === '') {
        $erros[] = 'Informe uma senha.';
    } elseif (strlen($senha) < 6) {
        $erros[] = 'A senha precisa ter pelo menos 6 caracteres.';
    }

    if ($senha !== $confirmar) {
        $erros[] = 'A confirmação de senha não é igual à senha.';
    }

    // ---------- E-mail já existe? ----------
    if (empty($erros)) {
        $pdo = getConexao();

        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado. Tente fazer login.';
        }
    }

    // ---------- Salvar ----------
    if (empty($erros)) {

        // A senha vira um hash. Nunca é salva em texto puro.
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        // O tipo é SEMPRE 'cliente'. Quem se cadastra pelo site nunca vira admin.
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (nome, email, senha, tipo)
             VALUES (:nome, :email, :senha, 'cliente')"
        );
        $stmt->execute([
            ':nome'  => $nome,
            ':email' => $email,
            ':senha' => $hash,
        ]);

        $_SESSION['flash'] = [
            'tipo'  => 'success',
            'texto' => 'Cadastro realizado com sucesso! Faça seu login.',
        ];
        header('Location: /futzone/login.php');
        exit;
    }
}

$tituloPagina = 'Cadastro - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">

            <div class="card card-auth shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h3 fw-bold mb-1">Criar conta</h1>
                    <p class="text-muted">Cadastre-se para comprar na FUTZONE.</p>

                    <?php if (!empty($erros)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= htmlspecialchars($erro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="/futzone/cadastro.php" method="post" novalidate>

                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" id="nome" name="nome" class="form-control"
                                   value="<?= htmlspecialchars($nome) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" id="senha" name="senha" class="form-control"
                                   minlength="6" required>
                            <div class="form-text">Mínimo de 6 caracteres.</div>
                        </div>

                        <div class="mb-4">
                            <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-verde btn-lg w-100">Cadastrar</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Já tem conta? <a href="/futzone/login.php">Entrar</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>