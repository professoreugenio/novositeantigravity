<?php

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));

define('COMPONENTES_ROOT', APP_ROOT . '/componentes');


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

if (empty($_SESSION['admin_logado'])) {
    jsonExit(false, 'Somente o professor pode avaliar.');
}

$id = (int)($_POST['id'] ?? 0);
$avaliacao = (int)($_POST['avaliacao'] ?? 0);

if ($id <= 0 || $avaliacao < 1 || $avaliacao > 5) {
    jsonExit(false, 'Avaliação inválida.');
}

try {
    $stmt = $con->prepare("UPDATE a_curso_AtividadeAnexos
                           SET avaliacaoAA = :avaliacao
                           WHERE codigoatividadeanexos = :id");
    $stmt->bindValue(':avaliacao', $avaliacao, PDO::PARAM_INT);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    jsonExit(true, 'Avaliação salva com sucesso.');
} catch (Throwable $e) {
    jsonExit(false, 'Erro ao salvar avaliação.');
}
