<?php
declare(strict_types=1)
;
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
// ✅ pasta acima do public_html (ex.: /home/usuario)
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
$percentualCurso = isset($percentualCurso) ? (int)$percentualCurso : 0;
// --- Lógica Dinâmica ---
$activeModulo = $_SESSION['dadosmodulo'] ?? 0;
$activeModulo = (int)encrypt_secure($activeModulo,'d');
$activeDia = isset($_SESSION['dadosdia']) ? (int) $_SESSION['dadosdia'] : 1;

$modulosData = [];
try {
    $stmtModulos = $con->prepare("
        SELECT codigomodulos, nomemodulo, bgcolorsm 
        FROM new_sistema_modulos_PJA 
        WHERE codcursos = :idCurso AND visivelm = '1' AND visivelhome = 1
        ORDER BY codigomodulos ASC
    ");
    $stmtModulos->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
    $stmtModulos->execute();
    while ($row = $stmtModulos->fetch(PDO::FETCH_ASSOC)) {
        $modId = (int)$row['codigomodulos'];
        
        $stmtTotMod = $con->prepare("
            SELECT COUNT(*) as tot 
            FROM a_aluno_publicacoes_cursos 
            WHERE idmodulopc = :idMod AND visivelpc = 1
        ");
        $stmtTotMod->bindValue(':idMod', $modId, PDO::PARAM_INT);
        $stmtTotMod->execute();
        $totMod = (int)($stmtTotMod->fetch(PDO::FETCH_ASSOC)['tot'] ?? 0);
        
        $stmtAssisMod = $con->prepare("
            SELECT COUNT(DISTINCT idpublicaa) as ass 
            FROM a_aluno_andamento_aula 
            WHERE idmoduloaa = :idMod AND idcursoaa = :idCurso AND idturmaaa = :idTurma AND idalunoaa = :idUser
        ");
        $stmtAssisMod->bindValue(':idMod', $modId, PDO::PARAM_INT);
        $stmtAssisMod->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtAssisMod->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
        $stmtAssisMod->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
        $stmtAssisMod->execute();
        $assisMod = (int)($stmtAssisMod->fetch(PDO::FETCH_ASSOC)['ass'] ?? 0);
        
        $percMod = $totMod > 0 ? round(($assisMod / $totMod) * 100) : 0;
        if($percMod > 100) $percMod = 100;
        
        $modulosData[] = [
            'id' => $modId,
            'nome' => $row['nomemodulo'],
            'bgcolor' => !empty($row['bgcolorsm']) ? $row['bgcolorsm'] : 'rgba(13,110,253,0.05)',
            'tot' => $totMod,
            'assis' => $assisMod,
            'perc' => $percMod
        ];
    }
    
    if ($activeModulo == 0 && count($modulosData) > 0) {
        $stmtLastMod = $con->prepare("
            SELECT idmoduloaa FROM a_aluno_andamento_aula
            WHERE idalunoaa = :idUser AND idcursoaa = :idCurso AND idturmaaa = :idTurma
            ORDER BY dataaa DESC, horaaa DESC, codigoandamento DESC LIMIT 1
        ");
        $stmtLastMod->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
        $stmtLastMod->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtLastMod->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
        $stmtLastMod->execute();
        $lastRow = $stmtLastMod->fetch(PDO::FETCH_ASSOC);

        if ($lastRow && $lastRow['idmoduloaa'] > 0) {
            $activeModulo = (int)$lastRow['idmoduloaa'];
        } else {
            $activeModulo = $modulosData[0]['id'];
        }
    }
} catch (Throwable $e) {}

$activeModuloNome = 'Sem Módulos';
foreach ($modulosData as $m) {
    if ($m['id'] == $activeModulo) {
        $activeModuloNome = $m['nome'];
        break;
    }
}

$diasData = [];
try {
    if ($activeModulo > 0) {
        $stmtDias = $con->prepare("
            SELECT pc.diapc,
                   COUNT(DISTINCT pc.idpublicacaopc) as tot_aulas,
                   COUNT(DISTINCT a.idpublicaa) as assistidas
            FROM a_aluno_publicacoes_cursos pc
            LEFT JOIN a_aluno_andamento_aula a 
                   ON a.idpublicaa = pc.idpublicacaopc 
                  AND a.idalunoaa = :idUser
                  AND a.idcursoaa = :idCurso
                  AND a.idturmaaa = :idTurma
                  AND a.idmoduloaa = :idMod
            WHERE pc.idmodulopc = :idMod 
              AND pc.idcursopc = :idCurso 
              AND pc.visivelpc = 1 
              AND pc.aulaliberadapc = 1
            GROUP BY pc.diapc
            ORDER BY pc.diapc ASC
        ");
        $stmtDias->bindValue(':idMod', $activeModulo, PDO::PARAM_INT);
        $stmtDias->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtDias->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
        $stmtDias->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
        
        $stmtDias->execute();
        while ($row = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
            $diasData[] = [
                'dia' => (int)$row['diapc'],
                'tem_pendente' => ((int)$row['tot_aulas'] > (int)$row['assistidas'])
            ];
        }
        
        if ($activeDia === 0 && count($diasData) > 0) {
            $activeDia = $diasData[0]['dia'];
        }
    }
} catch (Throwable $e) {}

$aulasData = [];
try {
    if ($activeModulo > 0 && $activeDia > 0) {
        $stmtAulas = $con->prepare("
            SELECT p.codigopublicacoes, p.titulo, p.olho, 
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
            WHERE pc.idmodulopc = :idMod AND pc.diapc = :dia AND pc.idcursopc = :idCurso AND pc.visivelpc = 1 AND pc.aulaliberadapc = 1
            ORDER BY pc.ordempc ASC
        ");
        $stmtAulas->bindValue(':idMod', $activeModulo, PDO::PARAM_INT);
        $stmtAulas->bindValue(':dia', $activeDia, PDO::PARAM_INT);
        $stmtAulas->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtAulas->bindValue(':idTurma', $idTurma, PDO::PARAM_STR);
        $stmtAulas->bindValue(':idUser', $codigoUser, PDO::PARAM_INT);
        $stmtAulas->execute();
        while ($row = $stmtAulas->fetch(PDO::FETCH_ASSOC)) {
            $aulasData[] = $row;
        }
    }
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso <?= $nomeCurso ?> | Professor Eugênio</title>

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

    <!-- Modules Dashboard Content -->
    <main class="container py-5 d-flex flex-column justify-content-center" style="margin-top: 30px; flex: 1;">

        <div id="head-curso" class="border-bottom d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center small text-muted fw-medium">
                    <a href="index.php" class="text-decoration-none text-muted hover-primary">Meus Cursos</a>
                    <i class="bi bi-chevron-right mx-2" style="font-size: 0.75rem;"></i>
                    <span class="text-muted" title="<?= htmlspecialchars($nomeCurso) ?>">
                        <?= mb_strlen($nomeCurso, 'UTF-8') > 20 ? htmlspecialchars(mb_substr($nomeCurso, 0, 20, 'UTF-8')) . '...' : htmlspecialchars($nomeCurso) ?></span> - <?= $idCurso ?> <?= $idTurma ?>
                </div>
                <h3 class="fw-bold mb-1 text-body-emphasis"><?= $nomeTurma ?> </h3>
               <div><?php echo encrypt_secure($_COOKIE['registraacesso'],'d');  ?></div>
                <?php if (!empty($userTempoRestante)): ?>
                    <div id="count-temporestante"
                        class="text-muted small border bg-white rounded-pill px-2 py-1 shadow-sm mt-3 d-inline-block">
                        <?php echo $userTempoRestante; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
            $colorClassProgress = 'text-success';
            if ($percentualCurso < 36) {
                $colorClassProgress = 'text-danger';
            } elseif ($percentualCurso < 51) {
                $colorClassProgress = 'text-warning';
            } elseif ($percentualCurso < 76) {
                $colorClassProgress = 'text-primary';
            }
            ?>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="small fw-medium text-muted mb-1" style="font-size: 0.85rem;">Progresso do curso</div>
                    <div class="fs-3 fw-bold <?= $colorClassProgress ?>" style="line-height: 1;"><?= $percentualCurso ?>%</div>
                </div>
                <!-- Circular progress SVG -->
                <div id="Circular-progress" class="position-relative" style="width: 60px; height: 60px;">
                    <svg viewBox="0 0 36 36" class="w-100 h-100" style="transform: rotate(-90deg);">
                        <path class="text-muted opacity-25"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="currentColor" stroke-width="3" />
                        <path class="<?= $colorClassProgress ?>"
                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="currentColor" stroke-width="3"
                            stroke-dasharray="<?= $percentualCurso ?>, 100" />
                    </svg>
                </div>
            </div>
        </div>

        <div id="row-Modulos" class="row g-4 align-items-start mt-2">
            <!-- Sidebar: Modules -->
            <div class="col-12 col-lg-4">
                <div class="card bg-body border-0 shadow-sm rounded-4">
                    <div id="head-modulos-do-curso" class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4" >
                        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2" >
                            <i class="bi bi-layers text-primary"></i> Módulos do Curso <?=$activeModulo;?>
                        </h5>
                    </div>
                    <div class="card-body p-0" id="list-modulos">
                        <?php if (empty($modulosData)): ?>
                            <div class="p-4 text-center text-muted small">Nenhum módulo disponível.</div>
                            <div class="p-4 text-center text-muted small"><?= $idCurso ?></div>
                        <?php else: ?>
                            <?php foreach ($modulosData as $idx => $mod): 
                                $isActive = ($mod['id'] == $activeModulo);
                                $bgColorStyle = $isActive ? "background: linear-gradient(90deg, rgb(62 135 177 / 11%) 0%, rgb(230 132 235 / 15%) 100%);" : "";
                                $borderClass = $isActive ? "border-start border-4 border-primary position-relative" : "border-bottom";
                                
                                $colorClass = 'text-success';
                                $bgProgressClass = 'bg-success';
                                if ($mod['perc'] < 36) {
                                    $colorClass = 'text-danger';
                                    $bgProgressClass = 'bg-danger';
                                } elseif ($mod['perc'] < 51) {
                                    $colorClass = 'text-warning';
                                    $bgProgressClass = 'bg-warning';
                                } elseif ($mod['perc'] < 76) {
                                    $colorClass = 'text-primary';
                                    $bgProgressClass = 'bg-primary';
                                }
                            ?>
                            <?php
                            $encMdl = encrypt_secure($mod['id'],'e');
                           
                            ?>
                            <a href="action.php?tokemModulo=<?= time();?>&modulo=<?= urlencode($encMdl); ?>&dia=1" class="text-decoration-none text-reset d-block <?= $borderClass ?>" style="<?= $bgColorStyle ?>">
                                <div class="p-4 <?= !$isActive && $mod['perc'] === 0 ? 'opacity-75' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars((string)$mod['nome']) ?> | <?= $mod['id']?> <?=$activeModulo;?> </h6>
                                        <span class="small fw-bold <?= $colorClass ?>"><?= $mod['perc'] ?>%</span>
                                    </div>
                                    
                                    <div class="progress mb-2" style="height: 6px;">
                                        <div class="progress-bar <?= $bgProgressClass ?>" role="progressbar" style="width: <?= $mod['perc'] ?>%"
                                            aria-valuenow="<?= $mod['perc'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= $mod['tot'] ?> aulas</div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content: Search and Lessons -->
            <div class="col-12 col-lg-8">
                <!-- Search Box -->
                

                <!-- Schedule by Day -->
                <div id="list-dias" class="card bg-body border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="text-muted fw-bold mb-3 text-uppercase"
                            style="font-size: 0.8rem; letter-spacing: 0.5px;">Programação por Dia <i class="bi bi-calendar-event"></i>
                            <?= $activeModulo ?>
                        
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if (empty($diasData)): ?>
                                <span class="text-muted small">Sem programação disponível.</span> <?= $activeModulo ?>
                            <?php else: ?>
                                <?php foreach ($diasData as $diaInfo): 
                                    $diaValue = $diaInfo['dia'];
                                     $isActiveDia = ($diaValue === $activeDia);
                                    if ($isActiveDia) {
                                        $btnClass = "btn btn-primary rounded-pill px-3 py-1 fw-medium d-flex align-items-center gap-2 position-relative border-0 shadow-sm";
                                        $btnStyle = "background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;";
                                    } else {
                                        $btnClass = "btn btn-day-inactive rounded-pill px-3 py-1 fw-medium d-flex align-items-center gap-2 shadow-sm position-relative border";
                                        $btnStyle = "";
                                    }
                                    $encActiveMdl = encrypt_secure($activeModulo,'e');
                                ?>
                                <a id="btn-dia" href="action.php?tokemModulo=<?= time();?>&modulo=<?= urlencode($encActiveMdl) ?>&dia=<?= $diaValue ?>" class="<?= $btnClass ?>" style="<?= $btnStyle ?>">
                                    <?= $diaValue ?>º
                                    <?php if ($diaInfo['tem_pendente']): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle p-1 bg-warning border border-light rounded-circle" title="Lições não vistas neste dia">
                                            <span class="visually-hidden">Lições não vistas</span>
                                        </span>
                                    <?php endif; ?>
                                </a>

                              
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Lessons List -->
                <div id="list-tiulosPublicacoes" class="card bg-body border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center"
                        style="background: linear-gradient(90deg, rgb(62 135 177 / 11%) 0%, rgb(230 132 235 / 15%) 100%);">
                        <h6 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2">
                            <i class="bi bi-brightness-high text-warning"></i> <?= $activeDia ?>º - <?= htmlspecialchars($activeModuloNome) ?>
                        </h6>
                        <span class="text-muted small"><?= count($aulasData) ?> aulas</span>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($aulasData)): ?>
                            <div class="p-4 text-center text-muted small">Nenhuma aula disponível.</div>
                        <?php else: ?>
                            <?php foreach ($aulasData as $idx => $aula): 
                                $temVideoaula = !empty($aula['tem_videoaula']);
                                $temYoutube = !empty($aula['tem_youtube']);
                                $temQuest = !empty($aula['tem_questionario']);
                                $questResp = !empty($aula['quest_respondido']);
                                
                                $jaAssistiu = !empty($aula['assistido']);
                                
                                $isQuestionarioPrimary = ($temQuest && !$temVideoaula && !$temYoutube);
                                $jaConcluiu = $isQuestionarioPrimary ? $questResp : $jaAssistiu;

                                if ($jaConcluiu) {
                                    $itemBaseClass = "d-flex align-items-center p-3 px-4 border-bottom bg-body lesson-item-concluido";
                                    $itemHoverClass = "d-flex align-items-center p-3 px-4 border-bottom bg-body-secondary lesson-item-concluido";
                                    $itemStyle = "cursor: pointer;";
                                    
                                    $checkIconClass = "bi-check-lg text-success";
                                    $checkBgClass = "rounded-circle d-flex align-items-center justify-content-center me-3 bg-success bg-opacity-10";
                                    $checkStyle = "width: 40px; height: 40px; min-width: 40px;";
                                    
                                    $iconeStatusRight = "text-success";
                                } else {
                                    $itemBaseClass = "d-flex align-items-center p-3 px-4 border-bottom bg-warning bg-opacity-10 lesson-item-pendente";
                                    $itemHoverClass = "d-flex align-items-center p-3 px-4 border-bottom bg-warning bg-opacity-25 shadow-sm lesson-item-pendente";
                                    $itemStyle = "cursor: pointer;";
                                    
                                    $checkIconClass = "bi-play-fill text-warning";
                                    $checkBgClass = "rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm bg-body";
                                    $checkStyle = "width: 40px; height: 40px; min-width: 40px;";
                                    
                                    $iconeStatusRight = "text-muted";
                                }
                            ?>
                            <div id="item-listaaulas-<?= $idx ?>" class="<?= $itemBaseClass ?>" style="<?= $itemStyle ?>"
                                onmouseover="this.className='<?= $itemHoverClass ?>'"
                                onmouseout="this.className='<?= $itemBaseClass ?>'">
                                <div class="<?= $checkBgClass ?>" style="<?= $checkStyle ?>">
                                    <i class="bi <?= $checkIconClass ?> fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <?php
                                    $encIdPub = encrypt_secure($aula['codigopublicacoes'], 'e');
                                    $encModulo = encrypt_secure($activeModulo, 'e');
                                    ?>
                                    <a href="action.php?tokemPublicacao=<?= time();?>&publicacao=<?= urlencode($encIdPub) ?>&modulo=<?= urlencode($encModulo); ?>" class="text-decoration-none d-block text-body">
                                        <h6 class="fw-bold mb-0 text-body-emphasis" style="font-size: 1.05rem;"><?= ($idx + 1) ?>. <?= htmlspecialchars($aula['titulo']) ?></h6>
                                        <div class="text-secondary mt-1" style="font-size: 0.85rem; font-weight: 400;"><?= !empty($aula['olho']) ? htmlspecialchars((string)$aula['olho']) : 'Aula ' . ($idx + 1) ?></div>
                                    </a>
                                </div>
                                <div class="d-flex align-items-center gap-2 ms-3 icones_licao">
                                    <div class="d-flex gap-2 align-items-center ms-2">
                                        <?php if ($temVideoaula || $temYoutube): ?>
                                            <i class="bi bi-camera-video-fill fs-5 <?= $iconeStatusRight ?>" title="Vídeoaula"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($temQuest): ?>
                                            <i class="bi bi-card-checklist fs-5 <?= $iconeStatusRight ?>" title="Questionário"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($jaConcluiu): ?>
                                            <i class="bi bi-check-circle-fill text-success fs-5 ms-1" title="Concluído"></i>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Theme Toggle Logic
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if (savedTheme === 'dark') {
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        }

        toggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            if (newTheme === 'dark') {
                sunIcon.classList.add('d-none');
                moonIcon.classList.remove('d-none');
            } else {
                sunIcon.classList.remove('d-none');
                moonIcon.classList.add('d-none');
            }
        });
    </script>
</body>

</html>