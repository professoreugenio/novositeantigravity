<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));
define('APP_ROOT_LOCAL', dirname(__DIR__, 2));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');
define('SITE_PUBLIC_ROOT', dirname(__DIR__, 3));

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

date_default_timezone_set('America/Fortaleza');
header('Content-Type: application/json; charset=utf-8');

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



/** @var PDO $con */

function jsonExit(bool $status, string $msg): void
{
    echo json_encode(['status' => $status, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonExit(false, 'Método inválido.');
}

$id = (int)($_POST['id'] ?? 0);
$idUsuarioLogado = (int)($codigocadastro ?? $codigoUsuario ?? $codigousuario ?? $_SESSION['codigousuario'] ?? 0);
$isProfessor = !empty($_SESSION['admin_logado']) || !empty($_SESSION['usuario_logado']);

if ($id <= 0) {
    jsonExit(false, 'Registro inválido.');
}

$stmt = $con->prepare("SELECT * FROM a_curso_AtividadeAnexos WHERE codigoatividadeanexos = :id LIMIT 1");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    jsonExit(false, 'Print não localizado.');
}

$dono = (int)($item['idalulnoAA'] ?? 0);
if (!$isProfessor && $idUsuarioLogado !== $dono) {
    jsonExit(false, 'Você não tem permissão para excluir este print.');
}

$arquivo = SITE_PUBLIC_ROOT . '/fotos/atividades/' . $item['pastaAA'] . '/' . $item['fotoAA'];
$pasta = SITE_PUBLIC_ROOT . '/fotos/atividades/' . $item['pastaAA'];

try {
    $con->beginTransaction();

    $stmtDelC = $con->prepare("DELETE FROM a_curso_AtividadeComentario WHERE idfileAnexoAAC = :idfile");
    $stmtDelC->bindValue(':idfile', $id, PDO::PARAM_INT);
    $stmtDelC->execute();

    $stmtDel = $con->prepare("DELETE FROM a_curso_AtividadeAnexos WHERE codigoatividadeanexos = :id");
    $stmtDel->bindValue(':id', $id, PDO::PARAM_INT);
    $stmtDel->execute();

    $con->commit();

    if (is_file($arquivo)) {
        @unlink($arquivo);
    }

    if (is_dir($pasta)) {
        $files = array_diff(scandir($pasta), ['.', '..']);
        if (empty($files)) {
            @rmdir($pasta);
        }
    }

    jsonExit(true, 'Print excluído com sucesso.');
} catch (Throwable $e) {
    if ($con->inTransaction()) {
        $con->rollBack();
    }
    jsonExit(false, 'Erro ao excluir o print.');
}
