 <nav class="navbar navbar-expand-lg fixed-top shadow-sm custom-nav">
        <div class="container">
            <!-- Left: Logo -->
            <a class="navbar-brand custom-logo py-0" href="index.php">
                <img src="images/logo.png" alt="Logo do Canal" class="custom-logo-img">
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
                    <li class="nav-item"><a class="nav-link" href="#cursos">Cursos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#blog">Blog</a></li>
                    <li class="nav-item"><a class="nav-link" href="#depoimentos">Depoimentos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#ebooks">Ebooks</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contato">Contato</a></li>
                </ul>
                
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-icon theme-toggle" id="theme-toggle" aria-label="Alternar Tema">
                        <!-- Sun Icon -->
                        <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        <!-- Moon Icon -->
                        <svg class="moon-icon d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                    </button>
                    
                    <?php if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true): ?>
                        <a href="/curso" class="auth-pill text-decoration-none status-logado">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="Foto do Usuário" class="auth-avatar">
                            <span class="auth-text">Painel</span>
                        </a>
                    <?php else: ?>
                        <a href="LoginAluno.php" class="auth-pill text-decoration-none">
                            <div class="auth-avatar bg-primary text-white d-flex align-items-center justify-content-center rounded-circle">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </div>
                            <span class="auth-text">Entrar</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
  
