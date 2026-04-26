<?php
require_once 'componentes/v1/Query_head.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryUsuario.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryCurso.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryModulo.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryPublicacao.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Conexão
|--------------------------------------------------------------------------
| Mantém compatibilidade caso sua conexão já venha como $con.
| Se não vier, tenta buscar pela classe config::connect().
*/
if (!isset($con) || !($con instanceof PDO)) {
    if (class_exists('config')) {
        $con = config::connect();
    }
}

/*
|--------------------------------------------------------------------------
| Funções auxiliares
|--------------------------------------------------------------------------
*/
function depoH($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function depoInt($valor): int
{
    return filter_var($valor, FILTER_VALIDATE_INT) !== false ? (int)$valor : 0;
}

function depoDataHora(?string $data, ?string $hora): string
{
    if (empty($data)) {
        return 'Data não informada';
    }

    $dataBR = date('d/m/Y', strtotime($data));
    $horaBR = !empty($hora) ? substr($hora, 0, 5) : '';

    return trim($dataBR . ' ' . $horaBR);
}

function depoStatusPermissao($permissao): array
{
    $permissao = is_null($permissao) ? 0 : (int)$permissao;

    if ($permissao === 1) {
        return [
            'texto' => 'Depoimento liberado',
            'classe' => 'text-bg-success',
            'icone' => 'bi-check-circle'
        ];
    }

    if ($permissao === 2) {
        return [
            'texto' => 'Depoimento não liberado',
            'classe' => 'text-bg-danger',
            'icone' => 'bi-x-circle'
        ];
    }

    return [
        'texto' => 'Aguardando a liberação do professor',
        'classe' => 'text-bg-warning',
        'icone' => 'bi-hourglass-split'
    ];
}

/*
|--------------------------------------------------------------------------
| Identificação do usuário e publicação
|--------------------------------------------------------------------------
| Ajustei com várias possibilidades de variáveis, pois seus arquivos Query*
| podem trazer nomes diferentes dependendo da página.
*/
$idUsuario = 0;

$candidatosUsuario = [
    $codigoUser ?? null,
    $codigoUsuario ?? null,
    $codigocadastro ?? null,
    $idUsuarioLogado ?? null,
    $usuarioLogado['codigocadastro'] ?? null,
    $dadosUsuario['codigocadastro'] ?? null,
    $user['codigocadastro'] ?? null,
    $Usuario['codigocadastro'] ?? null,
];

foreach ($candidatosUsuario as $valor) {
    $valor = depoInt($valor);
    if ($valor > 0) {
        $idUsuario = $valor;
        break;
    }
}

$idPublicacao = 0;

$candidatosPublicacao = [
    $idPublicacaoAtiva ?? null,
    $idPublicacao ?? null,
    $codigopublicacoes ?? null,
    $PubCodigo ?? null,
    $PubId ?? null,
    $PublicacaoID ?? null,
    $Publicacao['codigopublicacoes'] ?? null,
    $publicacao['codigopublicacoes'] ?? null,
];

foreach ($candidatosPublicacao as $valor) {
    $valor = depoInt($valor);
    if ($valor > 0) {
        $idPublicacao = $valor;
        break;
    }
}

/*
|--------------------------------------------------------------------------
| CSRF Token
|--------------------------------------------------------------------------
*/
if (empty($_SESSION['csrf_depoimento'])) {
    $_SESSION['csrf_depoimento'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_depoimento'];

$mensagemSucesso = '';
$mensagemErro = '';

if (isset($_GET['depoimento']) && $_GET['depoimento'] === 'ok') {
    $mensagemSucesso = 'Depoimento enviado com sucesso. Ele ficará aguardando a liberação do professor.';
}

/*
|--------------------------------------------------------------------------
| Cadastro do depoimento
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'adicionar_depoimento') {
    try {
        if (!isset($con) || !($con instanceof PDO)) {
            throw new RuntimeException('Conexão com o banco de dados não encontrada.');
        }

        $csrfPost = (string)($_POST['csrf_depoimento'] ?? '');

        if (!hash_equals($csrfToken, $csrfPost)) {
            throw new RuntimeException('Falha de segurança. Atualize a página e tente novamente.');
        }

        if ($idUsuario <= 0) {
            throw new RuntimeException('Usuário não identificado. Faça login novamente.');
        }

        $textoCF = trim((string)($_POST['textoCF'] ?? ''));
        $textoCF = strip_tags($textoCF);

        if (mb_strlen($textoCF, 'UTF-8') < 10) {
            throw new RuntimeException('Digite um depoimento com pelo menos 10 caracteres.');
        }

        if (mb_strlen($textoCF, 'UTF-8') > 2000) {
            $textoCF = mb_substr($textoCF, 0, 2000, 'UTF-8');
        }

        $sqlInsert = "
            INSERT INTO a_curso_depoimentos
            (
                idusuarioCF,
                idartigoCF,
                idcodforumCF,
                textoCF,
                visivelCF,
                acessadoCF,
                dataCF,
                destaqueCF,
                permissaoCF,
                horaCF
            )
            VALUES
            (
                :idusuarioCF,
                :idartigoCF,
                NULL,
                :textoCF,
                0,
                0,
                CURDATE(),
                0,
                0,
                CURTIME()
            )
        ";

        $stmtInsert = $con->prepare($sqlInsert);
        $stmtInsert->bindValue(':idusuarioCF', $idUsuario, PDO::PARAM_INT);

        if ($idPublicacao > 0) {
            $stmtInsert->bindValue(':idartigoCF', $idPublicacao, PDO::PARAM_INT);
        } else {
            $stmtInsert->bindValue(':idartigoCF', null, PDO::PARAM_NULL);
        }

        $stmtInsert->bindValue(':textoCF', $textoCF, PDO::PARAM_STR);
        $stmtInsert->execute();

        $queryAtual = $_GET;
        $queryAtual['depoimento'] = 'ok';

        $urlRedirect = strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query($queryAtual);

        header('Location: ' . $urlRedirect);
        exit;
    } catch (Throwable $e) {
        $mensagemErro = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| Listagem dos depoimentos do usuário
|--------------------------------------------------------------------------
*/
$depoimentos = [];

try {
    if (isset($con) && ($con instanceof PDO) && $idUsuario > 0) {
        $wherePublicacao = '';
        $params = [
            ':idusuarioCF' => $idUsuario,
        ];

        if ($idPublicacao > 0) {
            $wherePublicacao = " AND d.idartigoCF = :idartigoCF ";
            $params[':idartigoCF'] = $idPublicacao;
        }

        $sqlDepoimentos = "
            SELECT
                d.codigodepoimento,
                d.idusuarioCF,
                d.idartigoCF,
                d.textoCF,
                d.visivelCF,
                d.dataCF,
                d.horaCF,
                d.destaqueCF,
                d.permissaoCF,
                COALESCE(likes.total_likes, 0) AS total_likes
            FROM a_curso_depoimentos d
            LEFT JOIN (
                SELECT
                    idcodigodepoimento_cdl,
                    SUM(
                        CASE
                            WHEN count_cdl IS NULL OR count_cdl <= 0 THEN 1
                            ELSE count_cdl
                        END
                    ) AS total_likes
                FROM a_curso_depoimentos_like
                GROUP BY idcodigodepoimento_cdl
            ) likes ON likes.idcodigodepoimento_cdl = d.codigodepoimento
            WHERE d.idusuarioCF = :idusuarioCF
            {$wherePublicacao}
            ORDER BY d.codigodepoimento DESC
        ";

        $stmtDepoimentos = $con->prepare($sqlDepoimentos);

        foreach ($params as $chave => $valor) {
            $stmtDepoimentos->bindValue($chave, $valor, PDO::PARAM_INT);
        }

        $stmtDepoimentos->execute();
        $depoimentos = $stmtDepoimentos->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $mensagemErro = 'Não foi possível carregar os depoimentos no momento.';
}

$totalDepoimentos = count($depoimentos);

$pageTitle = 'Depoimentos | Professor Eugênio';
$pageDescription = 'Envie seu depoimento sobre a aula e acompanhe a liberação pelo professor.';
$pageUrl = 'https://professoreugenio.com/curso/Depoimentos.php';
$pageImage = 'https://professoreugenio.com/img/logosite.png';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= depoH($pageTitle) ?></title>

    <meta name="description" content="<?= depoH($pageDescription) ?>">
    <meta name="theme-color" content="#1d468b">

    <!-- Compartilhamento em redes sociais -->
    <meta property="og:title" content="<?= depoH($pageTitle) ?>">
    <meta property="og:description" content="<?= depoH($pageDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= depoH($pageUrl) ?>">
    <meta property="og:image" content="<?= depoH($pageImage) ?>">
    <meta property="og:site_name" content="Professor Eugênio">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= depoH($pageTitle) ?>">
    <meta name="twitter:description" content="<?= depoH($pageDescription) ?>">
    <meta name="twitter:image" content="<?= depoH($pageImage) ?>">

    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/img/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&family=Caveat:wght@500;600;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/depoimentos.css">


</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 20px; flex: 1;">

        <div class="d-flex justify-content-between align-items-center pb-3 mt-4">
            <div>
                <h1 class="fw-bold mb-1">Depoimentos</h1>

                <div class="mb-4">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="index.php" class="text-decoration-none">Meus Cursos</a>
                            </li>

                            <li class="breadcrumb-item">
                                <a href="modulos.php" class="text-decoration-none border-bottom border-primary">
                                    <?= depoH($nomeCurso ?? 'Curso') ?>
                                </a>
                            </li>

                            <li class="breadcrumb-item active" aria-current="page">
                                <?= depoH($ModuloNome ?? 'Módulo') ?>
                            </li>

                            <li class="breadcrumb-item active" aria-current="page">
                                <a href="aula.php" class="text-decoration-none border-bottom border-primary">
                                    <?php
                                    $tituloAula = (string)($PubTitulo ?? 'Aula');
                                    echo mb_strlen($tituloAula, 'UTF-8') > 20
                                        ? depoH(mb_substr($tituloAula, 0, 20, 'UTF-8')) . '...'
                                        : depoH($tituloAula);
                                    ?>
                                </a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="text-muted small border bg-white rounded-pill px-3 py-1 shadow-sm">
                <?= $userTempoRestante ?? '' ?>
            </div>
        </div>

        <?php if (!empty($mensagemSucesso)): ?>
            <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
                <div
                    id="toastDepoimentoSucesso"
                    class="toast align-items-center text-bg-success border-0 shadow-lg rounded-4"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                    data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center gap-2">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span><?= depoH($mensagemSucesso) ?></span>
                        </div>

                        <button
                            type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"
                            aria-label="Fechar">
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($mensagemErro)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex gap-2 align-items-start">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?= depoH($mensagemErro) ?></div>
            </div>
        <?php endif; ?>

        <section class="depoimentos-hero mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge text-bg-primary rounded-pill mb-3">
                        <i class="bi bi-chat-heart me-1"></i>
                        Sua opinião é importante
                    </span>

                    <div class="depoimento-section-title">
                        Adicione seu depoimento sobre esta aula
                    </div>

                    <p class="text-muted mb-0">
                        Escreva como foi sua experiência, o que aprendeu e como o conteúdo ajudou no seu desenvolvimento.
                        Após o envio, o depoimento ficará aguardando a liberação do professor.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <a href="../depoimentos.php" class="btn btn-outline-primary btn-lg rounded-pill">
                        <i class="bi bi-eye me-2"></i>
                        Visualizar todos os depoimentos
                    </a>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="depoimento-form-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="depoimento-icon-box">
                            <i class="bi bi-pencil-square fs-4"></i>
                        </div>

                        <div>
                            <div class="fw-bold fs-5">Novo depoimento</div>
                            <div class="text-muted small">Compartilhe sua experiência com o curso.</div>
                        </div>
                    </div>

                    <form method="post" action="">
                        <input type="hidden" name="acao" value="adicionar_depoimento">
                        <input type="hidden" name="csrf_depoimento" value="<?= depoH($csrfToken) ?>">

                        <div class="mb-3">
                            <label for="textoCF" class="form-label fw-semibold">
                                Seu depoimento
                            </label>

                            <textarea
                                name="textoCF"
                                id="textoCF"
                                class="form-control rounded-4"
                                maxlength="2000"
                                placeholder="Digite aqui seu depoimento..."
                                required></textarea>

                            <div class="form-text">
                                Mínimo de 10 caracteres. Máximo de 2000 caracteres.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">
                            <i class="bi bi-send me-2"></i>
                            Enviar depoimento
                        </button>
                        <div class="alert alert-warning rounded-4 small mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Ao enviar, o depoimento será salvo com a situação:
                            <strong>aguardando a liberação do professor</strong>.
                        </div>

                    </form>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="depoimento-section-title">
                            Meus depoimentos adicionados
                        </div>
                        <p class="text-muted mb-0">
                            Acompanhe abaixo seus depoimentos e a quantidade de likes.
                        </p>
                    </div>

                    <div class="depoimento-counter" title="Total de depoimentos">
                        <?= (int)$totalDepoimentos ?>
                    </div>
                </div>

                <?php if ($idUsuario <= 0): ?>
                    <div class="alert alert-warning rounded-4 border-0 shadow-sm">
                        <i class="bi bi-person-exclamation me-1"></i>
                        Usuário não identificado. Faça login novamente para adicionar e visualizar seus depoimentos.
                    </div>
                <?php elseif (empty($depoimentos)): ?>
                    <div class="depoimento-card p-4 text-center">
                        <i class="bi bi-chat-square-text text-primary display-5"></i>
                        <div class="fw-bold mt-3">Nenhum depoimento enviado ainda</div>
                        <p class="text-muted mb-0">
                            Envie seu primeiro depoimento usando o formulário ao lado.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="vstack gap-3">
                        <?php foreach ($depoimentos as $depoimento): ?>
                            <?php
                            $status = depoStatusPermissao($depoimento['permissaoCF'] ?? 0);
                            $totalLikes = (int)($depoimento['total_likes'] ?? 0);
                            ?>

                            <article class="depoimento-card p-4">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="depoimento-icon-box">
                                            <i class="bi bi-chat-quote fs-4"></i>
                                        </div>

                                        <div>
                                            <div class="fw-bold">
                                                Depoimento #<?= (int)$depoimento['codigodepoimento'] ?>
                                            </div>

                                            <div class="text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i>
                                                <?= depoH(depoDataHora($depoimento['dataCF'] ?? null, $depoimento['horaCF'] ?? null)) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <span class="badge <?= depoH($status['classe']) ?> rounded-pill">
                                        <i class="bi <?= depoH($status['icone']) ?> me-1"></i>
                                        <?= depoH($status['texto']) ?>
                                    </span>
                                </div>

                                <div class="depoimento-texto mb-3">
                                    <?= nl2br(depoH($depoimento['textoCF'] ?? '')) ?>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-3">
                                    <div class="depoimento-like-badge">
                                        <i class="bi bi-heart-fill"></i>
                                        <?= $totalLikes ?>
                                        <?= $totalLikes === 1 ? 'like' : 'likes' ?>
                                    </div>

                                    <?php if ((int)($depoimento['permissaoCF'] ?? 0) !== 1): ?>
                                        <div class="text-muted small">
                                            <i class="bi bi-lock me-1"></i>
                                            Ainda não aparece na página pública.
                                        </div>
                                    <?php else: ?>
                                        <div class="text-success small fw-semibold">
                                            <i class="bi bi-unlock me-1"></i>
                                            Já pode aparecer na página pública.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-body-tertiary py-4 border-top mt-auto d-print-none">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">
                &copy; <?= date('Y') ?> Professor Eugênio
            </p>

            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item">
                    <a href="#" class="nav-link px-2 text-muted">Suporte</a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link px-2 text-muted">Termos</a>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link px-2 text-muted">Privacidade</a>
                </li>
            </ul>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../assets/js/temaToggle.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toastEl = document.getElementById('toastDepoimentoSucesso');

            if (toastEl) {
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>
</body>

</html>