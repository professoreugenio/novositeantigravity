<?php
declare(strict_types=1)
;
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
// ✅ pasta acima do public_html (ex.: /home/usuario)
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
require_once PUBLIC_ROOT . '/componentes/v1/QueryCurso.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryModulo.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryPublicacao.php';
function formataDataForum($data, $hora)
{
    if (empty($data))
        return "Recentemente";
    $str = date('d/m/Y', strtotime($data));
    if (!empty($hora))
        $str .= " às " . date('H:i', strtotime($hora));
    return $str;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'enviar_comentario') {
    $textoComentario = trim($_POST['texto_comentario'] ?? '');
    $idParent = (int) ($_POST['id_parent'] ?? 0);
    if (!empty($textoComentario) && !empty($codigoUser) && !empty($idPublicacaoAtiva)) {
        try {
            $stmtInsertCom = $con->prepare("
                INSERT INTO new_sistema_mensagens_publicacoes 
                (idturma_af, idpublic_af, idmodulo_af, idcodmsgpub_af, texto_af, idfrom_af, libera_af, data_af, hora_af)
                VALUES 
                (:idTurma, :idPub, :idMod, :idParent, :texto, :idFrom, 1, CURDATE(), CURTIME())
            ");
            $stmtInsertCom->bindValue(':idTurma', $idTurma ?? 0, PDO::PARAM_INT);
            $stmtInsertCom->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
            $stmtInsertCom->bindValue(':idMod', $idModuloAtivo ?? 0, PDO::PARAM_INT);
            $stmtInsertCom->bindValue(':idParent', $idParent, PDO::PARAM_INT);
            $stmtInsertCom->bindValue(':texto', $textoComentario, PDO::PARAM_STR);
            $stmtInsertCom->bindValue(':idFrom', $codigoUser, PDO::PARAM_INT);
            $stmtInsertCom->execute();
        } catch (Throwable $e) {
        }
    }
    $redirectUrl = strtok($_SERVER["REQUEST_URI"], '#') . '#comentarios';
    header("Location: " . $redirectUrl);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'toggle_like') {
    header('Content-Type: application/json; charset=utf-8');
    $idRef = (int) ($_POST['id_ref'] ?? 0);
    if ($idRef <= 0 || empty($codigoUser)) {
        echo json_encode([
            'success' => false,
            'message' => 'Dados inválidos.'
        ]);
        exit;
    }
    try {
        $stmt = $con->prepare("
            SELECT codigolike, visivel_mlk
            FROM new_sistema_msg_like
            WHERE idde_mlk = :user
              AND idmsg_mlk = :id
            LIMIT 1
        ");
        $stmt->bindValue(':user', $codigoUser, PDO::PARAM_INT);
        $stmt->bindValue(':id', $idRef, PDO::PARAM_INT);
        $stmt->execute();
        $like = $stmt->fetch(PDO::FETCH_ASSOC);
        $liked = false;
        if ($like) {
            $newStatus = ((int) $like['visivel_mlk'] === 1) ? 0 : 1;
            $upd = $con->prepare("
                UPDATE new_sistema_msg_like
                SET visivel_mlk = :st,
                    dataml = CURDATE(),
                    horaml = CURTIME()
                WHERE codigolike = :idLike
            ");
            $upd->bindValue(':st', $newStatus, PDO::PARAM_INT);
            $upd->bindValue(':idLike', $like['codigolike'], PDO::PARAM_INT);
            $upd->execute();
            $liked = ($newStatus === 1);
        } else {
            $ins = $con->prepare("
                INSERT INTO new_sistema_msg_like
                (idde_mlk, idmsg_mlk, count_mlk, visivel_mlk, dataml, horaml)
                VALUES
                (:user, :id, 1, 1, CURDATE(), CURTIME())
            ");
            $ins->bindValue(':user', $codigoUser, PDO::PARAM_INT);
            $ins->bindValue(':id', $idRef, PDO::PARAM_INT);
            $ins->execute();
            $liked = true;
        }
        $stmtC = $con->prepare("
            SELECT COUNT(*)
            FROM new_sistema_msg_like
            WHERE idmsg_mlk = :id
              AND visivel_mlk = 1
        ");
        $stmtC->bindValue(':id', $idRef, PDO::PARAM_INT);
        $stmtC->execute();
        $newCount = (int) $stmtC->fetchColumn();
        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'count' => $newCount
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao processar like.'
        ]);
        exit;
    }
}
?>
<?php
$midiaTipo = 'nenhum';
$videoAula = null;
$youtubeAula = null;
$fotoCapa = null;
$posterVideo = 'https://professoreugenio.com/img/capavideos.jpg';
$videoLocalUrl = '';
$youtubeEmbedUrl = '';
/**
 * Converte URL/chave do YouTube em link embed.
 */
function getYoutubeEmbedUrl(?string $url, ?string $chave = null): string
{
    $chave = trim((string) $chave);
    if ($chave !== '') {
        return 'https://www.youtube.com/embed/' . rawurlencode($chave) . '?rel=0';
    }
    $url = trim((string) $url);
    if ($url === '') {
        return '';
    }
    // youtube.com/watch?v=
    if (preg_match('~[?&]v=([^&]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . rawurlencode($m[1]) . '?rel=0';
    }
    // youtu.be/
    if (preg_match('~youtu\.be/([^?&/]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . rawurlencode($m[1]) . '?rel=0';
    }
    // youtube.com/embed/
    if (preg_match('~/embed/([^?&/]+)~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . rawurlencode($m[1]) . '?rel=0';
    }
    return '';
}
if (!empty($idPublicacaoAtiva)) {
    try {
        /**
         * 1. Buscar foto favorita para usar como capa
         */
        $stmtFoto = $con->prepare("
            SELECT codigomfotos, urlpf, foto, pasta, ext
            FROM new_sistema_publicacoes_fotos_PJA
            WHERE codpublicacao = :idPub
              AND favorito_pf = 1
              AND visivel = 1
            ORDER BY codigomfotos DESC
            LIMIT 1
        ");
        $stmtFoto->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtFoto->execute();
        $fotoCapa = $stmtFoto->fetch(PDO::FETCH_ASSOC);
        if (!empty($fotoCapa)) {
            $urlpf = trim((string) ($fotoCapa['urlpf'] ?? ''));
            $foto = trim((string) ($fotoCapa['foto'] ?? ''));
            $pasta = trim((string) ($fotoCapa['pasta'] ?? ''));
            if ($urlpf !== '' && $urlpf !== '#') {
                $posterVideo = $urlpf;
            } elseif ($foto !== '') {
                $posterVideo = '/fotos/publicacoes/' . ($pasta !== '' ? $pasta . '/' : '') . $foto;
            }
        }
        /**
         * 2. Buscar vídeo local
         * Prioridade 1
         */
        $stmtVideo = $con->prepare("
            SELECT codigovideos, titulo, pasta, video, legenda, totalhoras, online, tipo
            FROM a_curso_videoaulas
            WHERE idpublicacaocva = :idPub
            ORDER BY codigovideos DESC
            LIMIT 1
        ");
        $stmtVideo->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtVideo->execute();
        $videoAula = $stmtVideo->fetch(PDO::FETCH_ASSOC);
        if (!empty($videoAula)) {
            $pastaVideo = trim((string) ($videoAula['pasta'] ?? ''));
            $arquivoVid = trim((string) ($videoAula['video'] ?? ''));
            if ($arquivoVid !== '') {
                $videoLocalUrl = 'https://professoreugenio.com/videos/publicacoes/' . ($pastaVideo !== '' ? $pastaVideo . '/' : '') . $arquivoVid;
                $midiaTipo = 'video';
            }
        }
        /**
         * 3. Buscar YouTube
         * Só entra se não houver vídeo local
         */
        if ($midiaTipo === 'nenhum') {
            $stmtYoutube = $con->prepare("
                SELECT codigoyoutube, url_sy, canal_sy, chavetube_sy, titulo_sy, tempo_sy, visivel_sy
                FROM new_sistema_youtube_PJA
                WHERE codpublicacao_sy = :idPub
                  AND visivel_sy = 1
                ORDER BY favorito_sy DESC, codigoyoutube DESC
                LIMIT 1
            ");
            $stmtYoutube->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
            $stmtYoutube->execute();
            $youtubeAula = $stmtYoutube->fetch(PDO::FETCH_ASSOC);
            if (!empty($youtubeAula)) {
                $youtubeEmbedUrl = getYoutubeEmbedUrl(
                    $youtubeAula['url_sy'] ?? '',
                    $youtubeAula['chavetube_sy'] ?? ''
                );
                if ($youtubeEmbedUrl !== '') {
                    $midiaTipo = 'youtube';
                }
            }
        }
    } catch (Throwable $e) {
        $midiaTipo = 'nenhum';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $ModuloNome ?>: <?= $PubTitulo ?> | Professor Eugênio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?= filemtime(__DIR__ . '/../assets/css/styles.css'); ?>">
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>
    <!-- Classroom Area -->
    <main class="container-fluid px-lg-5 py-4" style="margin-top: 60px; flex: 1;">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Meus Cursos</a></li>
                    <li class="breadcrumb-item"><a href="modulos.php"
                            class="text-decoration-none border-bottom border-primary"
                            title="<?= htmlspecialchars($nomeCurso) ?>"><?= mb_strlen($nomeCurso, 'UTF-8') > 20 ? htmlspecialchars(mb_substr($nomeCurso, 0, 20, 'UTF-8')) . '...' : htmlspecialchars($nomeCurso) ?></a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $ModuloNome ?></li>
                </ol>
            </nav>
        </div>
        <div class="row g-4" id="publicacao">
            <!-- Coluna Esquerda: Vídeo e Ações (col-lg-4) -->
            <div class="col-12 col-lg-4" id="video-acoesdaAula">
                <!-- Video Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 pt-0 pt-2" id="video-aula">
                        <div class="ratio ratio-16x9 bg-dark rounded-3 overflow-hidden position-relative">
                            <?php if ($midiaTipo === 'video' && $videoLocalUrl !== ''): ?>
                                <video class="w-100 h-100 object-fit-cover" controls preload="metadata" playsinline
                                    poster="<?= htmlspecialchars($posterVideo) ?>">
                                    <source src="<?= htmlspecialchars($videoLocalUrl) ?>" type="video/mp4">
                                    Seu navegador não suporta reprodução de vídeo.
                                </video>
                            <?php elseif ($midiaTipo === 'youtube' && $youtubeEmbedUrl !== ''): ?>
                                <iframe class="w-100 h-100" src="<?= htmlspecialchars($youtubeEmbedUrl) ?>"
                                    title="<?= htmlspecialchars((string) ($youtubeAula['titulo_sy'] ?? $PubTitulo ?? 'Vídeo da aula')) ?>"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                            <?php else: ?>
                                <div
                                    class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white text-center p-4">
                                    <i class="bi bi-camera-video-off fs-1 mb-3 opacity-75"></i>
                                    <div class="fw-semibold">Vídeo ainda não disponível</div>
                                    <small class="opacity-75">Esta aula não possui vídeo cadastrado no momento.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="header-curtircomentar" class="card-footer bg-white border-top-0 px-4 pb-3 pt-0">
                        <div class="social-actions">
                            <?php
                            $qtdLikesPub = 0;
                            $userLikedPub = false;
                            if (!empty($idPublicacaoAtiva)) {
                                try {
                                    $stL = $con->prepare("SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = :id AND visivel_mlk = 1");
                                    $stL->bindValue(':id', $idPublicacaoAtiva, PDO::PARAM_INT);
                                    $stL->execute();
                                    $qtdLikesPub = (int) $stL->fetchColumn();
                                    $stU = $con->prepare("SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = :id AND idde_mlk = :usr AND visivel_mlk = 1");
                                    $stU->bindValue(':id', $idPublicacaoAtiva, PDO::PARAM_INT);
                                    $stU->bindValue(':usr', $codigoUser, PDO::PARAM_INT);
                                    $stU->execute();
                                    $userLikedPub = ($stU->fetchColumn() > 0);
                                } catch (Throwable $e) {
                                }
                            }
                            ?>
                            <span class="social-pill social-pill-like <?= $userLikedPub ? 'is-active' : '' ?>"
                                onclick="toggleLike(this, <?= (int) $idPublicacaoAtiva ?>)">
                                <i class="bi <?= $userLikedPub ? 'bi-suit-heart-fill' : 'bi-suit-heart-fill' ?>"></i>
                                <span class="social-count like-count"><?= $qtdLikesPub ?></span>
                            </span>
                            <?php
                            $qtdComentarios = 0;
                            $qtdComentariosStr = "Comentar";
                            if (!empty($idPublicacaoAtiva)) {
                                try {
                                    $stmtC = $con->prepare("SELECT COUNT(*) FROM new_sistema_mensagens_publicacoes WHERE idpublic_af = :idPub AND libera_af = 1");
                                    $stmtC->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                                    $stmtC->execute();
                                    $qtdComentarios = (int) $stmtC->fetchColumn();
                                    if ($qtdComentarios > 0) {
                                        $qtdComentariosStr = $qtdComentarios . " Comentário" . ($qtdComentarios > 1 ? "s" : "");
                                    }
                                } catch (Throwable $e) {
                                }
                            }
                            ?>
                            <a href="#comentarios" class="social-pill social-pill-comment">
                                <i class="bi bi-chat-left-fill"></i>
                                <span class="social-text"><?= $qtdComentariosStr ?></span>
                            </a>
                        </div>
                        <div class="social-view">
                            <i class="bi bi-chat-dots-fill"></i> 12 - Ver
                        </div>
                    </div>
                </div>
                <?php
                $anexosAula = [];
                if (!empty($idPublicacaoAtiva)) {
                    try {
                        $stmtAnexos = $con->prepare("SELECT * FROM new_sistema_publicacoes_anexos_PJA WHERE codpublicacao = :idPub AND visivel = 1 ORDER BY datapa DESC, horapa DESC");
                        $stmtAnexos->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                        $stmtAnexos->execute();
                        $anexosAula = $stmtAnexos->fetchAll(PDO::FETCH_ASSOC);
                    } catch (Throwable $e) {
                    }
                }
                $qtdAnexos = count($anexosAula);
                ?>
                <!-- Actions Card -->
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4 text-uppercase fw-bold text-muted fw-bold"
                        style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        Ações da Aula
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="row g-3">
                            <div class="col-6">
                                <button type="button" id="bt-anexos"
                                    class="btn btn-action-custom btn-action-blue w-100 d-flex justify-content-between align-items-center <?= $qtdAnexos == 0 ? 'opacity-50' : '' ?>"
                                    style="font-size: 0.85rem;" <?= $qtdAnexos > 0 ? 'data-bs-toggle="modal" data-bs-target="#modalAnexos"' : 'disabled' ?>>
                                    <span><i class="bi bi-paperclip fw-bold me-1"></i> Anexos</span>
                                    <span class="action-badge badge-blue"><?= $qtdAnexos ?></span>
                                </button>
                            </div>
                            <div class="col-6">
                                <a href="bancoAnexos.php" id="br-anexo"
                                    class="btn btn-action-custom btn-action-purple w-100 d-flex justify-content-between align-items-center"
                                    style="font-size: 0.85rem; text-decoration: none;">
                                    <span><i class="bi bi-folder-fill me-1"></i> Banco Anexos</span>
                                    <i class="bi bi-chevron-right opacity-75"></i>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="Questionario.php"
                                    class="btn btn-action-custom btn-action-green w-100 d-flex justify-content-between align-items-center"
                                    style="font-size: 0.85rem; text-decoration: none;">
                                    <span><i class="bi bi-clipboard-check-fill me-1"></i> Questionário</span>
                                    <span class="action-badge badge-green"><i class="bi bi-check2"></i></span>
                                </a>
                            </div>
                            <div class="col-6">
                                <?php if ($PubTemAtividade): ?>
                                    <button
                                        class="btn btn-action-custom btn-action-yellow w-100 d-flex justify-content-between align-items-center"
                                        style="font-size: 0.85rem;">
                                        <span><i class="bi bi-upload me-1"></i> Atividades</span>
                                        <span class="action-badge badge-yellow"><i class="bi bi-clock-fill"
                                                style="transform: scale(0.8)"></i></span>
                                    </button>
                                <?php else: ?>
                                    <button disabled
                                        class="btn btn-action-custom btn-action-yellow w-100 d-flex justify-content-between align-items-center opacity-50"
                                        style="font-size: 0.85rem;">
                                        <span><i class="bi bi-upload me-1"></i> Atividades</span>
                                        <span class="action-badge badge-yellow"><i class="bi bi-slash-circle"
                                                style="transform: scale(0.8)"></i></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="col-6">
                                <button
                                    class="btn btn-action-custom btn-action-pink w-100 d-flex justify-content-between align-items-center"
                                    style="font-size: 0.85rem;">
                                    <span><i class="bi bi-star-fill me-1"></i> Depoimento</span>
                                    <i class="bi bi-chevron-right opacity-75"></i>
                                </button>
                            </div>
                            <div class="col-6">
                                <button
                                    class="btn btn-action-custom btn-action-indigo w-100 d-flex justify-content-between align-items-center"
                                    style="font-size: 0.85rem;">
                                    <span><i class="bi bi-journal-bookmark-fill me-1"></i> Meu Caderno</span>
                                    <span class="action-badge badge-indigo">3</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Coluna Direita: Título, Texto e Comentários (col-lg-8) -->
            <div class="col-12 col-lg-8" id="conteudo">
                <div class="col-12" id="publicacao-aula">
                    <!-- Header Lesson Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div id="header-titulopublicacao"
                            class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h3 class="fw-bold text-dark mb-1"><?= $PubTitulo ?: 'Título da Aula' ?></h3>
                                <div class="text-muted small"><?= $ModuloNome ?> &bull;
                                    <?= htmlspecialchars((string) $nomeCurso) ?> <?= $idPublicacaoAtiva; ?>
                                </div>
                            </div>
                            <button id="btn-maisLicoes" data-bs-toggle="offcanvas" data-bs-target="#offcanvasLicoes"
                                class="btn btn-primary rounded-3 px-3 py-2 fw-medium d-flex align-items-center gap-2 text-nowrap"
                                style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none;">
                                <i class="bi bi-list-ul"></i> Mais Lições
                            </button>
                        </div>
                    </div>
                    <!-- Text Content Card -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="text-dark fs-5 lh-lg content-text">
                                <div id="textoAula">
                                    <?php if (!empty($PubTexto)): ?>
                                        <?= $PubTexto ?>
                                    <?php else: ?>
                                        <h3 class="fw-bold mb-4 text-dark">Sem publicações </h3>
                                    <?php endif; ?>
                                </div>
                                <hr class="mt-5 mb-4 opacity-25">
                                <div
                                    class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3 mt-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php
                                        $autorNome = !empty($PubAutor) ? $PubAutor : 'Prof. Eugênio';
                                        $autorUrlSafe = urlencode($autorNome);
                                        ?>
                                        <div class="rounded-circle d-flex align-items-center justify-content-center p-1"
                                            style="width: 58px; height: 58px; background: linear-gradient(135deg, #0d6efd, #3b82f6);">
                                            <img src="https://ui-avatars.com/api/?name=<?= $autorUrlSafe ?>&background=ffffff&color=0d6efd&bold=true&rounded=true"
                                                alt="<?= htmlspecialchars($autorNome) ?>"
                                                class="rounded-circle w-100 h-100 shadow-sm border border-2 border-white object-fit-cover">
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.05rem;">
                                                <?= htmlspecialchars($autorNome) ?>
                                            </h6>
                                            <span class="text-muted small">Especialista em Excel</span>
                                        </div>
                                    </div>
                                    <a href="#"
                                        class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2 fw-semibold text-primary">
                                        <i class="bi bi-envelope-fill"></i> Enviar dúvida
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12" id="comentarios">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div id="header-comentarios"
                            class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-chat-left-fill text-primary"></i> Comentários dos Alunos
                            </h5>
                            <button id="btn-comentar"
                                class="btn btn-primary rounded-3 px-3 py-2 fw-medium d-flex align-items-center gap-2"
                                style="background: #2563eb; border: none;" data-bs-toggle="modal"
                                data-bs-target="#modalComentar"
                                onclick="document.getElementById('id_parent_input').value='0'; document.getElementById('tituloModalComentar').innerText='Novo Comentário';">
                                <i class="bi bi-plus-lg"></i> Comentar
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <?php
                            $comentarios = [];
                            try {
                                $stmtCom = $con->prepare("
                                    SELECT m.codigomsgpublicacao, m.texto_af, m.idfrom_af, m.data_af, m.hora_af, c.nome, c.pastasc, c.imagem50,
                                    (SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = m.codigomsgpublicacao AND visivel_mlk = 1) AS qtd_likes,
                                    (SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = m.codigomsgpublicacao AND idde_mlk = :userLogado AND visivel_mlk = 1) AS user_liked
                                    FROM new_sistema_mensagens_publicacoes m
                                    LEFT JOIN new_sistema_cadastro c ON m.idfrom_af = c.codigocadastro
                                    WHERE m.idpublic_af = :idPub 
                                      AND m.libera_af = 1 
                                      AND (m.idcodmsgpub_af IS NULL OR m.idcodmsgpub_af = 0)
                                    ORDER BY m.data_af DESC, m.hora_af DESC
                                ");
                                $stmtCom->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                                $stmtCom->bindValue(':userLogado', $codigoUser, PDO::PARAM_INT);
                                $stmtCom->execute();
                                $comentarios = $stmtCom->fetchAll(PDO::FETCH_ASSOC);
                            } catch (Throwable $e) {
                            }
                            ?>
                            <?php if (empty($comentarios)): ?>
                                <div class="text-center text-muted p-4">Nenhum comentário ainda. Seja o primeiro a
                                    participar!</div>
                            <?php else: ?>
                                <?php foreach ($comentarios as $com):
                                    // Buscar respostas (Threads) para este comentário pai
                                    $respostas = [];
                                    try {
                                        $stmtRes = $con->prepare("
                                            SELECT m.codigomsgpublicacao, m.texto_af, m.idfrom_af, m.data_af, m.hora_af, c.nome, c.pastasc, c.imagem50,
                                            (SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = m.codigomsgpublicacao AND visivel_mlk = 1) AS qtd_likes,
                                            (SELECT COUNT(*) FROM new_sistema_msg_like WHERE idmsg_mlk = m.codigomsgpublicacao AND idde_mlk = :userLogado AND visivel_mlk = 1) AS user_liked
                                            FROM new_sistema_mensagens_publicacoes m
                                            LEFT JOIN new_sistema_cadastro c ON m.idfrom_af = c.codigocadastro
                                            WHERE m.idpublic_af = :idPub 
                                              AND m.libera_af = 1 
                                              AND m.idcodmsgpub_af = :idParent
                                            ORDER BY m.data_af ASC, m.hora_af ASC
                                        ");
                                        $stmtRes->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                                        $stmtRes->bindValue(':idParent', $com['codigomsgpublicacao'], PDO::PARAM_INT);
                                        $stmtRes->bindValue(':userLogado', $codigoUser, PDO::PARAM_INT);
                                        $stmtRes->execute();
                                        $respostas = $stmtRes->fetchAll(PDO::FETCH_ASSOC);
                                    } catch (Throwable $e) {
                                    }
                                    // Lógica visual e nome do autor do comentário
                                    $nomeAutor = !empty($com['nome']) ? $com['nome'] : "Aluno #" . $com['idfrom_af'];
                                    $autorInitials = urlencode((string) $nomeAutor);
                                    $imgUser = "/fotos/usuarios/" . htmlspecialchars((string) $com['pastasc']) . "/" . htmlspecialchars((string) $com['imagem50']);
                                    $tempoCom = formataDataForum($com['data_af'] ?? null, $com['hora_af'] ?? null);
                                    ?>
                                    <div class="d-flex gap-3 mb-4" id="colentarios-lista">
                                        <img src="<?= $imgUser ?>" onerror="this.src='/fotos/usuarios/usuario.png';"
                                            alt="<?= htmlspecialchars($nomeAutor) ?>"
                                            class="rounded-circle object-fit-cover shadow-sm" width="46" height="46">
                                        <div class="w-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($nomeAutor) ?></h6>
                                                <span class="text-muted small"><?= $tempoCom ?></span>
                                            </div>
                                            <p class="mb-2 text-dark" style="font-size: 0.95rem;">
                                                <?= nl2br(htmlspecialchars((string) $com['texto_af'])) ?></p>
                                            <div class="d-flex gap-3 text-muted small fw-medium mb-3">
                                                <span
                                                    class="hover-primary like-trigger <?= !empty($com['user_liked']) ? 'text-danger is-liked' : '' ?>"
                                                    data-liked="<?= !empty($com['user_liked']) ? '1' : '0' ?>"
                                                    style="cursor: pointer;" role="button" tabindex="0"
                                                    onclick="toggleLike(this, <?= (int) $com['codigomsgpublicacao'] ?>)">
                                                    <i
                                                        class="bi <?= !empty($com['user_liked']) ? 'bi-suit-heart-fill' : 'bi-suit-heart' ?>"></i>
                                                    Curtir<span
                                                        class="like-count fw-bold"><?= !empty($com['qtd_likes']) ? ' ' . (int) $com['qtd_likes'] : '' ?></span>
                                                </span>
                                                <span class="hover-primary" style="cursor: pointer;" data-bs-toggle="modal"
                                                    data-bs-target="#modalComentar"
                                                    onclick="document.getElementById('id_parent_input').value='<?= $com['codigomsgpublicacao'] ?>'; document.getElementById('tituloModalComentar').innerText='Responder a <?= htmlspecialchars(addslashes((string) $nomeAutor)) ?>';">
                                                    <i class="bi bi-chat-left-text"></i> Responder
                                                </span>
                                            </div>
                                            <!-- Respostas -->
                                            <?php foreach ($respostas as $resp):
                                                $nomeResp = !empty($resp['nome']) ? $resp['nome'] : "Autor #" . $resp['idfrom_af'];
                                                $respSrtConf = urlencode((string) $nomeResp);
                                                $imgResp = "/fotos/usuarios/usuario.png";
                                                if (!empty($resp['pastasc']) && !empty($resp['imagem50'])) {
                                                    $pathLocalResp = dirname(__DIR__) . "/fotos/usuarios/" . $resp['pastasc'] . "/" . $resp['imagem50'];
                                                    if (file_exists($pathLocalResp)) {
                                                        $imgResp = "/fotos/usuarios/" . htmlspecialchars((string) $resp['pastasc']) . "/" . htmlspecialchars((string) $resp['imagem50']);
                                                    }
                                                }
                                                $tempoResp = formataDataForum($resp['data_af'] ?? null, $resp['hora_af'] ?? null);
                                                // Identificar visualmente se a resposta é de um Instrutor/Autor da aula
                                                $badgeInstrutor = false; // Em breve podemos habilitar essa regra.
                                                ?>
                                                <div
                                                    class="d-flex gap-3 ps-4 border-start border-2 border-primary border-opacity-25 mb-2 pt-1 pb-1">
                                                    <img src="<?= $imgResp ?>" alt="<?= htmlspecialchars($nomeResp) ?>"
                                                        class="rounded-circle object-fit-cover shadow-sm" width="38" height="38">
                                                    <div class="w-100">
                                                        <div class="d-flex align-items-center gap-2 mb-1">
                                                            <h6 class="fw-bold mb-0 text-dark" style="font-size: 0.9rem;">
                                                                <?= htmlspecialchars($nomeResp) ?></h6>
                                                            <?php if ($badgeInstrutor): ?>
                                                                <span
                                                                    class="badge bg-primary bg-opacity-10 text-primary fw-medium rounded-pill px-2">Instrutor</span>
                                                            <?php endif; ?>
                                                            <span class="text-muted ms-auto"
                                                                style="font-size: 0.75rem;"><?= $tempoResp ?></span>
                                                        </div>
                                                        <p class="mb-0 text-dark" style="font-size: 0.90rem;">
                                                            <?= nl2br(htmlspecialchars((string) $resp['texto_af'])) ?></p>
                                                        <div class="d-flex gap-3 text-muted small fw-medium mt-1">
                                                            <span
                                                                class="hover-primary like-trigger <?= !empty($resp['user_liked']) ? 'text-danger is-liked' : '' ?>"
                                                                data-liked="<?= !empty($resp['user_liked']) ? '1' : '0' ?>"
                                                                style="cursor: pointer;" role="button" tabindex="0"
                                                                onclick="toggleLike(this, <?= (int) $resp['codigomsgpublicacao'] ?>)">
                                                                <i
                                                                    class="bi <?= !empty($resp['user_liked']) ? 'bi-suit-heart-fill' : 'bi-suit-heart' ?>"></i>
                                                                Curtir<span
                                                                    class="like-count fw-bold"><?= !empty($resp['qtd_likes']) ? ' ' . (int) $resp['qtd_likes'] : '' ?></span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <hr class="opacity-10 mb-4">
                                <?php endforeach; ?>
                                <!-- Load More Button -->
                                <button
                                    class="btn btn-outline-secondary w-100 rounded-3 py-2 mt-2 text-muted fw-semibold border-light-subtle">
                                    <i class="bi bi-chevron-down me-2"></i> Carregar mais comentários
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Offcanvas PHP Logic & HTML -->
    <?php
    $activeDiaOffcanvas = isset($_SESSION['dadosdia']) ? (int) $_SESSION['dadosdia'] : 1;
    $aulasOffcanvas = [];
    if (!empty($idCurso) && !empty($idModuloAtivo) && $activeDiaOffcanvas > 0) {
        try {
            $stmtOffcanvas = $con->prepare("
                SELECT p.codigopublicacoes, p.titulo, 
                       (SELECT 1 FROM a_aluno_andamento_aula AND_A 
                        WHERE AND_A.idpublicaa = p.codigopublicacoes 
                          AND AND_A.idalunoaa = :idUser 
                        LIMIT 1) as assistido,
                       (SELECT 1 FROM a_curso_videoaulas WHERE idpublicacaocva = p.codigopublicacoes LIMIT 1) as tem_videoaula,
                       (SELECT 1 FROM new_sistema_youtube_PJA WHERE codpublicacao_sy = p.codigopublicacoes LIMIT 1) as tem_youtube,
                       (SELECT 1 FROM a_curso_questionario WHERE idpublicacaocq = p.codigopublicacoes LIMIT 1) as tem_questionario,
                       (SELECT 1 FROM a_curso_questionario_resposta qr JOIN a_curso_questionario q ON qr.idquestionarioqr = q.codigoquestionario WHERE q.idpublicacaocq = p.codigopublicacoes AND qr.idalunoqr = :idUser LIMIT 1) as quest_respondido
                FROM a_aluno_publicacoes_cursos pc
                INNER JOIN new_sistema_publicacoes_PJA p ON pc.idpublicacaopc = p.codigopublicacoes
                WHERE pc.idcursopc = :idCurso 
                  AND pc.idmodulopc = :idMod 
                  AND pc.diapc = :dia
                  AND pc.visivelpc = 1
                ORDER BY pc.ordempc ASC, p.codigopublicacoes ASC
            ");
            $stmtOffcanvas->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $stmtOffcanvas->bindValue(':idMod', $idModuloAtivo, PDO::PARAM_INT);
            $stmtOffcanvas->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
            $stmtOffcanvas->bindValue(':dia', $activeDiaOffcanvas, PDO::PARAM_INT);
            $userIdOffcanvas = !empty($codigoUser) ? $codigoUser : 0;
            $stmtOffcanvas->bindValue(':idUser', $userIdOffcanvas, PDO::PARAM_INT);
            $stmtOffcanvas->execute();
            $aulasOffcanvas = $stmtOffcanvas->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
        }
    }
    ?>
    <div class="offcanvas offcanvas-end shadow-lg border-0" tabindex="-1" id="offcanvasLicoes"
        aria-labelledby="offcanvasLicoesLabel" style="width: 400px;">
        <div class="offcanvas-header bg-light border-bottom px-4 py-3">
            <div>
                <h5 class="offcanvas-title fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                    id="offcanvasLicoesLabel">
                    <i class="bi bi-collection-play-fill text-primary"></i> Mais Lições
                </h5>
                <span class="text-muted small"><?= htmlspecialchars((string) $ModuloNome) ?> •
                    <?= $activeDiaOffcanvas ?>º Dia</span>
            </div>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 course-sidebar-scroll">
            <?php if (empty($aulasOffcanvas)): ?>
                <div class="p-4 text-center text-muted">Nenhuma lição encontrada para hoje.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($aulasOffcanvas as $idx => $aulaItem):
                        $idPubLoop = (int) $aulaItem['codigopublicacoes'];
                        $isAulaAtiva = (!empty($idPublicacaoAtiva) && $idPubLoop === $idPublicacaoAtiva);
                        $temVideoaula = !empty($aulaItem['tem_videoaula']);
                        $temYoutube = !empty($aulaItem['tem_youtube']);
                        $temQuest = !empty($aulaItem['tem_questionario']);
                        $questResp = !empty($aulaItem['quest_respondido']);
                        $jaAssistido = !empty($aulaItem['assistido']);
                        $isQuestionarioPrimary = ($temQuest && !$temVideoaula && !$temYoutube);
                        $jaConcluiu = $isQuestionarioPrimary ? $questResp : $jaAssistido;
                        $encPubId = encrypt_secure($idPubLoop, 'e');
                        $hrefUrl = "action.php?tokemPublicacao=" . time() . "&publicacao=" . urlencode($encPubId);
                        $bgStyle = $isAulaAtiva ? "background: linear-gradient(90deg, rgba(37,99,235,0.05) 0%, rgba(29,78,216,0.05) 100%); border-left: 4px solid #2563eb !important;" : "border-left: 4px solid transparent !important;";
                        $iconeTipo = $isQuestionarioPrimary ? "bi-card-list" : "bi-camera-video";
                        $iconeTipoTexto = $isQuestionarioPrimary ? "Questionário" : "Aula em Vídeo";
                        $checkPrincipalIcon = $jaConcluiu ? "bi-check-lg" : ($isQuestionarioPrimary ? "bi-pencil-square ms-1" : "bi-play-fill ms-1");
                        ?>
                        <a href="<?= $hrefUrl ?>"
                            class="list-group-item list-group-item-action border-0 border-bottom p-3 px-4 d-flex align-items-center gap-3 transition-all <?= $isAulaAtiva ? 'bg-light' : '' ?>"
                            style="<?= $bgStyle ?>">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 <?= $jaConcluiu ? 'bg-success text-white shadow-sm border border-success' : 'bg-body-secondary text-secondary border border-secondary-subtle' ?>"
                                style="width: 40px; height: 40px;">
                                <i class="bi <?= $checkPrincipalIcon ?> fs-5"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <?php
                                $limitePalavras = implode(' ', array_slice(explode(' ', (string) $aulaItem['titulo']), 0, 3));
                                $limitePalavras .= (str_word_count((string) $aulaItem['titulo']) > 3) ? '...' : '';
                                ?>
                                <h6 class="fw-semibold mb-1 text-truncate <?= $isAulaAtiva ? 'text-primary' : 'text-dark' ?>"
                                    title="<?= htmlspecialchars((string) $aulaItem['titulo']) ?>">
                                    <?= ($idx + 1) ?>. <?= htmlspecialchars($limitePalavras) ?>
                                </h6>
                                <span class="text-muted small d-flex align-items-center gap-2 icones_licao">
                                    <?php if ($temVideoaula || $temYoutube): ?>
                                        <span title="Videoaula"><i
                                                class="bi bi-camera-video <?= ($jaAssistido) ? 'text-success' : '' ?>"></i>
                                            Vídeo</span>
                                    <?php endif; ?>
                                    <?php if ($temQuest): ?>
                                        <span title="Questionário"><i
                                                class="bi bi-card-checklist <?= ($questResp) ? 'text-success' : '' ?>"></i>
                                            Quiz</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Modal Anexos -->
    <div class="modal fade" id="modalAnexos" tabindex="-1" aria-labelledby="modalAnexosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-sm rounded-4">
                <div class="modal-header border-bottom-0 bg-light rounded-top-4 px-4 py-3">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-dark" id="modalAnexosLabel">
                        <i class="bi bi-paperclip" style="color: #2563eb;"></i> Modal Anexos
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if ($qtdAnexos == 0): ?>
                        <p class="text-muted mb-0">Nenhum anexo disponível para esta aula.</p>
                    <?php else: ?>
                        <p class="text-muted mb-4">Arquivos anexados diretamente a esta aula (<?= $qtdAnexos ?>
                            arquivo<?= $qtdAnexos > 1 ? 's' : '' ?>).</p>
                        <div class="list-group">
                            <?php foreach ($anexosAula as $anexo):
                                $ext = strtolower($anexo['extpa'] ?? '');
                                $url = $anexo['urlpa'] ?? '#';
                                $isUrl = ($url !== '#' && filter_var($url, FILTER_VALIDATE_URL));
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                $iconClass = "bi-file-earmark-fill text-secondary";
                                $bgColor = "bg-secondary";
                                $textColor = "text-secondary";
                                $badgeText = strtoupper($ext ? $ext : 'ARQ');
                                if ($isUrl) {
                                    if (strpos($url, 'drive.google.com') !== false) {
                                        $iconClass = "bi-google text-success";
                                        $bgColor = "bg-success";
                                        $textColor = "text-success";
                                        $badgeText = "GDRIVE";
                                    } elseif (strpos($url, 'onedrive.live.com') !== false || strpos($url, '1drv.ms') !== false) {
                                        $iconClass = "bi-microsoft text-primary";
                                        $bgColor = "bg-primary";
                                        $textColor = "text-primary";
                                        $badgeText = "ONEDRIVE";
                                    } else {
                                        $iconClass = "bi-link-45deg text-info";
                                        $bgColor = "bg-info";
                                        $textColor = "text-info";
                                        $badgeText = "LINK";
                                    }
                                } else {
                                    switch ($ext) {
                                        case 'pdf':
                                            $iconClass = "bi-file-earmark-pdf-fill text-danger";
                                            $bgColor = "bg-danger";
                                            $textColor = "text-danger";
                                            break;
                                        case 'xls':
                                        case 'xlsx':
                                            $iconClass = "bi-file-earmark-excel-fill text-success";
                                            $bgColor = "bg-success";
                                            $textColor = "text-success";
                                            break;
                                        case 'doc':
                                        case 'docx':
                                            $iconClass = "bi-file-earmark-word-fill text-primary";
                                            $bgColor = "bg-primary";
                                            $textColor = "text-primary";
                                            break;
                                        case 'ppt':
                                        case 'pptx':
                                            $iconClass = "bi-file-earmark-ppt-fill text-warning";
                                            $bgColor = "bg-warning";
                                            $textColor = "text-warning";
                                            break;
                                        case 'zip':
                                        case 'rar':
                                        case '7z':
                                            $iconClass = "bi-file-earmark-zip-fill text-warning";
                                            $bgColor = "bg-warning";
                                            $textColor = "text-warning";
                                            break;
                                        case 'txt':
                                        case 'rtf':
                                            $iconClass = "bi-file-earmark-text-fill text-secondary";
                                            $bgColor = "bg-secondary";
                                            $textColor = "text-secondary";
                                            break;
                                        case 'psd':
                                        case 'crd':
                                            $iconClass = "bi-file-earmark-image-fill text-info";
                                            $bgColor = "bg-info";
                                            $textColor = "text-info";
                                            break;
                                        case 'bat':
                                        case 'sh':
                                        case 'cmd':
                                            $iconClass = "bi-file-earmark-code-fill text-dark";
                                            $bgColor = "bg-dark";
                                            $textColor = "text-dark";
                                            break;
                                    }
                                }
                                $pastaPrefix = !empty($anexo['pastapa']) ? htmlspecialchars((string) ($anexo['pastapa'])) . "/" : "";
                                $fileUrl = $isUrl ? htmlspecialchars($url) : "/anexos/publicacoes/" . $pastaPrefix . htmlspecialchars((string) ($anexo['anexopa']));
                                $downloadAttr = $isUrl ? 'target="_blank"' : 'download="' . htmlspecialchars((string) ($anexo['titulopa']) . '.' . $ext) . '" target="_blank"';
                                ?>
                                <a href="<?= $fileUrl ?>" <?= $downloadAttr ?>
                                    class="list-group-item list-group-item-action d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 py-3 border-0 border-bottom rounded-0">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($isImage && !$isUrl): ?>
                                            <div class="flex-shrink-0"
                                                style="width: 46px; height: 46px; border-radius: 8px; overflow: hidden; background: #e9ecef;">
                                                <img src="<?= $fileUrl ?>"
                                                    class="w-100 h-100 object-fit-cover shadow-sm border border-light"
                                                    style="cursor: pointer;" alt="miniatura" data-img-src="<?= $fileUrl ?>"
                                                    onclick="event.preventDefault(); document.getElementById('lightboxImage').src=this.dataset.imgSrc; var m = new bootstrap.Modal(document.getElementById('lightboxModal')); m.show();">
                                            </div>
                                        <?php else: ?>
                                            <div class="<?= $bgColor ?> bg-opacity-10 <?= $textColor ?> rounded-circle d-flex justify-content-center align-items-center flex-shrink-0"
                                                style="width: 46px; height: 46px;">
                                                <i class="bi <?= $iconClass ?> fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div id="titulo-anexo" style="width: 300px;">
                                            <h6 class="mb-1 fw-semibold text-dark">
                                                <?= htmlspecialchars((string) $anexo['titulopa']) ?></h6>
                                            <small class="text-muted d-flex align-items-center gap-2">
                                                <span class="badge bg-light text-dark border"><?= $badgeText ?></span>
                                                <?php if (!$isUrl && !empty($anexo['sizepa'])): ?>
                                                    <?= htmlspecialchars((string) $anexo['sizepa']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="mt-2 mt-sm-0 ms-sm-auto w-100 w-sm-auto d-flex justify-content-end">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium text-nowrap">
                                            <?php if ($isUrl): ?>
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Acessar
                                            <?php else: ?>
                                                <i class="bi bi-download me-1"></i> Baixar
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-top-0 bg-light rounded-bottom-4 py-3 px-4">
                    <button type="button" class="btn btn-secondary px-4 py-2 rounded-3 fw-medium shadow-sm"
                        data-bs-dismiss="modal">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Comentar -->
    <div class="modal fade" id="modalComentar" tabindex="-1" aria-labelledby="modalComentarLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm rounded-4">
                <form action="" method="POST">
                    <input type="hidden" name="acao" value="enviar_comentario">
                    <input type="hidden" name="id_parent" id="id_parent_input" value="0">
                    <div class="modal-header border-bottom-0 bg-light rounded-top-4 px-4 py-3">
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-dark"
                            id="modalComentarLabel">
                            <i class="bi bi-chat-left-dots-fill text-primary"></i> <span id="tituloModalComentar">Novo
                                Comentário</span>
                        </h5>
                        <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"
                            aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="texto_comentario" class="form-label fw-medium text-dark">Escreva sua
                                mensagem</label>
                            <textarea class="form-control" name="texto_comentario" id="texto_comentario" rows="4"
                                required placeholder="Digite aqui sua dúvida ou comentário..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light rounded-bottom-4 py-3 px-4">
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-3 fw-medium shadow-sm"
                            data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-medium shadow-sm">
                            <i class="bi bi-send me-1"></i> Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Lightbox (Para visualizar imagens anexadas) -->
    <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center p-0 position-relative">
                    <img id="lightboxImage" src="" class="img-fluid rounded shadow-lg bg-dark"
                        style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-body-tertiary py-4 border-top mt-auto">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; 2026 Professor Eugênio</p>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Apoio</a></li>
                <li class="nav-item"><a href="#" class="nav-link px-2 text-muted">Reportar Erro</a></li>
            </ul>
        </div>
    </footer>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>
    <script src="../assets/js/likeComent.js"></script>
    <script src="../assets/js/ajaxLikeComent.js"></script>
</body>

</html>