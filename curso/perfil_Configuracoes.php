<?php

declare(strict_types=1);
define('BASEPATH', true);
define('PUBLIC_ROOT', __DIR__);
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

$codigoUsuarioAtual = (int)($codigoUser ?? $userCod ?? 0);

// Carregar os dados atuais do banco
$dadosUser = [];
if (isset($con) && $con instanceof PDO && $codigoUsuarioAtual > 0) {
    try {
        $st = $con->prepare("
            SELECT 
                codigocadastro,
                nome,
                email,
                possuipc,
                imagem50,
                emailbloqueio,
                bloqueiopost,
                datanascimento_sc,
                estado,
                celular
            FROM new_sistema_cadastro
            WHERE codigocadastro = :cod
            LIMIT 1
        ");
        $st->bindValue(':cod', $codigoUsuarioAtual, PDO::PARAM_INT);
        $st->execute();
        $dadosUser = $st->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Exception $e) {
        $dadosUser = [];
    }
}

$v = fn($k) => htmlspecialchars((string)($dadosUser[$k] ?? ''), ENT_QUOTES, 'UTF-8');
$paginaAtual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Perfil | Professor Eugênio</title>

    <meta name="description" content="Gerencie seus dados de perfil, e-mail e senha.">
    <meta property="og:title" content="Configurações do Perfil | Professor Eugênio">
    <meta property="og:description" content="Gerencie seus dados de perfil, e-mail e senha.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="/img/logosite.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap"
        rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">

    <style>
        .senha-box {
            background: rgba(13, 110, 253, 0.04);
            border: 1px solid rgba(13, 110, 253, 0.12);
            border-radius: 1rem;
            padding: 1rem;
        }

        .toast-container {
            z-index: 1080;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100 bg-body-tertiary">

    <?php include PUBLIC_ROOT . '/componentes/v1/nav.php'; ?>

    <main class="container py-5" style="margin-top: 30px; flex: 1;">
        <div class="mb-5 pb-3 border-bottom d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Painel</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Configurações</li>
                    </ol>
                </nav>

                <p class="text-muted mb-0">Gerencie suas informações pessoais e os dados de sua conta.</p>
            </div>
            <div>
                <a href="index.php" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Voltar
                </a>
            </div>
        </div>

        <div class="row g-4" id="configuracoes">

            <?php require 'componentes/v1/nav_perfil.php' ?>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-4 custom-card p-4 p-md-5 bg-body mb-4">

                    <form id="formConfiguracoes" novalidate>
                        <input type="hidden" name="codigocadastro" value="<?= $v('codigocadastro') ?>">
                        <input type="hidden" name="email_atual" value="<?= $v('email') ?>">

                        <div class="row g-4 mb-4" id="sec-identificacao">
                            <div class="col-12 border-bottom pb-2 mb-2">
                                <h5 class="fw-bold text-primary">
                                    <i class="bi bi-person-badge me-2"></i> Dados de Identificação
                                </h5>
                            </div>

                            <div class="col-md-3">
                                <label for="codigocadastro_exibir" class="form-label fw-medium text-muted">Acesso Nº</label>
                                <input type="text" class="form-control text-center fw-bold bg-light" id="codigocadastro_exibir"
                                    value="<?= $v('codigocadastro') ?>" readonly>
                                <div class="form-text small">Não mutável.</div>
                            </div>

                            <div class="col-md-9">
                                <label for="nome" class="form-label fw-medium text-dark">Nome Completo</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?= $v('nome') ?>" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-medium text-dark">Novo E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= $v('email') ?>" required>
                                <div class="form-text">Ao alterar o e-mail, a senha será regravada com o novo e-mail.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="email_confirmacao" class="form-label fw-medium text-dark">Confirmar E-mail</label>
                                <input type="email" class="form-control" id="email_confirmacao" name="email_confirmacao" value="<?= $v('email') ?>" required>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-4">
                                <label for="datanascimento_sc" class="form-label fw-medium text-dark">Data de Nascimento</label>
                                <input type="date" class="form-control" id="datanascimento_sc" name="datanascimento_sc" value="<?= $v('datanascimento_sc') ?>">
                            </div>

                            <div class="col-md-2">
                                <label for="estado" class="form-label fw-medium text-dark">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Selecione...</option>
                                    <?php
                                    $estadosStr = "AC,AL,AP,AM,BA,CE,DF,ES,GO,MA,MT,MS,MG,PA,PB,PR,PE,PI,RJ,RN,RS,RO,RR,SC,SP,SE,TO";
                                    $estadosList = explode(',', $estadosStr);
                                    $atualUF = mb_strtoupper((string)($dadosUser['estado'] ?? ''), 'UTF-8');
                                    foreach ($estadosList as $uf) {
                                        $sel = ($uf === $atualUF) ? 'selected' : '';
                                        echo '<option value="' . $uf . '" ' . $sel . '>' . $uf . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="celular" class="form-label fw-medium text-dark">Celular / WhatsApp</label>
                                <input type="text" class="form-control" id="celular" name="celular"
                                    value="<?= $v('celular') ?>" placeholder="(00) 00000-0000">
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="possuipc" class="form-label fw-medium text-dark">Possui PC para estudos?</label>
                                <select class="form-select" id="possuipc" name="possuipc">
                                    <option value="1" <?= ((string)($dadosUser['possuipc'] ?? '') === '1') ? 'selected' : '' ?>>Sim</option>
                                    <option value="0" <?= ((string)($dadosUser['possuipc'] ?? '') === '0') ? 'selected' : '' ?>>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-12">
                                <div class="senha-box">
                                    <div class="border-bottom pb-2 mb-3">
                                        <h5 class="fw-bold text-primary mb-0">
                                            <i class="bi bi-shield-lock me-2"></i> Alterar Senha
                                        </h5>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label for="nova_senha" class="form-label fw-medium text-dark">Nova Senha</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="#nova_senha">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                            <div class="form-text">Preencha apenas se quiser trocar a senha.</div>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="confirmar_senha" class="form-label fw-medium text-dark">Confirmar Nova Senha</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" autocomplete="new-password">
                                                <button type="button" class="btn btn-outline-secondary" data-toggle-password="#confirmar_senha">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mb-5 pb-3">
                            <div class="col-12 border-bottom pb-2 mb-2">
                                <h5 class="fw-bold text-primary">
                                    <i class="bi bi-shield-check me-2"></i> Status e Permissões
                                </h5>
                            </div>

                            <div class="col-md-6">
                                <label for="emailbloqueio" class="form-label fw-medium text-muted">Travamento de Conta</label>
                                <input type="text" class="form-control text-center bg-light" id="emailbloqueio"
                                    value="<?= ((int)($dadosUser['emailbloqueio'] ?? 0) > 0) ? '🔴 Conta Bloqueada' : '🟢 Conta Ativa' ?>" readonly>
                            </div>

                            <div class="col-md-6">
                                <label for="bloqueiopost" class="form-label fw-medium text-muted">Status do Fórum</label>
                                <input type="text" class="form-control text-center bg-light" id="bloqueiopost"
                                    value="<?= ((int)($dadosUser['bloqueiopost'] ?? 0) > 0) ? '🔴 Postagem Restrita' : '🟢 Postagem Livre' ?>" readonly>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                            <button type="reset" class="btn btn-light rounded-pill px-4 fw-medium">Descartar</button>

                            <button type="submit" class="btn btn-custom-primary rounded-pill px-5 fw-bold shadow-sm"
                                id="btnSalvarConfig">
                                <span class="label-btn">
                                    <i class="bi bi-floppy me-2"></i> Salvar Alterações
                                </span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

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

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="toastMsg" class="toast align-items-center border-0 text-bg-dark" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsgBody">Mensagem</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const toggleBtn = document.getElementById('theme-toggle');
        const sunIcon = document.querySelector('.sun-icon');
        const moonIcon = document.querySelector('.moon-icon');
        const htmlElement = document.documentElement;

        const savedTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-bs-theme', savedTheme);

        if (savedTheme === 'dark' && sunIcon && moonIcon) {
            sunIcon.classList.add('d-none');
            moonIcon.classList.remove('d-none');
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                htmlElement.setAttribute('data-bs-theme', newTheme);
                localStorage.setItem('theme', newTheme);

                if (newTheme === 'dark') {
                    if (sunIcon) sunIcon.classList.add('d-none');
                    if (moonIcon) moonIcon.classList.remove('d-none');
                } else {
                    if (sunIcon) sunIcon.classList.remove('d-none');
                    if (moonIcon) moonIcon.classList.add('d-none');
                }
            });
        }

        function mostrarToast(msg, sucesso = true) {
            const el = document.getElementById('toastMsg');
            const body = document.getElementById('toastMsgBody');

            el.classList.remove('text-bg-dark', 'text-bg-success', 'text-bg-danger');
            el.classList.add(sucesso ? 'text-bg-success' : 'text-bg-danger');
            body.textContent = msg;

            const toast = new bootstrap.Toast(el, {
                delay: 4000
            });
            toast.show();
        }

        function setLoading(btn, status) {
            const spinner = btn.querySelector('.spinner-border');
            const label = btn.querySelector('.label-btn');

            btn.disabled = status;

            if (status) {
                spinner.classList.remove('d-none');
                label.classList.add('d-none');
            } else {
                spinner.classList.add('d-none');
                label.classList.remove('d-none');
            }
        }

        document.querySelectorAll('[data-toggle-password]').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.querySelector(this.getAttribute('data-toggle-password'));
                const icon = this.querySelector('i');

                if (!target) return;

                if (target.type === 'password') {
                    target.type = 'text';
                    icon.className = 'bi bi-eye-slash';
                } else {
                    target.type = 'password';
                    icon.className = 'bi bi-eye';
                }
            });
        });

        const celular = document.getElementById('celular');
        if (celular) {
            celular.addEventListener('input', function(e) {
                let v = e.target.value.replace(/\D/g, '').slice(0, 11);

                if (v.length > 10) {
                    v = v.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
                } else if (v.length > 6) {
                    v = v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else if (v.length > 2) {
                    v = v.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
                } else {
                    v = v.replace(/^(\d*)/, '($1');
                }

                e.target.value = v;
            });
        }

        const formConfiguracoes = document.getElementById('formConfiguracoes');
        const btnSalvarConfig = document.getElementById('btnSalvarConfig');

        formConfiguracoes.addEventListener('submit', async function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const emailConfirmacao = document.getElementById('email_confirmacao').value.trim();
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (email !== emailConfirmacao) {
                mostrarToast('Os e-mails informados não conferem.', false);
                return;
            }

            if ((novaSenha || confirmarSenha) && novaSenha !== confirmarSenha) {
                mostrarToast('A confirmação da nova senha não confere.', false);
                return;
            }

            if (novaSenha && novaSenha.length < 4) {
                mostrarToast('A nova senha deve ter pelo menos 4 caracteres.', false);
                return;
            }

            const formData = new FormData(formConfiguracoes);

            try {
                setLoading(btnSalvarConfig, true);

                const resp = await fetch('componentes/v1/ajax_perfilConfiguracoesUpdate.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await resp.json();

                if (!resp.ok || !data.status) {
                    throw new Error(data.msg || 'Não foi possível salvar os dados.');
                }

                mostrarToast(data.msg || 'Dados atualizados com sucesso.', true);

                document.querySelector('input[name="email_atual"]').value = email;
                document.getElementById('nova_senha').value = '';
                document.getElementById('confirmar_senha').value = '';
            } catch (error) {
                mostrarToast(error.message || 'Erro ao salvar.', false);
            } finally {
                setLoading(btnSalvarConfig, false);
            }
        });
    </script>
</body>

</html>