<nav class="navbar navbar-expand-lg fixed-top shadow-sm custom-nav">
    <div class="container">
        <!-- Left: Logo -->
        <a class="navbar-brand custom-logo py-0" href="../">
            <img src="../images/logo.png" alt="Logo do Canal" class="custom-logo-img">
            <div class="custom-logo-text ms-2">
                <span class="custom-logo-title" style="font-size: 1.1rem; margin-bottom: 0;">PROF. EUGÊNIO</span>
                <span class="custom-logo-subtitle" style="font-size: 0.75rem;">Plataforma de Cursos</span>
            </div>
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Center & Right -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item"><a class="nav-link" href="index.php">Meus Cursos</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#ebooks">Ebooks</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon theme-toggle" id="theme-toggle" aria-label="Alternar Tema">
                    <!-- Sun Icon -->
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <!-- Moon Icon -->
                    <svg class="moon-icon d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <!-- User Profile Dropdown -->
                <div class="dropdown" id="PainelUsuario">
                    <a href="#" class="auth-pill text-decoration-none dropdown-toggle" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= $foto50Url; ?>"
                            alt="Foto do Usuário" class="auth-avatar">
                        <span class="auth-text d-none d-sm-inline"><?php echo $userNome ?? 'Não definido'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 custom-dropdown"
                        aria-labelledby="userDropdown">
                        <li><a class="dropdown-item py-2" href="perfil_Configuracoes.php"><i
                                    class="bi bi-gear me-2 text-muted"></i> Configurações</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i
                                    class="bi bi-person-lines-fill me-2 text-muted"></i> Afiliados</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-whatsapp me-2 text-muted"></i>
                                WhatsApp</a></li>
                        <li><a class="dropdown-item py-2" href="#"><i class="bi bi-chat-dots me-2 text-muted"></i> Bate
                                papo</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a id="logoffuser" class="dropdown-item py-2 text-danger" href="#" data-bs-toggle="modal"
                                data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Modal de Confirmação de Saída -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="logoutModalLabel">Sair da Conta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-4"
                    style="width: 80px; height: 80px;">
                    <i class="bi bi-box-arrow-right" style="font-size: 2.5rem; margin-left: 5px;"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza que deseja sair?</h4>
                <p class="text-muted mb-0">Isso encerrará sua sessão atual e você precisará fazer login novamente para
                    acessar a plataforma de cursos.</p>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4 d-flex gap-2 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-medium"
                    data-bs-dismiss="modal">Cancelar</button>
                <!-- Confirmação acessa o script responsável por limpar as sessões -->
                <a href="logout.php" class="btn btn-danger rounded-pill px-4 fw-medium shadow-sm">Sim, sair agora</a>
            </div>
        </div>
    </div>
</div>