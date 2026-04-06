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
require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Professor Eugênio</title>
    
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
    <header class="py-5 bg-dark text-white text-center position-relative" style="margin-top: 65px; background: linear-gradient(135deg, rgba(13,27,42,0.9) 0%, rgba(27,38,59,0.95) 100%), url('https://images.unsplash.com/photo-1499951360447-b19be8fe80f5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
        <div class="container position-relative z-2 py-5">
            <h1 class="display-4 fw-bold mb-3">Blog do Prof. Eugênio</h1>
            <p class="lead opacity-75 max-w-700 mx-auto">Artigos, dicas práticas, novidades sobre tecnologia, análise de dados e design.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5 flex-grow-1">
        
        <!-- Pesquisa e Filtros -->
        <div class="row align-items-center mb-5 g-3">
            <div class="col-md-6 col-lg-4">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar artigos...">
                </div>
            </div>
            <div class="col-md-6 col-lg-8 d-flex justify-content-md-end gap-2 overflow-auto course-sidebar-scroll" style="white-space: nowrap; padding-bottom: 5px;">
                <button class="btn btn-custom-primary rounded-pill px-4">Todos</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Power BI</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Excel</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Dev Web</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Design</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Carreira</button>
            </div>
        </div>

        <!-- Destaque -->
        <div class="card bg-body border-0 shadow-sm rounded-4 overflow-hidden mb-5 custom-card">
            <div class="row g-0">
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" class="img-fluid h-100 object-fit-cover" alt="Destaque" style="min-height: 300px;">
                </div>
                <div class="col-lg-6 p-4 p-lg-5 d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                        <span class="badge bg-primary rounded-pill px-3 py-2">Power BI</span>
                        <span><i class="bi bi-calendar3"></i> 10 Mar 2026</span>
                    </div>
                    <h2 class="fw-bold mb-3 hover-primary cursor-pointer">Os Segredos de um Dashboard Perfeito no Power BI</h2>
                    <p class="text-muted fs-5 mb-4 line-clamp-3">Descubra as regras de UX e os truques de storytelling de dados que diferenciam os analistas amadores dos profissionais altamente requisitados pelas corporações modernas. Construa painéis de alto impacto em 5 passos.</p>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <div class="d-flex align-items-center gap-2">
                            <img src="images/logo.png" alt="Prof. Eugênio" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                            <div>
                                <span class="d-block fw-bold small text-body">Prof. Eugênio</span>
                            </div>
                        </div>
                        <a href="#" class="btn btn-link text-primary fw-bold text-decoration-none p-0 d-flex align-items-center gap-1">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grade de Artigos -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            
            <!-- Artigo 1 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 1" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75">Produtividade</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 05 Mar 2026</span>
                            <span><i class="bi bi-clock"></i> 4 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">Como organizar seus arquivos e rotinas no PC</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">Aprenda a estruturar pastas e ferramentas para aumentar sua velocidade e produtividade no dia a dia como desenvolvedor e analista.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Artigo 2 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1626785774573-4b799315345d?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 2" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-info position-absolute top-0 end-0 m-3 text-dark">Tech</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 28 Fev 2026</span>
                            <span><i class="bi bi-clock"></i> 6 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">O Futuro das Ferramentas No-Code</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">Até que ponto as ferramentas No-Code substituirão programadores? Uma análise de mercado e tendências para o próximo ano.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Artigo 3 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 3" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-success position-absolute top-0 end-0 m-3">Excel</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 15 Fev 2026</span>
                            <span><i class="bi bi-clock"></i> 8 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">10 Fórmulas Avançadas de Excel</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">Saia do PROCV. Conheça as funções XLOOKUP, FILTER e matrizes dinâmicas que vão reinventar suas planilhas de gestão.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Artigo 4 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 4" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3">Web</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 02 Fev 2026</span>
                            <span><i class="bi bi-clock"></i> 5 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">Front-end moderno com Vanilla JS</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">Você realmente precisa de um framework enorme? Como fazer projetos incríveis e performáticos utilizando apenas Javascript puro.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Artigo 5 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 5" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-danger position-absolute top-0 end-0 m-3">Design</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 20 Jan 2026</span>
                            <span><i class="bi bi-clock"></i> 3 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">Teoria das Cores em Interfaces</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">Os aspectos psicológicos das cores escolhidas no seu painel ou website. Acerte no visual gerando mais conforto visual e credibilidade.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Artigo 6 -->
            <div class="col">
                <div class="card h-100 border-0 shadow-sm rounded-4 custom-card overflow-hidden bg-body">
                    <div class="position-relative">
                        <img src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top" alt="Artigo 6" style="height: 220px; object-fit: cover;">
                        <span class="badge bg-secondary position-absolute top-0 end-0 m-3">Carreira</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 text-muted mb-3 small fw-medium">
                            <span><i class="bi bi-calendar3"></i> 10 Jan 2026</span>
                            <span><i class="bi bi-clock"></i> 7 min leitura</span>
                        </div>
                        <h4 class="card-title fw-bold mb-3 hover-primary cursor-pointer text-body">Como se destacar na entrevista técnica</h4>
                        <p class="card-text text-muted mb-4 flex-grow-1">O que os recrutadores de grandes empresas de TI esperam de você. Preparação técnica, mentalidade e portfolio ideal.</p>
                        <a href="#" class="text-primary fw-bold text-decoration-none mt-auto">Ler artigo <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Paginação -->
        <nav aria-label="Navegação de página">
            <ul class="pagination justify-content-center pagination-lg border-0 mb-0">
                <li class="page-item disabled">
                    <a class="page-link border-0 bg-transparent text-muted" href="#" tabindex="-1" aria-disabled="true"><i class="bi bi-arrow-left"></i></a>
                </li>
                <li class="page-item active" aria-current="page"><a class="page-link rounded-circle mx-1" href="#">1</a></li>
                <li class="page-item"><a class="page-link rounded-circle mx-1 bg-transparent text-body border-0 fw-medium" href="#">2</a></li>
                <li class="page-item"><a class="page-link rounded-circle mx-1 bg-transparent text-body border-0 fw-medium" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link border-0 bg-transparent text-body" href="#"><i class="bi bi-arrow-right"></i></a>
                </li>
            </ul>
        </nav>

    </main>

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

