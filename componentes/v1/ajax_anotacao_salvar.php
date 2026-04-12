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
$textosa    = (string)($_POST['textosa'] ?? '');

if ($idpublicsa <= 0 || $idusuario <= 0) {
    echo json_encode([
        'status' => false,
        'msg' => 'Dados inválidos.'
    ]);
    exit;
}

try {
    $textosa = trim($textosa);
    $textoLimpo = trim(strip_tags($textosa));

    // Se o conteúdo estiver vazio, remove a anotação
    if ($textoLimpo === '') {
        $stmtDelete = $con->prepare("
            DELETE FROM new_sistema_anotacoes
            WHERE idpublicsa = :idpublicsa
              AND idusuariosa = :idusuario
        ");
        $stmtDelete->bindValue(':idpublicsa', $idpublicsa, PDO::PARAM_INT);
        $stmtDelete->bindValue(':idusuario', $idusuario, PDO::PARAM_INT);
        $stmtDelete->execute();

        echo json_encode([
            'status' => true,
            'tem_anotacao' => 0,
            'msg' => 'Anotação vazia removida.'
        ]);
        exit;
    }

    // Limita tamanho por segurança
    $textosa = mb_substr($textosa, 0, 65000, 'UTF-8');

    // Verifica se já existe anotação para esta publicação e usuário
    $stmtCheck = $con->prepare("
        SELECT codigoanotacoes
        FROM new_sistema_anotacoes
        WHERE idpublicsa = :idpublicsa
          AND idusuariosa = :idusuario
        LIMIT 1
    ");
    $stmtCheck->bindValue(':idpublicsa', $idpublicsa, PDO::PARAM_INT);
    $stmtCheck->bindValue(':idusuario', $idusuario, PDO::PARAM_INT);
    $stmtCheck->execute();

    $anotacaoExistente = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($anotacaoExistente) {
        // Já existe: faz UPDATE
        $stmtUpdate = $con->prepare("
            UPDATE new_sistema_anotacoes
            SET textosa = :textosa,
                datasa = CURDATE(),
                horasa = CURTIME()
            WHERE idpublicsa = :idpublicsa
              AND idusuariosa = :idusuario
        ");
        $stmtUpdate->bindValue(':textosa', $textosa, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':idpublicsa', $idpublicsa, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':idusuario', $idusuario, PDO::PARAM_INT);
        $stmtUpdate->execute();

        echo json_encode([
            'status' => true,
            'tem_anotacao' => 1,
            'acao' => 'update',
            'msg' => 'Anotação atualizada com sucesso.'
        ]);
        exit;
    } else {
        // Não existe: faz INSERT
        $stmtInsert = $con->prepare("
            INSERT INTO new_sistema_anotacoes
                (idpublicsa, idusuariosa, textosa, datasa, horasa)
            VALUES
                (:idpublicsa, :idusuario, :textosa, CURDATE(), CURTIME())
        ");
        $stmtInsert->bindValue(':idpublicsa', $idpublicsa, PDO::PARAM_INT);
        $stmtInsert->bindValue(':idusuario', $idusuario, PDO::PARAM_INT);
        $stmtInsert->bindValue(':textosa', $textosa, PDO::PARAM_STR);
        $stmtInsert->execute();

        echo json_encode([
            'status' => true,
            'tem_anotacao' => 1,
            'acao' => 'insert',
            'msg' => 'Anotação criada com sucesso.'
        ]);
        exit;
    }
} catch (Throwable $e) {
    echo json_encode([
        'status' => false,
        'msg' => 'Erro ao salvar anotação.'
    ]);
    exit;
}
