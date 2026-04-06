<?php
declare(strict_types = 1)
;
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
echo BASEPATH;
echo PUBLIC_ROOT;
echo APP_ROOT;
echo COMPONENTES_ROOT;
require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ebooks e Materiais | Professor Eugênio</title>
    
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
<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <!-- Navbar -->
    <?php require_once PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <!-- Header Section -->
    <header class="py-5 bg-dark text-white text-center position-relative" style="margin-top: 65px; background: linear-gradient(135deg, rgba(13,27,42,0.95) 0%, rgba(27,38,59,0.98) 100%), url('https://images.unsplash.com/photo-1497633762265-9d179a990aa6?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
        <div class="container position-relative z-2 py-5">
            <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill fs-6 text-uppercase tracking-wider">Materiais Gratuitos</span>
            <h1 class="display-4 fw-bold mb-3">Biblioteca de Ebooks</h1>
            <p class="lead opacity-75 max-w-700 mx-auto">Baixe nossos conteúdos gratuitos e aprofunde seus conhecimentos agora mesmo. Guias, checklists e planilhas essenciais.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5 flex-grow-1">
        
        <!-- Pesquisa e Filtros -->
        <div class="row align-items-center mb-5 g-3">
            <div class="col-md-6 col-lg-4">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar ebook...">
                </div>
            </div>
            <div class="col-md-6 col-lg-8 d-flex justify-content-md-end gap-2 overflow-auto course-sidebar-scroll" style="white-space: nowrap; padding-bottom: 5px;">
                <button class="btn btn-custom-primary rounded-pill px-4">Todos</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Power BI</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Excel</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Carreira</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Design</button>
            </div>
        </div>

        <!-- Grade de Ebooks -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-5">
            
            <!-- Ebook 1 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-bl-3 rounded-tr-3 small fw-bold" style="border-bottom-left-radius: 12px;">Novo</div>
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Guia Definitivo Power BI</h5>
                        <p class="text-muted flex-grow-1 small">100 páginas de dicas e truques escondidos para painéis incríveis e velozes.</p>
                        <a href="#" class="btn btn-outline-primary rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 2 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-success mb-3">
                            <i class="bi bi-file-earmark-spreadsheet"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Atalhos do Excel Profissional</h5>
                        <p class="text-muted flex-grow-1 small">Domine o teclado e reduza em 50% seu tempo de operação nas planilhas do dia a dia.</p>
                        <a href="#" class="btn btn-outline-success rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 3 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-info mb-3">
                            <i class="bi bi-file-earmark-code"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Design Web Moderno</h5>
                        <p class="text-muted flex-grow-1 small">Tendências, acessibilidade e regras de performance essenciais para sites em 2026.</p>
                        <a href="#" class="btn btn-outline-info rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 4 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-danger mb-3">
                            <i class="bi bi-flower1"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Canva Masterclass</h5>
                        <p class="text-muted flex-grow-1 small">Como criar postagens envolventes utilizando técnicas simples de design gráfico.</p>
                        <a href="#" class="btn btn-outline-danger rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 5 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Curriculo Perfeito em TI</h5>
                        <p class="text-muted flex-grow-1 small">Aprenda a estruturar o currículo que passa na triagem das plataformas como Gupy e ATS.</p>
                        <a href="#" class="btn btn-outline-warning text-dark rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 6 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden position-relative">
                    <div class="position-absolute top-0 end-0 bg-primary text-white px-3 py-1 rounded-bl-3 rounded-tr-3 small fw-bold" style="border-bottom-left-radius: 12px;">Novo</div>
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-secondary mb-3">
                            <i class="bi bi-robot"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Prompt Eng. Starter</h5>
                        <p class="text-muted flex-grow-1 small">Como escrever prompts para a IA estruturar seus DAXs e blocos de código sem erros.</p>
                        <a href="#" class="btn btn-outline-secondary rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 7 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-dark mb-3">
                            <i class="bi bi-briefcase"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Guia do Freelancer</h5>
                        <p class="text-muted flex-grow-1 small">Modelos de contrato, proposta comercial e precificação para desenvolvedores e analistas.</p>
                        <a href="#" class="btn btn-outline-dark rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar pacote
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ebook 8 -->
            <div class="col">
                <div class="card h-100 shadow-sm border-0 custom-card text-center p-4 bg-body overflow-hidden">
                    <div class="card-body d-flex flex-column px-2">
                        <div class="display-4 text-indigo mb-3" style="color: #6610f2;">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Checklist Power BI</h5>
                        <p class="text-muted flex-grow-1 small">O checklist oficial de 30 pontos para homologar o seu Dashboard antes de entregar ao conselho.</p>
                        <a href="#" class="btn btn-outline-indigo rounded-pill w-100 mt-3 hover-lift d-flex align-items-center justify-content-center gap-2" style="border-color:#6610f2; color:#6610f2;">
                            <i class="bi bi-cloud-arrow-down-fill"></i> Baixar Anexo
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Pagination -->
        <nav aria-label="Navegação de página">
            <ul class="pagination justify-content-center pagination-lg border-0 mb-0">
                <li class="page-item disabled">
                    <a class="page-link border-0 bg-transparent text-muted" href="#" tabindex="-1" aria-disabled="true"><i class="bi bi-arrow-left"></i></a>
                </li>
                <li class="page-item active" aria-current="page"><a class="page-link rounded-circle mx-1" href="#">1</a></li>
                <li class="page-item"><a class="page-link rounded-circle mx-1 bg-transparent text-body border-0 fw-medium" href="#">2</a></li>
                <li class="page-item">
                    <a class="page-link border-0 bg-transparent text-body" href="#"><i class="bi bi-arrow-right"></i></a>
                </li>
            </ul>
        </nav>

    </main>

    <!-- Modal de Download (Opcional, mas dá charme ao clicar no baixar) -->
    <div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg p-3">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fs-4 fw-bold text-center w-100" id="downloadModalLabel"><i class="bi bi-cloud-arrow-down text-primary me-2"></i>Preparando Download</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pt-3">
                    <p class="text-muted mb-4">Insira seu melhor e-mail para receber o material gratuitamente e assinar nossa newsletter de novidades semanais.</p>
                    <form onsubmit="event.preventDefault(); alert('Enviado para seu e-mail!'); bootstrap.Modal.getInstance(document.getElementById('downloadModal')).hide();">
                        <input type="email" class="form-control form-control-lg mb-3 shadow-none focus-ring" placeholder="seu@melhoremail.com" required>
                        <button type="submit" class="btn btn-custom-primary btn-lg w-100 rounded-pill fw-bold">Receber Material</button>
                    </form>
                    <p class="small text-muted mt-3 mb-0">Protegemos sua privacidade. Zero Spam.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-body-tertiary py-4 border-top mt-auto">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; 2026 Professor Eugênio</p>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="cursos.php" class="nav-link px-2 text-muted">Cursos</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link px-2 text-muted">Blog</a></li>
                <li class="nav-item"><a href="Contato.php" class="nav-link px-2 text-muted">Contato</a></li>
            </ul>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Intercept download buttons to show modal
        document.querySelectorAll('a.btn-outline-primary, a.btn-outline-success, a.btn-outline-info, a.btn-outline-danger, a.btn-outline-warning, a.btn-outline-secondary, a.btn-outline-dark, a.btn-outline-indigo').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                let myModal = new bootstrap.Modal(document.getElementById('downloadModal'));
                myModal.show();
            });
        });

        // Theme Toggle Logic
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if(savedTheme === 'dark') {
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

