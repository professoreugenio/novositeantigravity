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
require_once PUBLIC_ROOT . '/componentes/v1/QueryCurso.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryModulo.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryPublicacao.php';


$idAlunoLogado = (int)($codigoUser ?? $codigoUsuario ?? $codigousuario ?? $userCod ?? 0);
$nomeAlunoLogado = trim((string)($nome ?? $nomeUsuario ?? $_SESSION['nome'] ?? 'Aluno'));
$pastaAlunoLogado = trim((string)($pastasc ?? $_SESSION['pastasc'] ?? ''));
$imagemAlunoLogado = trim((string)($imagem50 ?? $_SESSION['imagem50'] ?? 'usuario.png'));

$idPublicacaoAtual = (int)($idPublicacaoAtiva ?? $idpublicacao ?? $codigopublicacoes ?? $_GET['idpublicacao'] ?? 0);
$idModuloAtual = (int)($idModulo ?? $idmodulo ?? $codigomodulos ?? $idModuloAtivo ?? 0);

$nomeTurmaAtual = trim((string)($nometurma ?? $nomeTurma ?? $_SESSION['nometurma'] ?? ''));
$nomeCursoAtual = trim((string)($nomeCurso ?? 'Curso'));
$isProfessor = !empty($_SESSION['admin_logado']);

$fotoTopo = '/fotos/usuarios/usuario.png';
if ($pastaAlunoLogado !== '' && $imagemAlunoLogado !== '') {
    $fotoTopo = '/fotos/usuarios/' . rawurlencode($pastaAlunoLogado) . '/' . rawurlencode($imagemAlunoLogado);
}

function e(string $txt): string
{
    return htmlspecialchars($txt, ENT_QUOTES, 'UTF-8');
}

$tituloSeo = 'Printes da Atividade | Professor Eugênio';
$descricaoSeo = 'Envie seus printes da atividade para avaliação do professor.';
$urlAtual = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'professoreugenio.com') . ($_SERVER['REQUEST_URI'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloSeo) ?></title>
    <meta name="description" content="<?= e($descricaoSeo) ?>">

    <!-- Social Share -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($tituloSeo) ?>">
    <meta property="og:description" content="<?= e($descricaoSeo) ?>">
    <meta property="og:url" content="<?= e($urlAtual) ?>">
    <meta property="og:image" content="https://professoreugenio.com/img/logo.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($tituloSeo) ?>">
    <meta name="twitter:description" content="<?= e($descricaoSeo) ?>">
    <meta name="twitter:image" content="https://professoreugenio.com/img/logo.png">

    <link rel="icon" href="https://professoreugenio.com/favicon.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        :root {
            --print-accent: #00BB9C;
            --print-accent-2: #FF9C00;
            --print-dark: #112240;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .hero-printes {
            border: 0;
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(0, 187, 156, .12), rgba(255, 156, 0, .12));
            box-shadow: 0 18px 60px rgba(0, 0, 0, .08);
        }

        .hero-printes .badge-soft {
            background: rgba(255, 255, 255, .75);
            border: 1px solid rgba(0, 0, 0, .06);
            color: var(--print-dark);
            backdrop-filter: blur(6px);
        }

        .btn-print-primary {
            border: 0;
            color: #fff;
            font-weight: 700;
            border-radius: 14px;
            padding: 12px 18px;
            background: linear-gradient(135deg, var(--print-accent), #0e9f89);
            box-shadow: 0 12px 30px rgba(0, 187, 156, .25);
        }

        .btn-print-primary:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .print-card {
            border: 0;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .08);
            height: 100%;
        }

        .print-header {
            background: linear-gradient(135deg, rgba(17, 34, 64, .98), rgba(30, 58, 138, .95));
            color: #fff;
            padding: 14px 16px;
        }

        .print-user-photo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .35);
            background: #fff;
        }

        .print-media-wrap {
            padding: 14px;
            background: rgba(0, 0, 0, .02);
        }

        .print-image-box {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: #f8f9fa;
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-image-box img {
            width: 100%;
            height: 270px;
            object-fit: cover;
            display: block;
            cursor: zoom-in;
        }

        .print-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 14px;
        }

        .rating-box {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(255, 156, 0, .10);
        }

        .rating-star {
            border: 0;
            background: transparent;
            font-size: 1.25rem;
            line-height: 1;
            color: #c5c7cf;
            cursor: default;
            padding: 0;
        }

        .rating-star.active {
            color: #ffb400;
        }

        .rating-star.can-rate {
            cursor: pointer;
            transition: transform .15s ease;
        }

        .rating-star.can-rate:hover {
            transform: scale(1.12);
        }

        .comments-col {
            border-left: 1px solid rgba(0, 0, 0, .08);
            background: rgba(0, 0, 0, .015);
        }

        

        .comments-wrap {
            padding: 14px;
            display: flex;
            flex-direction: column;
            height: 100%;
            max-height: 330px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .comments-list {
            flex: 1;
            max-height: 360px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .comment-item {
            border-radius: 14px;
            padding: 10px 12px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .06);
            margin-bottom: 10px;
        }

        .comment-meta {
            font-size: .78rem;
            color: #6c757d;
            margin-bottom: 6px;
        }

        .comment-textarea {
            min-height: 100px;
            resize: none;
            border-radius: 14px;
        }

        .empty-state {
            border: 1px dashed rgba(0, 0, 0, .12);
            border-radius: 22px;
            padding: 32px 20px;
            text-align: center;
            background: rgba(255, 255, 255, .65);
        }

        .upload-zone {
            border: 2px dashed rgba(0, 187, 156, .28);
            border-radius: 18px;
            padding: 24px;
            text-align: center;
            background: rgba(0, 187, 156, .06);
        }

        .progress {
            height: 14px;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-bar {
            font-weight: 700;
            background: linear-gradient(90deg, var(--print-accent), var(--print-accent-2));
        }

        .modal-lightbox .modal-dialog {
            max-width: 80vw;
        }

        .modal-lightbox .modal-content {
            background: rgba(17, 34, 64, .98);
            border: 0;
            border-radius: 24px;
        }

        .modal-lightbox img {
            width: 100%;
            max-height: 80vh;
            object-fit: contain;
            display: block;
            border-radius: 18px;
        }

        [data-bs-theme="dark"] .hero-printes {
            background: linear-gradient(135deg, rgba(13, 148, 136, .18), rgba(255, 156, 0, .14));
        }

        [data-bs-theme="dark"] .badge-soft {
            background: rgba(17, 24, 39, .55);
            border-color: rgba(255, 255, 255, .08);
            color: #f8fafc;
        }

        [data-bs-theme="dark"] .print-card,
        [data-bs-theme="dark"] .comment-item,
        [data-bs-theme="dark"] .empty-state {
            background: #18253f;
            color: #e5e7eb;
            border-color: rgba(255, 255, 255, .08);
        }

        [data-bs-theme="dark"] .print-media-wrap,
        [data-bs-theme="dark"] .comments-col {
            background: rgba(255, 255, 255, .02);
        }

        [data-bs-theme="dark"] .comments-col {
            border-left-color: rgba(255, 255, 255, .08);
        }

        [data-bs-theme="dark"] .comment-item {
            background: #112240;
        }

        [data-bs-theme="dark"] .comment-meta,
        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        .fixed-upload-btn {
            position: fixed;
            top: 150px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
        }

        .fixed-upload-btn .btn {
            border-radius: 999px;
            padding: 14px 24px;
            white-space: nowrap;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }

        @media (max-width: 991.98px) {
            .comments-col {
                border-left: 0;
                border-top: 1px solid rgba(0, 0, 0, .08);
            }

            [data-bs-theme="dark"] .comments-col {
                border-top-color: rgba(255, 255, 255, .08);
            }

            .print-image-box img {
                height: 270px;
            }

            .modal-lightbox .modal-dialog {
                max-width: 95vw;
            }
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">


    <div class="fixed-upload-btn">
        <button type="button" class="btn btn-print-primary btn-lg shadow-lg" data-bs-toggle="modal" data-bs-target="#modalUploadPrint">
            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Enviar print
        </button>
    </div>


    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 20px; flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3 mt-4 flex-wrap gap-3">
            <div>
                <h3 class="page-title fw-bold mb-1">Área de envio das atividades</h3>

                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Meus Cursos</a></li>
                            <li class="breadcrumb-item">
                                <a href="modulos.php" class="text-decoration-none border-bottom border-primary">
                                    <?= e($nomeCursoAtual) ?>
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <?= e((string)($ModuloNome ?? 'Módulo')) ?>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="aula.php" class="text-decoration-none border-bottom border-primary">
                                    <?= isset($PubTitulo) ? (mb_strlen($PubTitulo, 'UTF-8') > 20 ? e(mb_substr($PubTitulo, 0, 20, 'UTF-8')) . '...' : e((string)$PubTitulo)) : 'Aula' ?>
                                </a>
                            </li>
                        </ol>
                    </nav>
                </div>


            </div>

            <div class="text-muted small border bg-white rounded-pill px-3 py-1 shadow-sm">
                <?= e((string)($userTempoRestante ?? '')) ?>
            </div>
        </div>



        <input type="hidden" id="idpublicacao" value="<?= (int)$idPublicacaoAtual ?>">
        <input type="hidden" id="idmodulo" value="<?= (int)$idModuloAtual ?>">
        <input type="hidden" id="idaluno" value="<?= (int)$idAlunoLogado ?>">
        <input type="hidden" id="nometurma" value="<?= e($nomeTurmaAtual) ?>">
        <input type="hidden" id="nomecurso" value="<?= e($nomeCursoAtual) ?>">
        <input type="hidden" id="isProfessor" value="<?= $isProfessor ? '1' : '0' ?>">

        <div id="printes-lista" class="row g-4">
            <div class="col-12">
                <div class="empty-state">
                    <div class="spinner-border text-success mb-3" role="status"></div>
                    <div class="fw-semibold">Carregando printes...</div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-body-tertiary py-4 border-top mt-auto d-print-none">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; <?= date('Y') ?> Professor Eugênio</p>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Suporte</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Termos</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Privacidade</a></li>
            </ul>
        </div>
    </footer>

    <!-- Modal Upload -->
    <div class="modal fade" id="modalUploadPrint" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <div class="fw-bold fs-5">Enviar print da atividade</div>
                        <div class="text-muted small">A imagem será reduzida automaticamente para até 100 KB.</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="formUploadPrint" enctype="multipart/form-data">
                        <div class="upload-zone mb-3">
                            <i class="bi bi-image fs-1 d-block mb-2 text-success"></i>
                            <div class="fw-semibold mb-1">Selecione a imagem da atividade</div>
                            <div class="text-muted small mb-3">Formatos aceitos: JPG, JPEG, PNG, WEBP</div>
                            <input type="file" name="imagem" id="imagem" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                        </div>

                        <div id="uploadPreview" class="d-none mb-3 text-center">
                            <img id="previewImg" src="" alt="Prévia" class="img-fluid rounded-4 shadow-sm" style="max-height: 260px; object-fit: contain;">
                        </div>

                        <div id="uploadProgressBox" class="d-none">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small fw-semibold">Enviando imagem...</span>
                                <span class="small fw-semibold" id="uploadProgressText">0%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%">0%</div>
                            </div>
                        </div>

                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-print-primary">
                                <i class="bi bi-send-fill me-2"></i> Enviar imagem
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Lightbox -->
    <div class="modal fade modal-lightbox" id="modalLightboxPrint" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <div class="modal-header border-0 pb-2">
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body pt-0 text-center">
                    <img id="lightboxPrintImg" src="" alt="Print ampliado">
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>

    <script>
        const urlsPrintes = {
            listar: 'componentes/v1/ajax_printes_listar.php',
            upload: 'componentes/v1/ajax_printes_upload.php',
            excluir: 'componentes/v1/ajax_printes_excluir.php',
            comentar: 'componentes/v1/ajax_printes_comentar.php',
            avaliar: 'componentes/v1/ajax_printes_avaliar.php'
        };

        const elLista = document.getElementById('printes-lista');
        const elForm = document.getElementById('formUploadPrint');
        const elImagem = document.getElementById('imagem');
        const elPreviewBox = document.getElementById('uploadPreview');
        const elPreviewImg = document.getElementById('previewImg');
        const elProgressBox = document.getElementById('uploadProgressBox');
        const elProgressBar = document.getElementById('uploadProgressBar');
        const elProgressText = document.getElementById('uploadProgressText');

        const modalUploadEl = document.getElementById('modalUploadPrint');
        const modalUpload = bootstrap.Modal.getOrCreateInstance(modalUploadEl);

        const modalLightboxEl = document.getElementById('modalLightboxPrint');
        const modalLightbox = bootstrap.Modal.getOrCreateInstance(modalLightboxEl);
        const lightboxPrintImg = document.getElementById('lightboxPrintImg');

        function baseData() {
            const fd = new FormData();
            fd.append('idpublicacao', document.getElementById('idpublicacao').value);
            fd.append('idmodulo', document.getElementById('idmodulo').value);
            fd.append('idaluno', document.getElementById('idaluno').value);
            fd.append('nometurma', document.getElementById('nometurma').value);
            fd.append('nomecurso', document.getElementById('nomecurso').value);
            fd.append('isProfessor', document.getElementById('isProfessor').value);
            return fd;
        }

        function showToast(message, type = 'success') {
            const id = 'toast_' + Date.now();
            const bgClass = type === 'danger' ? 'text-bg-danger' : (type === 'warning' ? 'text-bg-warning' : 'text-bg-success');

            const html = `
                <div id="${id}" class="toast align-items-center ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body fw-semibold">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                    </div>
                </div>
            `;

            document.getElementById('toastContainer').insertAdjacentHTML('beforeend', html);
            const toastEl = document.getElementById(id);
            const toast = new bootstrap.Toast(toastEl, {
                delay: 3500
            });
            toast.show();
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        async function carregarPrintes() {
            elLista.innerHTML = `
                <div class="col-12">
                    <div class="empty-state">
                        <div class="spinner-border text-success mb-3" role="status"></div>
                        <div class="fw-semibold">Atualizando printes...</div>
                    </div>
                </div>
            `;

            try {
                const response = await fetch(urlsPrintes.listar, {
                    method: 'POST',
                    body: baseData()
                });

                const html = await response.text();
                elLista.innerHTML = html;
            } catch (error) {
                elLista.innerHTML = `
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="bi bi-exclamation-triangle fs-1 text-danger d-block mb-2"></i>
                            <div class="fw-semibold">Não foi possível carregar os printes.</div>
                        </div>
                    </div>
                `;
            }
        }

        elImagem.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) {
                elPreviewBox.classList.add('d-none');
                elPreviewImg.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                elPreviewImg.src = e.target.result;
                elPreviewBox.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });

        elForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const file = elImagem.files[0];
            if (!file) {
                showToast('Selecione uma imagem.', 'warning');
                return;
            }

            const fd = baseData();
            fd.append('imagem', file);

            elProgressBox.classList.remove('d-none');
            elProgressBar.style.width = '0%';
            elProgressBar.textContent = '0%';
            elProgressText.textContent = '0%';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', urlsPrintes.upload, true);

            xhr.upload.addEventListener('progress', function(ev) {
                if (ev.lengthComputable) {
                    const percent = Math.round((ev.loaded / ev.total) * 100);
                    elProgressBar.style.width = percent + '%';
                    elProgressBar.textContent = percent + '%';
                    elProgressText.textContent = percent + '%';
                }
            });

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    try {
                        const json = JSON.parse(xhr.responseText);
                        if (json.status) {
                            showToast(json.msg || 'Imagem enviada com sucesso.');
                            elForm.reset();
                            elPreviewBox.classList.add('d-none');
                            elPreviewImg.src = '';
                            elProgressBox.classList.add('d-none');
                            modalUpload.hide();
                            carregarPrintes();
                        } else {
                            showToast(json.msg || 'Falha ao enviar imagem.', 'danger');
                        }
                    } catch (err) {
                        showToast('Erro inesperado no envio.', 'danger');
                    }
                }
            };

            xhr.send(fd);
        });

        document.addEventListener('click', async function(e) {
            const btnExcluir = e.target.closest('.btn-excluir-print');
            if (btnExcluir) {
                const id = btnExcluir.dataset.id;
                if (!confirm('Deseja excluir este print e seus comentários?')) {
                    return;
                }

                const fd = new FormData();
                fd.append('id', id);

                try {
                    const response = await fetch(urlsPrintes.excluir, {
                        method: 'POST',
                        body: fd
                    });
                    const json = await response.json();

                    if (json.status) {
                        showToast(json.msg || 'Print excluído com sucesso.');
                        carregarPrintes();
                    } else {
                        showToast(json.msg || 'Não foi possível excluir.', 'danger');
                    }
                } catch (error) {
                    showToast('Erro ao excluir o print.', 'danger');
                }
                return;
            }

            const btnComentar = e.target.closest('.btn-comentar-print');
            if (btnComentar) {
                const id = btnComentar.dataset.id;
                const idAluno = btnComentar.dataset.aluno || '0';
                const textarea = document.querySelector('#comentario_' + id);

                if (!textarea) {
                    return;
                }

                const texto = textarea.value.trim();
                if (texto === '') {
                    showToast('Digite um comentário.', 'warning');
                    return;
                }

                const fd = new FormData();
                fd.append('idfile', id);
                fd.append('idaluno', idAluno);
                fd.append('texto', texto);

                try {
                    const response = await fetch(urlsPrintes.comentar, {
                        method: 'POST',
                        body: fd
                    });

                    const json = await response.json();
                    if (json.status) {
                        showToast(json.msg || 'Comentário enviado.');
                        carregarPrintes();
                    } else {
                        showToast(json.msg || 'Falha ao comentar.', 'danger');
                    }
                } catch (error) {
                    showToast('Erro ao enviar comentário.', 'danger');
                }
                return;
            }

            const btnStar = e.target.closest('.rating-star.can-rate');
            if (btnStar) {
                const id = btnStar.dataset.id;
                const avaliacao = btnStar.dataset.star;

                const fd = new FormData();
                fd.append('id', id);
                fd.append('avaliacao', avaliacao);

                try {
                    const response = await fetch(urlsPrintes.avaliar, {
                        method: 'POST',
                        body: fd
                    });

                    const json = await response.json();
                    if (json.status) {
                        showToast(json.msg || 'Avaliação salva.');
                        carregarPrintes();
                    } else {
                        showToast(json.msg || 'Falha ao salvar avaliação.', 'danger');
                    }
                } catch (error) {
                    showToast('Erro ao avaliar a atividade.', 'danger');
                }
                return;
            }

            const btnLightbox = e.target.closest('.abrir-lightbox');
            if (btnLightbox) {
                e.preventDefault();
                const img = btnLightbox.getAttribute('href');
                if (img) {
                    lightboxPrintImg.src = img;
                    modalLightbox.show();
                }
            }
        });

        carregarPrintes();
    </script>
</body>

</html>