<?php
declare(strict_types=1);

/**
 * Página: cursos.php
 * ADMIN - Gerenciador de Cursos
 */

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));

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

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - (int) $_SESSION['LAST_ACTIVITY']) > $sessionLifetime) {
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
 * Helpers locais da página.
 * Não cria datbr() nem horabr(), pois já existem no autenticacao.php.
 */
if (!function_exists('adminCursosH')) {
    function adminCursosH($valor): string
    {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('adminCursosJson')) {
    function adminCursosJson(array $dados, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('adminCursosSlug')) {
    function adminCursosSlug(string $texto): string
    {
        $texto = trim($texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = strtolower((string) $texto);
        $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
        $texto = trim((string) $texto, '-');

        return $texto !== '' ? $texto : 'curso';
    }
}

if (!function_exists('adminCursosLimitar')) {
    function adminCursosLimitar($texto, int $limite = 90): string
    {
        $texto = trim((string) $texto);

        if ($texto === '') {
            return 'Curso sem nome';
        }

        if (mb_strlen($texto, 'UTF-8') <= $limite) {
            return $texto;
        }

        return mb_substr($texto, 0, $limite, 'UTF-8') . '...';
    }
}

/**
 * CSRF simples para ações AJAX.
 */
if (empty($_SESSION['csrf_cursos_admin'])) {
    $_SESSION['csrf_cursos_admin'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_cursos_admin'];

/**
 * Ações AJAX
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = trim((string) ($_POST['acao'] ?? ''));
    $csrf = trim((string) ($_POST['csrf'] ?? ''));

    if (!hash_equals($csrfToken, $csrf)) {
        adminCursosJson([
            'ok' => false,
            'msg' => 'Token de segurança inválido. Atualize a página e tente novamente.'
        ], 403);
    }

    try {
        if ($acao === 'salvar_curso') {
            $idCurso = (int) ($_POST['idcurso'] ?? 0);
            $nomeCurso = trim((string) ($_POST['nomecurso'] ?? ''));
            $tipoCurso = (int) ($_POST['tipocursosc'] ?? 0);
            $visivel = (int) ($_POST['visivelsc'] ?? 0);
            $visivelHome = (int) ($_POST['visivelhomesc'] ?? 0);

            $visivel = $visivel === 1 ? 1 : 0;
            $visivelHome = $visivelHome === 1 ? 1 : 0;

            if ($visivel === 0) {
                $visivelHome = 0;
            }

            if ($nomeCurso === '') {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Informe o nome do curso.'
                ], 422);
            }

            if ($idCurso > 0) {
                $sql = "
                    UPDATE new_sistema_cursos
                    SET 
                        nomecurso = :nomecurso,
                        tipocursosc = :tipocursosc,
                        visivelsc = :visivelsc,
                        visivelhomesc = :visivelhomesc
                    WHERE codigocursos = :id
                    LIMIT 1
                ";

                $stmt = $con->prepare($sql);
                $stmt->execute([
                    ':nomecurso' => $nomeCurso,
                    ':tipocursosc' => $tipoCurso > 0 ? $tipoCurso : null,
                    ':visivelsc' => $visivel,
                    ':visivelhomesc' => $visivelHome,
                    ':id' => $idCurso,
                ]);

                adminCursosJson([
                    'ok' => true,
                    'msg' => 'Curso atualizado com sucesso.'
                ]);
            }

            $proximaOrdem = (int) $con
                ->query("SELECT COALESCE(MAX(ordemsc), 0) + 1 FROM new_sistema_cursos")
                ->fetchColumn();

            $pastaBase = date('YmdHis') . '-' . adminCursosSlug($nomeCurso);
            $pastaBase = mb_substr($pastaBase, 0, 30, 'UTF-8');

            $sql = "
                INSERT INTO new_sistema_cursos
                (
                    nomecurso,
                    pasta,
                    diretorio,
                    tipocursosc,
                    visivelsc,
                    visivelhomesc,
                    ordemsc,
                    datasc,
                    horasc
                )
                VALUES
                (
                    :nomecurso,
                    :pasta,
                    :diretorio,
                    :tipocursosc,
                    :visivelsc,
                    :visivelhomesc,
                    :ordemsc,
                    CURDATE(),
                    CURTIME()
                )
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([
                ':nomecurso' => $nomeCurso,
                ':pasta' => $pastaBase,
                ':diretorio' => $pastaBase,
                ':tipocursosc' => $tipoCurso > 0 ? $tipoCurso : null,
                ':visivelsc' => $visivel,
                ':visivelhomesc' => $visivelHome,
                ':ordemsc' => $proximaOrdem,
            ]);

            adminCursosJson([
                'ok' => true,
                'msg' => 'Curso cadastrado com sucesso.'
            ]);
        }

        if ($acao === 'alterar_visivel') {
            $idCurso = (int) ($_POST['idcurso'] ?? 0);
            $status = (int) ($_POST['status'] ?? 0);
            $status = $status === 1 ? 1 : 0;

            if ($idCurso <= 0) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Curso inválido.'
                ], 422);
            }

            if ($status === 0) {
                $sql = "
                    UPDATE new_sistema_cursos
                    SET visivelsc = 0, visivelhomesc = 0
                    WHERE codigocursos = :id
                    LIMIT 1
                ";
            } else {
                $sql = "
                    UPDATE new_sistema_cursos
                    SET visivelsc = 1
                    WHERE codigocursos = :id
                    LIMIT 1
                ";
            }

            $stmt = $con->prepare($sql);
            $stmt->execute([':id' => $idCurso]);

            adminCursosJson([
                'ok' => true,
                'msg' => $status === 1 ? 'Curso marcado como visível.' : 'Curso ocultado com sucesso.'
            ]);
        }

        if ($acao === 'alterar_home') {
            $idCurso = (int) ($_POST['idcurso'] ?? 0);
            $status = (int) ($_POST['status'] ?? 0);
            $status = $status === 1 ? 1 : 0;

            if ($idCurso <= 0) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Curso inválido.'
                ], 422);
            }

            $cursoVisivel = (int) $con
                ->query("SELECT visivelsc FROM new_sistema_cursos WHERE codigocursos = {$idCurso} LIMIT 1")
                ->fetchColumn();

            if ($status === 1 && $cursoVisivel !== 1) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Para exibir na home, primeiro o curso precisa estar visível.'
                ], 422);
            }

            $sql = "
                UPDATE new_sistema_cursos
                SET visivelhomesc = :status
                WHERE codigocursos = :id
                LIMIT 1
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([
                ':status' => $status,
                ':id' => $idCurso,
            ]);

            adminCursosJson([
                'ok' => true,
                'msg' => $status === 1 ? 'Curso ativado na home.' : 'Curso removido da home.'
            ]);
        }

        if ($acao === 'excluir_curso') {
            $idCurso = (int) ($_POST['idcurso'] ?? 0);

            if ($idCurso <= 0) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Curso inválido.'
                ], 422);
            }

            $sql = "
                UPDATE new_sistema_cursos
                SET visivelsc = 2, visivelhomesc = 0
                WHERE codigocursos = :id
                LIMIT 1
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([':id' => $idCurso]);

            adminCursosJson([
                'ok' => true,
                'msg' => 'Curso enviado para a lixeira.'
            ]);
        }

        if ($acao === 'restaurar_curso') {
            $idCurso = (int) ($_POST['idcurso'] ?? 0);

            if ($idCurso <= 0) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Curso inválido.'
                ], 422);
            }

            $sql = "
                UPDATE new_sistema_cursos
                SET visivelsc = 0, visivelhomesc = 0
                WHERE codigocursos = :id
                LIMIT 1
            ";

            $stmt = $con->prepare($sql);
            $stmt->execute([':id' => $idCurso]);

            adminCursosJson([
                'ok' => true,
                'msg' => 'Curso restaurado como oculto.'
            ]);
        }

        if ($acao === 'salvar_ordem') {
            $ordem = $_POST['ordem'] ?? [];

            if (!is_array($ordem) || empty($ordem)) {
                adminCursosJson([
                    'ok' => false,
                    'msg' => 'Nenhum item recebido para ordenar.'
                ], 422);
            }

            $con->beginTransaction();

            $stmt = $con->prepare("
                UPDATE new_sistema_cursos
                SET ordemsc = :ordem
                WHERE codigocursos = :id
                LIMIT 1
            ");

            $posicao = 1;

            foreach ($ordem as $idItem) {
                $idItem = (int) $idItem;

                if ($idItem <= 0) {
                    continue;
                }

                $stmt->execute([
                    ':ordem' => $posicao,
                    ':id' => $idItem,
                ]);

                $posicao++;
            }

            $con->commit();

            adminCursosJson([
                'ok' => true,
                'msg' => 'Ordem dos cursos atualizada.'
            ]);
        }

        adminCursosJson([
            'ok' => false,
            'msg' => 'Ação inválida.'
        ], 400);

    } catch (Throwable $e) {
        if ($con->inTransaction()) {
            $con->rollBack();
        }

        adminCursosJson([
            'ok' => false,
            'msg' => 'Erro ao processar a solicitação.',
            'erro' => $e->getMessage()
        ], 500);
    }
}

/**
 * Filtro principal
 * 1 = visível
 * 0 = oculto
 * 2 = lixeira
 */
$statusFiltro = isset($_GET['status']) ? (int) $_GET['status'] : 1;

if (!in_array($statusFiltro, [0, 1, 2], true)) {
    $statusFiltro = 1;
}

$stmt = $con->prepare("
    SELECT
        c.codigocursos,
        c.nomecurso,
        c.pasta,
        c.diretorio,
        c.tipocursosc,
        c.visivelsc,
        c.visivelhomesc,
        c.ordemsc,
        c.datasc,
        c.horasc,

        (
            SELECT COUNT(*)
            FROM new_sistema_cursos_turmas t
            WHERE t.codcursost = c.codigocursos
              AND COALESCE(t.visivelst, 0) <> 2
        ) AS total_turmas,

        (
            SELECT COUNT(DISTINCT i.codigousuario)
            FROM new_sistema_inscricao_PJA i
            WHERE i.codcurso_ip = c.codigocursos
              AND COALESCE(i.visivel_ci, 0) <> 2
        ) AS total_alunos

    FROM new_sistema_cursos c
    WHERE c.visivelsc = :status
      AND c.tipocursosc IN (1, 2)

    ORDER BY c.ordemsc ASC, c.codigocursos DESC
");

$stmt->execute([
    ':status' => $statusFiltro
]);

$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalVisiveis = (int)$con
    ->query("SELECT COUNT(*) 
             FROM new_sistema_cursos 
             WHERE visivelsc = 1 
               AND tipocursosc IN (1, 2)")
    ->fetchColumn();

$totalOcultos = (int)$con
    ->query("SELECT COUNT(*) 
             FROM new_sistema_cursos 
             WHERE visivelsc = 0 
               AND tipocursosc IN (1, 2)")
    ->fetchColumn();

$totalExcluidos = (int)$con
    ->query("SELECT COUNT(*) 
             FROM new_sistema_cursos 
             WHERE visivelsc = 2 
               AND tipocursosc IN (1, 2)")
    ->fetchColumn();

$tituloFiltro = match ($statusFiltro) {
    0 => 'Cursos ocultos',
    2 => 'Cursos excluídos',
    default => 'Cursos visíveis',
};

?>
<!DOCTYPE html>
<html lang="pt-br" data-bs-theme="light">

<head>
    <meta charset="UTF-8">

    <title>Cursos | Admin Gerenciador de Conteúdo</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Segurança / SEO -->
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Página administrativa para gerenciamento de cursos do portal.">
    <meta name="author" content="Professor Eugênio">

    <!-- Compartilhamento em redes sociais -->
    <meta property="og:title" content="Cursos | Admin Gerenciador de Conteúdo">
    <meta property="og:description" content="Gerenciamento administrativo de cursos, turmas, alunos e visibilidade.">
    <meta property="og:type" content="website">
    <meta property="og:url"
        content="<?= adminCursosH((!empty($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')); ?>">
    <meta property="og:image" content="/img/logosite.png">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Cursos | Admin Gerenciador de Conteúdo">
    <meta name="twitter:description" content="Gerenciamento administrativo de cursos.">
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
            --admin-danger: #dc3545;
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

        .filter-tabs {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
        }

        .filter-tabs .btn {
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 6px 10px;
        }

        .search-box {
            position: relative;
            width: min(360px, 100%);
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
            font-size: 0.82rem;
        }

        .course-list-card {
            border: 1px solid var(--admin-border);
            border-radius: var(--admin-radius);
            background: var(--admin-card);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
        }

        .course-list-header {
            display: grid;
            grid-template-columns: 42px 1fr 320px;
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

        .course-list {
            display: grid;
            gap: 0;
        }

        .course-row {
            display: grid;
            grid-template-columns: 42px 1fr 320px;
            gap: 10px;
            align-items: center;
            padding: 9px 12px;
            border-bottom: 1px solid var(--admin-border);
            background: var(--admin-card);
            transition: 0.18s ease;
        }

        .course-row:last-child {
            border-bottom: 0;
        }

        .course-row:hover {
            background: var(--admin-card-soft);
        }

        .drag-handle {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            border: 1px solid var(--admin-border);
            background: var(--admin-card-soft);
            color: var(--admin-muted);
            cursor: grab;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        .sortable-ghost {
            opacity: 0.45;
            background: rgba(0, 187, 156, 0.12);
        }

        .course-main {
            min-width: 0;
        }

        .course-title-line {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .course-title {
            color: var(--admin-text);
            font-size: 0.92rem;
            font-weight: 850;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .course-title:hover {
            color: var(--admin-primary);
        }

        .course-id {
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

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 4px;
            color: var(--admin-muted);
            font-size: 0.72rem;
        }

        .course-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .course-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .btn-action {
            --bs-btn-padding-x: 0.45rem;
            --bs-btn-padding-y: 0.24rem;
            --bs-btn-font-size: 0.72rem;
            --bs-btn-border-radius: 999px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 800;
            line-height: 1.2;
        }

        .btn-icon-only {
            width: 28px;
            height: 28px;
            padding: 0;
            justify-content: center;
        }

        .empty-state {
            display: grid;
            place-items: center;
            gap: 7px;
            padding: 36px 14px;
            color: var(--admin-muted);
            text-align: center;
        }

        .empty-state i {
            color: var(--admin-primary);
            font-size: 2rem;
        }

        .modal-content {
            border: 1px solid var(--admin-border);
            border-radius: 18px;
            background: var(--admin-card);
            color: var(--admin-text);
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--admin-muted);
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            font-size: 0.86rem;
        }

        .form-check-label {
            font-size: 0.84rem;
            font-weight: 700;
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

            .course-list-header {
                display: none;
            }

            .course-row {
                grid-template-columns: 34px 1fr;
            }

            .course-actions {
                grid-column: 1 / -1;
                justify-content: flex-start;
                padding-left: 44px;
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

            .course-row {
                padding: 9px;
            }

            .course-title {
                font-size: 0.86rem;
            }

            .course-actions {
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
                    <i class="bi bi-mortarboard"></i>
                </div>

                <div>
                    <span>Admin / Gerenciador de Conteúdo</span>
                    <strong>Cursos</strong>
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
                <h1><?= adminCursosH($tituloFiltro); ?></h1>
                <p>
                    Organize os cursos do portal, controle a visibilidade, destaque na home,
                    edição rápida e ordem de exibição usando arrastar e soltar.
                </p>
            </div>

            <div class="hero-mini-card">
                <span>Total neste filtro</span>
                <strong id="contadorFiltro"><?= count($cursos); ?></strong>
                <span>Arraste os itens para alterar a ordem</span>
            </div>
        </section>

        <section class="toolbar-card">

            <div class="filter-tabs">
                <a href="cursos.php?status=1"
                    class="btn <?= $statusFiltro === 1 ? 'btn-success' : 'btn-outline-success'; ?>">
                    <i class="bi bi-eye"></i>
                    Visíveis
                    <span class="badge text-bg-light ms-1"><?= $totalVisiveis; ?></span>
                </a>

                <a href="cursos.php?status=0"
                    class="btn <?= $statusFiltro === 0 ? 'btn-secondary' : 'btn-outline-secondary'; ?>">
                    <i class="bi bi-eye-slash"></i>
                    Ocultos
                    <span class="badge text-bg-light ms-1"><?= $totalOcultos; ?></span>
                </a>

                <a href="cursos.php?status=2"
                    class="btn <?= $statusFiltro === 2 ? 'btn-danger' : 'btn-outline-danger'; ?>">
                    <i class="bi bi-trash"></i>
                    Excluídos
                    <span class="badge text-bg-light ms-1"><?= $totalExcluidos; ?></span>
                </a>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">

                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" id="pesquisaCurso"
                        placeholder="Pesquisar curso pelo nome...">
                </div>

                <button class="btn btn-primary rounded-pill fw-bold btn-sm" type="button" id="btnNovoCurso">
                    <i class="bi bi-plus-circle"></i>
                    Adicionar curso
                </button>

            </div>

        </section>

        <section class="course-list-card">

            <div class="course-list-header">
                <div>Ordem</div>
                <div>Curso</div>
                <div class="text-end">Ações</div>
            </div>

            <div class="course-list" id="listaCursos">

                <?php if (!empty($cursos)): ?>
                    <?php foreach ($cursos as $curso): ?>
                        <?php
                        $idCurso = (int) $curso['codigocursos'];
                        $nomeCurso = (string) ($curso['nomecurso'] ?? 'Curso sem nome');
                        $visivel = (int) ($curso['visivelsc'] ?? 0);
                        $visivelHome = (int) ($curso['visivelhomesc'] ?? 0);
                        $tipoCurso = (int) ($curso['tipocursosc'] ?? 0);

                        $idCriptografado = urlencode(encrypt_secure((string) $idCurso, 'e'));
                        $linkCurso = 'actionAdminCursos.php?tokemCurso=' . time() . '&id=' . $idCriptografado;
                        $tipoCurso = (int)($curso['tipocursosc'] ?? 0);
                        ?>

                        <article class="course-row <?= ((int)($curso['tipocursosc'] ?? 0) === 1) ? 'curso-tipo-1' : ''; ?>"
         data-id="<?= $idCurso; ?>"
                            data-name="<?= adminCursosH(mb_strtolower($nomeCurso, 'UTF-8')); ?>"
                            data-nome="<?= adminCursosH($nomeCurso); ?>" data-tipo="<?= $tipoCurso; ?>"
                            data-visivel="<?= $visivel; ?>" data-home="<?= $visivelHome; ?>">

                            <div class="drag-handle" title="Arrastar para ordenar">
                                <i class="bi bi-grip-vertical"></i>
                            </div>

                            <div class="course-main">

                                <div class="course-title-line">
                                    <a href="<?= adminCursosH($linkCurso); ?>" class="course-title">
                                       <?= $tipoCurso === 1 ? '<i style="color:orange" class="bi bi-star-fill"></i>' : ''; ?> <?= adminCursosH(adminCursosLimitar($nomeCurso, 95)); ?>
                                    </a>

                                    <span class="course-id">
                                        #<?= $idCurso; ?>
                                    </span>
                                </div>

                                <div class="course-meta">
                                    <span>
                                        <i class="bi bi-collection"></i>
                                        <?= (int) $curso['total_turmas']; ?> turma(s)
                                    </span>

                                    <span>
                                        <i class="bi bi-people"></i>
                                        <?= (int) $curso['total_alunos']; ?> aluno(s)
                                    </span>

                                    <span>
                                        <i class="bi bi-sort-numeric-down"></i>
                                        Ordem <?= (int) $curso['ordemsc']; ?>
                                    </span>

                                    <?php if (!empty($curso['pasta'])): ?>
                                        <span>
                                            <i class="bi bi-folder2"></i>
                                            <?= adminCursosH($curso['pasta']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <div class="course-actions">

                                <?php if ($statusFiltro === 2): ?>

                                    <button class="btn btn-outline-success btn-action btnRestaurar" type="button"
                                        data-id="<?= $idCurso; ?>">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                        Restaurar
                                    </button>

                                <?php else: ?>

                                    <button class="btn btn-outline-primary btn-action btn-icon-only btnEditar" type="button"
                                        title="Editar curso">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <button
                                        class="btn <?= $visivel === 1 ? 'btn-success' : 'btn-outline-secondary'; ?> btn-action btnToggleVisivel"
                                        type="button" data-id="<?= $idCurso; ?>" data-status="<?= $visivel === 1 ? 0 : 1; ?>">
                                        <i class="bi <?= $visivel === 1 ? 'bi-toggle-on' : 'bi-toggle-off'; ?>"></i>
                                        Visível
                                    </button>

                                    <button
                                        class="btn <?= $visivelHome === 1 ? 'btn-warning' : 'btn-outline-secondary'; ?> btn-action btnToggleHome"
                                        type="button" data-id="<?= $idCurso; ?>" data-status="<?= $visivelHome === 1 ? 0 : 1; ?>"
                                        <?= $visivel !== 1 ? 'disabled title="Ative o curso antes de exibir na home"' : ''; ?>>
                                        <i class="bi <?= $visivelHome === 1 ? 'bi-house-check-fill' : 'bi-house'; ?>"></i>
                                        Home
                                    </button>

                                    <button class="btn btn-outline-danger btn-action btn-icon-only btnExcluir" type="button"
                                        data-id="<?= $idCurso; ?>" title="Enviar para lixeira">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                <?php endif; ?>

                            </div>

                        </article>

                    <?php endforeach; ?>
                <?php else: ?>

                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>Nenhum curso encontrado.</strong>
                        <span>Use o botão “Adicionar curso” para cadastrar um novo curso.</span>
                    </div>

                <?php endif; ?>

            </div>

        </section>

    </div>

    <!-- Modal Curso -->
    <div class="modal fade" id="modalCurso" tabindex="-1" aria-labelledby="modalCursoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="formCurso">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="modalCursoLabel">
                        <i class="bi bi-mortarboard text-primary me-1"></i>
                        Novo curso
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="csrf" value="<?= adminCursosH($csrfToken); ?>">
                    <input type="hidden" name="acao" value="salvar_curso">
                    <input type="hidden" name="idcurso" id="idcurso" value="0">

                    <div class="mb-3">
                        <label for="nomecurso" class="form-label">Nome do curso</label>
                        <input type="text" class="form-control" name="nomecurso" id="nomecurso" maxlength="50" required
                            placeholder="Ex.: Excel Profissional">
                    </div>

                    <div class="mb-3">
                        <label for="tipocursosc" class="form-label">Tipo do curso</label>
                        <select class="form-select" name="tipocursosc" id="tipocursosc">
                            <option value="0">Não informado</option>
                            <option value="1">Comercial</option>
                            <option value="2">Institucional</option>
                            <option value="3">Conteúdo</option>
                        </select>
                    </div>

                    <div class="row g-2">

                        <div class="col-md-6">
                            <div class="form-check form-switch border rounded-4 p-3 ps-5">
                                <input class="form-check-input" type="checkbox" role="switch" name="visivelsc"
                                    id="visivelsc" value="1" checked>

                                <label class="form-check-label" for="visivelsc">
                                    Curso visível
                                </label>

                                <div class="small text-muted mt-1">
                                    Define `visivelsc = 1`.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-check form-switch border rounded-4 p-3 ps-5">
                                <input class="form-check-input" type="checkbox" role="switch" name="visivelhomesc"
                                    id="visivelhomesc" value="1">

                                <label class="form-check-label" for="visivelhomesc">
                                    Visível na home
                                </label>

                                <div class="small text-muted mt-1">
                                    Define `visivelhomesc = 1`.
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="alert alert-info border-0 rounded-4 small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Ao desativar o curso, a visibilidade na home também será desativada automaticamente.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary rounded-pill" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary rounded-pill fw-bold" id="btnSalvarCurso">
                        <i class="bi bi-check-circle me-1"></i>
                        Salvar curso
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
        <div id="toastAdmin" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMsg">
                    Mensagem
                </div>

                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Fechar"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

    <script>
        const csrfToken = '<?= adminCursosH($csrfToken); ?>';
        const modalCursoEl = document.getElementById('modalCurso');
        const modalCurso = new bootstrap.Modal(modalCursoEl);
        const formCurso = document.getElementById('formCurso');
        const listaCursos = document.getElementById('listaCursos');
        const pesquisaCurso = document.getElementById('pesquisaCurso');

        const toastEl = document.getElementById('toastAdmin');
        const toastMsg = document.getElementById('toastMsg');
        const toastAdmin = new bootstrap.Toast(toastEl, {
            delay: 2600
        });

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

        async function postAjax(dados) {
            const formData = new FormData();

            Object.keys(dados).forEach((key) => {
                const valor = dados[key];

                if (Array.isArray(valor)) {
                    valor.forEach((item) => {
                        formData.append(key + '[]', item);
                    });
                } else {
                    formData.append(key, valor);
                }
            });

            const resposta = await fetch('cursos.php?status=<?= (int) $statusFiltro; ?>', {
                method: 'POST',
                body: formData
            });

            return await resposta.json();
        }

        function recarregarDepois(msg, tipo = 'success') {
            mostrarToast(msg, tipo);

            setTimeout(() => {
                window.location.reload();
            }, 750);
        }

        document.getElementById('btnNovoCurso')?.addEventListener('click', () => {
            formCurso.reset();

            document.getElementById('modalCursoLabel').innerHTML = '<i class="bi bi-mortarboard text-primary me-1"></i> Novo curso';
            document.getElementById('idcurso').value = '0';
            document.getElementById('visivelsc').checked = true;
            document.getElementById('visivelhomesc').checked = false;
            document.getElementById('visivelhomesc').disabled = false;

            modalCurso.show();
        });

        document.querySelectorAll('.btnEditar').forEach((btn) => {
            btn.addEventListener('click', () => {
                const row = btn.closest('.course-row');

                document.getElementById('modalCursoLabel').innerHTML = '<i class="bi bi-pencil-square text-primary me-1"></i> Editar curso';

                document.getElementById('idcurso').value = row.dataset.id;
                document.getElementById('nomecurso').value = row.dataset.nome;
                document.getElementById('tipocursosc').value = row.dataset.tipo || '0';

                const visivel = row.dataset.visivel === '1';
                const home = row.dataset.home === '1';

                document.getElementById('visivelsc').checked = visivel;
                document.getElementById('visivelhomesc').checked = home;
                document.getElementById('visivelhomesc').disabled = !visivel;

                modalCurso.show();
            });
        });

        document.getElementById('visivelsc')?.addEventListener('change', function () {
            const home = document.getElementById('visivelhomesc');

            if (!this.checked) {
                home.checked = false;
                home.disabled = true;
            } else {
                home.disabled = false;
            }
        });

        formCurso?.addEventListener('submit', async (e) => {
            e.preventDefault();

            const btnSalvar = document.getElementById('btnSalvarCurso');
            btnSalvar.disabled = true;
            btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';

            const formData = new FormData(formCurso);

            if (!document.getElementById('visivelsc').checked) {
                formData.set('visivelsc', '0');
                formData.set('visivelhomesc', '0');
            } else {
                formData.set('visivelsc', '1');
                formData.set('visivelhomesc', document.getElementById('visivelhomesc').checked ? '1' : '0');
            }

            try {
                const resposta = await fetch('cursos.php?status=<?= (int) $statusFiltro; ?>', {
                    method: 'POST',
                    body: formData
                });

                const json = await resposta.json();

                if (!json.ok) {
                    mostrarToast(json.msg || 'Erro ao salvar curso.', 'danger');
                    return;
                }

                modalCurso.hide();
                recarregarDepois(json.msg || 'Curso salvo com sucesso.');

            } catch (error) {
                mostrarToast('Erro inesperado ao salvar curso.', 'danger');
            } finally {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = '<i class="bi bi-check-circle me-1"></i> Salvar curso';
            }
        });

        document.querySelectorAll('.btnToggleVisivel').forEach((btn) => {
            btn.addEventListener('click', async () => {
                try {
                    const json = await postAjax({
                        csrf: csrfToken,
                        acao: 'alterar_visivel',
                        idcurso: btn.dataset.id,
                        status: btn.dataset.status
                    });

                    if (!json.ok) {
                        mostrarToast(json.msg || 'Erro ao alterar visibilidade.', 'danger');
                        return;
                    }

                    recarregarDepois(json.msg);

                } catch (error) {
                    mostrarToast('Erro inesperado ao alterar visibilidade.', 'danger');
                }
            });
        });

        document.querySelectorAll('.btnToggleHome').forEach((btn) => {
            btn.addEventListener('click', async () => {
                try {
                    const json = await postAjax({
                        csrf: csrfToken,
                        acao: 'alterar_home',
                        idcurso: btn.dataset.id,
                        status: btn.dataset.status
                    });

                    if (!json.ok) {
                        mostrarToast(json.msg || 'Erro ao alterar home.', 'danger');
                        return;
                    }

                    recarregarDepois(json.msg);

                } catch (error) {
                    mostrarToast('Erro inesperado ao alterar home.', 'danger');
                }
            });
        });

        document.querySelectorAll('.btnExcluir').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const confirmar = confirm('Deseja enviar este curso para a lixeira?');

                if (!confirmar) {
                    return;
                }

                try {
                    const json = await postAjax({
                        csrf: csrfToken,
                        acao: 'excluir_curso',
                        idcurso: btn.dataset.id
                    });

                    if (!json.ok) {
                        mostrarToast(json.msg || 'Erro ao excluir curso.', 'danger');
                        return;
                    }

                    recarregarDepois(json.msg, 'warning');

                } catch (error) {
                    mostrarToast('Erro inesperado ao excluir curso.', 'danger');
                }
            });
        });

        document.querySelectorAll('.btnRestaurar').forEach((btn) => {
            btn.addEventListener('click', async () => {
                try {
                    const json = await postAjax({
                        csrf: csrfToken,
                        acao: 'restaurar_curso',
                        idcurso: btn.dataset.id
                    });

                    if (!json.ok) {
                        mostrarToast(json.msg || 'Erro ao restaurar curso.', 'danger');
                        return;
                    }

                    recarregarDepois(json.msg);

                } catch (error) {
                    mostrarToast('Erro inesperado ao restaurar curso.', 'danger');
                }
            });
        });

        pesquisaCurso?.addEventListener('input', () => {
            const termo = pesquisaCurso.value.trim().toLowerCase();
            let totalVisivel = 0;

            document.querySelectorAll('.course-row').forEach((row) => {
                const nome = row.dataset.name || '';
                const mostrar = nome.includes(termo);

                row.style.display = mostrar ? 'grid' : 'none';

                if (mostrar) {
                    totalVisivel++;
                }
            });

            const contadorFiltro = document.getElementById('contadorFiltro');

            if (contadorFiltro) {
                contadorFiltro.textContent = totalVisivel;
            }
        });

        if (listaCursos && typeof Sortable !== 'undefined') {
            new Sortable(listaCursos, {
                handle: '.drag-handle',
                animation: 160,
                ghostClass: 'sortable-ghost',

                onEnd: async () => {
                    const ordem = Array.from(document.querySelectorAll('.course-row'))
                        .filter((row) => row.style.display !== 'none')
                        .map((row) => row.dataset.id);

                    if (ordem.length === 0) {
                        return;
                    }

                    try {
                        const json = await postAjax({
                            csrf: csrfToken,
                            acao: 'salvar_ordem',
                            ordem: ordem
                        });

                        if (!json.ok) {
                            mostrarToast(json.msg || 'Erro ao salvar ordem.', 'danger');
                            return;
                        }

                        mostrarToast(json.msg || 'Ordem atualizada.');

                    } catch (error) {
                        mostrarToast('Erro inesperado ao salvar ordem.', 'danger');
                    }
                }
            });
        }

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