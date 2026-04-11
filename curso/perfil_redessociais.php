<?php

declare(strict_types=1);

define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__, 2));
define('RAIZ_ROOT', dirname(__DIR__, 1));
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
                imagem200,
                facebook,
                instagram,
                twitter,
                linkdin
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

$pastasc   = trim((string)($dadosUser['pastasc'] ?? ''));
$imagem200 = trim((string)($dadosUser['imagem200'] ?? ''));
$fotoPadraoUrl = '/fotos/usuarios/usuario.png';
$fotoPerfilUrl = $fotoPadraoUrl;

if ($pastasc !== '' && $imagem200 !== '' && $imagem200 !== 'usuario.png' && $imagem200 !== 'usuario.jpg') {
    $arquivoFoto = RAIZ_ROOT . '/fotos/usuarios/' . $pastasc . '/' . $imagem200;
    if (is_file($arquivoFoto)) {
        $fotoPerfilUrl = '/fotos/usuarios/' . rawurlencode($pastasc) . '/' . rawurlencode($imagem200);
    }
}

function socialButton(?string $url, string $icon, string $label, string $id): string
{
    $url = trim((string)$url);
    if ($url === '') {
        return '<span id="' . $id . '" class="btn btn-outline-secondary rounded-pill disabled"><i class="bi ' . $icon . ' me-2"></i>' . $label . '</span>';
    }

    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    return '<a id="' . $id . '" href="' . $safeUrl . '" target="_blank" class="btn btn-outline-primary rounded-pill"><i class="bi ' . $icon . ' me-2"></i>' . $label . '</a>';
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redes Sociais | Professor Eugênio</title>

    <meta name="description" content="Atualize suas redes sociais do perfil.">
    <meta property="og:title" content="Redes Sociais | Professor Eugênio">
    <meta property="og:description" content="Atualize suas redes sociais do perfil.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        .perfil-foto-social {
            width: 112px;
            height: 112px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, .9);
            box-shadow: 0 18px 35px rgba(0, 0, 0, .12);
            background: #f8f9fa;
        }

        .social-card {
            border: 1px solid rgba(13, 110, 253, .10);
            border-radius: 1.25rem;
            background: linear-gradient(180deg, rgba(13, 110, 253, .04), rgba(13, 110, 253, .015));
        }

        .social-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: rgba(13, 110, 253, .10);
            color: var(--bs-primary);
        }

        .toast-container {
            z-index: 1080;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 30px; flex: 1;">
        <div class="mb-5 pb-3 border-bottom d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Painel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Redes Sociais</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-1">Minhas Redes Sociais</h1>
                <p class="text-muted mb-0">Atualize os links do seu perfil para facilitar a conexão com outros usuários.</p>
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
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="social-card p-4 h-100 text-center">
                                <img src="<?= htmlspecialchars($fotoPerfilUrl, ENT_QUOTES, 'UTF-8') ?>?v=<?= time() ?>"
                                    alt="Foto do usuário"
                                    class="perfil-foto-social mb-3">

                                <div class="fw-bold fs-5"><?= $v('nome') !== '' ? $v('nome') : 'Usuário' ?></div>
                                <div class="text-muted small mb-4"><?= $v('email') ?></div>

                                <div class="d-grid gap-2" id="socialLinksPreview">
                                    <?= socialButton($dadosUser['facebook'] ?? '', 'bi-facebook', 'Facebook', 'previewFacebook') ?>
                                    <?= socialButton($dadosUser['instagram'] ?? '', 'bi-instagram', 'Instagram', 'previewInstagram') ?>
                                    <?= socialButton($dadosUser['twitter'] ?? '', 'bi-twitter-x', 'Twitter / X', 'previewTwitter') ?>
                                    <?= socialButton($dadosUser['linkdin'] ?? '', 'bi-linkedin', 'LinkedIn', 'previewLinkdin') ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <form id="formRedesSociais" novalidate>
                                <input type="hidden" name="codigocadastro" value="<?= (int)($dadosUser['codigocadastro'] ?? 0) ?>">

                                <div class="row g-4 mb-4">
                                    <div class="col-12 border-bottom pb-2 mb-2">
                                        <h5 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-share-fill me-2"></i> Links do Perfil
                                        </h5>
                                    </div>

                                    <div class="col-12">
                                        <div class="social-card p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="social-icon-box">
                                                    <i class="bi bi-facebook"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <label for="facebook" class="form-label fw-medium text-dark">Facebook</label>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="facebook"
                                                        name="facebook"
                                                        maxlength="100"
                                                        value="<?= $v('facebook') ?>"
                                                        placeholder="https://facebook.com/seuusuario ou seuusuario">
                                                    <div class="form-text">Aceita link completo ou apenas o nome do perfil.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="social-card p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="social-icon-box">
                                                    <i class="bi bi-instagram"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <label for="instagram" class="form-label fw-medium text-dark">Instagram</label>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="instagram"
                                                        name="instagram"
                                                        maxlength="100"
                                                        value="<?= $v('instagram') ?>"
                                                        placeholder="https://instagram.com/seuusuario ou @seuusuario">
                                                    <div class="form-text">Você pode informar o @usuário ou a URL completa.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="social-card p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="social-icon-box">
                                                    <i class="bi bi-twitter-x"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <label for="twitter" class="form-label fw-medium text-dark">Twitter / X</label>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="twitter"
                                                        name="twitter"
                                                        maxlength="100"
                                                        value="<?= $v('twitter') ?>"
                                                        placeholder="https://twitter.com/seuusuario ou @seuusuario">
                                                    <div class="form-text">Campo salvo na coluna <strong>twitter</strong> da tabela.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="social-card p-3">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="social-icon-box">
                                                    <i class="bi bi-linkedin"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <label for="linkdin" class="form-label fw-medium text-dark">LinkedIn</label>
                                                    <input type="text"
                                                        class="form-control"
                                                        id="linkdin"
                                                        name="linkdin"
                                                        maxlength="100"
                                                        value="<?= $v('linkdin') ?>"
                                                        placeholder="https://linkedin.com/in/seuusuario ou seuusuario">
                                                    <div class="form-text">No banco o campo está como <strong>linkdin</strong>, por isso mantive esse nome.</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-light border rounded-4 small mb-4">
                                    Dica: ao informar apenas o nome do perfil, o sistema monta o link automaticamente no padrão de cada rede.
                                </div>

                                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                                    <button type="reset" class="btn btn-light rounded-pill px-4 fw-medium" id="btnResetRedes">
                                        Descartar
                                    </button>

                                    <button type="submit" class="btn btn-custom-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSalvarRedes">
                                        <span class="label-btn">
                                            <i class="bi bi-floppy me-2"></i> Salvar Alterações
                                        </span>
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
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
                delay: 4000
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

        function atualizarBotaoPreview(id, url, iconClass, label) {
            const el = document.getElementById(id);
            if (!el) return;

            if (url) {
                el.outerHTML = '<a id="' + id + '" href="' + url + '" target="_blank" class="btn btn-outline-primary rounded-pill"><i class="bi ' + iconClass + ' me-2"></i>' + label + '</a>';
            } else {
                el.outerHTML = '<span id="' + id + '" class="btn btn-outline-secondary rounded-pill disabled"><i class="bi ' + iconClass + ' me-2"></i>' + label + '</span>';
            }
        }

        const formRedesSociais = document.getElementById('formRedesSociais');
        const btnSalvarRedes = document.getElementById('btnSalvarRedes');
        const btnResetRedes = document.getElementById('btnResetRedes');

        formRedesSociais.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(formRedesSociais);

            try {
                setLoading(btnSalvarRedes, true);

                const resp = await fetch('componentes/v1/ajax_perfilRedesSociaisUpdate.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await resp.json();

                if (!resp.ok || !data.status) {
                    throw new Error(data.msg || 'Não foi possível salvar as redes sociais.');
                }

                atualizarBotaoPreview('previewFacebook', data.facebook || '', 'bi-facebook', 'Facebook');
                atualizarBotaoPreview('previewInstagram', data.instagram || '', 'bi-instagram', 'Instagram');
                atualizarBotaoPreview('previewTwitter', data.twitter || '', 'bi-twitter-x', 'Twitter / X');
                atualizarBotaoPreview('previewLinkdin', data.linkdin || '', 'bi-linkedin', 'LinkedIn');

                document.getElementById('facebook').value = data.facebook || '';
                document.getElementById('instagram').value = data.instagram || '';
                document.getElementById('twitter').value = data.twitter || '';
                document.getElementById('linkdin').value = data.linkdin || '';

                mostrarToast(data.msg || 'Redes sociais atualizadas com sucesso.', true);
            } catch (error) {
                mostrarToast(error.message || 'Erro ao salvar.', false);
            } finally {
                setLoading(btnSalvarRedes, false);
            }
        });

        btnResetRedes.addEventListener('click', function() {
            setTimeout(() => {
                atualizarBotaoPreview('previewFacebook', <?= json_encode((string)($dadosUser['facebook'] ?? '')) ?>, 'bi-facebook', 'Facebook');
                atualizarBotaoPreview('previewInstagram', <?= json_encode((string)($dadosUser['instagram'] ?? '')) ?>, 'bi-instagram', 'Instagram');
                atualizarBotaoPreview('previewTwitter', <?= json_encode((string)($dadosUser['twitter'] ?? '')) ?>, 'bi-twitter-x', 'Twitter / X');
                atualizarBotaoPreview('previewLinkdin', <?= json_encode((string)($dadosUser['linkdin'] ?? '')) ?>, 'bi-linkedin', 'LinkedIn');
            }, 0);
        });
    </script>
</body>

</html>