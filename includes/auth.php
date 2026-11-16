<?php
/**
 * Funções de controle de acesso (validadas no servidor).
 */

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Só deixa passar quem está logado
function exigirLogin(string $redirect = ''): void
{
    iniciarSessao();

    if (!isset($_SESSION['usuario_id'])) {
        $extra = $redirect !== '' ? '?redirect=' . urlencode($redirect) : '';
        header('Location: /futzone/login.php' . $extra);
        exit;
    }
}

// Só deixa passar administradores
function exigirAdmin(): void
{
    iniciarSessao();

    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /futzone/login.php');
        exit;
    }

    if (($_SESSION['usuario_tipo'] ?? '') !== 'admin') {
        http_response_code(403);
        die('Acesso negado. Esta área é só para administradores. <a href="/futzone/index.php">Voltar</a>');
    }
}

function mensagemFlash(string $tipo, string $texto): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'texto' => $texto];
}
