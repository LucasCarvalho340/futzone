<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Se já está logado, vai para a página inicial
if (isset($_SESSION['usuario_id'])) {
    header('Location: /futzone/index.php');
    exit;
}

// Para onde ir depois do login.
// Só aceitamos dois destinos fixos, assim ninguém consegue mandar o usuário para outro site.
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$destino  = ($redirect === 'checkout') ? '/futzone/checkout.php' : '/futzone/index.php';

$erro  = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha o e-mail e a senha.';
    } else {
        $pdo = getConexao();

        // Busca o usuário pelo e-mail (prepared statement)
        $stmt = $pdo->prepare('SELECT id, nome, senha, tipo FROM usuarios WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        // password_verify compara a senha digitada com o hash do banco
        if ($usuario && password_verify($senha, $usuario['senha'])) {

            // Gera um novo id de sessão (proteção contra "session fixation")
            session_regenerate_id(true);

            $_SESSION['usuario_id']   = (int) $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['nome'];
            $_SESSION['usuario_tipo'] = $usuario['tipo'];

            header('Location: ' . $destino);
            exit;
        }

        // A mesma mensagem para "e-mail não existe" e "senha errada".
        // Assim ninguém descobre quais e-mails estão cadastrados.
        $erro = 'E-mail ou senha incorretos.';
    }
}

// Mensagem vinda do cadastro (aparece uma vez)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$tituloPagina = 'Entrar - FUTZONE';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">

            <div class="card card-auth shadow-sm">
                <div class="card-body p-4">

                    <h1 class="h3 fw-bold mb-1">Entrar</h1>
                    <p class="text-muted">Acesse sua conta FUTZONE.</p>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?= htmlspecialchars($flash['tipo']) ?>">
                            <?= htmlspecialchars($flash['texto']) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($erro !== ''): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>

                    <form action="/futzone/login.php" method="post">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($email) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" id="senha" name="senha" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-verde btn-lg w-100">Entrar</button>
                    </form>

                    <p class="text-center mt-3 mb-0">
                        Ainda não tem conta? <a href="/futzone/cadastro.php">Cadastre-se</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>