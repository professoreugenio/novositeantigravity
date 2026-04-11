<nav class="navbar navbar-expand-lg fixed-top shadow-sm custom-nav bg-body">
    <div class="container">
        <a class="navbar-brand custom-logo py-0" href="index.php">
            <img src="../../../assets/img/logo.png" alt="Logo do Sistema de Afiliados" class="custom-logo-img" style="width: 40px; height: 40px;">
            <div class="custom-logo-text ms-2">
                <span class="custom-logo-title" style="font-size: 1.1rem; margin-bottom: 0;">Sistema de Afiliados</span>
                <span class="custom-logo-subtitle" style="font-size: 0.75rem;">Prof. Eugênio</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item"><a class="nav-link" href="../index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="meusdados.php">Meus Dados</a></li>
                <li class="nav-item"><a class="nav-link" href="produtos.php">Produtos</a></li>
                <li class="nav-item"><a class="nav-link" href="pagamentos.php">Pagamentos</a></li>
                <li class="nav-item"><a class="nav-link" href="extrato.php">Extrato</a></li>
                <li class="nav-item"><a class="nav-link" href="solicitacoes.php">Solicitações</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon theme-toggle" id="theme-toggle" aria-label="Alternar Tema">
                    <svg class="sun-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                    <svg class="moon-icon d-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
                <a href="../../../logout.php" class="btn btn-outline-danger">Sair</a>
            </div>
        </div>
    </div>
</nav>