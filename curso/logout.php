<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Apagar todas as variáveis da sessão
$_SESSION = [];

// Destruir os cookies da sessão atual
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir a sessão no servidor
session_destroy();

// Se existir cookie de autenticação longa (como o que configuramos em startusuario), apaga também
if (isset($_COOKIE['startusuario'])) {
    setcookie('startusuario', '', time() - 3600, '/');
}

// Redireciona para fora do /curso (vai para a raiz do site, possivelmente caindo em index.php ou LoginAluno.php)
header('Location: ../');
exit;
