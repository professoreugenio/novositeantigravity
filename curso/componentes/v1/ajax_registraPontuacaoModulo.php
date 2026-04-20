<?php

declare(strict_types=1);
define('BASEPATH', true);
define('APP_ROOT', dirname(__DIR__, 4));
define('COMPONENTES_ROOT', APP_ROOT . '/componentes');

require_once COMPONENTES_ROOT . '/v1/class.conexao.php';
require_once COMPONENTES_ROOT . '/v1/autenticacao.php';

header('Content-Type: application/json; charset=utf-8');

function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $con = config::connect();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse([
            'success' => false,
            'inserted' => false,
            'message' => 'Método inválido.'
        ], 405);
    }

    $codigoUser = 0;
    $idCurso    = 0;
    $idTurma    = '';

    if (!empty($_SESSION['startusuario'])) {
        $Decdadosuser = encrypt_secure($_SESSION['startusuario'], 'd');

        if (is_string($Decdadosuser) && strpos($Decdadosuser, '&') !== false) {
            $dadosArray = explode('&', $Decdadosuser);
            $codigoUser = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
        }
    }

    if (!empty($_SESSION['dadoscurso'])) {
        $Decdadoscurso = encrypt_secure($_SESSION['dadoscurso'], 'd');

        if (is_string($Decdadoscurso) && strpos($Decdadoscurso, '&') !== false) {
            $dadosArray = explode('&', $Decdadoscurso);
            $idCurso = isset($dadosArray[0]) ? (int)$dadosArray[0] : 0;
            $idTurma = isset($dadosArray[1]) ? trim((string)$dadosArray[1]) : '';
        }
    }

    if ($codigoUser <= 0 || $idCurso <= 0 || $idTurma === '') {
        jsonResponse([
            'success' => false,
            'inserted' => false,
            'message' => 'Dados de usuário, curso ou turma inválidos.'
        ], 400);
    }

    $tipoPontuacao = 1;
    $pontos        = 50;
    $dataHoje      = date('Y-m-d');
    $horaAgora     = date('H:i:s');

    $sqlInsert = "
        INSERT INTO a_curso_pontuacao
        (
            idusuario_cp,
            idcurso_cp,
            idturma_cp,
            codigoitem_cp,
            pontos_cp,
            data_cp,
            hora_cp
        )
        SELECT
            :idusuario,
            :idcurso,
            :idturma,
            :tipo,
            :pontos,
            :datahoje,
            :horaagora
        FROM DUAL
        WHERE NOT EXISTS (
            SELECT 1
            FROM a_curso_pontuacao
            WHERE idusuario_cp = :idusuario_check
              AND idcurso_cp   = :idcurso_check
              AND idturma_cp   = :idturma_check
              AND codigoitem_cp = :tipo_check
              AND data_cp      = :datahoje_check
        )
    ";

    $stmt = $con->prepare($sqlInsert);
    $stmt->bindValue(':idusuario', $codigoUser, PDO::PARAM_INT);
    $stmt->bindValue(':idcurso', $idCurso, PDO::PARAM_INT);
    $stmt->bindValue(':idturma', $idTurma, PDO::PARAM_STR);
    $stmt->bindValue(':tipo', $tipoPontuacao, PDO::PARAM_INT);
    $stmt->bindValue(':pontos', $pontos, PDO::PARAM_INT);
    $stmt->bindValue(':datahoje', $dataHoje, PDO::PARAM_STR);
    $stmt->bindValue(':horaagora', $horaAgora, PDO::PARAM_STR);

    $stmt->bindValue(':idusuario_check', $codigoUser, PDO::PARAM_INT);
    $stmt->bindValue(':idcurso_check', $idCurso, PDO::PARAM_INT);
    $stmt->bindValue(':idturma_check', $idTurma, PDO::PARAM_STR);
    $stmt->bindValue(':tipo_check', $tipoPontuacao, PDO::PARAM_INT);
    $stmt->bindValue(':datahoje_check', $dataHoje, PDO::PARAM_STR);

    $stmt->execute();

    $inserted = ($stmt->rowCount() > 0);

    if ($inserted) {
        jsonResponse([
            'success' => true,
            'inserted' => true,
            'message' => 'Você ganhou 50 pontos!',
            'tipo' => $tipoPontuacao,
            'pontos' => $pontos
        ]);
    }

    jsonResponse([
        'success' => true,
        'inserted' => false,
        'message' => 'Pontuação do dia já registrada.',
        'tipo' => $tipoPontuacao,
        'pontos' => 0
    ]);
} catch (Throwable $e) {
    jsonResponse([
        'success' => false,
        'inserted' => false,
        'message' => 'Erro ao registrar pontuação.',
        'erro' => $e->getMessage()
    ], 500);
}
