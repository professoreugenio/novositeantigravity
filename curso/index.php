<?php

declare(strict_types=1);
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
define('RAIZ_ROOT', dirname(__DIR__, 1));
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

if (isset($_SESSION['dadosmodulo'])) {
    unset($_SESSION['dadosmodulo']);
}
if (isset($_SESSION['dadosdia'])) {
    unset($_SESSION['dadosdia']);
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Cursos | Professor Eugênio</title>
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
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>
    <!-- Dashboard Content -->
    <main class="container py-5" style="margin-top: 20px; flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3 mt-4">
            <div>
                <h1 class="fw-bold mb-1">Meus Cursos </h1>
                <p class="text-muted mb-0">Último acesso: <?= $ultimadata ?? 'sem registros' ?> <?= $codigoUser ?> Continue
                    de onde você parou e alcance seus objetivos.</p>
                <div><?php echo encrypt_secure($_COOKIE['registraacesso'], 'd');  ?></div>
            </div>
            <div class="text-muted small border bg-white rounded-pill px-3 py-1 shadow-sm">
                <?php echo $userTempoRestante; ?>
            </div>
        </div>
        <div class="row g-4 align-items-stretch">
            <?php
            try {
                // Initialize con if missing
                if (!isset($con) || !$con instanceof PDO) {
                    $con = config::connect();
                }

                $stmtInscricoes = $con->prepare("
                    SELECT 
                        i.codigoinscricao, 
                        i.chaveturma, 
                        c.codigocursos, 
                        c.pasta, 
                        c.nomecurso, 
                        c.bgcolor,
                        t.nometurma,
                        t.codigoturma,
                        (SELECT mfp.foto FROM new_sistema_midias_fotos_PJA mfp WHERE mfp.pasta = c.pasta LIMIT 1) as foto
                    FROM new_sistema_inscricao_PJA i
                    INNER JOIN new_sistema_cursos_turmas t ON i.chaveturma = t.chave
                    INNER JOIN new_sistema_cursos c ON t.codcursost = c.codigocursos
                    WHERE i.codigousuario = :codUser
                ");
                $stmtInscricoes->bindValue(':codUser', $codigoUser, PDO::PARAM_INT);
                $stmtInscricoes->execute();
                $inscricoes = $stmtInscricoes->fetchAll(PDO::FETCH_ASSOC);

                if (count($inscricoes) > 0) {
                    foreach ($inscricoes as $insc) {
                        $idCurso = (int) $insc['codigocursos'];
                        $idTurma = (int) $insc['codigoturma'];
                        $nomeCurso = $insc['nomecurso'];
                        $pastaCurso = $insc['pasta'];
                        $fotoDb = $insc['foto'];

                        $fotoUrl = 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80';
                        if (!empty($fotoDb) && !empty($pastaCurso)) {
                            $fotoUrl = rtrim($raizSite, '/') . '/fotos/midias/' . rawurlencode($pastaCurso) . '/' . rawurlencode($fotoDb);
                        }

                        // Total aulas do curso
                        $stmtTotal = $con->prepare("
                            SELECT COUNT(*) as total 
                            FROM a_aluno_publicacoes_cursos 
                            WHERE idcursopc = :idCurso
                        ");
                        $stmtTotal->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
                        $stmtTotal->execute();
                        $totalRow = $stmtTotal->fetch(PDO::FETCH_ASSOC);
                        $totalAulas = (int) ($totalRow['total'] ?? 0);

                        // Aulas assistidas
                        $stmtAssistidas = $con->prepare("
                            SELECT COUNT(DISTINCT idpublicaa) as assistidas 
                            FROM a_aluno_andamento_aula 
                            WHERE idalunoaa = :idUser AND idcursoaa = :idCurso
                        ");
                        $stmtAssistidas->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
                        $stmtAssistidas->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
                        $stmtAssistidas->execute();
                        $assistidasRow = $stmtAssistidas->fetch(PDO::FETCH_ASSOC);
                        $assistidas = (int) ($assistidasRow['assistidas'] ?? 0);

                        $percentualCurso = $totalAulas > 0 ? round(($assistidas / $totalAulas) * 100) : 0;
            ?>
                        <div class="col-md-6 col-lg-3" id="cursos">
                            <!-- Alterado a url para action.php com id do curso encriptado -->
                            <a href="action.php?tokemCurso=<?= time(); ?>&cur=<?= urlencode(encrypt_secure((string) $idCurso . "&" . (string) $idTurma, 'e')) ?>"
                                class="text-decoration-none text-reset d-block pb-3">
                                <div class="card h-100 shadow-sm custom-card border-0 bg-body d-flex flex-column">
                                    <div class="position-relative">
                                        <img src="<?= htmlspecialchars($fotoUrl) ?>" class="card-img-top course-img"
                                            alt="<?= htmlspecialchars((string) $nomeCurso) ?>">
                                        <span class="badge bg-primary position-absolute top-0 end-0 m-3 shadow-sm">Dados</span>
                                    </div>
                                    <div class="card-body d-flex flex-column p-2 flex-grow-1">
                                        <h6 class="card-title fw-bold mb-4 hover-primary">
                                            <?= htmlspecialchars(mb_strimwidth((string) $nomeCurso, 0, 20, '...')) ?></h6>
                                        <!-- Progress Section -->
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="small text-muted fw-medium">Progresso</span>
                                                <span class="small text-primary fw-bold"><?= $percentualCurso ?>%</span>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar progress-bar-custom bg-primary" role="progressbar"
                                                    style="width: <?= $percentualCurso ?>%" aria-valuenow="<?= $percentualCurso ?>"
                                                    aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div class="mt-4 text-end">
                                                <span class="btn btn-sm btn-outline-primary rounded-pill px-3">Continuar Aulas <i
                                                        class="bi bi-play-circle ms-1"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
            <?php
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">Você ainda não está inscrito em nenhum curso.</div></div>';
                }
            } catch (Throwable $e) {
                echo '<div class="col-12"><div class="alert alert-danger">Erro ao carregar cursos: ' . htmlspecialchars($e->getMessage()) . '</div></div>';
            }
            ?>
        </div>
    </main>
    <!-- Footer -->
    <footer class="bg-body-tertiary py-4 border-top mt-auto">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; 2026 Professor Eugênio</p>
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
</body>

</html>