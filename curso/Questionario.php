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

// Export CSV handler
if (isset($_GET['export_csv']) && $_GET['export_csv'] == 1 && !empty($codigoUser) && !empty($idPublicacaoAtiva)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="resultado_questionario.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Questão', 'Sua Resposta', 'Resposta Correta', 'Status']);

    try {
        $stmtQ = $con->prepare("SELECT * FROM a_curso_questionario WHERE idpublicacaocq = :idPub AND visivelcq = 1 ORDER BY ordemcq ASC");
        $stmtQ->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtQ->execute();
        $questoesExp = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        $stmtR = $con->prepare("SELECT * FROM a_curso_questionario_resposta WHERE idcursoqr = :idCurso AND idaulaqr = :idPub AND idalunoqr = :idAluno AND visivel = 1");
        $stmtR->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtR->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtR->bindValue(':idAluno', $codigoUser, PDO::PARAM_INT);
        $stmtR->execute();
        $respostasExp = $stmtR->fetchAll(PDO::FETCH_ASSOC);
        
        $respostasData = [];
        foreach($respostasExp as $rx) {
            $respostasData[$rx['idquestionarioqr']] = $rx['respostaqr'];
        }

        foreach ($questoesExp as $idx => $q) {
            $respS = $respostasData[$q['codigoquestionario']] ?? 'Não respondida';
            $respC = $q['respostacq'];
            $status = ($respS == $respC || $q['tipocq'] == 1) ? 'Correto' : 'Incorreto';
            $titulo = strip_tags($q['titulocq']);
            fputcsv($output, ["Questão " . ($idx+1) . ": " . $titulo, $respS, $respC, $status]);
        }
    } catch (Throwable $e) {}
    fclose($output);
    exit;
}

// Post Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && !empty($codigoUser)) {
    if ($_POST['acao'] === 'salvar_respostas') {
        try {
            $respostasSubmetidas = $_POST['respostas'] ?? [];
            
            // Invalidate old responses
            $del = $con->prepare("UPDATE a_curso_questionario_resposta SET visivel = 0 WHERE idcursoqr = :idCurso AND idaulaqr = :idPub AND idalunoqr = :idAluno");
            $del->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $del->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
            $del->bindValue(':idAluno', $codigoUser, PDO::PARAM_INT);
            $del->execute();

            $ins = $con->prepare("
                INSERT INTO a_curso_questionario_resposta 
                (idquestionarioqr, idalunoqr, idcursoqr, idaulaqr, respostaqr, cont, dataqr, horaqr, visivel)
                VALUES 
                (:idQuest, :idAluno, :idCurso, :idPub, :resposta, 1, CURDATE(), CURTIME(), 1)
            ");
            
            foreach ($respostasSubmetidas as $idQuest => $respostaVal) {
                // Handle JSON for type 3 arrays
                $strResp = is_array($respostaVal) ? json_encode($respostaVal) : trim((string)$respostaVal);
                $ins->bindValue(':idQuest', (int)$idQuest, PDO::PARAM_INT);
                $ins->bindValue(':idAluno', $codigoUser, PDO::PARAM_INT);
                $ins->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
                $ins->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
                $ins->bindValue(':resposta', $strResp, PDO::PARAM_STR);
                $ins->execute();
            }
            $_SESSION['msg_sucesso'] = "Questionário enviado com sucesso!";
        } catch (Throwable $e) {
            $_SESSION['msg_erro'] = "Erro ao salvar questionário: " . $e->getMessage();
        }
        header("Location: Questionario.php");
        exit;
    } elseif ($_POST['acao'] === 'refazer') {
        try {
            $del = $con->prepare("UPDATE a_curso_questionario_resposta SET visivel = 0 WHERE idcursoqr = :idCurso AND idaulaqr = :idPub AND idalunoqr = :idAluno");
            $del->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
            $del->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
            $del->bindValue(':idAluno', $codigoUser, PDO::PARAM_INT);
            $del->execute();
        } catch (Throwable $e) {}
        header("Location: Questionario.php");
        exit;
    }
}

// Fetch Questions and Answers
$questoes = [];
$respostasUser = [];
$isFinalizado = false;
$pontuacao = 0;
$totalScoreavel = 0;

if (!empty($idPublicacaoAtiva)) {
    try {
        $stmtQ = $con->prepare("SELECT * FROM a_curso_questionario WHERE idpublicacaocq = :idPub AND visivelcq = 1 ORDER BY ordemcq ASC");
        $stmtQ->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtQ->execute();
        $questoes = $stmtQ->fetchAll(PDO::FETCH_ASSOC);

        $stmtR = $con->prepare("SELECT * FROM a_curso_questionario_resposta WHERE idcursoqr = :idCurso AND idaulaqr = :idPub AND idalunoqr = :idAluno AND visivel = 1");
        $stmtR->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtR->bindValue(':idPub', $idPublicacaoAtiva, PDO::PARAM_INT);
        $stmtR->bindValue(':idAluno', $codigoUser, PDO::PARAM_INT);
        $stmtR->execute();
        $respostasQuery = $stmtR->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($respostasQuery as $r) {
            $respostasUser[$r['idquestionarioqr']] = $r['respostaqr'];
            $isFinalizado = true;
        }

        if ($isFinalizado) {
            foreach ($questoes as $q) {
                $tipo = (int)$q['tipocq'];
                if ($tipo === 1 || $tipo === 2 || $tipo === 3) {
                    $totalScoreavel++;
                    $idQ = $q['codigoquestionario'];
                    if ($tipo === 1) {
                        $pontuacao++;
                    } else {
                        if (isset($respostasUser[$idQ]) && trim($respostasUser[$idQ]) === trim($q['respostacq'])) {
                            $pontuacao++;
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {}
}

$msgSucesso = $_SESSION['msg_sucesso'] ?? '';
$msgErro = $_SESSION['msg_erro'] ?? '';
unset($_SESSION['msg_sucesso'], $_SESSION['msg_erro']);

?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Questionário | Professor Eugênio</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .question-card { display: none; }
        .question-card.active { display: block; animation: fadeIn 0.4s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .nav-btn { width: 40px; height: 40px; border-radius: 50%; font-weight: 600; display: flex; align-items: center; justify-content: center; }
        .nav-btn.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .nav-btn.answered { background-color: #198754; color: white; border-color: #198754; }
        .custom-radio-box { border: 2px solid #dee2e6; border-radius: 0.75rem; padding: 1rem; cursor: pointer; transition: all 0.2s; }
        .custom-radio-box:hover { border-color: #0d6efd; background-color: rgba(13, 110, 253, 0.03); }
        .custom-radio-box.selected { border-color: #0d6efd; background-color: rgba(13, 110, 253, 0.05); }
        .vf-group { background: #f8f9fa; border-radius: 0.5rem; padding: 1rem; margin-bottom: 0.75rem; }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 20px; flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3 mt-4">
            <div>
                <h1 class="fw-bold mb-1">Questionário</h1>
                <div class="mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Meus Cursos</a></li>
                        <li class="breadcrumb-item"><a href="modulos.php" class="text-decoration-none border-bottom border-primary"><?= htmlspecialchars($nomeCurso) ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?= $ModuloNome ?></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <a href="aula.php" class="text-decoration-none border-bottom border-primary"><?= mb_strlen($PubTitulo, 'UTF-8') > 20 ? htmlspecialchars(mb_substr($PubTitulo, 0, 20, 'UTF-8')) . '...' : htmlspecialchars($PubTitulo) ?></a>
                        </li>
                    </ol>
                </nav>
            </div>
            </div>
            <div class="text-muted small border bg-white rounded-pill px-3 py-1 shadow-sm">
                <?php echo $userTempoRestante ?? ''; ?>
            </div>
        </div>

        <?php if ($msgSucesso): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($msgSucesso) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($msgErro): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($msgErro) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($questoes)): ?>
            <div class="card shadow-sm custom-card border-0 bg-white p-5 text-center rounded-4">
                <div class="mb-3"><i class="bi bi-card-checklist text-muted" style="font-size: 3rem;"></i></div>
                <h4 class="mb-3 text-dark fw-bold">Nenhum questionário disponível</h4>
                <p class="text-muted mb-4">Ainda não há questões cadastradas para esta aula.</p>
                <a href="aula.php" class="btn btn-primary px-4 py-2 rounded-pill"><i class="bi bi-arrow-left me-2"></i> Voltar à Aula</a>
            </div>
        <?php else: ?>

            <?php if ($isFinalizado): ?>
                <!-- RESULTADOS VIEW -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 print-container">
                    <div class="bg-primary bg-gradient p-5 text-center text-white">
                        <h2 class="fw-bold mb-3">Resultados do Questionário</h2>
                        <div class="display-1 fw-bold mb-2">
                            <?= $pontuacao ?>/<?= $totalScoreavel ?>
                        </div>
                        <?php 
                            $percentual = $totalScoreavel > 0 ? round(($pontuacao / $totalScoreavel) * 100) : 0; 
                            $textoShare = "Acabei de finalizar um questionário com {$pontuacao}/{$totalScoreavel} acertos ({$percentual}%)! https://professoreugenio.com/avaliacao.php?k=123456789";
                            $urlWhatsapp = "https://api.whatsapp.com/send?text=" . rawurlencode($textoShare);
                        ?>
                        <h4 class="text-white-50 fw-semibold mb-2"><?= $percentual ?>% de aproveitamento</h4>
                        <p class="fs-5 opacity-75 mb-0">acertos na pontuação geral do questionário</p>
                    </div>
                    <div class="card-body p-4 p-md-5 bg-white">
                        <div class="d-flex flex-wrap gap-3 justify-content-center mb-5 d-print-none">
                            <form method="post" class="m-0">
                                <input type="hidden" name="acao" value="refazer">
                                <button type="submit" class="btn btn-warning fw-semibold px-4 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-arrow-clockwise me-2"></i> Refazer Questionário
                                </button>
                            </form>
                            <a href="?export_csv=1" class="btn btn-outline-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Exportar CSV
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-printer me-2"></i> Imprimir
                            </button>
                            <a href="<?= $urlWhatsapp ?>" target="_blank" class="btn btn-success fw-semibold px-4 py-2 rounded-pill shadow-sm">
                                <i class="bi bi-whatsapp me-2"></i> Compartilhar
                            </a>
                        </div>

                        <h4 class="fw-bold text-dark border-bottom pb-3 mb-4">Revisão de Respostas</h4>
                        
                        <?php foreach ($questoes as $idx => $q): 
                            $idQ = $q['codigoquestionario'];
                            $tipo = (int)$q['tipocq'];
                            $suaResp = $respostasUser[$idQ] ?? '';
                            $respCorreta = $q['respostacq'];
                            
                            $isCorreto = false;
                            if ($tipo === 1) {
                                $isCorreto = true; // Text is considered subjective
                            } else {
                                $isCorreto = (trim($suaResp) === trim($respCorreta));
                            }
                        ?>
                            <div class="mb-5 p-4 rounded-4 bg-body-tertiary <?= $tipo != 1 ? ($isCorreto ? 'border border-2 border-success' : 'border border-2 border-danger') : 'border' ?>">
                                <div class="d-flex gap-3 align-items-start">
                                    <div class="flex-shrink-0">
                                        <span class="badge bg-secondary rounded-circle p-2 fs-6 px-3"><?= ($idx + 1) ?></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="fw-semibold text-dark mb-4"><?= nl2br(htmlspecialchars($q['titulocq'])) ?></h5>
                                        
                                        <div class="row align-items-start">
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <p class="text-muted small fw-bold text-uppercase tracking-wider mb-2">Sua Resposta</p>
                                                <div class="p-3 bg-white rounded-3 shadow-sm <?= $tipo != 1 ? ($isCorreto ? 'text-success fw-semibold' : 'text-danger fw-semibold') : 'text-dark' ?>">
                                                    <?= $suaResp ? nl2br(htmlspecialchars($suaResp)) : '<i>Não respondida</i>' ?>
                                                    <?php if ($tipo != 1): ?>
                                                        <i class="bi <?= $isCorreto ? 'bi-check-circle-fill ms-2' : 'bi-x-circle-fill ms-2' ?>"></i>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if ($tipo != 1): ?>
                                            <div class="col-md-6">
                                                <p class="text-muted small fw-bold text-uppercase tracking-wider mb-2">Resposta Correta</p>
                                                <div class="p-3 bg-white rounded-3 shadow-sm text-success fw-semibold">
                                                    <?= htmlspecialchars($respCorreta) ?>
                                                    <i class="bi bi-check-circle-fill ms-2"></i>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- QUESTIONNAIRE FORM VIEW -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5">
                    
                    <!-- Navigation Numbers -->
                    <div class="d-flex flex-wrap gap-2 justify-content-center mb-5 pb-3 border-bottom" id="quizNav">
                        <?php foreach($questoes as $idx => $q): ?>
                            <button type="button" class="btn btn-outline-secondary nav-btn <?= $idx === 0 ? 'active' : '' ?>" onclick="goToQuestion(<?= $idx ?>)" id="navBtn-<?= $idx ?>">
                                <?= ($idx + 1) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <form id="formQuestionario" method="POST" action="Questionario.php">
                        <input type="hidden" name="acao" value="salvar_respostas">
                        
                        <?php foreach ($questoes as $idx => $q): 
                            $idQ = $q['codigoquestionario'];
                            $tipo = (int)$q['tipocq'];
                        ?>
                            <div class="question-card <?= $idx === 0 ? 'active' : '' ?>" id="question-<?= $idx ?>" data-index="<?= $idx ?>">
                                <h4 class="fw-bold text-dark lh-base mb-4">
                                    <span class="text-primary me-2"><?= ($idx + 1) ?>.</span> 
                                    <?= nl2br(htmlspecialchars($q['titulocq'])) ?>
                                </h4>
                                
                                <div class="options-container mb-5">
                                    <?php if ($tipo === 1): // Pergunta e Resposta ?>
                                        <textarea class="form-control bg-light fs-5 p-3 rounded-3 resposta-input" name="respostas[<?= $idQ ?>]" rows="5" placeholder="Digite sua resposta aqui..." oninput="markAnswered(<?= $idx ?>)"></textarea>
                                    
                                    <?php elseif ($tipo === 2): // Multipla escolha A,B,C,D ?>
                                        <div class="d-flex flex-column gap-3">
                                            <?php 
                                            $opts = ['A' => $q['opcaoa'], 'B' => $q['opcaob'], 'C' => $q['opcaoc'], 'D' => $q['opcaod']];
                                            foreach($opts as $letra => $txt): 
                                                if (empty($txt)) continue;
                                            ?>
                                                <label class="custom-radio-box w-100 d-flex align-items-center gap-3">
                                                    <input type="radio" name="respostas[<?= $idQ ?>]" value="<?= $letra ?>" class="form-check-input mt-0 resposta-input" style="scale: 1.3;" onchange="markAnswered(<?= $idx ?>); updateRadioStyles(this);">
                                                    <span class="fw-bold text-muted"><?= $letra ?>.</span>
                                                    <span class="text-dark fs-6"><?= htmlspecialchars($txt) ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    
                                    <?php elseif ($tipo === 3): // V e F ?>
                                        <div class="d-flex flex-column gap-3">
                                            <?php 
                                            $opts = ['A' => $q['opcaoa'], 'B' => $q['opcaob'], 'C' => $q['opcaoc'], 'D' => $q['opcaod']];
                                            foreach($opts as $letra => $txt): 
                                                if (empty($txt)) continue;
                                            ?>
                                                <div class="vf-group d-flex align-items-center justify-content-between gap-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="fw-bold text-muted"><?= $letra ?>.</span>
                                                        <span class="text-dark"><?= htmlspecialchars($txt) ?></span>
                                                    </div>
                                                    <div class="d-flex gap-3 bg-white p-2 rounded-3 shadow-sm">
                                                        <div class="form-check form-check-inline m-0">
                                                            <input class="form-check-input resposta-input vf-input" type="radio" name="respostas[<?= $idQ ?>][<?= $letra ?>]" id="vf_V_<?= $idQ ?>_<?= $letra ?>" value="V" onchange="markAnswered(<?= $idx ?>)">
                                                            <label class="form-check-label fw-bold text-success" for="vf_V_<?= $idQ ?>_<?= $letra ?>">V</label>
                                                        </div>
                                                        <div class="form-check form-check-inline m-0">
                                                            <input class="form-check-input resposta-input vf-input" type="radio" name="respostas[<?= $idQ ?>][<?= $letra ?>]" id="vf_F_<?= $idQ ?>_<?= $letra ?>" value="F" onchange="markAnswered(<?= $idx ?>)">
                                                            <label class="form-check-label fw-bold text-danger" for="vf_F_<?= $idQ ?>_<?= $letra ?>">F</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Actions -->
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <button type="button" class="btn btn-light px-4 py-2 rounded-pill fw-semibold" onclick="prevQuestion()" <?= $idx === 0 ? 'disabled' : '' ?>><i class="bi bi-chevron-left me-2"></i> Anterior</button>
                                    
                                    <div class="d-flex gap-3">
                                        <?php if ($idx < count($questoes) - 1): ?>
                                            <button type="button" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold" onclick="nextQuestion()">Pular <i class="bi bi-skip-forward ms-2"></i></button>
                                            <button type="button" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold" onclick="nextQuestion()">Próxima <i class="bi bi-chevron-right ms-2"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-success px-5 py-2 rounded-pill fw-bold shadow" onclick="submitForm()"><i class="bi bi-check-circle-fill me-2"></i> Finalizar Questionário</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <!-- Footer -->
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/temaToggle.js"></script>
    
    <script>
        const totalQuestions = <?= count($questoes) ?>;
        let currentIndex = 0;

        function goToQuestion(index) {
            document.querySelectorAll('.question-card').forEach(card => card.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
            
            document.getElementById('question-' + index).classList.add('active');
            document.getElementById('navBtn-' + index).classList.add('active');
            currentIndex = index;
        }

        function nextQuestion() {
            if (currentIndex < totalQuestions - 1) {
                goToQuestion(currentIndex + 1);
            }
        }

        function prevQuestion() {
            if (currentIndex > 0) {
                goToQuestion(currentIndex - 1);
            }
        }

        function markAnswered(index) {
            let isAnswered = false;
            const container = document.getElementById('question-' + index);
            
            // Check textareas
            const textareas = container.querySelectorAll('textarea');
            textareas.forEach(ta => { if(ta.value.trim() !== '') isAnswered = true; });
            
            // Check radios (type 2)
            const type2Radios = container.querySelectorAll('.custom-radio-box input[type="radio"]');
            type2Radios.forEach(r => { if(r.checked) isAnswered = true; });

            // Check VF (type 3)
            const vfInputs = container.querySelectorAll('.vf-input');
            if (vfInputs.length > 0) {
                // Must have at least one selected to mark as answered, but let's make it smarter:
                // Actually to mark answered, they should have checked at least one.
                let anyChecked = Array.from(vfInputs).some(r => r.checked);
                if(anyChecked) isAnswered = true;
            }

            const btn = document.getElementById('navBtn-' + index);
            if (isAnswered) {
                btn.classList.add('answered');
            } else {
                btn.classList.remove('answered');
            }
        }

        function updateRadioStyles(element) {
            const container = element.closest('.options-container');
            container.querySelectorAll('.custom-radio-box').forEach(box => {
                box.classList.remove('selected');
            });
            if (element.checked) {
                element.closest('.custom-radio-box').classList.add('selected');
            }
        }

        function submitForm() {
            let unanswered = [];
            for (let i = 0; i < totalQuestions; i++) {
                if (!document.getElementById('navBtn-' + i).classList.contains('answered')) {
                    unanswered.push(i + 1);
                }
            }

            if (unanswered.length > 0) {
                const proceed = confirm(`Atenção: Você não respondeu as questões: ${unanswered.join(', ')}.\nDeseja enviar assim mesmo?`);
                if (!proceed) return;
            }
            
            document.getElementById('formQuestionario').submit();
        }
    </script>
</body>
</html>