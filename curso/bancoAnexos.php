<?php
require_once 'componentes/v1/Query_head.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryUsuario.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryCurso.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryModulo.php';
require_once PUBLIC_ROOT . '/componentes/v1/QueryPublicacao.php';

$modulosAnexos = [];
$totalFiles = 0;

if (!empty($idCurso)) {
    try {
        $stmtMods = $con->prepare("
            SELECT pc.idmoduloorigem as codigomodulos, m.nomemodulo 
            FROM a_aluno_publicacoes_cursos pc
            INNER JOIN new_sistema_modulos_PJA m ON pc.idmoduloorigem = m.codigomodulos
            WHERE pc.idcursopc = :idCurso
            GROUP BY pc.idmoduloorigem, m.nomemodulo
        ");
        $stmtMods->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmtMods->execute();
        $modulos = $stmtMods->fetchAll(PDO::FETCH_ASSOC);

        $listModulosId = array_column($modulos, 'codigomodulos');
        if (!empty($listModulosId)) {
            $inQuery = implode(',', array_map('intval', $listModulosId));

            $stmtAnexos = $con->query("
                SELECT * 
                FROM new_sistema_publicacoes_anexos_PJA
                WHERE idmodulo_pa IN ($inQuery) 
                ORDER BY datapa DESC, horapa DESC
            ");
            $todosAnexos = $stmtAnexos->fetchAll(PDO::FETCH_ASSOC);
            $totalFiles = count($todosAnexos);
        } else {
            $todosAnexos = [];
            $totalFiles = 0;
        }

        $modulosNomes = array_column($modulos, 'nomemodulo', 'codigomodulos');
    } catch (Throwable $e) {
        $dbgError = $e->getMessage();
    }
}


?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banco de Anexos | Professor Eugênio</title>
    <meta name="theme-color" content="#1d468b">
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/img/favicon.ico">
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
        .filter-btn {
            transition: all 0.2s;
            border-width: 2px;
        }

        .filter-btn.active {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
            box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
        }

        .anexo-item {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .anexo-item:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }

        .file-icon-box {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">
    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 20px; flex: 1;">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 mt-4">
            <div>
                <h1 class="fw-bold mb-1">Banco de Anexos</h1>
                <div class="mb-2">
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

                <?php
                // ============= DEBUG START =============
                if (isset($_GET['debug'])) {
                    echo "<pre style='background:#111; color:#0f0; padding:20px; z-index:9999; position:relative;'>";
                    echo "========= DEBUG LOG =========\n";
                    echo "\$idCurso: " . var_export($idCurso ?? 'NULA', true) . "\n";
                    if (isset($dbgError)) echo "ERRO SQL: " . $dbgError . "\n";
                    echo "Módulos Encontrados (" . count($modulos ?? []) . "):\n";
                    print_r($modulos ?? []);
                    echo "Lista de Modulos IDs (\$listModulosId):\n";
                    print_r($listModulosId ?? []);
                    echo "\nResultado de Anexos Encontrados:\n";
                    print_r($todosAnexos ?? []);
                    echo "=============================\n";
                    echo "</pre>";
                }
                // ============= DEBUG END =============
                ?>
            </div>
            <div class="text-muted small border bg-white rounded-pill px-3 py-1 shadow-sm fs-6 fw-medium d-flex align-items-center gap-2">
                <i class="bi bi-cloud-arrow-down text-primary"></i> <?= $totalFiles ?> Arquivos
            </div>
        </div>

        <?php if ($totalFiles === 0): ?>
            <div class="card shadow-sm border-0 rounded-4 bg-white p-5 text-center mt-4">
                <div class="mb-3"><i class="bi bi-folder-x text-muted" style="font-size: 3.5rem;"></i></div>
                <h4 class="fw-bold text-dark mb-3">Nenhum anexo encontrado</h4>
                <p class="text-muted mb-4 opacity-75 fs-5">Este curso ainda não possui arquivos ou materiais de apoio cadastrados nos módulos.</p>
                <a href="index.php" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold">Voltar aos Cursos</a>
            </div>
        <?php else: ?>
            <!-- Filters -->
            <div class="d-flex flex-wrap gap-2 mb-4 list-filters">
                <button class="btn btn-outline-secondary filter-btn active rounded-pill px-4" data-filter="all">Todos</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="pdf"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> PDF</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="doc"><i class="bi bi-file-earmark-word-fill text-primary me-1"></i> DOC</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="xls"><i class="bi bi-file-earmark-excel-fill text-success me-1"></i> XLS</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="ppt"><i class="bi bi-file-earmark-slides-fill text-warning me-1"></i> PPT</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="zip"><i class="bi bi-file-earmark-zip-fill text-dark me-1"></i> ZIP/RAR</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="img"><i class="bi bi-image-fill text-info me-1"></i> Imagens</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="txt"><i class="bi bi-file-earmark-text-fill text-secondary me-1"></i> TXT</button>
                <button class="btn btn-outline-secondary filter-btn rounded-pill px-3" data-filter="link"><i class="bi bi-link-45deg text-primary fs-5 me-1" style="vertical-align: middle;"></i> Links</button>
            </div>

            <!-- List -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 list-container mb-5">
                <?php
                $numeroOrdem = 0;
                foreach ($todosAnexos as $anexo):
                    $numeroOrdem++;
                    $isUrl = ($anexo['urlpa'] !== '#' && filter_var($anexo['urlpa'], FILTER_VALIDATE_URL));
                    $ext = strtolower(trim((string)$anexo['extpa']));

                    $typeGrp = 'outros';
                    $iconClass = "bi-file-earmark-fill text-secondary";
                    $bgColor = "bg-secondary";
                    $textColor = "text-secondary";

                    if ($isUrl) {
                        $isDrive = stripos($anexo['urlpa'], 'drive.google.com') !== false || stripos($anexo['urlpa'], 'drive') !== false;
                        $typeGrp = 'link';
                        $iconClass = $isDrive ? "bi-google text-success" : "bi-link-45deg text-primary";
                        $bgColor = $isDrive ? "bg-success" : "bg-primary";
                        $textColor = $isDrive ? "text-success" : "text-primary";
                    } else {
                        if (in_array($ext, ['pdf'])) {
                            $typeGrp = 'pdf';
                            $iconClass = "bi-file-earmark-pdf-fill text-danger";
                            $bgColor = "bg-danger";
                            $textColor = "text-danger";
                        } elseif (in_array($ext, ['doc', 'docx'])) {
                            $typeGrp = 'doc';
                            $iconClass = "bi-file-earmark-word-fill text-primary";
                            $bgColor = "bg-primary";
                            $textColor = "text-primary";
                        } elseif (in_array($ext, ['xls', 'xlsx'])) {
                            $typeGrp = 'xls';
                            $iconClass = "bi-file-earmark-excel-fill text-success";
                            $bgColor = "bg-success";
                            $textColor = "text-success";
                        } elseif (in_array($ext, ['ppt', 'pptx'])) {
                            $typeGrp = 'ppt';
                            $iconClass = "bi-file-earmark-slides-fill text-warning";
                            $bgColor = "bg-warning";
                            $textColor = "text-warning";
                        } elseif (in_array($ext, ['txt'])) {
                            $typeGrp = 'txt';
                            $iconClass = "bi-file-earmark-text-fill text-secondary";
                            $bgColor = "bg-secondary";
                            $textColor = "text-secondary";
                        } elseif (in_array($ext, ['zip', 'rar'])) {
                            $typeGrp = 'zip';
                            $iconClass = "bi-file-earmark-zip-fill text-dark";
                            $bgColor = "bg-dark";
                            $textColor = "text-dark";
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                            $typeGrp = 'img';
                            $iconClass = "bi-image-fill text-info";
                            $bgColor = "bg-info";
                            $textColor = "text-info";
                        }
                    }

                    $pastaPrefix = !empty($anexo['pastapa']) ? htmlspecialchars((string)$anexo['pastapa']) . "/" : "";
                    $fileUrl = $isUrl ? htmlspecialchars($anexo['urlpa']) : "/anexos/publicacoes/" . $pastaPrefix . htmlspecialchars((string)$anexo['anexopa']);
                    $downloadAttr = $isUrl ? 'target="_blank"' : 'download="' . htmlspecialchars((string)$anexo['titulopa'] . '.' . $ext) . '" target="_blank"';

                    $nomeModuloDesc = $modulosNomes[$anexo['idmodulo_pa']] ?? 'Módulo Desconhecido';
                ?>
                    <div class="col anexo-item item-type-<?= $typeGrp ?> d-flex">
                        <div class="card shadow-sm border-0 rounded-4 w-100 position-relative text-center d-flex flex-column pt-4 p-3 transition-hover" style="transition: transform 0.2s;">
                            <!-- Contador -->
                            <span class="position-absolute top-0 start-0 m-2 badge bg-light border text-secondary rounded-circle shadow-sm" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 0.75rem;"><?= $numeroOrdem ?></span>

                            <div class="mb-3 d-flex justify-content-center">
                                <?php if ($typeGrp === 'img' && !$isUrl): ?>
                                    <div class="file-icon-box shadow-sm border" style="overflow: hidden; background: #e9ecef; cursor: pointer; width: 60px; height: 60px;"
                                        data-img-src="<?= $fileUrl ?>"
                                        onclick="event.preventDefault(); document.getElementById('lightboxImage').src=this.dataset.imgSrc; var m = new bootstrap.Modal(document.getElementById('lightboxModal')); m.show();">
                                        <img src="<?= $fileUrl ?>" class="w-100 h-100 object-fit-cover">
                                    </div>
                                <?php else: ?>
                                    <div class="file-icon-box <?= $bgColor ?> bg-opacity-10 <?= $textColor ?>" style="width: 60px; height: 60px;">
                                        <i class="bi <?= $iconClass ?> fs-3"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h6 class="fw-bold text-dark mb-1 flex-grow-1" style="font-size: 0.95rem; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;"><?= htmlspecialchars((string)$anexo['titulopa']) ?></h6>

                            <div class="text-muted small fw-medium mb-3 mt-1 px-1" style="font-size: 0.75rem; line-height: 1.2;">
                                <i class="bi bi-folder2-open opacity-75"></i> <?= htmlspecialchars((string)$nomeModuloDesc) ?>
                            </div>

                            <div class="mt-auto pt-3 border-top w-100 d-flex flex-column gap-2">
                                <?php if ($typeGrp === 'img' && !$isUrl): ?>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill fw-medium w-50"
                                            data-img-src="<?= $fileUrl ?>"
                                            onclick="event.preventDefault(); document.getElementById('lightboxImage').src=this.dataset.imgSrc; var m = new bootstrap.Modal(document.getElementById('lightboxModal')); m.show();">
                                            <i class="bi bi-eye"></i> Ver
                                        </button>
                                        <a href="<?= $fileUrl ?>" <?= $downloadAttr ?> class="btn btn-sm btn-outline-primary rounded-pill fw-medium w-50">
                                            <i class="bi bi-download"></i> Baixar
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= $fileUrl ?>" <?= $downloadAttr ?> class="btn btn-sm w-100 <?= $isUrl ? 'btn-outline-success' : 'btn-outline-primary' ?> rounded-pill fw-medium">
                                        <?php if ($isUrl): ?>
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Acessar Link
                                        <?php else: ?>
                                            <i class="bi bi-download me-1"></i> Baixar
                                        <?php endif; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Modal Lightbox (Visualização de Imagens) -->
    <div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body text-center p-0 position-relative">
                    <img id="lightboxImage" src="" class="img-fluid rounded shadow-lg bg-dark" style="max-height: 85vh;">
                </div>
            </div>
        </div>
    </div>

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
        document.addEventListener('DOMContentLoaded', function() {
            const filterBtns = document.querySelectorAll('.filter-btn');
            const items = document.querySelectorAll('.anexo-item');

            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');

                    const filterValue = this.getAttribute('data-filter');

                    items.forEach(item => {
                        if (filterValue === 'all' || item.classList.contains('item-type-' + filterValue)) {
                            item.classList.remove('d-none');
                            item.classList.add('d-flex');
                        } else {
                            item.classList.remove('d-flex');
                            item.classList.add('d-none');
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>