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
    <title>Fale Conosco | Professor Eugênio</title>
    
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
    <header class="py-5 bg-dark text-white text-center position-relative" style="margin-top: 65px; background: linear-gradient(135deg, rgba(13,27,42,0.95) 0%, rgba(27,38,59,0.98) 100%), url('https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') center/cover;">
        <div class="container position-relative z-2 py-5">
            <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill fs-6 text-uppercase tracking-wider">Atendimento</span>
            <h1 class="display-4 fw-bold mb-3">Fale Conosco</h1>
            <p class="lead opacity-75 max-w-700 mx-auto">Tire dúvidas sobre os cursos, convide-nos para dar palestras na sua empresa ou solicite um orçamento de consultoria em BI e Web.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5 flex-grow-1">
        
        <div class="row g-5 align-items-lg-start mb-5">
            
            <!-- Informações de Contato -->
            <div class="col-lg-5 order-2 order-lg-1">
                <div class="pe-lg-4">
                    <h2 class="fw-bold mb-4 text-body">Entre em Contato</h2>
                    <p class="text-muted fs-5 mb-5 lh-lg">Nosso time de atendimento funciona de segunda a sexta, das 09h às 18h. Respondemos a todas as solicitações em até 24 horas úteis.</p>
                    
                    <div class="d-flex flex-column gap-4">
                        
                        <!-- Telefone/WhatsApp -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-whatsapp fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">WhatsApp de Vendas</h5>
                                <p class="text-muted mb-1">+55 (11) 99999-9999</p>
                                <a href="https://wa.me/5511999999999" target="_blank" class="text-primary fw-bold text-decoration-none small">Chamar no WhatsApp <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-envelope-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">E-mail Comercial</h5>
                                <p class="text-muted mb-1">contato@professoreugenio.com</p>
                                <a href="mailto:contato@professoreugenio.com" class="text-info-emphasis fw-bold text-decoration-none small">Enviar E-mail <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 50px; height: 50px;">
                                <i class="bi bi-geo-alt-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">Sede Escritório</h5>
                                <p class="text-muted mb-0">Av. Paulista, 1000 - Cj. 40<br>São Paulo - SP, Brasil</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Formulário de Contato -->
            <div class="col-lg-7 order-1 order-lg-2">
                <div class="card bg-body custom-card border-0 shadow-sm rounded-4 p-4 p-md-5">
                    <h3 class="fw-bold mb-4">Envie uma mensagem</h3>
                    <form onsubmit="event.preventDefault(); alert('Sua mensagem foi enviada com sucesso! Nossa equipe entrará em contato.'); this.reset();">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium text-muted small">Nome Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-muted border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 shadow-none focus-ring" placeholder="João da Silva" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-medium text-muted small">Telefone / WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent text-muted border-end-0"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0 shadow-none focus-ring" placeholder="(11) 90000-0000" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">E-mail Profissional</label>
                            <div class="input-group">
                                <span class="input-group-text bg-transparent text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0 shadow-none focus-ring" placeholder="seu@email.com" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium text-muted small">Assunto</label>
                            <select class="form-select text-muted shadow-none focus-ring py-2 border-secondary-subtle" required>
                                <option value="" disabled selected>O que você precisa?</option>
                                <option value="duvida_curso">Dúvida sobre um Curso</option>
                                <option value="consultoria">Orçamento de Consultoria (Empresas)</option>
                                <option value="palestra">Palestra / Workshop Corporativo</option>
                                <option value="suporte">Suporte Técnico (Alunos)</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-medium text-muted small">Mensagem</label>
                            <textarea class="form-control shadow-none focus-ring border-secondary-subtle" rows="5" placeholder="Descreva de forma detalhada como podemos te ajudar..." required></textarea>
                        </div>

                        <button type="submit" class="btn btn-custom-primary btn-lg w-100 rounded-pill fw-bold hover-lift shadow-sm">
                            Enviar Mensagem Seguro <i class="bi bi-send ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            
        </div>

        <hr class="my-5 opacity-10">

        <!-- FAQ Section -->
        <div class="row justify-content-center py-4">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2 class="fw-bold mb-3">Dúvidas Frequentes</h2>
                    <p class="text-muted fs-5">Abaixo listamos as perguntas que mais recebemos de novos alunos e parceiros comerciais.</p>
                </div>

                <div class="accordion custom-accordion shadow-sm" id="faqAccordion">
                    
                    <!-- FAQ 1 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true">
                                Como funciona o suporte após a compra de um curso?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show border-top border-opacity-10" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-4 lh-lg">
                                Os alunos possuem suporte diário diretamente na plataforma de estudos. Abaixo de cada aula existe uma seção de comentários e nossa equipe técnica juntamente ao Prof. Eugênio responde a todas as perguntas num prazo de até 24 ou 48 horas úteis (dependendo do volume).
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false">
                                Vocês emitem nota fiscal para empresas?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse border-top border-opacity-10" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-4 lh-lg">
                                Sim. Para compras realizadas por pessoa jurídica, nossa empresa emite automaticamente a Nota Fiscal de Serviço eletrônica (NFS-e) assim que o pagamento é confirmado, enviando o documento diretamente para o e-mail cadastrado no ato da compra.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false">
                                Qual é o tempo de acesso aos cursos adquiridos?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse border-top border-opacity-10" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-4 lh-lg">
                                Todos os nossos cursos principais possuem <strong>Acesso Vitalício</strong>. Isso significa que você paga apenas uma vez e tem acesso livre à plataforma para sempre, incluindo quaisquer atualizações futuras de módulos e material didático daquela formação sem custos adicionais.
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-bold fs-5 bg-body text-body py-4" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false">
                                Presto consultoria para empresas. Como agendo uma reunião?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse border-top border-opacity-10" data-bs-parent="#faqAccordion">
                            <div class="accordion-body text-muted p-4 lh-lg">
                                Basta preencher o formulário acima selecionando a opção "Orçamento de Consultoria (Empresas)" no assunto. Entraremos em contato via telefone ou WhatsApp no dia seguinte com um agendamento prévio no Teams/Google Meet para batermos um papo e alinharmos o projeto da sua corporação.
                            </div>
                        </div>
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
                <li class="nav-item"><a href="cursos.php" class="nav-link px-2 text-muted">Cursos</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link px-2 text-muted">Blog</a></li>
                <li class="nav-item"><a href="Ebooks.php" class="nav-link px-2 text-muted">Ebooks</a></li>
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

