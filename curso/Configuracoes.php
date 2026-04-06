<?php
declare(strict_types = 1);
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
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

// Carregar os dados atuais do banco
$dadosUser = [];
if (isset($con) && $con instanceof PDO && $userCod > 0) {
    try {
        $st = $con->prepare("
            SELECT 
                codigocadastro, nome, email, possuipc, imagem50, 
                emailbloqueio, bloqueiopost, datanascimento_sc, estado, celular 
            FROM new_sistema_cadastro 
            WHERE codigocadastro = :cod LIMIT 1
        ");
        $st->bindValue(':cod', $userCod, PDO::PARAM_INT);
        $st->execute();
        $dadosUser = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        // Ignora erro por enquanto no front
    }
}

// Atalho para imprimir valores de forma segura
$v = fn($k) => htmlspecialchars((string)($dadosUser[$k] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Perfil | Professor Eugênio</title>
    
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
    <main class="container py-5" style="margin-top: 30px; flex: 1;">
        <div class="mb-5 pb-3 border-bottom d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Painel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Configurações</li>
                    </ol>
                </nav>
                <h1 class="fw-bold mb-1">Meu Perfil</h1>
                <p class="text-muted mb-0">Gerencie suas informações pessoais e os dados de sua conta.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i> Voltar</a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm rounded-4 custom-card p-4 p-md-5 bg-body">
                    
                    <form id="formConfiguracoes" method="POST" action="#">
                        
                        <div class="row g-4 mb-4">
                            <!-- Cabeçalho / Título do formulário -->
                            <div class="col-12 border-bottom pb-2 mb-2">
                                <h5 class="fw-bold text-primary"><i class="bi bi-person-badge me-2"></i> Dados de Identificação</h5>
                            </div>

                            <!-- Código de Cadastro (Bloqueado) -->
                            <div class="col-md-3">
                                <label for="codigocadastro" class="form-label fw-medium text-muted">Acesso Nº</label>
                                <input type="text" class="form-control text-center fw-bold bg-light" id="codigocadastro" name="codigocadastro" value="<?= $v('codigocadastro') ?>" readonly>
                                <div class="form-text small">Não mutável.</div>
                            </div>

                            <!-- Nome -->
                            <div class="col-md-9">
                                <label for="nome" class="form-label fw-medium text-dark">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= $v('nome') ?>" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Email -->
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium text-dark">Endereço de E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= $v('email') ?>" required>
                            </div>
                            
                            <!-- Imagem 50 (URL do Avatar) -->
                            <div class="col-md-6">
                                <label for="imagem50" class="form-label fw-medium text-dark">Foto do Perfil (URL)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-link-45deg"></i></span>
                                    <input type="url" class="form-control" id="imagem50" name="imagem50" placeholder="https://..." value="<?= $v('imagem50') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <!-- Data Nascimento -->
                            <div class="col-md-4">
                                <label for="datanascimento_sc" class="form-label fw-medium text-dark">Data de Nascimento</label>
                                <input type="date" class="form-control" id="datanascimento_sc" name="datanascimento_sc" value="<?= $v('datanascimento_sc') ?>">
                            </div>

                            <!-- Celular -->
                            <div class="col-md-4">
                                <label for="celular" class="form-label fw-medium text-dark">Celular / WhatsApp</label>
                                <input type="text" class="form-control" id="celular" name="celular" value="<?= $v('celular') ?>" placeholder="(00) 00000-0000">
                            </div>

                            <!-- Estado -->
                            <div class="col-md-4">
                                <label for="estado" class="form-label fw-medium text-dark">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Selecione...</option>
                                    <?php
                                        $estadosStr = "AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO";
                                        $estadosList = explode(',', $estadosStr);
                                        $atualUF = mb_strtoupper((string)($dadosUser['estado'] ?? ''), 'UTF-8');
                                        foreach($estadosList as $uf) {
                                            $sel = ($uf === $atualUF) ? 'selected' : '';
                                            echo "<option value=\"{$uf}\" {$sel}>{$uf}</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-5 pb-3">
                            <div class="col-12 border-bottom pb-2 mb-2">
                                <h5 class="fw-bold text-primary"><i class="bi bi-shield-check me-2"></i> Status e Permissões</h5>
                            </div>

                            <!-- Possui PC -->
                            <div class="col-md-4">
                                <label for="possuipc" class="form-label fw-medium text-dark">Possui PC para estudos?</label>
                                <select class="form-select" id="possuipc" name="possuipc">
                                    <option value="Sim" <?= ($v('possuipc') === 'Sim' || $v('possuipc') === '1') ? 'selected' : '' ?>>Sim</option>
                                    <option value="Não" <?= ($v('possuipc') === 'Não' || $v('possuipc') === '0') ? 'selected' : '' ?>>Não</option>
                                </select>
                            </div>

                            <!-- Bloqueios (Readonly) -->
                            <div class="col-md-4">
                                <label for="emailbloqueio" class="form-label fw-medium text-muted">Travamento de Conta</label>
                                <input type="text" class="form-control text-center bg-light" id="emailbloqueio" value="<?= (intval($v('emailbloqueio')) > 0) ? '🔴 Conta Bloqueada' : '🟢 Conta Ativa' ?>" readonly>
                            </div>

                            <div class="col-md-4">
                                <label for="bloqueiopost" class="form-label fw-medium text-muted">Status do Fórum</label>
                                <input type="text" class="form-control text-center bg-light" id="bloqueiopost" value="<?= (intval($v('bloqueiopost')) > 0) ? '🔴 Postagem Restrita' : '🟢 Postagem Livre' ?>" readonly>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <button type="reset" class="btn btn-light rounded-pill px-4 fw-medium">Descartar</button>
                            <button type="button" class="btn btn-custom-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSalvarConfig">
                                <i class="bi bi-floppy me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>

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
        // Lógica de Troca de Tema
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        if(savedTheme === 'dark' && sunIcon && moonIcon) {
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        }

        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                
                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                if (newTheme === 'dark') {
                    if(sunIcon) sunIcon.classList.add('d-none');
                    if(moonIcon) moonIcon.classList.remove('d-none');
                } else {
                    if(sunIcon) sunIcon.classList.remove('d-none');
                    if(moonIcon) moonIcon.classList.add('d-none');
                }
            });
        }
    </script>
</body>
</html>
