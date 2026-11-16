<?php
require_once __DIR__ . '/config/database.php';

$pdo = getConexao();

$totalCategorias = $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn();
$totalProdutos   = $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Teste de conexão - FUTZONE</title>
</head>
<body style="font-family: Arial; max-width: 600px; margin: 40px auto;">
    <h1>FUTZONE - Teste</h1>
    <p>✅ PHP funcionando (versão <?= PHP_VERSION ?>)</p>
    <p>✅ Conectado ao banco com sucesso!</p>
    <p>Categorias: <strong><?= (int) $totalCategorias ?></strong></p>
    <p>Produtos: <strong><?= (int) $totalProdutos ?></strong></p>
</body>
</html>