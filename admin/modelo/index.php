<?php
declare(strict_types=1);

/**
 * ADMIN - Gerenciador de Conteúdo
 * Página: index.php
 */

define('BASEPATH', true);

/**
 * Se este arquivo estiver em:
 * /public_html/admin/index.php
 *
 * E os componentes estiverem em:
 * /componentes/v1
 *
 * Então normalmente usamos dirname(__DIR__, 2).
 */
define('APP_ROOT', dirname(__DIR__, 3));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes/v1');

/**
 * Sessão com duração de 8 horas
 */
$sessionLifetime = 60 * 60 * 8;

ini_set('session.gc_maxlifetime', (string) $sessionLifetime);
ini_set('session.cookie_lifetime', (string) $sessionLifetime);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Controle de inatividade: 8 horas
 */
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - (int) $_SESSION['LAST_ACTIVITY']) > $sessionLifetime) {
    session_unset();
    session_destroy();

    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

/**
 * Includes protegidos fora da raiz pública
 */
require_once COMPONENTES_ROOT . '/class.conexao.php';
require_once COMPONENTES_ROOT . '/autenticacao.php';

/**
 * Conexão
 */
try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    die('Erro ao conectar com o banco de dados.');
}

/**
 * Helpers
 */
function h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function moedaBr($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

// function dataBr($data): string
// {
//     if (empty($data) || $data === '0000-00-00') {
//         return '-';
//     }

//     $dt = DateTime::createFromFormat('Y-m-d', (string)$data);

//     return $dt ? $dt->format('d/m/Y') : h($data);
// }

// function horaBr($hora): string
// {
//     if (empty($hora)) {
//         return '-';
//     }

//     return substr((string)$hora, 0, 5);
// }

function limitarTexto($texto, int $limite = 55): string
{
    $texto = trim((string) $texto);

    if ($texto === '') {
        return 'Sem título';
    }

    if (mb_strlen($texto, 'UTF-8') <= $limite) {
        return $texto;
    }

    return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
}

function buscarValor(PDO $con, string $sql, array $params = [], $default = 0)
{
    try {
        $stmt = $con->prepare($sql);
        $stmt->execute($params);

        $valor = $stmt->fetchColumn();

        return $valor !== false && $valor !== null ? $valor : $default;
    } catch (Throwable $e) {
        return $default;
    }
}

function buscarLista(PDO $con, string $sql, array $params = []): array
{
    try {
        $stmt = $con->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function badgeStatusVenda($status): string
{
    $status = (int) $status;

    return match ($status) {
        1 => '<span class="badge rounded-pill text-bg-success">Pago</span>',
        2 => '<span class="badge rounded-pill text-bg-warning">Pendente</span>',
        3 => '<span class="badge rounded-pill text-bg-danger">Cancelado</span>',
        default => '<span class="badge rounded-pill text-bg-secondary">Não informado</span>',
    };
}

function diasRestantes(?string $dataFim): string
{
    if (empty($dataFim) || $dataFim === '0000-00-00') {
        return '-';
    }

    try {
        $hoje = new DateTime('today');
        $fim = new DateTime($dataFim);
        $dias = (int) $hoje->diff($fim)->format('%r%a');

        if ($dias < 0) {
            return 'Encerrada';
        }

        if ($dias === 0) {
            return 'Encerra hoje';
        }

        return $dias . ' dias';
    } catch (Throwable $e) {
        return '-';
    }
}

/**
 * Nome do administrador
 * Ajuste conforme as variáveis criadas no seu autenticacao.php
 */
$nomeAdmin = $_SESSION['nome'] ?? $_SESSION['nome_admin'] ?? $_SESSION['usuario_nome'] ?? 'Administrador';

/**
 * Saudação
 */
$horaAtual = (int) date('H');

if ($horaAtual >= 5 && $horaAtual < 12) {
    $saudacao = 'Bom dia';
    $iconeSaudacao = 'bi-sunrise';
} elseif ($horaAtual >= 12 && $horaAtual < 18) {
    $saudacao = 'Boa tarde';
    $iconeSaudacao = 'bi-sun';
} else {
    $saudacao = 'Boa noite';
    $iconeSaudacao = 'bi-moon-stars';
}

/**
 * Indicadores principais
 */
$totalCursosAtivos = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_cursos WHERE visivelsc = 1"
);

$totalCursosOcultos = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_cursos WHERE visivelsc = 0"
);

$totalTurmasAndamento = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_cursos_turmas 
     WHERE andamento = 0 AND visivelst = 1"
);

$totalPublicacoes = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_publicacoes_PJA"
);

$totalPublicacoesRascunho = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_publicacoes_PJA 
     WHERE visivel = 0"
);

$totalAlunos = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_cadastro"
);

$totalInscricoes = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_inscricao_PJA"
);

$totalInscricoesPendentes = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_inscricao_PJA 
     WHERE pagamento = 0 OR pagamento IS NULL"
);

$totalDepoimentosPendentes = buscarValor(
    $con,
    "SELECT COUNT(*) FROM a_curso_depoimentos 
     WHERE permissaoCF = 0 OR permissaoCF IS NULL"
);

$totalEbooksAtivos = buscarValor(
    $con,
    "SELECT COUNT(*) FROM a_site_ebook 
     WHERE visivelse = 1"
);

$totalPaginasAdmin = buscarValor(
    $con,
    "SELECT COUNT(*) FROM new_sistema_paginasadmin 
     WHERE visivelpa = 1"
);

$totalAcessosHoje = buscarValor(
    $con,
    "SELECT COUNT(*) FROM a_site_registraacessos 
     WHERE datara = CURDATE()"
);

$vendasMes = buscarValor(
    $con,
    "SELECT COALESCE(SUM(valorvendasv), 0) 
     FROM a_site_vendas 
     WHERE MONTH(datacomprasv) = MONTH(CURDATE())
       AND YEAR(datacomprasv) = YEAR(CURDATE())"
);

$vendasHoje = buscarValor(
    $con,
    "SELECT COALESCE(SUM(valorvendasv), 0) 
     FROM a_site_vendas 
     WHERE datacomprasv = CURDATE()"
);


/**
 * Último usuário cadastrado
 */
$ultimoUsuario = buscarLista(
    $con,
    "SELECT nome 
     FROM new_sistema_cadastro
     ORDER BY codigocadastro DESC
     LIMIT 1"
);

$ultimoNomeCadastrado = $ultimoUsuario[0]['nome'] ?? 'Sem cadastro';

/**
 * Último acesso de hoje
 */
$ultimoAcessoHoje = buscarLista(
    $con,
    "SELECT horara 
     FROM a_site_registraacessos
     WHERE datara = CURDATE()
     ORDER BY horara DESC, idregistraacessos DESC
     LIMIT 1"
);

$horaUltimoAcessoHoje = $ultimoAcessoHoje[0]['horara'] ?? null;

/**
 * Listas
 */
$ultimasPublicacoes = buscarLista(
    $con,
    "SELECT 
        codigopublicacoes,
        titulo,
        visivel,
        COALESCE(dataatualizacao, datapub, data_pub, data) AS data_ref,
        COALESCE(horaatualizacao, horapub, hora) AS hora_ref
     FROM new_sistema_publicacoes_PJA
     ORDER BY data_ref DESC, hora_ref DESC
     LIMIT 6"
);

$ultimasVendas = buscarLista(
    $con,
    "SELECT 
        v.codigovendas,
        v.valorvendasv,
        v.tipopagamentosv,
        v.statussv,
        v.datacomprasv,
        v.horacomprasv,
        u.nome AS nome_aluno,
        c.nomecurso
     FROM a_site_vendas v
     LEFT JOIN new_sistema_cadastro u 
        ON u.codigocadastro = v.idalunosv
     LEFT JOIN new_sistema_cursos c 
        ON c.codigocursos = v.idcursosv
     ORDER BY v.datacomprasv DESC, v.horacomprasv DESC
     LIMIT 6"
);

$acessosPorDispositivo = buscarLista(
    $con,
    "SELECT 
        COALESCE(NULLIF(dispositivora, ''), 'Não informado') AS dispositivo,
        COALESCE(NULLIF(navegadorra, ''), 'Não informado') AS navegador,
        COUNT(*) AS total
     FROM a_site_registraacessos
     WHERE datara = CURDATE()
     GROUP BY dispositivo, navegador
     ORDER BY total DESC
     LIMIT 8"
);

$turmasEncerrando = buscarLista(
    $con,
    "SELECT 
        codigoturma,
        nometurma,
        nomeprofessor,
        datainiciost,
        datafimst,
        visivelst,
        andamento
     FROM new_sistema_cursos_turmas
     WHERE datafimst BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 15 DAY)
       AND andamento = 0
     ORDER BY datafimst ASC
     LIMIT 6"
);

/**
 * Atalhos rápidos
 */
$atalhos = [
    [
        'titulo' => 'Cursos',
        'descricao' => 'Gerenciar cursos, capas, valores e páginas de venda.',
        'icone' => 'bi-mortarboard',
        'link' => 'cursos.php',
        'cor' => 'primary'
    ],
    [
        'titulo' => 'Publicações',
        'descricao' => 'Criar aulas, textos, anexos e conteúdos do portal.',
        'icone' => 'bi-file-earmark-richtext',
        'link' => 'publicacoes.php',
        'cor' => 'success'
    ],
    [
        'titulo' => 'Alunos',
        'descricao' => 'Acompanhar cadastros, acessos e situação dos alunos.',
        'icone' => 'bi-people',
        'link' => 'alunos.php',
        'cor' => 'info'
    ],
    [
        'titulo' => 'Vendas',
        'descricao' => 'Conferir pagamentos, planos e matrículas.',
        'icone' => 'bi-cash-coin',
        'link' => 'vendas.php',
        'cor' => 'warning'
    ],
    [
        'titulo' => 'Depoimentos',
        'descricao' => 'Aprovar, destacar e moderar mensagens dos alunos.',
        'icone' => 'bi-chat-heart',
        'link' => 'depoimentos.php',
        'cor' => 'danger'
    ],
    [
        'titulo' => 'Ebooks',
        'descricao' => 'Organizar ebooks, descrições, valores e destaques.',
        'icone' => 'bi-journal-bookmark',
        'link' => 'ebooks.php',
        'cor' => 'secondary'
    ],
    [
        'titulo' => 'Páginas Admin',
        'descricao' => 'Controlar menus, links e páginas internas do sistema.',
        'icone' => 'bi-grid-1x2',
        'link' => 'paginasadmin.php',
        'cor' => 'dark'
    ],
    [
        'titulo' => 'Acessos',
        'descricao' => 'Visualizar registros de navegação e uso do portal.',
        'icone' => 'bi-activity',
        'link' => 'acessos.php',
        'cor' => 'primary'
    ],
];

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">

<head>
    <meta charset="UTF-8">

    <title>Admin | Gerenciador de Conteúdo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO / Segurança -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description"
        content="Painel administrativo para gerenciamento de conteúdo, cursos, publicações, alunos, vendas e acessos.">
    <meta name="author" content="Professor Eugênio">

    <!-- Compartilhamento em redes sociais -->
    <meta property="og:title" content="Admin | Gerenciador de Conteúdo">
    <meta property="og:description" content="Painel administrativo para rotina de gerenciamento do portal.">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="<?= h((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')); ?>">
    <meta property="og:image" content="/img/logosite.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Admin | Gerenciador de Conteúdo">
    <meta name="twitter:description" content="Painel administrativo para rotina de gerenciamento do portal.">
    <meta name="twitter:image" content="/img/logosite.png">

    <!-- Favicon -->
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" href="/img/logosite.png">

    <!-- Evita piscar tema claro antes de carregar o modo dark -->
    <script>
        (function () {
            const temaSalvo = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', temaSalvo);
        })();
    </script>

    <!-- Bootstrap 5 + Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS Global do Admin -->
    <link rel="stylesheet" href="assets/css/admin-global.css">
</head>

<body>

    <div class="admin-layout">

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">

            <div class="admin-brand">
                <div class="brand-icon">
                    <i class="bi bi-speedometer2"></i>
                </div>

                <div>
                    <strong>Admin</strong>
                    <span>Gerenciador de Conteúdo</span>
                </div>
            </div>

            <nav class="admin-menu" aria-label="Menu administrativo">
                <a href="index.php" class="active">
                    <i class="bi bi-house-door"></i>
                    <span>Início</span>
                </a>

                <a href="cursos.php">
                    <i class="bi bi-mortarboard"></i>
                    <span>Cursos</span>
                </a>

                <a href="publicacoes.php">
                    <i class="bi bi-file-earmark-richtext"></i>
                    <span>Publicações</span>
                </a>

                <a href="alunos.php">
                    <i class="bi bi-people"></i>
                    <span>Alunos</span>
                </a>

                <a href="vendas.php">
                    <i class="bi bi-cash-coin"></i>
                    <span>Vendas</span>
                </a>

                <a href="depoimentos.php">
                    <i class="bi bi-chat-heart"></i>
                    <span>Depoimentos</span>
                </a>

                <a href="ebooks.php">
                    <i class="bi bi-journal-bookmark"></i>
                    <span>Ebooks</span>
                </a>

                <a href="acessos.php">
                    <i class="bi bi-activity"></i>
                    <span>Acessos</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <small>Sessão ativa por até 8 horas</small>
            </div>

        </aside>

        <!-- Conteúdo -->
        <main class="admin-main">

            <!-- Topbar -->
            <header class="admin-topbar">

                <button class="btn btn-light border d-lg-none" type="button" id="btnMenuMobile" aria-label="Abrir menu">
                    <i class="bi bi-list"></i>
                </button>

                <div class="topbar-title">
                    <span><?= h(date('d/m/Y')); ?></span>
                    <strong>Rotina do Portal</strong>
                </div>

                <div class="topbar-actions">

                    <button class="btn btn-theme" type="button" id="btnTheme">
                        <i class="bi bi-moon-stars" id="themeIcon"></i>
                        <span id="themeText">Modo dark</span>
                    </button>

                    <div class="admin-user">
                        <div class="user-avatar">
                            <?= h(mb_strtoupper(mb_substr((string) $nomeAdmin, 0, 1, 'UTF-8'), 'UTF-8')); ?>
                        </div>
                        <div class="user-info">
                            <span>Logado como</span>
                            <strong><?= h($nomeAdmin); ?></strong>
                        </div>
                    </div>

                </div>

            </header>

            <!-- Hero -->
            <section class="admin-hero">

                <div>
                    <div class="hero-badge">
                        <i class="bi <?= h($iconeSaudacao); ?>"></i>
                        <?= h($saudacao); ?>, <?= h($nomeAdmin); ?>
                    </div>

                    <h4>Gerenciador de Conteúdo do Site</h4>

                    <p>
                        Acompanhe publicações, cursos, alunos, vendas, acessos e pendências importantes
                        para manter o portal organizado e atualizado.
                    </p>
                </div>

                <div class="hero-panel">
                    <span>Vendas no mês</span>
                    <strong><?= moedaBr($vendasMes); ?></strong>
                    <small>Hoje: <?= moedaBr($vendasHoje); ?></small>
                </div>

            </section>

            <!-- Indicadores -->
            <section class="dashboard-grid">

                <article class="metric-card">
                    <div class="metric-icon bg-primary-subtle text-primary">
                        <i class="bi bi-mortarboard"></i>
                    </div>

                    <div class="metric-content">
                        <span>Cursos ativos</span>

                        <div class="metric-value-line">
                            <strong><?= number_format((int) $totalCursosAtivos, 0, ',', '.'); ?></strong>
                        </div>

                        <small><?= number_format((int) $totalCursosOcultos, 0, ',', '.'); ?> ocultos</small>

                        <a href="cursos.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

                <article class="metric-card">
                    <div class="metric-icon bg-success-subtle text-success">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div class="metric-content">
                        <span>Publicações</span>

                        <div class="metric-value-line">
                            <strong><?= number_format((int) $totalPublicacoes, 0, ',', '.'); ?></strong>
                        </div>

                        <small><?= number_format((int) $totalPublicacoesRascunho, 0, ',', '.'); ?> em rascunho</small>

                        <a href="publicacoes.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

                <article class="metric-card">
                    <div class="metric-icon bg-info-subtle text-info">
                        <i class="bi bi-people"></i>
                    </div>

                    <div class="metric-content">
                        <span>Usuários cadastrados</span>

                        <div class="metric-value-line">
                            <strong><?= number_format((int) $totalAlunos, 0, ',', '.'); ?></strong>

                            <small class="metric-last">
                                Último: <?= h(limitarTexto($ultimoNomeCadastrado, 22)); ?>
                            </small>
                        </div>

                        <small><?= number_format((int) $totalInscricoes, 0, ',', '.'); ?> inscrições</small>

                        <a href="alunos.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

                <article class="metric-card">
                    <div class="metric-icon bg-warning-subtle text-warning">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                    <div class="metric-content">
                        <span>Vendas do mês</span>

                        <div class="metric-value-line">
                            <strong><?= moedaBr($vendasMes); ?></strong>
                        </div>

                        <small><?= moedaBr($vendasHoje); ?> hoje</small>

                        <a href="vendas.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

                <article class="metric-card">
                    <div class="metric-icon bg-danger-subtle text-danger">
                        <i class="bi bi-chat-heart"></i>
                    </div>

                    <div class="metric-content">
                        <span>Depoimentos pendentes</span>

                        <div class="metric-value-line">
                            <strong><?= number_format((int) $totalDepoimentosPendentes, 0, ',', '.'); ?></strong>
                        </div>

                        <small>Aguardando liberação</small>

                        <a href="depoimentos.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

                <article class="metric-card">
                    <div class="metric-icon bg-secondary-subtle text-secondary">
                        <i class="bi bi-activity"></i>
                    </div>

                    <div class="metric-content">
                        <span>Acessos hoje</span>

                        <div class="metric-value-line">
                            <strong><?= number_format((int) $totalAcessosHoje, 0, ',', '.'); ?></strong>

                            <small class="metric-last">
                                Último: <?= $horaUltimoAcessoHoje ? h(horaBr($horaUltimoAcessoHoje)) : '-'; ?>
                            </small>
                        </div>

                        <small>Registro de navegação</small>

                        <a href="acessos.php" class="metric-link">
                            Acessar
                            <i class="bi bi-arrow-right-short"></i>
                        </a>
                    </div>
                </article>

            </section>

            <!-- Atalhos -->
            <section class="content-section">

                <div class="section-header">
                    <div>
                        <span class="section-kicker">Acesso rápido</span>
                        <div class="section-title">Principais módulos do painel</div>
                    </div>
                </div>

                <div class="shortcut-grid">
                    <?php foreach ($atalhos as $atalho): ?>
                        <a href="<?= h($atalho['link']); ?>" class="shortcut-card">
                            <div class="shortcut-icon text-bg-<?= h($atalho['cor']); ?>">
                                <i class="bi <?= h($atalho['icone']); ?>"></i>
                            </div>

                            <div>
                                <strong><?= h($atalho['titulo']); ?></strong>
                                <span><?= h($atalho['descricao']); ?></span>
                            </div>

                            <i class="bi bi-arrow-right-short shortcut-arrow"></i>
                        </a>
                    <?php endforeach; ?>
                </div>

            </section>

            <div class="row g-4">

                <!-- Rotina -->
                <div class="col-xl-4">

                    <section class="content-card h-100">

                        <div class="card-title-area">
                            <div>
                                <span class="section-kicker">Checklist</span>
                                <div class="section-title">Pendências da rotina</div>
                            </div>
                            <i class="bi bi-list-check card-title-icon"></i>
                        </div>

                        <div class="routine-list">

                            <a href="depoimentos.php" class="routine-item">
                                <div>
                                    <strong>Depoimentos para aprovar</strong>
                                    <span>Mensagens aguardando liberação do professor.</span>
                                </div>
                                <span class="routine-number"><?= (int) $totalDepoimentosPendentes; ?></span>
                            </a>

                            <a href="inscricoes.php" class="routine-item">
                                <div>
                                    <strong>Inscrições sem pagamento</strong>
                                    <span>Alunos que ainda não finalizaram o pagamento.</span>
                                </div>
                                <span class="routine-number"><?= (int) $totalInscricoesPendentes; ?></span>
                            </a>

                            <a href="publicacoes.php" class="routine-item">
                                <div>
                                    <strong>Publicações em rascunho</strong>
                                    <span>Conteúdos cadastrados, mas ainda não publicados.</span>
                                </div>
                                <span class="routine-number"><?= (int) $totalPublicacoesRascunho; ?></span>
                            </a>

                            <a href="cursos.php" class="routine-item">
                                <div>
                                    <strong>Cursos ocultos</strong>
                                    <span>Cursos cadastrados, mas não visíveis no portal.</span>
                                </div>
                                <span class="routine-number"><?= (int) $totalCursosOcultos; ?></span>
                            </a>

                        </div>

                    </section>

                </div>

                <!-- Publicações recentes -->
                <div class="col-xl-8">

                    <section class="content-card h-100">

                        <div class="card-title-area">
                            <div>
                                <span class="section-kicker">Conteúdo</span>
                                <div class="section-title">Últimas publicações atualizadas</div>
                            </div>

                            <a href="publicacoes.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                Ver todas
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle admin-table">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Status</th>
                                        <th>Data</th>
                                        <th>Hora</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimasPublicacoes)): ?>
                                        <?php foreach ($ultimasPublicacoes as $pub): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= h(limitarTexto($pub['titulo'] ?? 'Sem título')); ?></strong>
                                                    <small>#<?= (int) $pub['codigopublicacoes']; ?></small>
                                                </td>
                                                <td>
                                                    <?php if ((int) ($pub['visivel'] ?? 0) === 1): ?>
                                                        <span class="badge rounded-pill text-bg-success">Publicado</span>
                                                    <?php else: ?>
                                                        <span class="badge rounded-pill text-bg-secondary">Rascunho</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= h(dataBr($pub['data_ref'] ?? null)); ?></td>
                                                <td><?= h(horaBr($pub['hora_ref'] ?? null)); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Nenhuma publicação encontrada.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </section>

                </div>

            </div>

            <div class="row g-4 mt-1">

                <!-- Vendas recentes -->
                <div class="col-xl-7">

                    <section class="content-card h-100">

                        <div class="card-title-area">
                            <div>
                                <span class="section-kicker">Comercial</span>
                                <div class="section-title">Últimas vendas</div>
                            </div>

                            <a href="vendas.php" class="btn btn-sm btn-outline-primary rounded-pill">
                                Ver vendas
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle admin-table">
                                <thead>
                                    <tr>
                                        <th>Aluno</th>
                                        <th>Curso</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($ultimasVendas)): ?>
                                        <?php foreach ($ultimasVendas as $venda): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= h($venda['nome_aluno'] ?: 'Aluno não informado'); ?></strong>
                                                    <small><?= h(dataBr($venda['datacomprasv'] ?? null)); ?> às
                                                        <?= h(horaBr($venda['horacomprasv'] ?? null)); ?></small>
                                                </td>
                                                <td><?= h(limitarTexto($venda['nomecurso'] ?? 'Curso não informado', 35)); ?>
                                                </td>
                                                <td><strong><?= moedaBr($venda['valorvendasv'] ?? 0); ?></strong></td>
                                                <td><?= badgeStatusVenda($venda['statussv'] ?? null); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                Nenhuma venda encontrada.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </section>

                </div>

                <!-- Acessos -->
                <div class="col-xl-5">

                    <section class="content-card h-100">

                        <div class="card-title-area">
                            <div>
                                <span class="section-kicker">Acessos</span>
                                <div class="section-title">Acessos de hoje por dispositivo</div>
                            </div>

                            <i class="bi bi-phone card-title-icon"></i>
                        </div>

                        <div class="access-list">

                            <?php if (!empty($acessosPorDispositivo)): ?>
                                <?php foreach ($acessosPorDispositivo as $acesso): ?>
                                    <div class="access-item">
                                        <div>
                                            <strong><?= h($acesso['dispositivo']); ?></strong>
                                            <span><?= h($acesso['navegador']); ?></span>
                                        </div>

                                        <span class="access-total">
                                            <?= (int) $acesso['total']; ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bi bi-activity"></i>
                                    <span>Nenhum acesso registrado hoje.</span>
                                </div>
                            <?php endif; ?>

                        </div>

                    </section>

                </div>

            </div>

            <!-- Turmas encerrando -->
            <section class="content-section">

                <div class="section-header">
                    <div>
                        <span class="section-kicker">Acompanhamento</span>
                        <div class="section-title">Turmas próximas do encerramento</div>
                    </div>

                    <a href="turmas.php" class="btn btn-sm btn-outline-primary rounded-pill">
                        Gerenciar turmas
                    </a>
                </div>

                <div class="row g-3">

                    <?php if (!empty($turmasEncerrando)): ?>
                        <?php foreach ($turmasEncerrando as $turma): ?>
                            <div class="col-md-6 col-xl-4">
                                <article class="class-card">
                                    <div class="class-card-top">
                                        <div>
                                            <strong><?= h(limitarTexto($turma['nometurma'] ?? 'Turma sem nome', 42)); ?></strong>
                                            <span>Prof. <?= h($turma['nomeprofessor'] ?? 'Não informado'); ?></span>
                                        </div>

                                        <span class="badge rounded-pill text-bg-warning">
                                            <?= h(diasRestantes($turma['datafimst'] ?? null)); ?>
                                        </span>
                                    </div>

                                    <div class="class-card-dates">
                                        <span>
                                            <i class="bi bi-calendar-event"></i>
                                            Início: <?= h(dataBr($turma['datainiciost'] ?? null)); ?>
                                        </span>

                                        <span>
                                            <i class="bi bi-calendar-check"></i>
                                            Fim: <?= h(dataBr($turma['datafimst'] ?? null)); ?>
                                        </span>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="empty-state large">
                                <i class="bi bi-calendar2-check"></i>
                                <span>Nenhuma turma encerrando nos próximos 15 dias.</span>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </section>

            <!-- Resumo técnico -->
            <section class="system-summary">

                <div>
                    <strong><?= number_format((int) $totalTurmasAndamento, 0, ',', '.'); ?></strong>
                    <span>Turmas em andamento</span>
                </div>

                <div>
                    <strong><?= number_format((int) $totalEbooksAtivos, 0, ',', '.'); ?></strong>
                    <span>Ebooks ativos</span>
                </div>

                <div>
                    <strong><?= number_format((int) $totalPaginasAdmin, 0, ',', '.'); ?></strong>
                    <span>Páginas admin visíveis</span>
                </div>

                <div>
                    <strong>8h</strong>
                    <span>Tempo de sessão</span>
                </div>

            </section>

            <footer class="admin-footer">
                <span>© <?= date('Y'); ?> Professor Eugênio</span>
                <span>Admin Gerenciador de Conteúdo</span>
            </footer>

        </main>

    </div>

    <!-- Overlay mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const html = document.documentElement;
        const btnTheme = document.getElementById('btnTheme');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

        function aplicarTema(tema) {
            html.setAttribute('data-bs-theme', tema);
            localStorage.setItem('admin-theme', tema);

            if (tema === 'dark') {
                themeIcon.className = 'bi bi-sun';
                themeText.textContent = 'Modo claro';
            } else {
                themeIcon.className = 'bi bi-moon-stars';
                themeText.textContent = 'Modo dark';
            }
        }

        aplicarTema(localStorage.getItem('admin-theme') || 'light');

        btnTheme?.addEventListener('click', () => {
            const temaAtual = html.getAttribute('data-bs-theme') || 'light';
            aplicarTema(temaAtual === 'dark' ? 'light' : 'dark');
        });

        const btnMenuMobile = document.getElementById('btnMenuMobile');
        const adminSidebar = document.getElementById('adminSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function abrirMenu() {
            adminSidebar.classList.add('show');
            sidebarOverlay.classList.add('show');
        }

        function fecharMenu() {
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }

        btnMenuMobile?.addEventListener('click', abrirMenu);
        sidebarOverlay?.addEventListener('click', fecharMenu);
    </script>

</body>

</html>