<?php
declare(strict_types=1);

/**
 * Página: alunos.php
 * ADMIN - Gerenciador de Alunos
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));

$sessionLifetime = 60 * 60 * 8;

ini_set('session.gc_maxlifetime', (string)$sessionLifetime);
ini_set('session.cookie_lifetime', (string)$sessionLifetime);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - (int)$_SESSION['LAST_ACTIVITY']) > $sessionLifetime) {
    session_unset();
    session_destroy();

    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time();

require_once APP_ROOT . '/componentes/v1/class.conexao.php';
require_once APP_ROOT . '/componentes/v1/autenticacao.php';

try {
    $con = config::connect();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
    http_response_code(500);
    exit('Erro ao conectar com o banco de dados.');
}

/**
 * Helpers locais
 * Não foram criadas funções datbr() nem horabr(),
 * pois você informou que elas já existem no autenticacao.php.
 */
if (!function_exists('adminAlunosH')) {
    function adminAlunosH($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('adminAlunosJson')) {
    function adminAlunosJson(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('adminAlunosPrimeiroNome')) {
    function adminAlunosPrimeiroNome($nome): string
    {
        $nome = trim((string)$nome);

        if ($nome === '') {
            return 'Aluno';
        }

        $partes = preg_split('/\s+/', $nome);

        return $partes[0] ?? $nome;
    }
}

if (!function_exists('adminAlunosIniciais')) {
    function adminAlunosIniciais($nome): string
    {
        $nome = trim((string)$nome);

        if ($nome === '') {
            return 'A';
        }

        $partes = preg_split('/\s+/', $nome);
        $primeira = mb_substr($partes[0] ?? 'A', 0, 1, 'UTF-8');
        $segunda = '';

        if (count($partes) > 1) {
            $segunda = mb_substr(end($partes), 0, 1, 'UTF-8');
        }

        return mb_strtoupper($primeira . $segunda, 'UTF-8');
    }
}

if (!function_exists('adminAlunosIdade')) {
    function adminAlunosIdade($dataNascimento): string
    {
        if (empty($dataNascimento) || $dataNascimento === '0000-00-00') {
            return '-';
        }

        try {
            $nascimento = new DateTime((string)$dataNascimento);
            $hoje = new DateTime('today');

            return (string)$nascimento->diff($hoje)->y;
        } catch (Throwable $e) {
            return '-';
        }
    }
}

if (!function_exists('adminAlunosAniversario')) {
    function adminAlunosAniversario($dataNascimento): string
    {
        if (empty($dataNascimento) || $dataNascimento === '0000-00-00') {
            return '-';
        }

        try {
            $data = new DateTime((string)$dataNascimento);

            return $data->format('d/m/Y');
        } catch (Throwable $e) {
            return '-';
        }
    }
}

if (!function_exists('adminAlunosFoto')) {
    function adminAlunosFoto(array $aluno): string
    {
        $pasta = trim((string)($aluno['pastasc'] ?? ''));
        $imagem = trim((string)($aluno['imagem50'] ?? ''));

        if ($pasta !== '' && $imagem !== '' && $imagem !== 'usuario.jpg') {
            return '/fotos/usuarios/' . rawurlencode($pasta) . '/' . rawurlencode($imagem);
        }

        return '/fotos/usuarios/usuario.png';
    }
}

if (!function_exists('adminAlunosBuscar')) {
    function adminAlunosBuscar(PDO $con, string $termo = '', int $limite = 20): array
    {
        $termo = trim($termo);
        $limite = max(1, min($limite, 80));

        $where = '';
        $params = [];

        if ($termo !== '') {
            $where = "
                WHERE 
                    u.nome LIKE :termo
                    OR u.email LIKE :termo
                    OR u.celular LIKE :termo
                    OR u.telefone LIKE :termo
            ";

            $params[':termo'] = '%' . $termo . '%';
        }

        $sql = "
            SELECT
                u.codigocadastro,
                u.nome,
                u.email,
                u.celular,
                u.telefone,
                u.pastasc,
                u.imagem50,
                u.datanascimento_sc,
                u.data,
                u.hora,

                (
                    SELECT t.nometurma
                    FROM new_sistema_inscricao_PJA i
                    LEFT JOIN new_sistema_cursos_turmas t
                        ON (
                            t.chave = i.chaveturma
                            OR CAST(t.codigoturma AS CHAR) = i.chaveturma
                        )
                    WHERE i.codigousuario = u.codigocadastro
                    ORDER BY
                        COALESCE(i.data_ins, i.data_ci, i.data, '0000-00-00') DESC,
                        COALESCE(i.hora_ins, i.hora_ci, i.hora, '00:00:00') DESC,
                        i.codigoinscricao DESC
                    LIMIT 1
                ) AS ultima_turma,

                (
                    SELECT i.codigoinscricao
                    FROM new_sistema_inscricao_PJA i
                    WHERE i.codigousuario = u.codigocadastro
                    ORDER BY
                        COALESCE(i.data_ins, i.data_ci, i.data, '0000-00-00') DESC,
                        COALESCE(i.hora_ins, i.hora_ci, i.hora, '00:00:00') DESC,
                        i.codigoinscricao DESC
                    LIMIT 1
                ) AS ultima_inscricao

            FROM new_sistema_cadastro u
            {$where}
            ORDER BY u.codigocadastro DESC
            LIMIT {$limite}
        ";

        $stmt = $con->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('adminAlunosRenderLista')) {
    function adminAlunosRenderLista(array $alunos): string
    {
        ob_start();

        if (empty($alunos)) {
            ?>
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <strong>Nenhum aluno encontrado.</strong>
                <span>Tente pesquisar por outro nome, e-mail ou celular.</span>
            </div>
            <?php

            return (string)ob_get_clean();
        }

        foreach ($alunos as $aluno) {
            $idAluno = (int)($aluno['codigocadastro'] ?? 0);
            $nome = trim((string)($aluno['nome'] ?? 'Aluno sem nome'));
            $email = trim((string)($aluno['email'] ?? ''));
            $celular = trim((string)($aluno['celular'] ?? ''));
            $telefone = trim((string)($aluno['telefone'] ?? ''));
            $foto = adminAlunosFoto($aluno);

            $idade = adminAlunosIdade($aluno['datanascimento_sc'] ?? null);
            $aniversario = adminAlunosAniversario($aluno['datanascimento_sc'] ?? null);

            $ultimaTurma = trim((string)($aluno['ultima_turma'] ?? ''));

            if ($ultimaTurma === '') {
                $ultimaTurma = 'Sem turma vinculada';
            }

            $idCriptografado = urlencode(encrypt_secure((string)$idAluno, 'e'));
            $linkAluno = 'actionAdminCursos.php?tokemAluno=' . time() . '&idusuario=' . $idCriptografado;
            ?>

            <article class="student-row" data-name="<?= adminAlunosH(mb_strtolower($nome . ' ' . $email . ' ' . $celular . ' ' . $telefone, 'UTF-8')); ?>">

                <div class="student-photo-wrap">
                    <img src="<?= adminAlunosH($foto); ?>"
                         alt="Foto de <?= adminAlunosH($nome); ?>"
                         class="student-photo"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';">

                    <div class="student-initials" style="display:none;">
                        <?= adminAlunosH(adminAlunosIniciais($nome)); ?>
                    </div>
                </div>

                <div class="student-main">

                    <div class="student-title-line">
                        <a href="<?= adminAlunosH($linkAluno); ?>" class="student-name">
                            <?= adminAlunosH($nome); ?>
                        </a>

                        <span class="student-id">
                            #<?= $idAluno; ?>
                        </span>
                    </div>

                    <div class="student-class">
                        <i class="bi bi-mortarboard"></i>
                        <?= adminAlunosH($ultimaTurma); ?>
                    </div>

                    <div class="student-contact">
                        <?php if ($email !== ''): ?>
                            <span>
                                <i class="bi bi-envelope"></i>
                                <?= adminAlunosH($email); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($celular !== '' && $celular !== '0'): ?>
                            <span>
                                <i class="bi bi-whatsapp"></i>
                                <?= adminAlunosH($celular); ?>
                            </span>
                        <?php elseif ($telefone !== '' && $telefone !== '0'): ?>
                            <span>
                                <i class="bi bi-telephone"></i>
                                <?= adminAlunosH($telefone); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="student-info">

                    <div class="info-pill">
                        <span>Idade</span>
                        <strong><?= adminAlunosH($idade); ?></strong>
                    </div>

                    <div class="info-pill">
                        <span>Aniversário</span>
                        <strong><?= adminAlunosH($aniversario); ?></strong>
                    </div>

                    <a href="<?= adminAlunosH($linkAluno); ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                        Acessar
                        <i class="bi bi-arrow-right-short"></i>
                    </a>

                </div>

            </article>

            <?php
        }

        return (string)ob_get_clean();
    }
}

/**
 * AJAX - Pesquisa em tempo real
 */
if (isset($_GET['acao']) && $_GET['acao'] === 'pesquisar') {
    $termo = trim((string)($_GET['q'] ?? ''));

    /**
     * Se pesquisar, traz até 50 resultados.
     * Se estiver vazio, volta para os últimos 20.
     */
    $limite = $termo !== '' ? 50 : 20;

    $alunos = adminAlunosBuscar($con, $termo, $limite);

    adminAlunosJson([
        'ok' => true,
        'total' => count($alunos),
        'html' => adminAlunosRenderLista($alunos),
    ]);
}

/**
 * Listagem inicial
 */
$alunos = adminAlunosBuscar($con, '', 20);

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">

<head>
    <meta charset="UTF-8">

    <title>Alunos | Admin Gerenciador de Conteúdo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Segurança / SEO -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Página administrativa para gerenciamento e pesquisa de alunos.">
    <meta name="author" content="Professor Eugênio">

    <!-- Compartilhamento em redes sociais -->
    <meta property="og:title" content="Alunos | Admin Gerenciador de Conteúdo">
    <meta property="og:description" content="Gerenciamento administrativo de alunos, turmas e cadastros.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= adminAlunosH((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')); ?>">
    <meta property="og:image" content="/img/logosite.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Alunos | Admin Gerenciador de Conteúdo">
    <meta name="twitter:description" content="Gerenciamento administrativo de alunos.">
    <meta name="twitter:image" content="/img/logosite.png">

    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <script>
        (function () {
            const temaSalvo = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', temaSalvo);
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --admin-bg: #f5f7fb;
            --admin-card: #ffffff;
            --admin-card-soft: #f8fafc;
            --admin-text: #111827;
            --admin-muted: #6b7280;
            --admin-border: rgba(15, 23, 42, 0.10);
            --admin-primary: #00BB9C;
            --admin-secondary: #FF9C00;
            --admin-radius: 16px;
            --admin-shadow: 0 14px 38px rgba(15, 23, 42, 0.08);
        }

        [data-bs-theme="dark"] {
            --admin-bg: #07111f;
            --admin-card: #101b2d;
            --admin-card-soft: #132238;
            --admin-text: #f8fafc;
            --admin-muted: #94a3b8;
            --admin-border: rgba(255, 255, 255, 0.10);
            --admin-shadow: 0 16px 38px rgba(0, 0, 0, 0.35);
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(circle at top left, rgba(0, 187, 156, 0.10), transparent 32%),
                radial-gradient(circle at top right, rgba(255, 156, 0, 0.10), transparent 28%),
                var(--admin-bg);
            color: var(--admin-text);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            font-size: 0.86rem;
        }

        a {
            text-decoration: none;
        }

        .page-wrap {
            width: min(1480px, calc(100% - 24px));
            margin: 0 auto;
            padding: 14px 0 26px;
        }

        .admin-topbar {
            position: sticky;
            top: 10px;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            margin-bottom: 12px;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            background: color-mix(in srgb, var(--admin-card) 90%, transparent);
            box-shadow: var(--admin-shadow);
            backdrop-filter: blur(14px);
        }

        .topbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            color: #fff;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            font-size: 1rem;
        }

        .topbar-title span {
            display: block;
            color: var(--admin-muted);
            font-size: 0.7rem;
            line-height: 1.1;
        }

        .topbar-title strong {
            display: block;
            color: var(--admin-text);
            font-size: 0.98rem;
            line-height: 1.2;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-theme {
            border: 1px solid var(--admin-border);
            background: var(--admin-card-soft);
            color: var(--admin-text);
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 6px 10px;
        }

        .page-hero {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            align-items: stretch;
            padding: 16px;
            margin-bottom: 12px;
            border-radius: 22px;
            color: #ffffff;
            background:
                linear-gradient(135deg, rgba(17, 34, 64, 0.98), rgba(0, 187, 156, 0.86)),
                #112240;
            box-shadow: var(--admin-shadow);
        }

        .page-hero h1 {
            margin: 0 0 4px;
            font-size: clamp(1.3rem, 2.6vw, 2rem);
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .page-hero p {
            margin: 0;
            max-width: 780px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.84rem;
        }

        .hero-mini-card {
            min-width: 220px;
            padding: 12px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.13);
            backdrop-filter: blur(12px);
        }

        .hero-mini-card span {
            display: block;
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.72rem;
        }

        .hero-mini-card strong {
            display: block;
            font-size: 1.45rem;
            line-height: 1.1;
        }

        .toolbar-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            background: var(--admin-card);
            box-shadow: var(--admin-shadow);
        }

        .search-box {
            position: relative;
            width: min(520px, 100%);
        }

        .search-box i {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--admin-muted);
            font-size: 0.9rem;
        }

        .search-box input {
            width: 100%;
            padding-left: 34px;
            border-radius: 999px;
            font-size: 0.84rem;
        }

        .student-list-card {
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            background: var(--admin-card);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
        }

        .student-list-header {
            display: grid;
            grid-template-columns: 62px 1fr 300px;
            gap: 10px;
            align-items: center;
            padding: 9px 12px;
            border-bottom: 1px solid var(--admin-border);
            color: var(--admin-muted);
            font-size: 0.68rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: var(--admin-card-soft);
        }

        .student-list {
            display: grid;
        }

        .student-row {
            display: grid;
            grid-template-columns: 62px 1fr 300px;
            gap: 10px;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid var(--admin-border);
            background: var(--admin-card);
            transition: 0.18s ease;
        }

        .student-row:last-child {
            border-bottom: 0;
        }

        .student-row:hover {
            background: var(--admin-card-soft);
        }

        .student-photo-wrap {
            width: 48px;
            height: 48px;
            position: relative;
        }

        .student-photo,
        .student-initials {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 187, 156, 0.35);
            background: var(--admin-card-soft);
        }

        .student-initials {
            place-items: center;
            color: #ffffff;
            font-weight: 850;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
        }

        .student-main {
            min-width: 0;
        }

        .student-title-line {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
        }

        .student-name {
            color: var(--admin-text);
            font-size: 0.94rem;
            font-weight: 850;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .student-name:hover {
            color: var(--admin-primary);
        }

        .student-id {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 2px 7px;
            border-radius: 999px;
            background: rgba(0, 187, 156, 0.12);
            color: var(--admin-primary);
            font-size: 0.66rem;
            font-weight: 850;
        }

        .student-class {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 3px;
            color: var(--admin-muted);
            font-size: 0.76rem;
            font-weight: 700;
        }

        .student-class i {
            color: var(--admin-secondary);
        }

        .student-contact {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 4px;
            color: var(--admin-muted);
            font-size: 0.72rem;
        }

        .student-contact span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
        }

        .student-info {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
        }

        .info-pill {
            min-width: 86px;
            padding: 6px 9px;
            border-radius: 14px;
            border: 1px solid var(--admin-border);
            background: var(--admin-card-soft);
            text-align: center;
        }

        .info-pill span {
            display: block;
            color: var(--admin-muted);
            font-size: 0.64rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .info-pill strong {
            display: block;
            color: var(--admin-text);
            font-size: 0.82rem;
            font-weight: 850;
        }

        .empty-state {
            display: grid;
            place-items: center;
            gap: 7px;
            padding: 38px 14px;
            color: var(--admin-muted);
            text-align: center;
        }

        .empty-state i {
            color: var(--admin-primary);
            font-size: 2rem;
        }

        .loading-state {
            opacity: 0.55;
            pointer-events: none;
        }

        @media (max-width: 991px) {
            .page-hero,
            .toolbar-card {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-mini-card {
                min-width: initial;
            }

            .search-box {
                width: 100%;
            }

            .student-list-header {
                display: none;
            }

            .student-row {
                grid-template-columns: 54px 1fr;
            }

            .student-info {
                grid-column: 1 / -1;
                justify-content: flex-start;
                padding-left: 64px;
            }
        }

        @media (max-width: 575px) {
            .page-wrap {
                width: min(100% - 14px, 1480px);
                padding-top: 8px;
            }

            .admin-topbar,
            .page-hero,
            .toolbar-card {
                border-radius: 14px;
            }

            .topbar-actions {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .student-row {
                padding: 9px;
            }

            .student-name {
                font-size: 0.86rem;
            }

            .student-info {
                padding-left: 0;
            }

            .info-pill {
                min-width: 78px;
            }
        }
    </style>
</head>

<body>

<div class="page-wrap">

    <header class="admin-topbar">
        <div class="topbar-title">
            <div class="topbar-icon">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <span>Admin / Gerenciador de Conteúdo</span>
                <strong>Alunos</strong>
            </div>
        </div>

        <div class="topbar-actions">
            <a href="index.php" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-left-short"></i>
                Dashboard
            </a>

            <button class="btn btn-theme" type="button" id="btnTheme">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
                <span id="themeText">Modo dark</span>
            </button>
        </div>
    </header>

    <section class="page-hero">
        <div>
            <h1>Alunos cadastrados</h1>
            <p>
                Visualize os últimos alunos cadastrados e pesquise rapidamente por nome,
                e-mail, celular ou telefone. O nome do aluno abre a área administrativa vinculada.
            </p>
        </div>

        <div class="hero-mini-card">
            <span>Resultado exibido</span>
            <strong id="contadorAlunos"><?= count($alunos); ?></strong>
            <span id="descricaoResultado">Últimos 20 cadastros</span>
        </div>
    </section>

    <section class="toolbar-card">

        <div>
            <strong>Pesquisar alunos</strong>
            <div class="small text-muted">
                Digite nome, e-mail, celular ou telefone.
            </div>
        </div>

        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="search"
                   class="form-control"
                   id="pesquisaAluno"
                   placeholder="Ex.: Maria, maria@email.com ou 85999999999"
                   autocomplete="off">
        </div>

    </section>

    <section class="student-list-card">

        <div class="student-list-header">
            <div>Foto</div>
            <div>Aluno</div>
            <div class="text-end">Informações</div>
        </div>

        <div class="student-list" id="listaAlunos">
            <?= adminAlunosRenderLista($alunos); ?>
        </div>

    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const pesquisaAluno = document.getElementById('pesquisaAluno');
    const listaAlunos = document.getElementById('listaAlunos');
    const contadorAlunos = document.getElementById('contadorAlunos');
    const descricaoResultado = document.getElementById('descricaoResultado');

    let timerPesquisa = null;
    let controllerPesquisa = null;

    async function pesquisarAlunos(termo) {
        if (controllerPesquisa) {
            controllerPesquisa.abort();
        }

        controllerPesquisa = new AbortController();

        listaAlunos.classList.add('loading-state');

        try {
            const url = 'alunos.php?acao=pesquisar&q=' + encodeURIComponent(termo);

            const resposta = await fetch(url, {
                method: 'GET',
                signal: controllerPesquisa.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const json = await resposta.json();

            if (!json.ok) {
                return;
            }

            listaAlunos.innerHTML = json.html;
            contadorAlunos.textContent = json.total;

            if (termo.trim() === '') {
                descricaoResultado.textContent = 'Últimos 20 cadastros';
            } else {
                descricaoResultado.textContent = 'Resultado da pesquisa';
            }

        } catch (error) {
            if (error.name !== 'AbortError') {
                listaAlunos.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Erro ao pesquisar alunos.</strong>
                        <span>Tente novamente em alguns instantes.</span>
                    </div>
                `;
            }
        } finally {
            listaAlunos.classList.remove('loading-state');
        }
    }

    pesquisaAluno?.addEventListener('input', () => {
        clearTimeout(timerPesquisa);

        timerPesquisa = setTimeout(() => {
            pesquisarAlunos(pesquisaAluno.value);
        }, 280);
    });

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
</script>

</body>
</html>