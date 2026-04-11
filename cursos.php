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
    <title>Cursos Online | Professor Eugênio</title>
    
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
    <header class="py-5 bg-dark text-white text-center position-relative" style="margin-top: 65px; background: linear-gradient(135deg, rgba(13,27,42,0.9) 0%, rgba(27,38,59,0.95) 100%), url('https://images.unsplash.com/photo-1516321497487-e288fb19713f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
        <div class="container position-relative z-2 py-5">
            <h1 class="display-4 fw-bold mb-3">Escale o seu conhecimento</h1>
            <p class="lead opacity-75 max-w-700 mx-auto">Conheça nosso catálogo completo de formações online com didática aprovada por milhares de alunos.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5 flex-grow-1">
        
        <!-- Pesquisa e Filtros -->
        <div class="row align-items-center mb-5 g-3">
            <div class="col-md-6 col-lg-4">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-body border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" placeholder="Buscar cursos...">
                </div>
            </div>
            <div class="col-md-6 col-lg-8 d-flex justify-content-md-end gap-2 overflow-auto course-sidebar-scroll" style="white-space: nowrap; padding-bottom: 5px;">
                <button class="btn btn-custom-primary rounded-pill px-4">Todos</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Dados</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Design</button>
                <button class="btn btn-outline-secondary rounded-pill px-4 bg-body">Desenvolvimento Web</button>
            </div>
        </div>

        <!-- Grade de Cursos -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            
            <!-- Curso 1 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="Power BI">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Dados</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="text-muted ms-1">(1.204)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">Power BI Avançado</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">Aprenda a criar dashboards interativos e análises complexas (DAX, Star Schema) para tomada de decisões exigida pelo mercado.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 497</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 297</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Curso 2 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="Excel">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Dados</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <span class="text-muted ms-1">(854)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">Excel Expert</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">Domine desde funções fundamentais, matrizes dinâmicas até automação corporativa com Macros e noções de VBA.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 297</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 147</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Curso 3 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="Dev Web">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Desenvolvimento</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="text-muted ms-1">(2.140)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">Desenvolvimento Web (Full Stack)</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">Crie sites responsivos e aplicações interativas do zero entendendo Front-end (HTML/CSS/JS) e fundamentos de Back-end.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 697</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 397</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Curso 4 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1611162617474-5b21e879e113?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="Canva">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Design</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <span class="text-muted ms-1">(1.045)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">Canva Masterclass</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">Deixe posts e apresentações comerciais com ar extremamente caprichado em muito menos tempo com técnicas ocultas do Canva e princípios de Design.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 197</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 97</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Curso 5 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="Python">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Dados / Backend</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <span class="text-muted ms-1">(520)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">Python: Automação e Dados</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">A linguagem mais poderosa do mundo ensinada sem jargão inútil. Aprenda a automatizar processos do dia a dia e limpar grandes massas de dados via Pandas.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 597</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 347</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Curso 6 -->
            <div class="col">
                <div class="card h-100 shadow-sm custom-card border-0 overflow-hidden bg-body">
                    <a href="cursos_view.php" class="text-decoration-none text-reset h-100 d-flex flex-column">
                        <div class="position-relative">
                            <img src="https://images.unsplash.com/photo-1561070791-2526d30994b5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top course-img" alt="UX/UI Design">
                            <span class="badge bg-dark position-absolute top-0 end-0 m-3 opacity-75 px-2 py-1">Design / BI</span>
                        </div>
                        <div class="card-body d-flex flex-column p-4">
                            <div class="d-flex align-items-center gap-1 mb-2 text-warning small">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="text-muted ms-1">(780)</span>
                            </div>
                            <h4 class="card-title fw-bold text-body">UX/UI para Dashboards</h4>
                            <p class="card-text text-muted mb-4 flex-grow-1">Pare de criar painéis poluídos! Curso focado totalmente no aspecto visual de BI, cores, hierarquia visual e experiência limpa para o usuário.</p>
                            
                            <hr class="opacity-10 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">R$ 297</span>
                                    <h5 class="fw-bold text-primary mb-0">R$ 197</h5>
                                </div>
                                <span class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Ver Detalhes</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

        </div>

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

