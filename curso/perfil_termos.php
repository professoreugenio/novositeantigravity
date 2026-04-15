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
    <title>Termos do site | Professor Eugênio</title>

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
                        <li class="breadcrumb-item active" aria-current="page">Termos do site</li>
                    </ol>
                </nav>
               
                
                <p class="text-muted mb-0">Leia e compreenda os termos e condições de uso do nosso site.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>

        <div class="row g-4" id="configuracoes">

            <div class="col-lg-3">
                <div class="perfil-menu-lateral">
                    <div class="perfil-menu-titulo">Meu Perfil</div>

                    <div class="d-grid gap-2">
                        <a href="perfil_Configuracoes.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_Configuracoes.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-person-gear"></i></span>
                            <span>Editar Perfil</span>
                        </a>

                        <a href="perfil_fotos.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_fotos.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-image"></i></span>
                            <span>Atualizar foto</span>
                        </a>

                        <a href="perfil_redessociais.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_redessociais.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-share"></i></span>
                            <span>Redes sociais</span>
                        </a>

                        <a href="perfil_ranking.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_ranking.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-trophy"></i></span>
                            <span>Meu Ranking</span>
                        </a>

                        <a href="perfil_mascote.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_mascote.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-emoji-smile"></i></span>
                            <span>Mascote</span>
                        </a>

                        <a href="perfil_termos.php"
                            class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_termos.php' ? 'active' : '' ?>">
                            <span class="perfil-menu-icone"><i class="bi bi-file-text"></i></span>
                            <span>Termos do site</span>
                        </a>
                    </div>
                </div>
            </div>

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

    <script>
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);

        if (savedTheme === 'dark' && sunIcon && moonIcon) {
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                if (newTheme === 'dark') {
                    if (sunIcon) sunIcon.classList.add('d-none');
                    if (moonIcon) moonIcon.classList.remove('d-none');
                } else {
                    if (sunIcon) sunIcon.classList.remove('d-none');
                    if (moonIcon) moonIcon.classList.add('d-none');
                }
            });
        }

        function mostrarToast(msg, sucesso = true) {
            const el = document.getElementById('toastMsg');
            const body = document.getElementById('toastMsgBody');

            el.classList.remove('text-bg-dark', 'text-bg-success', 'text-bg-danger');
            el.classList.add(sucesso ? 'text-bg-success' : 'text-bg-danger');
            body.textContent = msg;

            const toast = new bootstrap.Toast(el, {
                delay: 4500
            });
            toast.show();
        }

        function setLoading(btn, status) {
            const spinner = btn.querySelector('.spinner-border');
            const label = btn.querySelector('.label-btn');

            btn.disabled = status;

            if (status) {
                spinner.classList.remove('d-none');
                label.classList.add('d-none');
            } else {
                spinner.classList.add('d-none');
                label.classList.remove('d-none');
            }
        }

        const formUploadFoto = document.getElementById('formUploadFoto');
        const inputFoto = document.getElementById('fotoPerfil');
        const btnEnviarFoto = document.getElementById('btnEnviarFoto');
        const btnResetFoto = document.getElementById('btnResetFoto');
        const boxPreviewNova = document.getElementById('boxPreviewNova');
        const previewNovaFoto = document.getElementById('previewNovaFoto');
        const boxProgresso = document.getElementById('boxProgresso');
        const barraUpload = document.getElementById('barraUpload');
        const textoPercentual = document.getElementById('textoPercentual');
        const fotoAtualGrande = document.getElementById('fotoAtualGrande');
        const fotoAtualMini = document.getElementById('fotoAtualMini');

        inputFoto.addEventListener('change', function() {
            const file = this.files && this.files[0] ? this.files[0] : null;

            if (!file) {
                boxPreviewNova.classList.add('d-none');
                previewNovaFoto.src = '/img/usuario.jpg';
                return;
            }

            const permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!permitidos.includes(file.type)) {
                this.value = '';
                boxPreviewNova.classList.add('d-none');
                mostrarToast('Selecione uma imagem JPG, PNG, WEBP ou GIF.', false);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewNovaFoto.src = e.target.result;
                boxPreviewNova.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });

        btnResetFoto.addEventListener('click', function() {
            boxPreviewNova.classList.add('d-none');
            boxProgresso.classList.add('d-none');
            barraUpload.style.width = '0%';
            barraUpload.setAttribute('aria-valuenow', '0');
            textoPercentual.textContent = '0%';
            previewNovaFoto.src = '/img/usuario.jpg';
        });

        formUploadFoto.addEventListener('submit', function(e) {
            e.preventDefault();

            const file = inputFoto.files && inputFoto.files[0] ? inputFoto.files[0] : null;
            if (!file) {
                mostrarToast('Selecione uma imagem antes de enviar.', false);
                return;
            }

            const formData = new FormData(formUploadFoto);
            const xhr = new XMLHttpRequest();

            xhr.open('POST', 'componentes/v1/ajax_perfilFotoUpload.php', true);
            xhr.responseType = 'json';

            xhr.upload.addEventListener('progress', function(event) {
                if (event.lengthComputable) {
                    const percent = Math.round((event.loaded / event.total) * 100);
                    boxProgresso.classList.remove('d-none');
                    barraUpload.style.width = percent + '%';
                    barraUpload.setAttribute('aria-valuenow', String(percent));
                    textoPercentual.textContent = percent + '%';
                }
            });

            xhr.onloadstart = function() {
                setLoading(btnEnviarFoto, true);
                boxProgresso.classList.remove('d-none');
                barraUpload.style.width = '0%';
                textoPercentual.textContent = '0%';
            };

            xhr.onerror = function() {
                setLoading(btnEnviarFoto, false);
                mostrarToast('Erro de comunicação ao enviar a imagem.', false);
            };

            xhr.onload = function() {
                setLoading(btnEnviarFoto, false);

                const data = xhr.response || {};

                if (xhr.status !== 200 || !data.status) {
                    mostrarToast(data.msg || 'Não foi possível atualizar a foto.', false);
                    return;
                }

                barraUpload.style.width = '100%';
                textoPercentual.textContent = '100%';

                const cache = '?v=' + Date.now();

                if (data.url_imagem200) {
                    fotoAtualGrande.src = data.url_imagem200 + cache;
                }

                if (data.url_imagem50) {
                    fotoAtualMini.src = data.url_imagem50 + cache;
                }

                mostrarToast(data.msg || 'Foto atualizada com sucesso.', true);
                formUploadFoto.reset();
                boxPreviewNova.classList.add('d-none');
            };

            xhr.send(formData);
        });
    </script>
</body>

</html>