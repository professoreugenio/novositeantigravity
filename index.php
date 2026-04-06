<?php
declare(strict_types = 1);
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
// ✅ pasta acima do public_html (ex.: /home/usuario)
define('APP_ROOT', dirname(__DIR__, 1));
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
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle; ?></title>
    <meta name="description" content="<?= $metaDescription; ?>">
    <meta name="keywords" content="<?= $metaKeywords; ?>">
    <meta name="author" content="<?= $autor; ?>">
    
    <!-- Open Graph / Social Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $URL_ATUAL; ?>">
    <meta property="og:title" content="<?= $ogTitle; ?>">
    <meta property="og:description" content="<?= $ogDescription; ?>">
    <meta property="og:image" content="<?= $ogImage; ?>">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $URL_ATUAL; ?>">
    <meta name="twitter:title" content="<?= $twitterTitle; ?>">
    <meta name="twitter:description" content="<?= $twitterDescription; ?>">
    <meta name="twitter:image" content="<?= $ogImage; ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar -->

    <?php require_once PUBLIC_ROOT . '/componentes/home/v1/nav.php'; ?>
    <!-- Hero Section -->
    <header class="hero position-relative d-flex align-items-center">
        <div class="hero-bg-image"></div>
        <div class="hero-overlay"></div>
        <div class="container position-relative z-2">
            <div class="row align-items-center gy-5">
                <div class="col-lg-6">
                    <div class="badge-custom mb-4">🚀 Eleve o nível da sua carreira</div>
                    <h1 class="display-4 fw-bold mb-4 text-white">Domine Dados, Design e <span class="gradient-text">Desenvolvimento</span></h1>
                    <p class="lead text-light opacity-75 mb-5">Aprenda de forma prática com cursos focados no mercado. Tenha acesso a conteúdos exclusivos de Power BI, Excel, Canva, Design Gráfico e Web.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="#cursos" class="btn btn-custom-primary btn-lg rounded-pill px-4">Ver Cursos</a>
                        <a href="#consultoria" class="btn btn-outline-light btn-lg rounded-pill px-4">Consultoria</a>
                        <a href="LoginAluno.php" class="btn btn-outline-light btn-lg rounded-pill px-4">Aluno</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" alt="Professor" class="img-fluid rounded-4 shadow-lg hero-image">
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Cursos -->
    <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireCursos.php'; ?>

    <!-- Consultoria e Serviços -->
    <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireConsultorias.php'; ?>

    <!-- Aplicações Web -->
    <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireAplicacoes.php'; ?>

    <!-- Ebooks Section -->
    <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireEbooks.php'; ?>
    <!-- Blog Section -->
   <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireBlog.php'; ?>

    <!-- Depoimentos -->
   <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireDepoimentos.php'; ?>

    <!-- Contato -->
   <?php require_once PUBLIC_ROOT . '/componentes/home/v1/requireContato.php'; ?>

    <!-- Footer -->
    <?php require_once PUBLIC_ROOT . '/componentes/v1/footer.php'; ?>

    <!-- Welcome Modal (Bootstrap Native) -->
    <div class="modal fade" id="welcomeModal" tabindex="-1" aria-labelledby="welcomeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fs-4 fw-bold w-100 text-center" id="welcomeModalLabel">Seja muito bem-vindo! 👋</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-2">
                    <p class="text-muted mb-4">O que você está procurando hoje?</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-custom-primary btn-lg rounded-pill" data-bs-dismiss="modal">Sou Aluno (Login)</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill" data-bs-dismiss="modal" onclick="location.href='#ebooks'">Baixe Ebooks</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg rounded-pill" data-bs-dismiss="modal" onclick="location.href='#cursos'">Cursos</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Social Share Bar -->
    <div class="social-share-bar">
        <button class="share-btn share-facebook" aria-label="Compartilhar no Facebook">
            <i class="bi bi-facebook"></i>
        </button>
        <button class="share-btn share-twitter" aria-label="Compartilhar no Twitter/X">
            <i class="bi bi-twitter-x"></i>
        </button>
        <button class="share-btn share-linkedin" aria-label="Compartilhar no LinkedIn">
            <i class="bi bi-linkedin"></i>
        </button>
        <button class="share-btn share-whatsapp" aria-label="Compartilhar via WhatsApp">
            <i class="bi bi-whatsapp"></i>
        </button>
        <button class="share-btn share-link" aria-label="Copiar Link" onclick="navigator.clipboard.writeText(window.location.href); alert('Link copiado!');">
            <i class="bi bi-link-45deg"></i>
        </button>
    </div>

    

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="componentes/scripts/togglewhatssocialm.js?<?= time() ?>"></script>
    
    <!-- Registro de Acesso AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.cookie.split('; ').find(row => row.startsWith('registraacesso='))) {
                fetch('componentes/v1/ajax_registraacesso.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            console.log('Acesso registrado com sucesso.');
                        }
                    })
                    .catch(error => console.error('Erro ao registrar acesso:', error));
            }
        });
    </script>
    
    <!-- Hidden Admin Link -->
    <a href="LoginAdm.php" target="_blank" style="position: fixed; bottom: 0; left: 0; width: 50px; height: 50px; z-index: 9999; display: block; background-color: transparent;"></a>
</body>
</html>
