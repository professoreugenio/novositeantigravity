<?php

declare(strict_types=1);

define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
define('RAIZ_ROOT', dirname(__DIR__, 1));
define('APP_ROOT', dirname(__DIR__, 2));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');

date_default_timezone_set('America/Fortaleza');
header('Content-Type: text/html; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryUsuario.php';

$codigoUsuarioAtual = (int)($codigoUser ?? $userCod ?? 0);

$dadosUser = [];
if (isset($con) && $con instanceof PDO && $codigoUsuarioAtual > 0) {
    try {
        $st = $con->prepare("
            SELECT 
                codigocadastro,
                nome,
                email,
                pastasc,
                imagem50,
                imagem200
            FROM new_sistema_cadastro
            WHERE codigocadastro = :cod
            LIMIT 1
        ");
        $st->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
        $st->execute();
        $dadosUser = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $dadosUser = [];
    }
}

$v = fn(string $k): string => htmlspecialchars((string)($dadosUser[$k] ?? ''), ENT_QUOTES, 'UTF-8');

$paginaAtual = basename($_SERVER['PHP_SELF']);
$pastasc     = trim((string)($dadosUser['pastasc'] ?? ''));
$imagem50    = trim((string)($dadosUser['imagem50'] ?? 'usuario.jpg'));
$imagem200   = trim((string)($dadosUser['imagem200'] ?? 'usuario.jpg'));

$fotoPadraoUrl = '/fotos/usuarios/usuario.png';

$foto200Url = $fotoPadraoUrl;
if ($pastasc !== '' && $imagem200 !== '') {
    $arquivo200 = RAIZ_ROOT . '/fotos/usuarios/' . $pastasc . '/' . $imagem200;
    if (is_file($arquivo200)) {
        $foto200Url = '/fotos/usuarios/' . rawurlencode($pastasc) . '/' . rawurlencode($imagem200);
    }
}

$foto50Url = $fotoPadraoUrl;
if ($pastasc !== '' && $imagem50 !== '') {
    $arquivo50 = RAIZ_ROOT . '/fotos/usuarios/' . $pastasc . '/' . $imagem50;
    if (is_file($arquivo50)) {
        $foto50Url = '/fotos/usuarios/' . rawurlencode($pastasc) . '/' . rawurlencode($imagem50);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mascote | Professor Eugênio</title>

    <meta name="description" content="Atualize sua foto de perfil com geração automática das versões 50 e 200.">
    <meta property="og:title" content="Atualizar Foto | Professor Eugênio">
    <meta property="og:description" content="Atualize sua foto de perfil com geração automática das versões 50 e 200.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= htmlspecialchars($foto200Url, ENT_QUOTES, 'UTF-8') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../assets/css/styles.css">


</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 30px; flex: 1;">
        <div class="mb-5 pb-3 border-bottom d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Painel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mascote</li>
                    </ol>
                </nav>


                <p class="text-muted mb-0">Personalize seu mascote e mostre-o para os outros usuários.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>

        <div class="row g-4" id="configuracoes">

            <?php require 'componentes/v1/nav_perfil.php' ?>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 custom-card p-4 p-md-5 bg-body mb-4">
                    <div class="row g-4 align-items-start">
                        <div class="col-lg-12">
                            Conteúdo aqui
                        </div>
                    </div>
                </div>
            </div>
    </main>

    <footer class="bg-body-tertiary py-4 border-top mt-auto">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; 2026 Professor Eugênio</p>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Apoio</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Termos</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Privacidade</a></li>
            </ul>
        </div>
    </footer>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastMsg" class="toast align-items-center border-0 text-bg-dark" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsgBody">Mensagem</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/temaToggle.js"></script>
</body>

</html>