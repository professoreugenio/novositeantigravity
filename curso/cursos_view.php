<?php

declare(strict_types=1);
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
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Curso | Professor Eugênio</title>
    <meta name="theme-color" content="#1d468b">
    <link rel="icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="../assets/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="../assets/img/favicon.ico">
    <meta name="description" content="Conheça todos os detalhes do nosso curso completo e transforme sua carreira.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <!-- Navbar -->
    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <!-- Main Content -->
    <main style="margin-top: 65px;">
        <!-- Hero Section do Curso -->
        <section class="bg-dark text-white py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(13,27,42,0.9) 0%, rgba(27,38,59,0.95) 100%), url('https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
            <div class="container py-5 position-relative z-2">
                <div class="row align-items-center gy-5">
                    <div class="col-lg-7">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php" class="text-white opacity-75 text-decoration-none">Home</a></li>
                                <li class="breadcrumb-item"><a href="cursos.php" class="text-white opacity-75 text-decoration-none">Cursos</a></li>
                                <li class="breadcrumb-item active text-white fw-bold" aria-current="page">Power BI Avançado</li>
                            </ol>
                        </nav>
                        <span class="badge bg-primary mb-3 px-3 py-2 fs-6 rounded-pill">Dados e Análise</span>
                        <h1 class="display-4 fw-bold mb-4">Formação em Power BI Avançado</h1>
                        <p class="lead opacity-75 mb-4 mb-lg-5">Domine desde a extração de dados no Power Query até modelagens complexas em DAX, e crie dashboards interativos reais requisitados pelo mercado corporativo.</p>

                        <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
                            <div class="d-flex align-items-center text-warning gap-1">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-half"></i>
                                <span class="ms-1 text-white fw-bold">4.9 <span class="fw-normal opacity-75">(1.204 avaliações)</span></span>
                            </div>
                            <div class="d-flex align-items-center text-white opacity-75 gap-2">
                                <i class="bi bi-people-fill"></i> 15.340 alunos
                            </div>
                            <div class="d-flex align-items-center text-white opacity-75 gap-2">
                                <i class="bi bi-clock-history"></i> 40h de conteúdo
                            </div>
                        </div>

                        <div class="d-flex gap-3 align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <img src="images/logo.png" alt="Instrutor" class="rounded-circle" width="45" height="45" style="object-fit: cover;">
                                <div>
                                    <span class="d-block small text-white opacity-75">Criado por</span>
                                    <span class="fw-bold">Professor Eugênio</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Course Details -->
        <section class="py-5">
            <div class="container py-4">
                <div class="row g-5">

                    <!-- Coluna Esquerda: Descrição, O que vai aprender, Grade -->
                    <div class="col-lg-8">

                        <!-- O que você vai aprender -->
                        <div class="card bg-body custom-card border-0 shadow-sm rounded-4 mb-5 p-4 p-md-5">
                            <h3 class="fw-bold mb-4">O que você vai aprender</h3>
                            <div class="row g-4">
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Conectar a múltiplas fontes de dados (Excel, SQL, Web, APIs).</span>
                                </div>
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Tratar e limpar bases de dados usando o Power Query Editor.</span>
                                </div>
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Criar modelos de dados relacionais perfeitos (Star Schema).</span>
                                </div>
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Dominar a linguagem DAX desde funções básicas até análise temporal avançada.</span>
                                </div>
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Aplicar regras de Data Storytelling e UX Design nos painéis.</span>
                                </div>
                                <div class="col-md-6 d-flex gap-3">
                                    <i class="bi bi-check2 text-primary fs-4"></i>
                                    <span class="text-muted">Publicar, compartilhar relatórios e gerenciar espaços de trabalho na nuvem.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição Completa -->
                        <div class="mb-5">
                            <h3 class="fw-bold mb-4">Descrição do Curso</h3>
                            <div class="text-muted fs-5 lh-lg">
                                <p>Vivemos na era dos dados, mas dados brutos sem interpretação não geram valor. O Power BI é hoje a ferramenta número 1 exigida por empresas no mundo inteiro para inteligência de negócios (Business Intelligence).</p>
                                <p>Este curso foi desenhado para te levar do absoluto zero até o nível avançado. Você não vai apenas aprender "onde clicar", vai aprender <strong>como pensar analiticamente</strong>.</p>
                                <p>Desenvolveremos projetos práticos baseados em cenários reais de grandes empresas: desde análises financeiras complexas, painéis de recursos humanos, até dashboards comerciais de alta performance de vendas.</p>
                                <p>Se você quer ser promovido, ganhar destaque na sua equipe, ou abrir novas portas de emprego, dominar dados é hoje o melhor caminho.</p>
                            </div>
                        </div>

                        <!-- Conteúdo do Curso (Accordion) -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h3 class="fw-bold mb-0">Conteúdo do Curso</h3>
                                <span class="text-muted fw-medium">3 Módulos • 24 Aulas</span>
                            </div>

                            <div class="accordion custom-accordion shadow-sm" id="courseAccordion">

                                <!-- Módulo 1 -->
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            Módulo 1: Introdução e Primeiros Passos
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show border-top border-opacity-10" data-bs-parent="#courseAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> Visão Geral da Ferramenta</span>
                                                    <span class="small">10:45 min</span>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> Instalação e Configuração</span>
                                                    <span class="small">08:20 min</span>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> O Fluxo de Trabalho de BI</span>
                                                    <span class="small">15:10 min</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo 2 -->
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            Módulo 2: O Poder do Power Query (ETL)
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse border-top border-opacity-10" data-bs-parent="#courseAccordion">
                                        <div class="accordion-body p-0">
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> Tratamento de Nulos e Erros</span>
                                                    <span class="small">12:30 min</span>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> Mesclar e Acrescentar Consultas</span>
                                                    <span class="small">18:15 min</span>
                                                </div>
                                                <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3 border-opacity-10 text-muted px-4">
                                                    <span><i class="bi bi-play-circle me-3 text-primary"></i> Colunas Condicionais e Agrupamento</span>
                                                    <span class="small">14:50 min</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Módulo 3 -->
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                            Módulo 3: Modelagem de Dados
                                        </button>
                                    </h2>
                                    <div id="collapseThree" class="accordion-collapse collapse border-top border-opacity-10" data-bs-parent="#courseAccordion">
                                        <div class="accordion-body text-muted p-4">
                                            Mais detalhamento abordando relacionamentos One-to-Many, tabelas Fato e Dimensão, Chaves Primárias e muito mais ao longo das outras aulas deste módulo.
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    <!-- Coluna Direita: Box de Preço e Info Lateral -->
                    <div class="col-lg-4">
                        <div class="card shadow-lg border-0 bg-body rounded-4 position-sticky" style="top: 100px;">
                            <!-- Imagem do Curso (Miniatura do Vídeo) -->
                            <div class="position-relative">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" class="card-img-top rounded-top-4" alt="Power BI Preview" style="height: 200px; object-fit: cover; filter: brightness(0.8);">
                                <div class="position-absolute top-50 start-50 translate-middle">
                                    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;" aria-label="Reproduzir vídeo">
                                        <i class="bi bi-play-fill" style="margin-left: 5px;"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-4 p-xl-5">
                                <div class="mb-4">
                                    <h2 class="display-5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        R$ 297<span class="fs-5 text-muted fw-normal">,00</span>
                                    </h2>
                                    <p class="text-danger fw-medium fs-6 mt-1 mb-0"><i class="bi bi-clock-history"></i> Oferta termina em 2 dias!</p>
                                </div>

                                <div class="d-grid gap-3 mb-4">
                                    <a href="#" class="btn btn-custom-primary btn-lg rounded-pill fw-bold shadow-sm">Comprar Agora</a>
                                    <a href="#" class="btn btn-outline-secondary btn-lg rounded-pill fw-bold">Adicionar ao Carrinho</a>
                                </div>

                                <p class="text-center text-muted small mb-4">Garantia de 7 dias de devolução incondicional. Acesso vitalício.</p>

                                <h6 class="fw-bold mb-3">Este curso inclui:</h6>
                                <ul class="list-unstyled text-muted d-flex flex-column gap-3 mb-0">
                                    <li><i class="bi bi-camera-video me-2 text-primary"></i> 40 horas de vídeo sob demanda</li>
                                    <li><i class="bi bi-file-earmark-arrow-down me-2 text-primary"></i> 15 recursos para download</li>
                                    <li><i class="bi bi-phone me-2 text-primary"></i> Acesso no dispositivo móvel e na TV</li>
                                    <li><i class="bi bi-infinity me-2 text-primary"></i> Acesso total vitalício</li>
                                    <li><i class="bi bi-trophy me-2 text-primary"></i> Certificado de conclusão</li>
                                </ul>

                                <hr class="my-4 opacity-10">

                                <div class="text-center">
                                    <a href="#" class="text-decoration-none text-muted small fw-medium hover-primary d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-share"></i> Compartilhar Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-body-tertiary py-4 border-top mt-auto">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <p class="col-md-4 mb-0 text-muted">&copy; 2026 Professor Eugênio</p>
            <ul class="nav col-md-4 justify-content-end">
                <li class="nav-item"><a href="cursos.php" class="nav-link px-2 text-muted">Cursos</a></li>
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