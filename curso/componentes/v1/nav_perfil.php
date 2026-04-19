<div class="col-lg-3">
    <div class="perfil-menu-lateral">
        <div class="perfil-menu-titulo">Meu Perfil</div>

        <div class="d-grid gap-2">
            <a href="perfil_Configuracoes.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_Configuracoes.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-person-gear"></i></span>
                <span>Editar Perfil</span>
            </a>

            <a href="perfil_fotos.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_fotos.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-image"></i></span>
                <span>Atualizar foto</span>
            </a>

            <a href="perfil_redessociais.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_redessociais.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-share"></i></span>
                <span>Redes sociais</span>
            </a>

            <a href="perfil_ranking.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_ranking.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-trophy"></i></span>
                <span>Meu Ranking</span>
            </a>

            <a href="perfil_mascote.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_mascote.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-emoji-smile"></i></span>
                <span>Mascote</span>
            </a>

            <a href="perfil_termos.php"
                class="btn perfil-menu-btn <?= $paginaAtual === 'perfil_termos.php' ? 'active' : '' ?>">
                <span class="perfil-menu-icone"><i class="bi bi-file-text"></i></span>
                <span>Termos do site</span>
            </a>
        </div>
    </div>
</div>