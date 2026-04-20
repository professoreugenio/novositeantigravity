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

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';


/** @var PDO $con */

function jsonExit(bool $status, string $msg): void
{
    echo json_encode(['status' => $status, 'msg' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonExit(false, 'Método inválido.');
}

$idFile = (int)($_POST['idfile'] ?? 0);
$idAluno = (int)($_POST['idaluno'] ?? 0);
$texto = trim((string)($_POST['texto'] ?? ''));

$idUsuarioLogado = (int)($codigocadastro ?? $codigoUsuario ?? $codigousuario ?? $_SESSION['codigousuario'] ?? 0);
$isProfessor = !empty($_SESSION['admin_logado']);

if ($idFile <= 0 || $texto === '') {
    jsonExit(false, 'Dados inválidos para comentário.');
}

if (mb_strlen($texto, 'UTF-8') > 300) {
    jsonExit(false, 'O comentário pode ter até 300 caracteres.');
}

$idDe = $idUsuarioLogado;
$idPara = $isProfessor ? $idAluno : 0;

try {
    $stmt = $con->prepare("INSERT INTO a_curso_AtividadeComentario
        (
            idfileAnexoAAC,
            iduserdeAAC,
            iduserparaAAC,
            textoAAC,
            lidaAAC,
            dataAAC,
            horaAAC
        ) VALUES (
            :idfile,
            :idde,
            :idpara,
            :texto,
            0,
            :dataaac,
            :horaaac
        )");

    $stmt->bindValue(':idfile', $idFile, PDO::PARAM_INT);
    $stmt->bindValue(':idde', $idDe, PDO::PARAM_INT);
    $stmt->bindValue(':idpara', $idPara, PDO::PARAM_INT);
    $stmt->bindValue(':texto', $texto, PDO::PARAM_STR);
    $stmt->bindValue(':dataaac', date('Y-m-d'), PDO::PARAM_STR);
    $stmt->bindValue(':horaaac', date('H:i:s'), PDO::PARAM_STR);
    $stmt->execute();

    jsonExit(true, 'Comentário enviado com sucesso.');
} catch (Throwable $e) {
    jsonExit(false, 'Erro ao salvar comentário.');
}
