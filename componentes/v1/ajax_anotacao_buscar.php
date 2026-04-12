<?php

declare(strict_types=1);

define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 3));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'msg' => 'Método inválido.'
    ]);
    exit;
}

$idpublicsa = (int)($_POST['idpublicsa'] ?? 0);
$idusuario  = (int)($codigoUser ?? 0);

if ($idpublicsa <= 0 || $idusuario <= 0) {
    echo json_encode([
        'status' => false,
        'msg' => 'Dados inválidos.'
    ]);
    exit;
}

try {
    $stmt = $con->prepare("
        SELECT codigoanotacoes, textosa, datasa, horasa
        FROM new_sistema_anotacoes
        WHERE idpublicsa = :idpublicsa
          AND idusuariosa = :idusuario
        LIMIT 1
    ");
    $stmt->bindValue(':idpublicsa', $idpublicsa, PDO::PARAM_INT);
    $stmt->bindValue(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmt->execute();

    $anotacao = $stmt->fetch(PDO::FETCH_ASSOC);

    $texto = (string)($anotacao['textosa'] ?? '');
    $temAnotacao = trim(strip_tags($texto)) !== '' ? 1 : 0;

    echo json_encode([
        'status' => true,
        'texto' => $texto,
        'codigoanotacoes' => (int)($anotacao['codigoanotacoes'] ?? 0),
        'tem_anotacao' => $temAnotacao,
        'msg' => 'Anotação carregada.'
    ]);
    exit;
} catch (Throwable $e) {
    echo json_encode([
        'status' => false,
        'msg' => 'Erro ao buscar anotação.'
    ]);
    exit;
}
