<?php
/**
 * Conexão com o banco de dados.
 * As credenciais NÃO ficam aqui: são lidas do arquivo .env (que o Git ignora).
 */

// Lê o arquivo .env (linhas no formato CHAVE=valor) e guarda em $_ENV
function carregarEnv(string $arquivo): void
{
    if (!is_file($arquivo)) {
        return;
    }

    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        // Ignora comentários e linhas sem "="
        if ($linha === '' || $linha[0] === '#' || strpos($linha, '=') === false) {
            continue;
        }

        [$chave, $valor] = explode('=', $linha, 2);
        $_ENV[trim($chave)] = trim($valor, " \t\"'");
    }
}

function getConexao(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    carregarEnv(__DIR__ . '/../.env');

    $host  = $_ENV['DB_HOST'] ?? 'localhost';
    $banco = $_ENV['DB_NAME'] ?? '';
    $user  = $_ENV['DB_USER'] ?? '';
    $senha = $_ENV['DB_PASS'] ?? '';

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$banco;charset=utf8mb4",
            $user,
            $senha,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    } catch (PDOException $e) {
        // Não mostra detalhes (usuário/host) para o visitante
        die('Erro ao conectar ao banco de dados. Verifique o arquivo .env.');
    }

    return $pdo;
}
