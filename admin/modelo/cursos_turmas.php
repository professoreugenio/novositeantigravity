<?php
declare(strict_types=1);

/**
 * Página: cursos_turmas.php
 * ADMIN - Gerenciador de Turmas
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
 * Helpers locais.
 * Não foram criadas funções datbr() nem horabr(),
 * pois você informou que elas já existem no autenticacao.php.
 */
if (!function_exists('adminTurmasH')) {
    function adminTurmasH($valor): string
    {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('adminTurmasJson')) {
    function adminTurmasJson(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('adminTurmasLimparHora')) {
    function adminTurmasLimparHora($hora): string
    {
        $hora = trim((string)$hora);

        if ($hora === '' || $hora === '00:00:00') {
            return '-';
        }

        if (function_exists('horabr')) {
            return (string)horabr($hora);
        }

        return substr($hora, 0, 5);
    }
}

if (!function_exists('adminTurmasData')) {
    function adminTurmasData($data): string
    {
        $data = trim((string)$data);

        if ($data === '' || $data === '0000-00-00') {
            return '-';
        }

        if (function_exists('datbr')) {
            return (string)datbr($data);
        }

        return $data;
    }
}

if (!function_exists('adminTurmasSlug')) {
    function adminTurmasSlug(string $texto): string
    {
        $texto = trim($texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = strtolower((string)$texto);
        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
        $texto = trim((string)$texto, '-');

        return $texto !== '' ? $texto : 'turma';
    }
}

if (!function_exists('adminTurmasBuscar')) {
    function adminTurmasBuscar(PDO $con, string $ano = ''): array
    {
        $ano = preg_replace('/[^0-9]/', '', $ano);
        $params = [];
        $where = "WHERE COALESCE(t.visivelst, 0) <> 2";

        /**
         * Se o ano for informado, filtra por ano_turma.
         * Se não for informado, traz as últimas 10 turmas cadastradas.
         */
        if ($ano !== '') {
            $where .= " AND t.ano_turma LIKE :ano";
            $params[':ano'] = $ano . '%';
            $limit = 80;
        } else {
            $limit = 10;
        }

        $sql = "
            SELECT
                t.codigoturma,
                t.chave,
                t.nometurma,
                t.nomeprofessor,
                t.linkwhatsapp,
                t.datainiciost,
                t.datafimst,
                t.horainiciost,
                t.horafimst,
                t.ano_turma,
                t.visivelst,
                t.andamento,
                t.datast,
                t.horast,

                (
                    SELECT COUNT(*)
                    FROM new_sistema_inscricao_PJA i
                    WHERE 
                        (
                            i.chaveturma = t.chave
                            OR i.chaveturma = CAST(t.codigoturma AS CHAR)
                        )
                        AND COALESCE(i.visivel_ci, 0) <> 2
                ) AS total_alunos

            FROM new_sistema_cursos_turmas t
            {$where}
            ORDER BY 
                COALESCE(t.datast, t.data, t.datainiciost, '0000-00-00') DESC,
                COALESCE(t.horast, t.hora, '00:00:00') DESC,
                t.codigoturma DESC
            LIMIT {$limit}
        ";

        $stmt = $con->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('adminTurmasRenderLista')) {
    function adminTurmasRenderLista(array $turmas): string
    {
        ob_start();

        if (empty($turmas)) {
            ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <strong>Nenhuma turma encontrada.</strong>
                <span>Digite outro ano ou cadastre uma nova turma.</span>
            </div>
            <?php

            return (string)ob_get_clean();
        }

        foreach ($turmas as $turma) {
            $idTurma = (int)($turma['codigoturma'] ?? 0);
            $nomeTurma = trim((string)($turma['nometurma'] ?? 'Turma sem nome'));
            $professor = trim((string)($turma['nomeprofessor'] ?? ''));
            $linkWhatsapp = trim((string)($turma['linkwhatsapp'] ?? '#'));
            $anoTurma = trim((string)($turma['ano_turma'] ?? ''));
            $visivel = (int)($turma['visivelst'] ?? 0);
            $andamento = (int)($turma['andamento'] ?? 0);
            $totalAlunos = (int)($turma['total_alunos'] ?? 0);

            $dataInicio = adminTurmasData($turma['datainiciost'] ?? '');
            $dataFim = adminTurmasData($turma['datafimst'] ?? '');
            $horaInicio = adminTurmasLimparHora($turma['horainiciost'] ?? '');
            $horaFim = adminTurmasLimparHora($turma['horafimst'] ?? '');
            ?>

            <article class="turma-row" data-id="<?= $idTurma; ?>">

                <div class="turma-icon">
                    <i class="bi bi-calendar2-week"></i>
                </div>

                <div class="turma-main">

                    <div class="turma-title-line">
                        <strong class="turma-title">
                            <?= adminTurmasH($nomeTurma); ?>
                        </strong>

                        <span class="turma-id">#<?= $idTurma; ?></span>

                        <?php if ($visivel === 1): ?>
                            <span class="badge rounded-pill text-bg-success">Visível</span>
                        <?php else: ?>
                            <span class="badge rounded-pill text-bg-secondary">Oculta</span>
                        <?php endif; ?>

                        <?php if ($andamento === 1): ?>
                            <span class="badge rounded-pill text-bg-danger">Finalizada</span>
                        <?php else: ?>
                            <span class="badge rounded-pill text-bg-primary">Em andamento</span>
                        <?php endif; ?>
                    </div>

                    <div class="turma-professor">
                        <i class="bi bi-person-badge"></i>
                        Professor: <?= adminTurmasH($professor !== '' ? $professor : 'Não informado'); ?>
                    </div>

                    <div class="turma-meta">
                        <span>
                            <i class="bi bi-calendar-event"></i>
                            Início: <?= adminTurmasH($dataInicio); ?>
                        </span>

                        <span>
                            <i class="bi bi-calendar-check"></i>
                            Fim: <?= adminTurmasH($dataFim); ?>
                        </span>

                        <span>
                            <i class="bi bi-clock"></i>
                            <?= adminTurmasH($horaInicio); ?> às <?= adminTurmasH($horaFim); ?>
                        </span>

                        <span>
                            <i class="bi bi-calendar3"></i>
                            Ano: <?= adminTurmasH($anoTurma !== '' ? $anoTurma : '-'); ?>
                        </span>

                        <span>
                            <i class="bi bi-people"></i>
                            <?= $totalAlunos; ?> aluno(s)
                        </span>
                    </div>

                </div>

                <div class="turma-actions">

                    <?php if ($linkWhatsapp !== '' && $linkWhatsapp !== '#'): ?>
                        <a href="<?= adminTurmasH($linkWhatsapp); ?>"
                           target="_blank"
                           rel="noopener"
                           class="btn btn-sm btn-outline-success rounded-pill fw-bold">
                            <i class="bi bi-whatsapp"></i>
                            WhatsApp
                        </a>
                    <?php endif; ?>

                    <?php if ($andamento === 1): ?>
                        <button type="button"
                                class="btn btn-sm btn-secondary rounded-pill fw-bold"
                                disabled>
                            <i class="bi bi-check-circle"></i>
                            Finalizada
                        </button>
                    <?php else: ?>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger rounded-pill fw-bold btnFinalizarTurma"
                                data-id="<?= $idTurma; ?>">
                            <i class="bi bi-flag-fill"></i>
                            Finalizar Turma
                        </button>
                    <?php endif; ?>

                </div>

            </article>

            <?php
        }

        return (string)ob_get_clean();
    }
}

/**
 * CSRF simples
 */
if (empty($_SESSION['csrf_turmas_admin'])) {
    $_SESSION['csrf_turmas_admin'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_turmas_admin'];

/**
 * AJAX - Buscar turmas por ano
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['acao'] ?? '') === 'buscar') {
    $ano = trim((string)($_GET['ano'] ?? ''));
    $turmas = adminTurmasBuscar($con, $ano);

    adminTurmasJson([
        'ok' => true,
        'total' => count($turmas),
        'html' => adminTurmasRenderLista($turmas),
        'descricao' => $ano !== '' ? 'Resultado filtrado por ano' : 'Últimas 10 turmas cadastradas',
    ]);
}

/**
 * AJAX - Ações POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = trim((string)($_POST['acao'] ?? ''));
    $csrf = trim((string)($_POST['csrf'] ?? ''));

    if (!hash_equals($csrfToken, $csrf)) {
        adminTurmasJson([
            'ok' => false,
            'msg' => 'Token de segurança inválido. Atualize a página e tente novamente.'
        ], 403);
    }

    try {
        if ($acao === 'adicionar_turma') {
            $visivel = isset($_POST['visivelst']) ? 1 : 0;
            $nomeTurma = trim((string)($_POST['nometurma'] ?? ''));
            $nomeProfessor = trim((string)($_POST['nomeprofessor'] ?? ''));
            $linkWhatsapp = trim((string)($_POST['linkwhatsapp'] ?? '#'));
            $dataInicio = trim((string)($_POST['datainiciost'] ?? ''));
            $dataFim = trim((string)($_POST['datafimst'] ?? ''));
            $horaInicio = trim((string)($_POST['horainiciost'] ?? ''));
            $horaFim = trim((string)($_POST['horafimst'] ?? ''));
            $anoTurma = (int)($_POST['ano_turma'] ?? 0);

            if ($nomeTurma === '') {
                adminTurmasJson([
                    'ok' => false,
                    'msg' => 'Informe o nome da turma.'
                ], 422);
            }

            if ($anoTurma <= 0) {
                adminTurmasJson([
                    'ok' => false,
                    'msg' => 'Informe o ano da turma.'
                ], 422);
            }

            if ($dataInicio === '') {
                adminTurmasJson([
                    'ok' => false,
                    'msg' => 'Informe a data de início.'
                ], 422);
            }

            if ($dataFim === '') {
                adminTurmasJson([
                    'ok' => false,
                    'msg' => 'Informe a data de fim.'
                ], 422);
            }

            /**
             * Chave simples para vínculo da turma.
             * Mantém padrão único e fácil de rastrear.
             */
            $chaveTurma = date('YmdHis') . '-' . adminTurmasSlug($nomeTurma);
            $chaveTurma = mb_substr($chaveTurma, 0, 100, 'UTF-8');

            $sql = "
                INSERT INTO new_sistema_cursos_turmas
                (
                    chave,
                    nometurma,
                    nomeprofessor,
                    linkwhatsapp,
                    datainiciost,
                    datafimst,
                    horainiciost,
                    horafimst,
                    ano_turma,
                    visivelst,
                    andamento,
                    datast,
                    horast,
                    data,
                    hora
                )
                VALUES
                (
                    :chave,
                    :nometurma,
                    :nomeprofessor,
                    :linkwhatsapp,
                    :datainiciost,
                    :datafimst,
                    :horainiciost,
                    :horafimst,
                    :ano_turma,
                    :visivelst,
                    0,
                    CURDATE(),
                    CURTIME(),
                    CURDATE(),
                    CURTIME()
                )
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([
                ':chave'         => $chaveTurma,
                ':nometurma'     => $nomeTurma,
                ':nomeprofessor' => $nomeProfessor,
                ':linkwhatsapp'  => $linkWhatsapp !== '' ? $linkWhatsapp : '#',
                ':datainiciost'  => $dataInicio,
                ':datafimst'     => $dataFim,
                ':horainiciost'  => $horaInicio !== '' ? $horaInicio : null,
                ':horafimst'     => $horaFim !== '' ? $horaFim : null,
                ':ano_turma'     => $anoTurma,
                ':visivelst'     => $visivel,
            ]);

            adminTurmasJson([
                'ok' => true,
                'msg' => 'Turma cadastrada com sucesso.'
            ]);
        }

        if ($acao === 'finalizar_turma') {
            $idTurma = (int)($_POST['idturma'] ?? 0);

            if ($idTurma <= 0) {
                adminTurmasJson([
                    'ok' => false,
                    'msg' => 'Turma inválida.'
                ], 422);
            }

            $sql = "
                UPDATE new_sistema_cursos_turmas
                SET 
                    andamento = 1,
                    dataatualizacao = CURDATE(),
                    horaatualizacao = CURTIME()
                WHERE codigoturma = :id
                LIMIT 1
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([
                ':id' => $idTurma
            ]);

            adminTurmasJson([
                'ok' => true,
                'msg' => 'Turma finalizada com sucesso.'
            ]);
        }

        adminTurmasJson([
            'ok' => false,
            'msg' => 'Ação inválida.'
        ], 400);

    } catch (Throwable $e) {
        adminTurmasJson([
            'ok' => false,
            'msg' => 'Erro ao processar solicitação.',
            'erro' => $e->getMessage()
        ], 500);
    }
}

/**
 * Listagem inicial
 */
$turmas = adminTurmasBuscar($con, '');

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">

<head>
    <meta charset="UTF-8">

    <title>Turmas | Admin Gerenciador de Conteúdo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Segurança / SEO -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Página administrativa para gerenciamento de turmas.">
    <meta name="author" content="Professor Eugênio">

    <!-- Compartilhamento em redes sociais -->
    <meta property="og:title" content="Turmas | Admin Gerenciador de Conteúdo">
    <meta property="og:description" content="Gerenciamento administrativo de turmas, datas, horários e professores.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= adminTurmasH((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')); ?>">
    <meta property="og:image" content="/img/logosite.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Turmas | Admin Gerenciador de Conteúdo">
    <meta name="twitter:description" content="Gerenciamento administrativo de turmas.">
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
            flex-wrap: wrap;
            justify-content: flex-end;
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
            width: min(340px, 100%);
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

        .turma-list-card {
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            background: var(--admin-card);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
        }

        .turma-list-header {
            display: grid;
            grid-template-columns: 52px 1fr 240px;
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

        .turma-list {
            display: grid;
        }

        .turma-row {
            display: grid;
            grid-template-columns: 52px 1fr 240px;
            gap: 10px;
            align-items: center;
            padding: 10px 12px;
            border-bottom: 1px solid var(--admin-border);
            background: var(--admin-card);
            transition: 0.18s ease;
        }

        .turma-row:last-child {
            border-bottom: 0;
        }

        .turma-row:hover {
            background: var(--admin-card-soft);
        }

        .turma-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            color: #ffffff;
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary));
            font-size: 1rem;
        }

        .turma-main {
            min-width: 0;
        }

        .turma-title-line {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
            min-width: 0;
        }

        .turma-title {
            color: var(--admin-text);
            font-size: 0.94rem;
            font-weight: 850;
        }

        .turma-id {
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

        .turma-professor {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
            color: var(--admin-muted);
            font-size: 0.75rem;
            font-weight: 700;
        }

        .turma-professor i {
            color: var(--admin-secondary);
        }

        .turma-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
            color: var(--admin-muted);
            font-size: 0.72rem;
        }

        .turma-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .turma-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 7px;
            flex-wrap: wrap;
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

        .modal-content {
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            background: var(--admin-card);
            color: var(--admin-text);
        }

        .form-label {
            color: var(--admin-muted);
            font-size: 0.74rem;
            font-weight: 850;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            font-size: 0.86rem;
        }

        .toast-container {
            z-index: 2000;
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

            .turma-list-header {
                display: none;
            }

            .turma-row {
                grid-template-columns: 48px 1fr;
            }

            .turma-actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
                padding-left: 58px;
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

            .turma-row {
                padding: 9px;
            }

            .turma-title {
                font-size: 0.86rem;
            }

            .turma-actions {
                padding-left: 0;
            }
        }
    </style>
</head>

<body>

<div class="page-wrap">

    <header class="admin-topbar">
        <div class="topbar-title">
            <div class="topbar-icon">
                <i class="bi bi-calendar2-week"></i>
            </div>

            <div>
                <span>Admin / Gerenciador de Conteúdo</span>
                <strong>Turmas</strong>
            </div>
        </div>

        <div class="topbar-actions">

            <a href="cursos.php" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold">
                <i class="bi bi-arrow-left-short"></i>
                Voltar
            </a>

            <a href="curso_modulos.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                <i class="bi bi-journal-richtext"></i>
                Publicações
            </a>

            <button class="btn btn-sm btn-primary rounded-pill fw-bold"
                    type="button"
                    data-bs-toggle="modal"
                    data-bs-target="#modalTurma">
                <i class="bi bi-plus-circle"></i>
                Adicionar Turma
            </button>

            <button class="btn btn-theme" type="button" id="btnTheme">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
                <span id="themeText">Modo dark</span>
            </button>

        </div>
    </header>

    <section class="page-hero">
        <div>
            <h1>Turmas cadastradas</h1>
            <p>
                Visualize as últimas turmas cadastradas, filtre por ano em tempo real
                e finalize turmas que já encerraram suas atividades.
            </p>
        </div>

        <div class="hero-mini-card">
            <span>Resultado exibido</span>
            <strong id="contadorTurmas"><?= count($turmas); ?></strong>
            <span id="descricaoResultado">Últimas 10 turmas cadastradas</span>
        </div>
    </section>

    <section class="toolbar-card">

        <div>
            <strong>Filtrar por ano</strong>
            <div class="small text-muted">
                Digite o ano da turma. Exemplo: 2026.
            </div>
        </div>

        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="search"
                   class="form-control"
                   id="pesquisaAno"
                   inputmode="numeric"
                   maxlength="4"
                   placeholder="Digite o ano...">
        </div>

    </section>

    <section class="turma-list-card">

        <div class="turma-list-header">
            <div>Turma</div>
            <div>Informações</div>
            <div class="text-end">Ações</div>
        </div>

        <div class="turma-list" id="listaTurmas">
            <?= adminTurmasRenderLista($turmas); ?>
        </div>

    </section>

</div>

<!-- Modal Adicionar Turma -->
<div class="modal fade" id="modalTurma" tabindex="-1" aria-labelledby="modalTurmaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">

        <form class="modal-content" id="formTurma">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTurmaLabel">
                    <i class="bi bi-plus-circle text-primary me-1"></i>
                    Adicionar Turma
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="csrf" value="<?= adminTurmasH($csrfToken); ?>">
                <input type="hidden" name="acao" value="adicionar_turma">

                <div class="row g-3">

                    <div class="col-12">
                        <div class="form-check form-switch border rounded-4 p-3 ps-5">
                            <input class="form-check-input"
                                   type="checkbox"
                                   role="switch"
                                   name="visivelst"
                                   id="visivelst"
                                   value="1"
                                   checked>

                            <label class="form-check-label fw-bold" for="visivelst">
                                Turma visível
                            </label>

                            <div class="small text-muted">
                                Quando marcado, salva `visivelst = 1`.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <label for="nometurma" class="form-label">Nome da turma</label>
                        <input type="text"
                               class="form-control"
                               name="nometurma"
                               id="nometurma"
                               maxlength="250"
                               required
                               placeholder="Ex.: Turma Excel Profissional 2026">
                    </div>

                    <div class="col-md-4">
                        <label for="ano_turma" class="form-label">Ano da turma</label>
                        <input type="number"
                               class="form-control"
                               name="ano_turma"
                               id="ano_turma"
                               min="2000"
                               max="2100"
                               value="<?= date('Y'); ?>"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="nomeprofessor" class="form-label">Nome do professor</label>
                        <input type="text"
                               class="form-control"
                               name="nomeprofessor"
                               id="nomeprofessor"
                               maxlength="200"
                               placeholder="Ex.: Professor Eugênio">
                    </div>

                    <div class="col-md-6">
                        <label for="linkwhatsapp" class="form-label">Link WhatsApp</label>
                        <input type="url"
                               class="form-control"
                               name="linkwhatsapp"
                               id="linkwhatsapp"
                               placeholder="https://wa.me/5585999999999">
                    </div>

                    <div class="col-md-6">
                        <label for="datainiciost" class="form-label">Data início</label>
                        <input type="date"
                               class="form-control"
                               name="datainiciost"
                               id="datainiciost"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="datafimst" class="form-label">Data fim</label>
                        <input type="date"
                               class="form-control"
                               name="datafimst"
                               id="datafimst"
                               required>
                    </div>

                    <div class="col-md-6">
                        <label for="horainiciost" class="form-label">Hora início</label>
                        <input type="time"
                               class="form-control"
                               name="horainiciost"
                               id="horainiciost">
                    </div>

                    <div class="col-md-6">
                        <label for="horafimst" class="form-label">Hora fim</label>
                        <input type="time"
                               class="form-control"
                               name="horafimst"
                               id="horafimst">
                    </div>

                </div>

                <div class="alert alert-info border-0 rounded-4 small mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Ao cadastrar, a turma entra como <strong>em andamento</strong>. Use o botão
                    <strong>Finalizar Turma</strong> quando ela for encerrada.
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn btn-primary rounded-pill fw-bold" id="btnSalvarTurma">
                    <i class="bi bi-check-circle me-1"></i>
                    Salvar turma
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Toast -->
<div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
    <div id="toastAdmin" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="toastMsg">
                Mensagem
            </div>

            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const csrfToken = '<?= adminTurmasH($csrfToken); ?>';

    const pesquisaAno = document.getElementById('pesquisaAno');
    const listaTurmas = document.getElementById('listaTurmas');
    const contadorTurmas = document.getElementById('contadorTurmas');
    const descricaoResultado = document.getElementById('descricaoResultado');

    const formTurma = document.getElementById('formTurma');
    const modalTurmaEl = document.getElementById('modalTurma');
    const modalTurma = new bootstrap.Modal(modalTurmaEl);

    const toastEl = document.getElementById('toastAdmin');
    const toastMsg = document.getElementById('toastMsg');
    const toastAdmin = new bootstrap.Toast(toastEl, {
        delay: 2600
    });

    let timerPesquisa = null;
    let controllerPesquisa = null;

    function mostrarToast(msg, tipo = 'success') {
        toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');

        if (tipo === 'danger') {
            toastEl.classList.add('text-bg-danger');
        } else if (tipo === 'warning') {
            toastEl.classList.add('text-bg-warning');
        } else if (tipo === 'info') {
            toastEl.classList.add('text-bg-info');
        } else {
            toastEl.classList.add('text-bg-success');
        }

        toastMsg.textContent = msg;
        toastAdmin.show();
    }

    async function pesquisarTurmas(ano) {
        if (controllerPesquisa) {
            controllerPesquisa.abort();
        }

        controllerPesquisa = new AbortController();

        listaTurmas.classList.add('loading-state');

        try {
            const url = 'cursos_turmas.php?acao=buscar&ano=' + encodeURIComponent(ano);

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

            listaTurmas.innerHTML = json.html;
            contadorTurmas.textContent = json.total;
            descricaoResultado.textContent = json.descricao;

        } catch (error) {
            if (error.name !== 'AbortError') {
                listaTurmas.innerHTML = `
                    <div class="empty-state">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Erro ao pesquisar turmas.</strong>
                        <span>Tente novamente em alguns instantes.</span>
                    </div>
                `;
            }
        } finally {
            listaTurmas.classList.remove('loading-state');
        }
    }

    pesquisaAno?.addEventListener('input', () => {
        pesquisaAno.value = pesquisaAno.value.replace(/\D/g, '').slice(0, 4);

        clearTimeout(timerPesquisa);

        timerPesquisa = setTimeout(() => {
            pesquisarTurmas(pesquisaAno.value);
        }, 280);
    });

    async function postAjax(formData) {
        const resposta = await fetch('cursos_turmas.php', {
            method: 'POST',
            body: formData
        });

        return await resposta.json();
    }

    formTurma?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btnSalvar = document.getElementById('btnSalvarTurma');

        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';

        try {
            const formData = new FormData(formTurma);
            const json = await postAjax(formData);

            if (!json.ok) {
                mostrarToast(json.msg || 'Erro ao cadastrar turma.', 'danger');
                return;
            }

            modalTurma.hide();
            formTurma.reset();

            document.getElementById('ano_turma').value = new Date().getFullYear();
            document.getElementById('visivelst').checked = true;

            mostrarToast(json.msg || 'Turma cadastrada com sucesso.');

            setTimeout(() => {
                pesquisarTurmas(pesquisaAno.value);
            }, 400);

        } catch (error) {
            mostrarToast('Erro inesperado ao cadastrar turma.', 'danger');
        } finally {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Salvar turma';
        }
    });

    listaTurmas?.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btnFinalizarTurma');

        if (!btn) {
            return;
        }

        const confirmar = confirm('Deseja finalizar esta turma?');

        if (!confirmar) {
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Finalizando...';

        const formData = new FormData();
        formData.append('csrf', csrfToken);
        formData.append('acao', 'finalizar_turma');
        formData.append('idturma', btn.dataset.id);

        try {
            const json = await postAjax(formData);

            if (!json.ok) {
                mostrarToast(json.msg || 'Erro ao finalizar turma.', 'danger');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-flag-fill"></i> Finalizar Turma';
                return;
            }

            mostrarToast(json.msg || 'Turma finalizada com sucesso.', 'warning');

            setTimeout(() => {
                pesquisarTurmas(pesquisaAno.value);
            }, 400);

        } catch (error) {
            mostrarToast('Erro inesperado ao finalizar turma.', 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-flag-fill"></i> Finalizar Turma';
        }
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