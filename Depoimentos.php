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
    <title>Depoimentos | Professor Eugênio</title>
    
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
    <header class="py-5 bg-dark text-white text-center position-relative" style="margin-top: 65px; background: linear-gradient(135deg, rgba(13,27,42,0.9) 0%, rgba(27,38,59,0.5) 100%), url('assets/img/header_bg_young_computer.png') center/cover fixed;">
        <div class="container position-relative z-2 py-5">
            <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill fs-6 text-uppercase tracking-wider">Histórias Reais</span>
            <!-- <h1 class="display-4 fw-bold mb-3">O que nossos alunos dizem</h1> -->
            <p class="lead opacity-75 max-w-700 mx-auto">Milhares de carreiras transformadas através de nossos cursos, do Power BI ao Desenvolvimento Web. Confira e inspire-se!</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5 flex-grow-1">
        
        <!-- Painel de estatísticas rápio -->
        <div class="row g-4 text-center mb-5 pb-4 border-bottom border-opacity-10 justify-content-center">
            <div class="col-6 col-md-3">
                <h3 class="display-5 fw-bold text-primary mb-1">+15k</h3>
                <p class="text-muted fw-medium m-0">Alunos Satisfeitos</p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="display-5 fw-bold text-success mb-1">4.9</h3>
                <p class="text-muted fw-medium m-0">Avaliação Média <i class="bi bi-star-fill text-warning"></i></p>
            </div>
            <div class="col-6 col-md-3">
                <h3 class="display-5 fw-bold text-info mb-1">+40</h3>
                <p class="text-muted fw-medium m-0">Horas de Conteúdo</p>
            </div>
        </div>

        <!-- Grade de Depoimentos -->
        <div class="row g-4 justify-content-center">
            
            <!-- Depoimento 1 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge">Marketing Digital</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Mariana+Costa&background=random&color=fff&rounded=true" alt="Mariana" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Mariana Costa</h5>
                            <span class="text-muted small fw-medium">Empreendedora</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Estratégias validadas que aumentaram minhas vendas online em 150%. Recomendo demais!</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 2 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #059669 0%, #10B981 100%);">Análise de Dados</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Carlos+Silva&background=0D8ABC&color=fff&rounded=true" alt="Carlos" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Carlos Silva</h5>
                            <span class="text-muted small fw-medium">Analista Pleno</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Minha promoção veio em 2 meses após aplicar tudo o que aprendi em Power BI e modelagem.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 3 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #DB2777 0%, #F43F5E 100%);">Desenvolvimento</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Lucas+Mendes&background=0dcaf0&color=fff&rounded=true" alt="Lucas" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Lucas Mendes</h5>
                            <span class="text-muted small fw-medium">Dev Full-Stack</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Hoje desenvolvo aplicações web do absoluto zero. A didática do curso é o verdadeiro diferencial.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-half text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 4 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #D97706 0%, #F59E0B 100%);">Design Gráfico</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Ana+Paula&background=dc3545&color=fff&rounded=true" alt="Ana" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Ana Paula</h5>
                            <span class="text-muted small fw-medium">Freelancer</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Minhas artes agora têm apelo altamente comercial, com técnicas que não achei no Youtube livre.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 5 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #2563EB 0%, #3B82F6 100%);">Freelancer</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Rafael+Souza&background=2563eb&color=fff&rounded=true" alt="Rafael" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Rafael Souza</h5>
                            <span class="text-muted small fw-medium">Web Designer</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Consegui fechar 3 novos clientes internacionais após aprender as táticas de prospecção do canal!</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 6 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #059669 0%, #10B981 100%);">Análise de Dados</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Juliana+M&background=0D8ABC&color=fff&rounded=true" alt="Juliana" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Juliana M.</h5>
                            <span class="text-muted small fw-medium">Consultora</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">O nível de detalhe no tratamento do Power Query foi algo que nunca vi em nenhum outro curso focado.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 7 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge" style="background: linear-gradient(135deg, #8B5CF6 0%, #A855F7 100%);">Automação</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Tiago+Ferreira&background=8B5CF6&color=fff&rounded=true" alt="Tiago" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Tiago Ferreira</h5>
                            <span class="text-muted small fw-medium">Administrativo</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">Reduzi meu trabalho de 3 dias para 2 cliques usando as macros de Excel ensinadas na Masterclass.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Depoimento 8 -->
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="testimonial-card h-100 shadow-sm bg-body">
                    <span class="testimonial-badge">Marketing Digital</span>
                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="testimonial-photo-wrap flex-shrink-0">
                            <img src="https://ui-avatars.com/api/?name=Camila+Lima&background=random&color=fff&rounded=true" alt="Camila" class="testimonial-photo">
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-body fs-6">Camila Lima</h5>
                            <span class="text-muted small fw-medium">Gestora de Tráfego</span>
                        </div>
                    </div>
                    <div class="testimonial-quote-area">
                        <p class="testimonial-quote-text mb-0">A didática do professor é fantástica. Do básico ao avançado em uma linguagem extremamente acessível.</p>
                    </div>
                    <div class="testimonial-stars mt-auto">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                    </div>
                </div>
            </div>

        </div>

    </main>
    
    <!-- CTA Final -->
    <section class="py-5 bg-custom-light text-center border-top">
        <div class="container py-4">
            <h2 class="fw-bold mb-3">Pronto para escrever a sua própria história?</h2>
            <p class="text-muted fs-5 mb-4 max-w-700 mx-auto">Junte-se a milhares de profissionais que estão escalando o sucesso sendo dominantes em ferramentas vitais.</p>
            <a href="cursos.php" class="btn btn-custom-primary btn-lg rounded-pill px-5 shadow-sm fw-bold">Escolher Meu Curso</a>
        </div>
    </section>

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

